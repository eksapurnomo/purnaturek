# PurNatureK AI Assistant — Native Bagisto Admin Specification

**Document Version:** 2.0  
**Status:** Approved for Local Implementation  
**Platform:** Bagisto 2.4.14 · Laravel 12.69.2 · B2B Suite 2.2.2 · PHP 8.3  
**Theme:** `purnaturek` (Parent: `default`)  
**Scope:** Local Development (`/home/eksa/projects/purnaturek-wholesale`)

---

## 1. Executive Summary & UI Boundaries

This specification defines the architecture, data structures, and user interface for the **PurNatureK AI Assistant**, strictly adhering to native Bagisto 2.4.14 admin navigation, layout, permissions, and theme conventions.

### Strict UI & Naming Requirements

| Context | Permitted Title | Prohibited Names |
| :--- | :--- | :--- |
| **Admin Sidebar Submenu** | `AI Assistant` | *PurNatureK AI*, *AI Dashboard*, *Wholesale Bot* |
| **Admin Page Title / Heading** | `AI Assistant` | *PurNatureK AI Wholesale Assistant*, *Assistant App* |
| **Customer-Facing Widget Title** | `Assistant` | *PurNatureK AI Wholesale Assistant*, *AI Bot*, *Chatbot* |
| **Navigation Hierarchy** | Submenu under **Configure** | Top-level sidebar item, separate dashboard app |

### Key Architectural Tenets
1. **Zero New Top-Level Sidebar Sections**: The feature registers strictly as a child submenu beneath the existing `Configure` menu.
2. **Native Bagisto Admin Conventions**: Uses `<x-admin::layouts>`, native breadcrumbs (`Configure / AI Assistant`), Bagisto UI component styling, icons, and ACL bouncer permissions.
3. **No Separate Dashboard Application**: All configuration, knowledge bases, FAQ prompt chips, widget settings, security controls, and conversation logs are consolidated into one unified native Bagisto admin view.
4. **Magic AI Non-Destructive Compatibility**: Preserves the existing Magic AI system configuration and provides seamless reuse of existing provider credentials without overwriting or breaking core settings.
5. **Private Catalog Protection**: The assistant operates with strict B2B security awareness—it never leaks private member pricing or confidential documents to unverified guests.

---

## 2. Navigation & Routing Architecture

### 2.1 Navigation Structure

```text
Admin Sidebar
  ├── Dashboard
  ├── Sales
  ├── Catalog
  ├── Customers
  ├── Marketing
  ├── Reporting
  ├── Appearance
  ├── Settings
  └── Configure (key: 'configuration')
        ├── Magic AI (key: 'configuration.magic_ai', route: 'admin.configuration.magic_ai')
        └── AI Assistant     (key: 'configuration.ai_assistant', route: 'admin.ai_assistant.index')
```

### 2.2 Route Specifications

| Route Name | Method | URI | Controller Action | Description |
| :--- | :---: | :--- | :--- | :--- |
| `admin.configuration.magic_ai` | `GET` | `admin/configuration/magic-ai` | Closure / Controller | Redirects to Bagisto's existing Magic AI (`magic_ai/general`) |
| `admin.ai_assistant.index` | `GET` | `admin/configuration/ai-assistant` | `Admin\AiAssistantController@index` | Main consolidated configuration view |
| `admin.ai_assistant.update` | `POST` | `admin/configuration/ai-assistant` | `Admin\AiAssistantController@update` | Save consolidated settings |
| `admin.ai_assistant.test_prompt` | `POST` | `admin/configuration/ai-assistant/test-prompt` | `Admin\AiAssistantController@testPrompt` | Real-time console prompt tester |
| `admin.ai_assistant.logs.delete` | `POST` | `admin/configuration/ai-assistant/logs/{id}/delete` | `Admin\AiAssistantController@deleteLog` | Delete single conversation log |
| `admin.ai_assistant.logs.clear` | `POST` | `admin/configuration/ai-assistant/logs/clear` | `Admin\AiAssistantController@clearLogs` | Purge conversation logs |
| `api.ai_assistant.chat` | `POST` | `api/v1/ai-assistant/chat` | `Shop\AiAssistantChatController@chat` | Customer-facing widget chat API |
| `api.ai_assistant.config` | `GET` | `api/v1/ai-assistant/config` | `Shop\AiAssistantChatController@config` | Public widget config & FAQ chips |

### 2.3 Access Control (ACL)
Registered in `config/acl.php`:
```php
[
    'key'   => 'configuration.magic_ai',
    'name'  => 'Magic AI',
    'route' => 'admin.configuration.magic_ai',
    'sort'  => 1,
],
[
    'key'   => 'configuration.ai_assistant',
    'name'  => 'AI Assistant',
    'route' => 'admin.ai_assistant.index',
    'sort'  => 2,
]
```

---

## 3. Magic AI Compatibility & Shared Provider Infrastructure

Bagisto 2.4.14 includes `Webkul\MagicAI` powered by `Laravel\Ai`. The PurNatureK AI Assistant bridges with this infrastructure safely:

### Supported Providers
- **OpenAI** (`openai`) — GPT-4o, GPT-4o-mini
- **Anthropic** (`anthropic`) — Claude 3.5 Sonnet, Claude 3 Haiku
- **Google Gemini** (`gemini`) — Gemini 1.5 Pro, Gemini 1.5 Flash
- **Groq** (`groq`) — Llama 3.3 70B, Mixtral 8x7b
- **DeepSeek** (`deepseek`) — DeepSeek Chat, DeepSeek Coder
- **Mistral** (`mistral`) — Mistral Large, Mistral Small
- **Ollama** (`ollama`) — Local self-hosted LLMs
- **xAI** (`xai`) — Grok Beta

