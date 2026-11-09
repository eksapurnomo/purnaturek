<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AiAssistant\AiAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\MagicAI\AiProvider;

class AiAssistantController extends Controller
{
    public function __construct(
        protected AiAssistantService $assistantService,
        protected CoreConfigRepository $coreConfigRepository
    ) {}

    /**
     * Enforce administrator permission for AI Assistant configuration.
     */
    protected function authorizePermission(): void
    {
        abort_if(! bouncer()->hasPermission('configuration.ai_assistant'), 403, 'Unauthorized access to AI Assistant configuration.');
    }

    /**
     * Display consolidated AI Assistant configuration page.
     */
    public function index(): View
    {
        $this->authorizePermission();

        $config = $this->assistantService->getConfig();

        // Get text providers and model options from Magic AI registry
        $providers = [];
        foreach (AiProvider::textProviders() as $p) {
            $status = $this->assistantService->getMagicAiCredentialStatus($p->value);
            $providers[] = [
                'value'               => $p->value,
                'label'               => AiProvider::label($p->value),
                'has_magic_ai_key'    => $status['has_credentials'],
                'masked_magic_ai_key' => $status['configured_val'],
            ];
        }

        $allModels = AiProvider::textModelOptions();

        // Retrieve recent conversations logged for ai_assistant
        $logs = [];
        try {
            $conversations = DB::table('agent_conversations')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('agent_conversation_messages')
                        ->whereColumn('agent_conversation_messages.conversation_id', 'agent_conversations.id')
                        ->where('agent_conversation_messages.agent', 'ai_assistant');
                })
                ->orderByDesc('updated_at')
                ->limit(30)
                ->get();

