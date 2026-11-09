<?php

namespace App\Services\AiAssistant;

use App\Models\CompanySupplyPage;
use App\Models\WholesalePartnershipPage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\MagicAI\AiProvider;
use Webkul\Product\Repositories\ProductRepository;

class AiAssistantService
{
    /**
     * Default approved FAQs / prompt chips.
     */
    public const DEFAULT_FAQS = [
        [
            'question' => 'What are your Minimum Order Quantities (MOQs)?',
            'category' => 'Ordering',
        ],
        [
            'question' => 'How do I request a Certificate of Analysis (COA)?',
            'category' => 'Quality & Compliance',
        ],
        [
            'question' => 'How does wholesale tier pricing work?',
            'category' => 'Pricing & Tiers',
        ],
        [
            'question' => 'What botanical extracts and active ingredients do you offer?',
            'category' => 'Products',
        ],
        [
            'question' => 'How do I apply for wholesale buyer verification?',
            'category' => 'Account',
        ],
    ];

    /**
     * Default system persona prompt.
     */
    public const DEFAULT_SYSTEM_PROMPT = <<<EOT
You are the official wholesale AI Assistant for PurNatureK Bulk & Wholesale.
PurNatureK is a premier B2B supplier of high-purity botanical extracts, standardized herbal ingredients, and bulk raw actives for commercial formulators, wellness brands, nutraceutical manufacturers, and specialty retailers.

Guidelines:
1. Tone: Professional, authoritative, welcoming, concise, and B2B-oriented.
2. Knowledge scope: Botanical specifications, standard pack sizes, MOQs (typical standard batch starts at 25 kg), documentation (COA, MSDS, Spec Sheets), and wholesale account verification.
3. Private Catalog Protection: Wholesale pricing, tiered volume discounts, and custom supply agreements are strictly reserved for verified business members. If a visitor asks for specific prices without being a verified buyer, inform them courteously that pricing is gated and invite them to Sign In or complete Wholesale Registration.
4. Compliance: Do not make unverified medicinal or disease-treatment claims. Focus on quality, purity, assay percentages, and supply reliability.
EOT;

    /**
     * Default welcome greeting.
     */
    public const DEFAULT_WELCOME_MESSAGE = 'Welcome to PurNatureK Bulk & Wholesale. I can assist you with botanical ingredient specifications, bulk order MOQs, quality compliance documents, and buyer verification. How can I help your business today?';

    /**
     * Default input placeholder.
     */
    public const DEFAULT_PLACEHOLDER = 'Ask about botanical extracts, specifications, MOQs...';

    /**
     * Get consolidated assistant configuration.
     */
    public function getConfig(): array
    {
        $enabled = (bool) core()->getConfigData('ai_assistant.general.status');
        $provider = core()->getConfigData('ai_assistant.general.provider') ?: 'openai';
        $model = core()->getConfigData('ai_assistant.general.model') ?: 'gpt-4o-mini';
        $useMagicAiCredentials = core()->getConfigData('ai_assistant.general.use_magic_ai_credentials');
        if ($useMagicAiCredentials === null) {
            $useMagicAiCredentials = true;
        } else {
            $useMagicAiCredentials = (bool) $useMagicAiCredentials;
        }

        $apiKey = core()->getConfigData('ai_assistant.general.api_key') ?: '';
        $temperature = (float) (core()->getConfigData('ai_assistant.general.temperature') ?? 0.3);

        $systemPrompt = core()->getConfigData('ai_assistant.knowledge.system_prompt') ?: self::DEFAULT_SYSTEM_PROMPT;
        $customContext = core()->getConfigData('ai_assistant.knowledge.custom_context') ?: '';
        $includeCatalog = (bool) (core()->getConfigData('ai_assistant.knowledge.include_catalog') ?? true);
        $includeCompanySupply = (bool) (core()->getConfigData('ai_assistant.knowledge.include_company_supply') ?? true);
        $includePartnershipInfo = (bool) (core()->getConfigData('ai_assistant.knowledge.include_partnership_info') ?? true);

        $welcomeMessage = core()->getConfigData('ai_assistant.widget.welcome_message') ?: self::DEFAULT_WELCOME_MESSAGE;
        $placeholder = core()->getConfigData('ai_assistant.widget.placeholder') ?: self::DEFAULT_PLACEHOLDER;
        $position = core()->getConfigData('ai_assistant.widget.position') ?: 'bottom-right';
        $themeColor = core()->getConfigData('ai_assistant.widget.theme_color') ?: '#0f4a3e';
        $autoOpen = (bool) (core()->getConfigData('ai_assistant.widget.auto_open') ?? false);

        $rawFaqs = core()->getConfigData('ai_assistant.faqs.items');
        $faqs = is_string($rawFaqs) ? json_decode($rawFaqs, true) : (is_array($rawFaqs) ? $rawFaqs : self::DEFAULT_FAQS);
        if (empty($faqs)) {
            $faqs = self::DEFAULT_FAQS;
        }

        $rateLimit = (int) (core()->getConfigData('ai_assistant.security.rate_limit_per_minute') ?: 20);
        $requireAuth = (bool) (core()->getConfigData('ai_assistant.security.require_auth') ?? false);
        $gatePrivatePrices = (bool) (core()->getConfigData('ai_assistant.security.gate_private_prices') ?? true);

        return [
            'status'                      => $enabled,
            'provider'                    => $provider,
            'model'                       => $model,
            'use_magic_ai_credentials'    => $useMagicAiCredentials,
            'api_key'                     => $apiKey,
            'temperature'                 => $temperature,
            'system_prompt'               => $systemPrompt,
            'custom_context'              => $customContext,
            'include_catalog'             => $includeCatalog,
            'include_company_supply'      => $includeCompanySupply,
            'include_partnership_info'    => $includePartnershipInfo,
            'welcome_message'             => $welcomeMessage,
            'placeholder'                 => $placeholder,
            'position'                    => $position,
            'theme_color'                 => $themeColor,
            'auto_open'                   => $autoOpen,
            'faqs'                        => $faqs,
            'rate_limit_per_minute'       => $rateLimit,
            'require_auth'                => $requireAuth,
            'gate_private_prices'         => $gatePrivatePrices,
        ];
    }

