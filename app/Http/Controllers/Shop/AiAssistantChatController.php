<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\AiAssistant\AiAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AiAssistantChatController extends Controller
{
    public function __construct(
        protected AiAssistantService $assistantService
    ) {}

    /**
     * Get public widget configuration.
     */
    public function config(): JsonResponse
    {
        $config = $this->assistantService->getConfig();

        return response()->json([
            'enabled'         => $config['status'],
            'title'           => 'Assistant', // strictly "Assistant"
            'welcome_message' => $config['welcome_message'],
            'placeholder'     => $config['placeholder'],
            'position'        => $config['position'],
            'theme_color'     => $config['theme_color'],
            'auto_open'       => $config['auto_open'],
            'faqs'            => $config['faqs'],
        ]);
    }

    /**
     * Handle incoming chat message.
     */
    public function chat(Request $request): JsonResponse
    {
        $config = $this->assistantService->getConfig();

        if (! $config['status']) {
            return response()->json([
                'success' => false,
                'reply'   => 'The wholesale assistant is currently offline. Please contact our support team.',
            ], 503);
        }

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        // Rate limiting per IP / Session
        $ip = $request->ip();
        $rateLimitKey = 'ai_assistant_chat:' . $ip;
        $maxAttempts = $config['rate_limit_per_minute'] ?: 20;

        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return response()->json([
                'success' => false,
                'reply'   => "Too many inquiries sent. Please wait {$seconds} seconds before sending another message.",
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, 60);

        $userId = auth()->guard('customer')->id();
        $sessionId = session()->getId();

        $response = $this->assistantService->ask($request->input('message'), $userId, $sessionId);

        return response()->json($response);
    }
}