### Non-Destructive Credential Reuse
1. **Magic AI Credential Detection**: When an admin selects a provider (e.g., `gemini` or `openai`), the system checks if `core()->getConfigData("magic_ai.providers.{$provider}.api_key")` is set.
2. **Reuse Toggle**: If enabled (`ai_assistant.general.use_magic_ai_credentials = true`), the assistant automatically pulls the active Magic AI credentials at runtime.
3. **Dedicated Key Option**: If the admin desires isolated usage tracking or budgeting, a dedicated API key can be supplied under `ai_assistant.general.api_key`.
4. **Fallback Resilience**: If no external AI provider is configured or reachable, the assistant automatically uses an intelligent local rules and knowledge-retrieval engine to answer inquiries without downtime.

---

## 4. Consolidated Admin Configuration Page

The page is structured as a native Bagisto tabbed view with 6 consolidated sections:

### Section 1: Provider & Model Configuration
- **Status Toggle**: Enable / Disable the assistant entirely.
- **Provider Selector**: Dropdown of 8 supported providers.
- **Model Selector**: Dynamic dropdown of text models for the chosen provider.
- **Magic AI Integration**: Checkbox to reuse existing Magic AI credentials with visual indicator badge.
- **Custom API Key**: Secure masked input for dedicated API key.
- **Temperature / Creativity**: Slider (0.0 to 1.0; recommended 0.3 for factual B2B responses).

### Section 2: Knowledge Base Management
- **Wholesale Persona & System Instructions**: Baseline prompt instructing the assistant on tone, botanical accuracy, and wholesale guidelines.
- **Dynamic Knowledge Ingestion Toggles**:
  - `Include Active Catalog Summary`: Ingests names, categories, and published botanical specs.
  - `Include Company & Supply Info`: Ingests active sections from Module 2.6 (`CompanySupplyPage`).
  - `Include Partnership Info`: Ingests active sections from Module 2.7 (`WholesalePartnershipPage`).
- **Custom Knowledge Entries (CRUD)**:
  - Add, edit, or delete bespoke business rules (e.g., *"MOQ is 25 kg for standard botanical extracts"*, *"Lead time for custom spray-dried powders is 3–4 weeks"*).

### Section 3: Welcome Message & Approved FAQs
- **Welcome Greeting**: Initial message rendered when the visitor opens the widget.
- **Input Placeholder**: Placeholder text in the message field.
- **Approved FAQ Prompt Chips**:
  - Admin can add, reorder, or delete suggested prompt chips displayed above the input.
  - Examples:
    * *"What are your Minimum Order Quantities (MOQs)?"*
    * *"How do I request a Certificate of Analysis (COA)?"*
    * *"How do I apply for wholesale buyer verification?"*
    * *"What botanical extracts and active ingredients are available?"*

### Section 4: Widget Configuration & Embed Snippet
- **Launcher Position**: Bottom-right (default) or Bottom-left.
- **Theme Color Palette**: Forest Green (`#0f4a3e`) primary, Teal (`#17705d`) hover.
- **Auto-Open Greeting**: Toggle whether the greeting pops up on first visit.
- **Embed Snippet**: Copyable HTML `<script>` tag for external websites.
- **Live Preview Box**: Interactive preview of the widget launcher and opened dialogue.

### Section 5: Chat & API Security Settings
- **Rate Limiting**: Configurable maximum inquiries per minute per IP/Session (default: 20).
- **Private Catalog Protection**: Hard requirement. When enabled, the assistant detects unverified guests asking for wholesale prices and answers:
  > *"Wholesale tier pricing and bulk contracts are reserved for verified business members. Please sign in or register your wholesale account to view commercial rates."*
- **Authentication Gating**: Option to allow all visitors (with pricing protection) or restrict strictly to logged-in members.
- **Prompt Injection Defense**: Pre-prompt system instruction isolating the model within commercial botanical domain boundaries.

### Section 6: Conversation & Inquiry Logs
- Displays logs using Bagisto's native table styling.
- Columns: Date/Time, Visitor Type (Guest / Verified Buyer), Inquiry, Assistant Response, Model Used, Action (View / Delete).
- Purge Button: One-click clear logs action with confirmation modal.

---

## 5. Customer-Facing Widget Specification

- **Visible Title**: **`Assistant`**
- **Aesthetic**: Minimalist botanical styling matching the `purnaturek` child theme.
- **Behavior**:
  - Floating circular trigger button with botanical chat icon at bottom-right.
  - Smooth expandable chat panel (380px wide × 560px high).
  - Displays greeting message and approved FAQ chips.
  - Typing indicator and streaming/instant response bubble.
  - Preserves session chat history during page navigation.

---

## 6. Data Persistence Strategy

- **Configuration Settings**: Persisted in Bagisto's existing `core_config` table via `CoreConfigRepository`. No new migrations required for settings.
- **Conversation Logs**: Leverages Bagisto / Laravel AI's native `agent_conversations` and `agent_conversation_messages` tables with `agent = 'ai_assistant'`.
- **Zero Destructive Changes**: No alterations to `vendor/`, `packages/Webkul/`, or existing production databases.