    /**
     * Check if Magic AI credentials are configured for the specified provider.
     */
    public function getMagicAiCredentialStatus(string $provider): array
    {
        $key = core()->getConfigData("magic_ai.providers.{$provider}.api_key");
        $url = core()->getConfigData("magic_ai.providers.{$provider}.url");

        if ($provider === 'ollama') {
            return [
                'has_credentials' => ! empty($url),
                'configured_val'  => $url ? $url : null,
            ];
        }

        return [
            'has_credentials' => ! empty($key),
            'configured_val'  => $key ? substr($key, 0, 4) . '...' . substr($key, -4) : null,
        ];
    }

    /**
     * Build the active wholesale knowledge context.
     */
    public function buildKnowledgeContext(bool $isVerifiedBuyer): string
    {
        $config = $this->getConfig();
        $contextParts = [];

        $contextParts[] = "### BASE PERSONA & POLICIES\n" . $config['system_prompt'];

        if (! empty($config['custom_context'])) {
            $contextParts[] = "### CUSTOM BUSINESS KNOWLEDGE\n" . $config['custom_context'];
        }

        // Dynamic Company & Supply Info (Module 2.6)
        if ($config['include_company_supply']) {
            try {
                $page = CompanySupplyPage::with('sections')->first();
                if ($page && $page->sections->isNotEmpty()) {
                    $supplyInfo = "### COMPANY & SUPPLY INFORMATION\n";
                    $supplyInfo .= "Title: " . $page->title . "\n";
                    foreach ($page->sections->where('status', true)->sortBy('sort_order') as $sec) {
                        $supplyInfo .= "- " . $sec->title . ": " . strip_tags($sec->content) . "\n";
                    }
                    $contextParts[] = $supplyInfo;
                }
            } catch (\Throwable $e) {
                // Silently skip if model unavailable
            }
        }

        // Dynamic Wholesale Partnership Info (Module 2.7)
        if ($config['include_partnership_info']) {
            try {
                $page = WholesalePartnershipPage::with('sections')->first();
                if ($page && $page->sections->isNotEmpty()) {
                    $partnerInfo = "### WHOLESALE PARTNERSHIP CRITERIA\n";
                    $partnerInfo .= "Title: " . $page->title . "\n";
                    foreach ($page->sections->where('status', true)->sortBy('sort_order') as $sec) {
                        $partnerInfo .= "- " . $sec->title . ": " . strip_tags($sec->content) . "\n";
                    }
                    $contextParts[] = $partnerInfo;
                }
            } catch (\Throwable $e) {
                // Silently skip if model unavailable
            }
        }

        // Active Products Summary
        if ($config['include_catalog']) {
            try {
                $products = DB::table('products')
                    ->join('product_flat', 'products.id', '=', 'product_flat.product_id')
                    ->where('product_flat.status', 1)
                    ->select('product_flat.name', 'product_flat.sku', 'product_flat.short_description')
                    ->limit(20)
                    ->get();

                if ($products->isNotEmpty()) {
                    $catInfo = "### AVAILABLE PRODUCT RANGE\n";
                    foreach ($products as $p) {
                        $catInfo .= "- Product: {$p->name} (SKU: {$p->sku})\n";
                        if (! empty($p->short_description)) {
                            $catInfo .= "  Details: " . Str::limit(strip_tags($p->short_description), 140) . "\n";
                        }
                    }
                    $contextParts[] = $catInfo;
                }
            } catch (\Throwable $e) {
                // Skip if error
            }
        }

        // Verification & Gating Status
        if ($isVerifiedBuyer) {
            $contextParts[] = "### USER STATUS: VERIFIED WHOLESALE MEMBER\nThe user is authenticated and verified. You may discuss commercial order workflows, bulk documentation, and supply logistics.";
        } else {
            $contextParts[] = "### USER STATUS: UNVERIFIED VISITOR\nThe user is NOT yet a verified member. You must NOT disclose specific dollar pricing, volume discount rates, or contract clauses. Guide them to Sign In or submit Buyer Verification for approved pricing.";
        }

        return implode("\n\n", $contextParts);
    }

