<x-admin::layouts>
    <x-slot:title>
        AI Assistant
    </x-slot>

    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="grid gap-1">
            <div class="flex items-center gap-x-2 text-xs text-gray-500 dark:text-gray-400">
                <a href="{{ route('admin.configuration.index') }}" class="hover:text-gray-700 dark:hover:text-gray-200">
                    Configure
                </a>
                <span>/</span>
                <span class="font-semibold text-gray-800 dark:text-gray-200">AI Assistant</span>
            </div>
            <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                AI Assistant
            </h1>
        </div>

        <div class="flex items-center gap-x-2.5">
            <button
                type="button"
                onclick="openTestConsole()"
                class="secondary-button flex items-center gap-1.5"
            >
                <span class="icon-search text-xl"></span>
                Test Console
            </button>

            <button
                type="submit"
                form="ai-assistant-form"
                class="primary-button"
            >
                Save Configuration
            </button>
        </div>
    </div>

    <!-- Flash message alerts -->
    @if (session('success'))
        <div class="mb-5 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-300">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Navigation Tabs -->
    <div class="tabs mb-5 flex flex-wrap gap-2 border-b border-gray-200 pb-2 dark:border-gray-800">
        <button
            type="button"
            onclick="switchTab('tab-general')"
            id="btn-tab-general"
            class="tab-btn active inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-all bg-blue-600 text-white"
        >
            <span class="icon-configuration text-lg"></span>
            Provider & Model
        </button>

        <button
            type="button"
            onclick="switchTab('tab-knowledge')"
            id="btn-tab-knowledge"
            class="tab-btn inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
        >
            <span class="icon-attribute text-lg"></span>
            Knowledge Base
        </button>

        <button
            type="button"
            onclick="switchTab('tab-faqs')"
            id="btn-tab-faqs"
            class="tab-btn inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
        >
            <span class="icon-mail text-lg"></span>
            Welcome & FAQs
        </button>

        <button
            type="button"
            onclick="switchTab('tab-widget')"
            id="btn-tab-widget"
            class="tab-btn inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
        >
            <span class="icon-store text-lg"></span>
            Widget & Embed
        </button>

        <button
            type="button"
            onclick="switchTab('tab-security')"
            id="btn-tab-security"
            class="tab-btn inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
        >
            <span class="icon-settings text-lg"></span>
            Chat & Security
        </button>

        <button
            type="button"
            onclick="switchTab('tab-logs')"
            id="btn-tab-logs"
            class="tab-btn inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
        >
            <span class="icon-report text-lg"></span>
            Conversation Logs ({{ count($logs) }})
        </button>
    </div>

    <!-- Consolidated Form -->
    <form id="ai-assistant-form" method="POST" action="{{ route('admin.ai_assistant.update') }}">
        @csrf

        <!-- TAB 1: General & Provider -->
        <div id="tab-general" class="tab-content grid gap-6">
            <div class="box-shadow rounded-lg bg-white p-6 dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                <div class="mb-5 flex items-center justify-between border-b pb-4 dark:border-gray-800">
                    <div>
                        <h2 class="text-base font-bold text-gray-800 dark:text-white">Assistant Status</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Enable or disable the customer-facing wholesale AI Assistant on storefronts.</p>
                    </div>

                    <label class="relative inline-flex cursor-pointer items-center">
                        <input
                            type="checkbox"
                            name="status"
                            value="1"
                            class="peer sr-only"
                            {{ $config['status'] ? 'checked' : '' }}
                        >
                        <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:top-[2px] after:left-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none dark:bg-gray-700"></div>
                    </label>
                </div>

                <div class="grid grid-cols-2 gap-6 max-md:grid-cols-1">
                    <!-- Provider Selection -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-white">
                            AI Provider
                        </label>
                        <select
                            name="provider"
                            id="ai-provider-select"
                            onchange="onProviderChange()"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition-all focus:border-blue-600 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300"
                        >
                            @foreach ($providers as $p)
                                <option
                                    value="{{ $p['value'] }}"
                                    data-has-magic="{{ $p['has_magic_ai_key'] ? '1' : '0' }}"
                                    data-masked="{{ $p['masked_magic_ai_key'] ?? '' }}"
                                    {{ $config['provider'] === $p['value'] ? 'selected' : '' }}
                                >
                                    {{ $p['label'] }} {{ $p['has_magic_ai_key'] ? '• (Magic AI Key Available)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Providers natively supported by Bagisto's Magic AI infrastructure.
                        </p>
                    </div>

                    <!-- Model Selection -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-white">
                            Model Identifier
                        </label>
                        <input
                            type="text"
                            name="model"
                            id="ai-model-input"
                            value="{{ old('model', $config['model']) }}"
                            placeholder="e.g. gpt-4o-mini, claude-3-5-sonnet, gemini-1.5-flash"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition-all focus:border-blue-600 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300"
                            required
                        >
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Recommended fast models: <code class="text-blue-600">gpt-4o-mini</code>, <code class="text-blue-600">claude-3-5-haiku</code>, <code class="text-blue-600">gemini-1.5-flash</code>.
                        </p>
                    </div>
                </div>

                <!-- Magic AI Credentials Section -->
                <div class="mt-6 rounded-lg border border-blue-100 bg-blue-50/50 p-4 dark:border-gray-800 dark:bg-gray-950">
                    <div class="flex items-start gap-3">
                        <input
                            type="checkbox"
                            name="use_magic_ai_credentials"
                            id="use_magic_ai_credentials"
                            value="1"
                            {{ $config['use_magic_ai_credentials'] ? 'checked' : '' }}
                            onchange="toggleApiKeyField()"
                            class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                        <div class="grid gap-1">
                            <label for="use_magic_ai_credentials" class="cursor-pointer text-sm font-semibold text-gray-800 dark:text-white">
                                Reuse Magic AI Provider Credentials
                            </label>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                When enabled, the assistant automatically uses the API keys configured under <a href="{{ route('admin.configuration.magic_ai') }}" class="text-blue-600 underline">Configure &gt; Existing Magic AI</a>. No duplicated credentials needed.
                            </p>
                            <div id="magic-ai-status-badge" class="mt-2 text-xs font-medium"></div>
                        </div>
                    </div>

                    <!-- Dedicated API Key Input (shown if not reusing Magic AI credentials) -->
                    <div id="dedicated-key-container" class="mt-4 pt-4 border-t border-blue-100 dark:border-gray-800" style="{{ $config['use_magic_ai_credentials'] ? 'display:none;' : '' }}">
                        <label class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-white">
                            Dedicated API Key (Optional)
                        </label>
                        <input
                            type="password"
                            name="api_key"
                            value="{{ old('api_key', $config['api_key']) }}"
                            placeholder="Enter dedicated provider API key if not reusing Magic AI key"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition-all focus:border-blue-600 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300"
                        >
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-6 max-md:grid-cols-1">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-white">
                            Temperature (Creativity: {{ $config['temperature'] }})
                        </label>
                        <input
                            type="range"
                            name="temperature"
                            min="0"
                            max="1"
                            step="0.05"
                            value="{{ $config['temperature'] }}"
                            oninput="document.getElementById('temp-val').innerText = this.value"
                            class="w-full"
                        >
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>0.0 (Precise / Factual)</span>
                            <span id="temp-val" class="font-bold text-blue-600">{{ $config['temperature'] }}</span>
                            <span>1.0 (Creative)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: Knowledge Base Management -->
        <div id="tab-knowledge" class="tab-content hidden grid gap-6">
            <div class="box-shadow rounded-lg bg-white p-6 dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                <div class="mb-5 border-b pb-4 dark:border-gray-800">
                    <h2 class="text-base font-bold text-gray-800 dark:text-white">Wholesale Knowledge Base & Persona</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Configure the background instructions, business rules, botanical specifications, and dynamic module ingestion.</p>
                </div>

                <div class="grid gap-6">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-white">
                            System Prompt / Business Persona
                        </label>
                        <textarea
                            name="system_prompt"
                            rows="7"
                            class="block w-full rounded-lg border border-gray-300 bg-white p-3 text-sm font-mono text-gray-700 transition-all focus:border-blue-600 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300"
                            required
                        >{{ old('system_prompt', $config['system_prompt']) }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Defines the assistant's boundaries, compliance rules, and botanical wholesale demeanor.</p>
                    </div>

                    <!-- Dynamic Ingestion Toggles -->
                    <div class="rounded-lg border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-950">
                        <h3 class="mb-3 text-sm font-bold text-gray-800 dark:text-white">Dynamic Portal Knowledge Sources</h3>

                        <div class="space-y-3">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="include_catalog"
                                    value="1"
                                    {{ $config['include_catalog'] ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <div>
                                    <span class="text-sm font-semibold text-gray-800 dark:text-white">Include Published Product Catalog Range</span>
                                    <p class="text-xs text-gray-500">Dynamically exposes active product names, SKUs, and specifications to the assistant context.</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="include_company_supply"
                                    value="1"
                                    {{ $config['include_company_supply'] ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <div>
                                    <span class="text-sm font-semibold text-gray-800 dark:text-white">Include Company & Supply Information (Module 2.6)</span>
                                    <p class="text-xs text-gray-500">Ingests sourcing standards, organic certifications, facility accreditations, and supply capabilities.</p>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="include_partnership_info"
                                    value="1"
                                    {{ $config['include_partnership_info'] ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <div>
                                    <span class="text-sm font-semibold text-gray-800 dark:text-white">Include Wholesale Partnership Information (Module 2.7)</span>
                                    <p class="text-xs text-gray-500">Ingests account criteria, contract terms, minimum spend tiers, and distributor policies.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Custom Knowledge Notes -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-white">
                            Custom Knowledge Context / FAQs (Free-form)
                        </label>
                        <textarea
                            name="custom_context"
                            rows="6"
                            placeholder="Add specific rules, e.g.
- Standard payment terms: Net 30 upon approved credit application
- Warehouses located in California & New Jersey
- Free freight threshold: $5,000+ orders within continental US"
                            class="block w-full rounded-lg border border-gray-300 bg-white p-3 text-sm text-gray-700 transition-all focus:border-blue-600 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300"
                        >{{ old('custom_context', $config['custom_context']) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: Welcome Message & Approved FAQs -->
        <div id="tab-faqs" class="tab-content hidden grid gap-6">
            <div class="box-shadow rounded-lg bg-white p-6 dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                <div class="mb-5 border-b pb-4 dark:border-gray-800">
                    <h2 class="text-base font-bold text-gray-800 dark:text-white">Welcome Greeting & Quick FAQ Prompts</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Manage the greeting text and the approved prompt chips displayed to wholesale visitors.</p>
                </div>

                <div class="grid gap-6">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-white">
                            Welcome Message
                        </label>
                        <textarea
                            name="welcome_message"
                            rows="3"
                            class="block w-full rounded-lg border border-gray-300 bg-white p-3 text-sm text-gray-700 transition-all focus:border-blue-600 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300"
                            required
                        >{{ old('welcome_message', $config['welcome_message']) }}</textarea>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-white">
                            Input Placeholder Text
                        </label>
                        <input
                            type="text"
                            name="placeholder"
                            value="{{ old('placeholder', $config['placeholder']) }}"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 transition-all focus:border-blue-600 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300"
                            required
                        >
                    </div>

                    <!-- FAQ Prompt Chips Editor -->
                    <div>
                        <div class="mb-3 flex items-center justify-between">
                            <div>
                                <label class="block text-sm font-medium text-gray-800 dark:text-white">
                                    Approved FAQ Prompt Chips
                                </label>
                                <p class="text-xs text-gray-500">Quick-tap question buttons displayed above the user input box.</p>
                            </div>
                            <button
                                type="button"
                                onclick="addFaqRow()"
                                class="secondary-button text-xs py-1.5 px-3"
                            >
                                + Add FAQ Chip
                            </button>
                        </div>

                        <div id="faq-rows-container" class="space-y-2.5">
                            @foreach ($config['faqs'] as $index => $faq)
                                <div class="faq-row flex items-center gap-3">
                                    <input
                                        type="text"
                                        name="faqs[{{ $index }}][question]"
                                        value="{{ $faq['question'] ?? '' }}"
                                        placeholder="e.g. What are your Minimum Order Quantities (MOQ)?"
                                        class="block flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300"
                                        required
                                    >
                                    <input
                                        type="text"
                                        name="faqs[{{ $index }}][category]"
                                        value="{{ $faq['category'] ?? 'General' }}"
                                        placeholder="Category tag"
                                        class="block w-40 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300"
                                    >
                                    <button
                                        type="button"
                                        onclick="removeFaqRow(this)"
                                        class="text-red-500 hover:text-red-700 text-lg px-2"
                                        title="Delete"
                                    >
                                        &times;
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 4: Widget & Embed -->
        <div id="tab-widget" class="tab-content hidden grid gap-6">
            <div class="box-shadow rounded-lg bg-white p-6 dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                <div class="mb-5 border-b pb-4 dark:border-gray-800">
                    <h2 class="text-base font-bold text-gray-800 dark:text-white">Customer-Facing Widget Configuration</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">The customer-facing title is strictly <strong>"Assistant"</strong>. Configure branding, position, and embed code.</p>
                </div>

                <div class="grid grid-cols-2 gap-6 max-md:grid-cols-1">
                    <div>
                        <div class="mb-4">
                            <label class="mb-1 block text-sm font-medium text-gray-800 dark:text-white">
                                Customer-Facing Widget Title
                            </label>
                            <input
                                type="text"
                                value="Assistant"
                                disabled
                                class="block w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-600 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400"
                            >
                            <p class="mt-1 text-xs text-green-700 dark:text-green-400 font-medium">✓ Enforces required "Assistant" title on customer interfaces.</p>
                        </div>

                        <div class="mb-4">
                            <label class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-white">
                                Launcher Position
                            </label>
                            <select
                                name="position"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300"
                            >
                                <option value="bottom-right" {{ $config['position'] === 'bottom-right' ? 'selected' : '' }}>Bottom Right (Default)</option>
                                <option value="bottom-left" {{ $config['position'] === 'bottom-left' ? 'selected' : '' }}>Bottom Left</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-white">
                                Primary Accent Color
                            </label>
                            <div class="flex items-center gap-3">
                                <input
                                    type="color"
                                    name="theme_color"
                                    id="theme_color_picker"
                                    value="{{ $config['theme_color'] }}"
                                    class="h-10 w-14 cursor-pointer rounded border p-1"
                                >
                                <input
                                    type="text"
                                    id="theme_color_text"
                                    value="{{ $config['theme_color'] }}"
                                    onchange="document.getElementById('theme_color_picker').value = this.value"
                                    class="block w-32 rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono dark:border-gray-700 dark:bg-gray-950"
                                >
                                <button
                                    type="button"
                                    onclick="setBrandGreen()"
                                    class="text-xs text-blue-600 underline"
                                >
                                    Reset to PurNatureK Green (#0f4a3e)
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="auto_open"
                                    value="1"
                                    {{ $config['auto_open'] ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600"
                                >
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    Pop open greeting bubble on first user page visit
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Embed Snippet Box -->
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950 flex flex-col gap-4">
                        <div>
                            <h3 class="mb-1 text-sm font-bold text-gray-800 dark:text-white">Standard HTML Script Tag (Storefront & Landing Pages)</h3>
                            <p class="mb-2 text-xs text-gray-500">
                                Place before <code class="text-xs">&lt;/body&gt;</code> on external landing pages (<code class="text-xs">purnaturekbulk.com</code>):
                            </p>
                            <div class="relative">
                                <pre id="snippet-html" class="overflow-x-auto rounded bg-gray-900 p-3 text-xs text-green-400 font-mono"><code>{{ $embedSnippet }}</code></pre>
                                <button
                                    type="button"
                                    onclick="copyToClipboard('{{ addslashes($embedSnippet) }}')"
                                    class="absolute top-2 right-2 rounded bg-gray-700 px-2 py-1 text-xs text-white hover:bg-gray-600"
                                >
                                    Copy HTML
                                </button>
                            </div>
                        </div>

                        <div>
                            <h3 class="mb-1 text-sm font-bold text-gray-800 dark:text-white">React / Vite Component Hook (<code class="text-xs">purnaturekbulk.com</code>)</h3>
                            <p class="mb-2 text-xs text-gray-500">
                                Add to your root App component or Landing layout:
                            </p>
                            <div class="relative">
                                <pre id="snippet-react" class="overflow-x-auto rounded bg-gray-900 p-3 text-xs text-green-400 font-mono whitespace-pre-wrap"><code>{{ $reactSnippet }}</code></pre>
                                <button
                                    type="button"
                                    onclick="copyToClipboard(`{{ addslashes($reactSnippet) }}`)"
                                    class="absolute top-2 right-2 rounded bg-gray-700 px-2 py-1 text-xs text-white hover:bg-gray-600"
                                >
                                    Copy React
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 5: Chat & API Security -->
        <div id="tab-security" class="tab-content hidden grid gap-6">
            <div class="box-shadow rounded-lg bg-white p-6 dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                <div class="mb-5 border-b pb-4 dark:border-gray-800">
                    <h2 class="text-base font-bold text-gray-800 dark:text-white">Chat & API Security Settings</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Configure rate limiting, private catalog price protection, and access authentication rules.</p>
                </div>

                <div class="grid gap-6">
                    <div class="rounded-lg border border-green-200 bg-green-50/50 p-4 dark:border-green-900 dark:bg-gray-950">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input
                                type="checkbox"
                                name="gate_private_prices"
                                value="1"
                                {{ $config['gate_private_prices'] ? 'checked' : '' }}
                                class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600"
                            >
                            <div>
                                <span class="text-sm font-bold text-green-950 dark:text-green-300">
                                    Enforce Strict Private Catalog Protection (Wholesale B2B Shield)
                                </span>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                                    When enabled, the assistant strictly prevents pricing leaks to unverified visitors. If an unverified user asks for specific dollar prices, tiered rates, or contracts, the assistant automatically provides product specifications while shielding pricing and providing a direct link to sign in or register.
                                </p>
                            </div>
                        </label>
                    </div>

                    <div class="grid grid-cols-2 gap-6 max-md:grid-cols-1">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-white">
                                Inquiries Rate Limit (Requests per minute per IP)
                            </label>
                            <input
                                type="number"
                                name="rate_limit_per_minute"
                                value="{{ old('rate_limit_per_minute', $config['rate_limit_per_minute']) }}"
                                min="1"
                                max="120"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300"
                                required
                            >
                            <p class="mt-1 text-xs text-gray-500">Defends against scraping, spam, or denial of service attacks.</p>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-white">
                                Authentication Access Mode
                            </label>
                            <div class="space-y-2 pt-1">
                                <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300">
                                    <input
                                        type="radio"
                                        name="require_auth"
                                        value="0"
                                        {{ ! $config['require_auth'] ? 'checked' : '' }}
                                        class="h-4 w-4 text-blue-600"
                                    >
                                    Public Inquiries Allowed (With Private Price Gating)
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300">
                                    <input
                                        type="radio"
                                        name="require_auth"
                                        value="1"
                                        {{ $config['require_auth'] ? 'checked' : '' }}
                                        class="h-4 w-4 text-blue-600"
                                    >
                                    Members Only (Requires active customer login)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 6: Conversation Logs -->
        <div id="tab-logs" class="tab-content hidden grid gap-6">
            <div class="box-shadow rounded-lg bg-white p-6 dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
                <div class="mb-5 flex items-center justify-between border-b pb-4 dark:border-gray-800">
                    <div>
                        <h2 class="text-base font-bold text-gray-800 dark:text-white">Recent Inquiries & Conversation Logs</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">View customer interactions, inquiries, model usage, and pricing gate activations.</p>
                    </div>

                    @if (count($logs) > 0)
                        <button
                            type="button"
                            onclick="confirmClearLogs()"
                            class="secondary-button text-red-600 hover:text-red-700 border-red-200 hover:bg-red-50 text-xs py-1.5 px-3"
                        >
                            Clear All Logs
                        </button>
                    @endif
                </div>

                @if (empty($logs))
                    <div class="py-12 text-center text-sm text-gray-500">
                        No conversations logged yet. Customer conversations and console tests will appear here.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                            <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-700 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-400">
                                <tr>
                                    <th class="py-3 px-4">Date / Time</th>
                                    <th class="py-3 px-4">User / Visitor</th>
                                    <th class="py-3 px-4">Inquiry</th>
                                    <th class="py-3 px-4">Assistant Reply</th>
                                    <th class="py-3 px-4">Model / Status</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                @foreach ($logs as $log)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-950/50">
                                        <td class="py-3 px-4 whitespace-nowrap text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($log['created_at'])->format('M d, Y H:i') }}
                                        </td>
                                        <td class="py-3 px-4 whitespace-nowrap font-medium text-gray-800 dark:text-white">
                                            @if ($log['user_id'])
                                                <span class="inline-flex items-center gap-1 text-green-700 dark:text-green-400 font-semibold">
                                                    ✓ Member #{{ $log['user_id'] }}
                                                </span>
                                            @else
                                                <span class="text-gray-500">Guest Visitor</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 max-w-xs truncate text-xs" title="{{ $log['inquiry'] }}">
                                            {{ $log['inquiry'] }}
                                        </td>
                                        <td class="py-3 px-4 max-w-sm truncate text-xs text-gray-500" title="{{ $log['reply'] }}">
                                            {{ $log['reply'] }}
                                        </td>
                                        <td class="py-3 px-4 whitespace-nowrap text-xs">
                                            <span class="rounded bg-gray-100 px-2 py-0.5 text-xs font-mono dark:bg-gray-800">
                                                {{ $log['model'] }}
                                            </span>
                                            @if (! empty($log['gated']))
                                                <span class="ml-1 rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-800">
                                                    Price Gated
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right whitespace-nowrap">
                                            <button
                                                type="button"
                                                onclick="viewTranscript({{ json_encode($log['inquiry']) }}, {{ json_encode($log['reply']) }})"
                                                class="text-blue-600 hover:underline text-xs mr-2"
                                            >
                                                View
                                            </button>
                                            <button
                                                type="button"
                                                onclick="deleteLogEntry('{{ $log['id'] }}')"
                                                class="text-red-500 hover:underline text-xs"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </form>

    <!-- Hidden form for deleting single log -->
    <form id="delete-log-form" method="POST" action="" style="display:none;">
        @csrf
    </form>

    <!-- Hidden form for clearing all logs -->
    <form id="clear-logs-form" method="POST" action="{{ route('admin.ai_assistant.logs.clear') }}" style="display:none;">
        @csrf
    </form>

    <!-- Real-time Test Console Modal -->
    <div id="test-console-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" style="display:none;">
        <div class="w-full max-w-2xl rounded-xl bg-white shadow-2xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="flex items-center justify-between border-b px-6 py-4 dark:border-gray-800">
                <div class="flex items-center gap-2">
                    <span class="h-3 w-3 rounded-full bg-green-500 animate-pulse"></span>
                    <h3 class="font-bold text-gray-800 dark:text-white">AI Assistant — Test Console</h3>
                </div>
                <button type="button" onclick="closeTestConsole()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Test Inquiry Message</label>
                    <textarea
                        id="test-console-input"
                        rows="3"
                        placeholder="e.g. What is your minimum order quantity for certified organic ashwagandha extract?"
                        class="w-full rounded-lg border border-gray-300 p-3 text-sm focus:border-blue-600 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-gray-200"
                    ></textarea>
                </div>

                <div class="flex justify-between items-center">
                    <div class="flex gap-2 text-xs">
                        <button type="button" onclick="setTestPrompt('What are your MOQs?')" class="rounded bg-gray-100 dark:bg-gray-800 px-2.5 py-1 text-gray-600 dark:text-gray-300 hover:bg-gray-200">MOQ Query</button>
                        <button type="button" onclick="setTestPrompt('How much does 100kg organic extract cost?')" class="rounded bg-gray-100 dark:bg-gray-800 px-2.5 py-1 text-gray-600 dark:text-gray-300 hover:bg-gray-200">Price Gating Test</button>
                        <button type="button" onclick="setTestPrompt('Can you provide a COA for batch analysis?')" class="rounded bg-gray-100 dark:bg-gray-800 px-2.5 py-1 text-gray-600 dark:text-gray-300 hover:bg-gray-200">COA Query</button>
                    </div>

                    <button
                        type="button"
                        id="test-console-send-btn"
                        onclick="runTestPrompt()"
                        class="primary-button text-xs py-1.5 px-4"
                    >
                        Send Inquiry
                    </button>
                </div>

                <div id="test-console-output-container" class="rounded-lg bg-gray-50 dark:bg-gray-950 p-4 border border-gray-200 dark:border-gray-800 min-h-[140px] max-h-[260px] overflow-y-auto">
                    <p class="text-xs font-semibold text-gray-500 mb-2">Assistant Response Preview:</p>
                    <div id="test-console-output" class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed">
                        <em>Enter a question above and click "Send Inquiry" to test how the assistant responds with current settings.</em>
                    </div>
                    <div id="test-console-meta" class="mt-3 text-[11px] text-gray-400"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transcript View Modal -->
    <div id="transcript-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" style="display:none;">
        <div class="w-full max-w-xl rounded-xl bg-white shadow-2xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="flex items-center justify-between border-b px-6 py-4 dark:border-gray-800">
                <h3 class="font-bold text-gray-800 dark:text-white">Conversation Transcript</h3>
                <button type="button" onclick="closeTranscriptModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Visitor Inquiry</span>
                    <div id="transcript-inquiry" class="mt-1 rounded-lg bg-gray-100 p-3 text-sm text-gray-800 dark:bg-gray-800 dark:text-gray-200 font-medium"></div>
                </div>
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Assistant Response</span>
                    <div id="transcript-reply" class="mt-1 rounded-lg bg-blue-50/70 p-3 text-sm text-gray-800 dark:bg-gray-950 dark:text-gray-200 leading-relaxed border border-blue-100 dark:border-gray-800"></div>
                </div>
            </div>
        </div>
    </div>

    @pushOnce('scripts')
        <script>
            function switchTab(tabId) {
                document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.classList.remove('active', 'bg-blue-600', 'text-white');
                    btn.classList.add('text-gray-600', 'dark:text-gray-300');
                });

                document.getElementById(tabId).classList.remove('hidden');
                const activeBtn = document.getElementById('btn-' + tabId);
                if (activeBtn) {
                    activeBtn.classList.add('active', 'bg-blue-600', 'text-white');
                    activeBtn.classList.remove('text-gray-600', 'dark:text-gray-300');
                }
            }

            function onProviderChange() {
                const select = document.getElementById('ai-provider-select');
                const selectedOpt = select.options[select.selectedIndex];
                const hasMagic = selectedOpt.getAttribute('data-has-magic') === '1';
                const masked = selectedOpt.getAttribute('data-masked');

                const badge = document.getElementById('magic-ai-status-badge');
                if (hasMagic) {
                    badge.innerHTML = '<span class="text-green-600 dark:text-green-400">✓ Active Magic AI credentials found for ' + selectedOpt.text.split('•')[0] + ' (' + masked + ')</span>';
                } else {
                    badge.innerHTML = '<span class="text-amber-600 dark:text-amber-400">⚠ No key set in Magic AI for this provider yet. Provide a key under Configure &gt; Existing Magic AI or enter a dedicated key below.</span>';
                }
            }

            function toggleApiKeyField() {
                const cb = document.getElementById('use_magic_ai_credentials');
                const container = document.getElementById('dedicated-key-container');
                container.style.display = cb.checked ? 'none' : 'block';
            }

            function setBrandGreen() {
                document.getElementById('theme_color_picker').value = '#0f4a3e';
                document.getElementById('theme_color_text').value = '#0f4a3e';
            }

            function copyEmbedSnippet() {
                const code = '<script src="{{ url('/ai-assistant/widget.js') }}" async><\/script>';
                navigator.clipboard.writeText(code).then(() => {
                    alert('Embed snippet copied to clipboard!');
                });
            }

            let faqIndex = {{ count($config['faqs']) }};

            function addFaqRow() {
                const container = document.getElementById('faq-rows-container');
                const row = document.createElement('div');
                row.className = 'faq-row flex items-center gap-3';
                row.innerHTML = `
                    <input
                        type="text"
                        name="faqs[${faqIndex}][question]"
                        placeholder="e.g. Can you provide custom formulation services?"
                        class="block flex-1 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300"
                        required
                    >
                    <input
                        type="text"
                        name="faqs[${faqIndex}][category]"
                        value="General"
                        placeholder="Category"
                        class="block w-40 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300"
                    >
                    <button
                        type="button"
                        onclick="removeFaqRow(this)"
                        class="text-red-500 hover:text-red-700 text-lg px-2"
                        title="Delete"
                    >&times;</button>
                `;
                container.appendChild(row);
                faqIndex++;
            }

            function removeFaqRow(btn) {
                btn.closest('.faq-row').remove();
            }

            function openTestConsole() {
                document.getElementById('test-console-modal').style.display = 'flex';
                document.getElementById('test-console-input').focus();
            }

            function closeTestConsole() {
                document.getElementById('test-console-modal').style.display = 'none';
            }

            function setTestPrompt(text) {
                document.getElementById('test-console-input').value = text;
            }

            function runTestPrompt() {
                const input = document.getElementById('test-console-input');
                const msg = input.value.trim();
                if (!msg) return;

                const sendBtn = document.getElementById('test-console-send-btn');
                const output = document.getElementById('test-console-output');
                const meta = document.getElementById('test-console-meta');

                sendBtn.disabled = true;
                sendBtn.innerText = 'Consulting...';
                output.innerHTML = '<span class="text-gray-400">Processing inquiry against PurNatureK knowledge base...</span>';
                meta.innerText = '';

                fetch('{{ route('admin.ai_assistant.test_prompt') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message: msg })
                })
                .then(res => res.json())
                .then(data => {
                    sendBtn.disabled = false;
                    sendBtn.innerText = 'Send Inquiry';
                    if (data.reply) {
                        output.innerHTML = data.reply.replace(/\n/g, '<br>');
                        meta.innerText = 'Response generated in ' + data.duration_ms + ' ms' + (data.gated ? ' • Private Catalog Price Protection Activated' : '');
                    } else {
                        output.innerText = 'No response returned.';
                    }
                })
                .catch(err => {
                    sendBtn.disabled = false;
                    sendBtn.innerText = 'Send Inquiry';
                    output.innerText = 'Error testing inquiry: ' + err.message;
                });
            }

            function viewTranscript(inquiry, reply) {
                document.getElementById('transcript-inquiry').innerText = inquiry;
                document.getElementById('transcript-reply').innerText = reply;
                document.getElementById('transcript-modal').style.display = 'flex';
            }

            function closeTranscriptModal() {
                document.getElementById('transcript-modal').style.display = 'none';
            }

            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(() => {
                    alert('Copied to clipboard!');
                }).catch(() => {
                    prompt('Copy embed snippet manually:', text);
                });
            }

            function copyEmbedSnippet() {
                const text = '<script src="{{ url('/ai-assistant/widget.js') }}" data-api-base="{{ url('/') }}" async><\/script>';
                copyToClipboard(text);
            }

            function deleteLogEntry(id) {
                if (confirm('Delete this conversation log?')) {
                    const form = document.getElementById('delete-log-form');
                    form.action = '{{ url('/admin/configuration/ai-assistant/logs') }}/' + id + '/delete';
                    form.submit();
                }
            }

            function confirmClearLogs() {
                if (confirm('Are you sure you want to permanently clear all assistant conversation logs?')) {
                    document.getElementById('clear-logs-form').submit();
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                onProviderChange();
            });
        </script>
    @endpushOnce
</x-admin::layouts>