            foreach ($conversations as $c) {
                $messages = DB::table('agent_conversation_messages')
                    ->where('conversation_id', $c->id)
                    ->orderBy('created_at')
                    ->get();

                $firstUserMsg = $messages->firstWhere('role', 'user');
                $lastAssistantMsg = $messages->where('role', 'assistant')->last();

                $userName = 'Guest Visitor';
                if ($c->user_id) {
                    $customer = DB::table('customers')->where('id', $c->user_id)->first();
                    if ($customer) {
                        $userName = $customer->first_name . ' ' . $customer->last_name . ' (' . $customer->email . ')';
                    }
                }

                $usage = $lastAssistantMsg && $lastAssistantMsg->usage ? json_decode($lastAssistantMsg->usage, true) : [];

                $logs[] = [
                    'id'                 => $c->id,
                    'title'              => $c->title,
                    'user'               => $userName,
                    'user_id'            => $c->user_id,
                    'inquiry'            => $firstUserMsg ? $firstUserMsg->content : '',
                    'reply'              => $lastAssistantMsg ? $lastAssistantMsg->content : '',
                    'model'              => $usage['model'] ?? $config['model'],
                    'gated'              => $usage['gated'] ?? false,
                    'created_at'         => $c->created_at,
                    'messages_count'     => $messages->count(),
                ];
            }
        } catch (\Throwable $e) {
            // Silently fallback if table query fails
        }

        $widgetUrl = url('/ai-assistant/widget.js');
        $apiBaseUrl = url('/');

        $embedSnippet = '<script src="' . $widgetUrl . '" data-api-base="' . $apiBaseUrl . '" async></script>';
        $reactSnippet = "// React / Vite landing page (e.g., at purnaturekbulk.com)\n"
            . "useEffect(() => {\n"
            . "  const script = document.createElement('script');\n"
            . "  script.src = '{$widgetUrl}';\n"
            . "  script.dataset.apiBase = '{$apiBaseUrl}';\n"
            . "  script.async = true;\n"
            . "  document.body.appendChild(script);\n"
            . "  return () => {\n"
            . "    script.remove();\n"
            . "  };\n"
            . "}, []);";

        return view('admin.ai_assistant.index', compact('config', 'providers', 'allModels', 'logs', 'embedSnippet', 'reactSnippet'));
    }

    /**
     * Save consolidated AI Assistant settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $this->authorizePermission();

        $validated = $request->validate([
            'status'                      => 'nullable|boolean',
            'provider'                    => 'required|string',
            'model'                       => 'required|string',
            'use_magic_ai_credentials'    => 'nullable|boolean',
            'api_key'                     => 'nullable|string',
            'temperature'                 => 'nullable|numeric|min:0|max:1',
            'system_prompt'               => 'required|string',
            'custom_context'              => 'nullable|string',
            'include_catalog'             => 'nullable|boolean',
            'include_company_supply'      => 'nullable|boolean',
            'include_partnership_info'    => 'nullable|boolean',
            'welcome_message'             => 'required|string',
            'placeholder'                 => 'required|string',
            'position'                    => 'required|in:bottom-right,bottom-left',
            'theme_color'                 => 'required|string',
            'auto_open'                   => 'nullable|boolean',
            'faqs'                        => 'nullable|array',
            'rate_limit_per_minute'       => 'required|integer|min:1|max:120',
            'require_auth'                => 'nullable|boolean',
            'gate_private_prices'         => 'nullable|boolean',
        ]);

        $settings = [
            'ai_assistant.general.status'                      => ! empty($validated['status']),
            'ai_assistant.general.provider'                    => $validated['provider'],
            'ai_assistant.general.model'                       => $validated['model'],
            'ai_assistant.general.use_magic_ai_credentials'    => ! empty($validated['use_magic_ai_credentials']),
            'ai_assistant.general.api_key'                     => $validated['api_key'] ?? '',
            'ai_assistant.general.temperature'                 => (float) ($validated['temperature'] ?? 0.3),
            'ai_assistant.knowledge.system_prompt'             => $validated['system_prompt'],
            'ai_assistant.knowledge.custom_context'            => $validated['custom_context'] ?? '',
            'ai_assistant.knowledge.include_catalog'           => ! empty($validated['include_catalog']),
            'ai_assistant.knowledge.include_company_supply'    => ! empty($validated['include_company_supply']),
            'ai_assistant.knowledge.include_partnership_info'  => ! empty($validated['include_partnership_info']),
            'ai_assistant.widget.welcome_message'             => $validated['welcome_message'],
            'ai_assistant.widget.placeholder'                 => $validated['placeholder'],
            'ai_assistant.widget.position'                    => $validated['position'],
            'ai_assistant.widget.theme_color'                 => $validated['theme_color'],
            'ai_assistant.widget.auto_open'                   => ! empty($validated['auto_open']),
            'ai_assistant.faqs.items'                         => json_encode(array_values(array_filter($validated['faqs'] ?? []))),
            'ai_assistant.security.rate_limit_per_minute'      => (int) $validated['rate_limit_per_minute'],
            'ai_assistant.security.require_auth'               => ! empty($validated['require_auth']),
            'ai_assistant.security.gate_private_prices'        => ! empty($validated['gate_private_prices']),
        ];

        foreach ($settings as $code => $value) {
            $this->coreConfigRepository->updateOrCreate(
                [
                    'code'         => $code,
                    'channel_code' => core()->getRequestedChannelCode(),
                    'locale_code'  => core()->getRequestedLocaleCode(),
                ],
                [
                    'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                ]
            );
        }

        session()->flash('success', 'AI Assistant configuration saved successfully.');

        return redirect()->route('admin.ai_assistant.index');
    }

    /**
     * Test prompt in real-time console.
     */
    public function testPrompt(Request $request): JsonResponse
    {
        $this->authorizePermission();

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $start = microtime(true);
        $result = $this->assistantService->ask($request->input('message'), auth()->guard('admin')->id(), 'admin-test-session');
        $duration = round((microtime(true) - $start) * 1000, 1);

        return response()->json([
            'success'     => $result['success'],
            'reply'       => $result['reply'],
            'gated'       => $result['gated'] ?? false,
            'duration_ms' => $duration,
        ]);
    }

    /**
     * Delete a single conversation log.
     */
    public function deleteLog(string $id): RedirectResponse
    {
        $this->authorizePermission();

        DB::table('agent_conversation_messages')->where('conversation_id', $id)->delete();
        DB::table('agent_conversations')->where('id', $id)->delete();

        session()->flash('success', 'Conversation log deleted.');

        return redirect()->route('admin.ai_assistant.index');
    }

    /**
     * Purge all AI assistant conversation logs.
     */
    public function clearLogs(): RedirectResponse
    {
        $this->authorizePermission();

        $ids = DB::table('agent_conversation_messages')
            ->where('agent', 'ai_assistant')
            ->pluck('conversation_id');

        DB::table('agent_conversation_messages')->whereIn('conversation_id', $ids)->delete();
        DB::table('agent_conversations')->whereIn('id', $ids)->delete();

        session()->flash('success', 'All AI assistant conversation logs have been cleared.');

        return redirect()->route('admin.ai_assistant.index');
    }
}