    /**
     * Process an incoming inquiry message.
     */
    public function ask(string $message, ?int $userId = null, ?string $sessionId = null): array
    {
        $config = $this->getConfig();

        if (! $config['status']) {
            return [
                'success' => false,
                'message' => 'The wholesale assistant is currently offline. Please contact our support team or use Message Admin.',
            ];
        }

        $isVerifiedBuyer = false;
        $customer = null;

        if ($userId) {
            $customer = app(CustomerRepository::class)->find($userId);
            if ($customer && $customer->buyer_verification_status === 'approved') {
                $isVerifiedBuyer = true;
            }
        }

        // Check authentication requirement
        if ($config['require_auth'] && ! $userId) {
            return [
                'success' => true,
                'reply'   => 'The PurNatureK Assistant is reserved for registered wholesale members. Please sign in to your wholesale account to consult with the assistant.',
                'gated'   => true,
            ];
        }

        // Private pricing gating check
        $pricingKeywords = ['price', 'pricing', 'cost', 'discount', 'rate', 'how much', 'quote', 'tiered'];
        $mentionsPricing = false;
        foreach ($pricingKeywords as $kw) {
            if (stripos($message, $kw) !== false) {
                $mentionsPricing = true;
                break;
            }
        }

        if ($config['gate_private_prices'] && ! $isVerifiedBuyer && $mentionsPricing) {
            $reply = "Wholesale tier pricing and bulk contracts at PurNatureK are reserved exclusively for approved commercial accounts.\n\nTo view current bulk rates or request custom contract pricing, please [Sign In](/customer/login) to your account or [Register as a Wholesale Member](/customer/register). Our team will review your business credentials promptly.";

            $this->logMessage($message, $reply, $config['model'], $userId, $sessionId, true);

            return [
                'success' => true,
                'reply'   => $reply,
                'gated'   => true,
            ];
        }

        $knowledgeContext = $this->buildKnowledgeContext($isVerifiedBuyer);

        // Attempt LLM generation
        $reply = $this->generateLlmResponse($message, $knowledgeContext, $config);

        $this->logMessage($message, $reply, $config['model'], $userId, $sessionId, false);

        return [
            'success' => true,
            'reply'   => $reply,
            'gated'   => false,
        ];
    }

    /**
     * Generate LLM response using MagicAI / Laravel AI or intelligent fallback.
     */
    protected function generateLlmResponse(string $message, string $context, array $config): string
    {
        $provider = $config['provider'];
        $model = $config['model'];

        // Determine effective API key
        $apiKey = $config['api_key'];
        if ($config['use_magic_ai_credentials'] || empty($apiKey)) {
            $magicKey = core()->getConfigData("magic_ai.providers.{$provider}.api_key");
            if ($magicKey) {
                $apiKey = $magicKey;
            }
        }

        if ($apiKey) {
            config(["ai.providers.{$provider}.key" => $apiKey]);
        }

        if ($provider === 'ollama') {
            $url = core()->getConfigData('magic_ai.providers.ollama.url');
            if ($url) {
                config(['ai.providers.ollama.url' => $url]);
            }
        }

        $prompt = "Context:\n" . $context . "\n\nUser Question:\n" . $message . "\n\nAnswer concisely and professionally according to the PurNatureK wholesale guidelines:";

        try {
            if (function_exists('Laravel\Ai\agent') && $apiKey) {
                $response = \Laravel\Ai\agent()->prompt($prompt, provider: $provider, model: $model);
                if (! empty($response->text)) {
                    return trim($response->text);
                }
            }
        } catch (\Throwable $e) {
            // Log warning and gracefully fall back to local grounded response
        }

        return $this->generateGroundedFallbackResponse($message, $context);
    }

    /**
     * Generate intelligent local grounded response when LLM API is unavailable.
     */
    protected function generateGroundedFallbackResponse(string $message, string $context): string
    {
        $m = strtolower($message);

        if (str_contains($m, 'moq') || str_contains($m, 'minimum')) {
            return "At PurNatureK, our standard Minimum Order Quantity (MOQ) begins at 25 kg for catalog botanical extracts and standard drum packaging. For custom active ingredient formulations or specialized spray-dried powders, batch MOQs may vary based on synthesis and pilot run requirements. Verified members can request specific batch allocations through the Member Portal.";
        }

        if (str_contains($m, 'coa') || str_contains($m, 'analysis') || str_contains($m, 'specification') || str_contains($m, 'spec sheet') || str_contains($m, 'documentation') || str_contains($m, 'document') || str_contains($m, 'msds') || str_contains($m, 'certificate')) {
            return "Lot-specific Certificates of Analysis (COA), HPLC assay reports, and Material Safety Data Sheets (MSDS) are available for all our botanical ingredients. Verified business members can download official compliance documents directly from the Product Documentation tab in the Member Portal.";
        }

        if (str_contains($m, 'register') || str_contains($m, 'verification') || str_contains($m, 'apply') || str_contains($m, 'buyer')) {
            return "To open a wholesale purchasing account with PurNatureK, submit your business registration, tax ID, or reseller certificate via our [Wholesale Member Registration](/customer/register) page. Verification is typically reviewed within 1–2 business days by our compliance team.";
        }

        if (str_contains($m, 'lead time') || str_contains($m, 'shipping') || str_contains($m, 'delivery')) {
            return "Standard stock extracts ship domestically within 2–4 business days via freight or ground carrier from our US warehouse facility. Custom extraction runs or metric-ton bulk orders typically carry a 3–5 week lead time depending on raw harvest availability and laboratory quality clearance.";
        }

        if (str_contains($m, 'product') || str_contains($m, 'extract') || str_contains($m, 'catalog') || str_contains($m, 'ingredient')) {
            return "PurNatureK specializes in certified organic and standardized botanical extracts, including high-potency adaptogens, standardized polyphenols, water-soluble herbal powders, and active cosmeceutical botanicals. Please browse our product catalog or contact our commercial sales desk for full specification sheets.";
        }

        return "Thank you for inquiring with PurNatureK Bulk & Wholesale. We supply premium standardized botanical extracts and commercial ingredients to qualified businesses. For specific technical inquiries, custom batch formulation, or verified wholesale pricing, please sign in to your member account or contact our sales specialists directly.";
    }

    /**
     * Record assistant conversation into agent_conversations.
     */
    protected function logMessage(string $userPrompt, string $assistantReply, string $model, ?int $userId, ?string $sessionId, bool $gated): void
    {
        try {
            $conversationId = (string) Str::uuid();

            DB::table('agent_conversations')->insert([
                'id'         => $conversationId,
                'user_id'    => $userId,
                'title'      => Str::limit($userPrompt, 60),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('agent_conversation_messages')->insert([
                [
                    'id'              => (string) Str::uuid(),
                    'conversation_id' => $conversationId,
                    'user_id'         => $userId,
                    'agent'           => 'ai_assistant',
                    'role'            => 'user',
                    'content'         => $userPrompt,
                    'attachments'     => json_encode([]),
                    'tool_calls'      => json_encode([]),
                    'tool_results'    => json_encode([]),
                    'usage'           => json_encode(['session_id' => $sessionId]),
                    'meta'            => json_encode(['ip' => request()->ip()]),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
                [
                    'id'              => (string) Str::uuid(),
                    'conversation_id' => $conversationId,
                    'user_id'         => $userId,
                    'agent'           => 'ai_assistant',
                    'role'            => 'assistant',
                    'content'         => $assistantReply,
                    'attachments'     => json_encode([]),
                    'tool_calls'      => json_encode([]),
                    'tool_results'    => json_encode([]),
                    'usage'           => json_encode(['model' => $model, 'gated' => $gated]),
                    'meta'            => json_encode([]),
                    'created_at'      => now()->addSecond(),
                    'updated_at'      => now()->addSecond(),
                ],
            ]);
        } catch (\Throwable $e) {
            // Never break runtime if log write fails
        }
    }
}

