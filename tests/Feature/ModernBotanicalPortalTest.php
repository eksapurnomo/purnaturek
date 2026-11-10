<?php

use App\Models\BuyerVerification;
use App\Models\BuyerVerificationDocument;
use App\Models\MemberContract;
use App\Models\MemberConversation;
use App\Support\BuyerVerificationManager;
use App\Support\ContractAgreementManager;
use App\Support\MemberConversationManager;
use App\Support\WholesalePricingManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Webkul\Core\Models\Channel;
use Webkul\Core\Models\CoreConfig;
use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;
use Webkul\User\Models\Admin;

uses(Tests\TestCase::class);

function makePortalCustomer(array $overrides = []): Customer
{
    return Customer::create(array_merge([
        'first_name'        => 'Elena',
        'last_name'         => 'Botanist',
        'email'             => 'member.' . Str::uuid() . '@example.test',
        'password'          => Hash::make('Password123!'),
        'is_verified'       => 1,
        'status'            => 1,
        'channel_id'        => 1,
        'customer_group_id' => 2,
    ], $overrides));
}

test('guest public routes render Modern Botanical pages with approved brand identity and zero forbidden copy', function () {
    $homeResponse = $this->get(route('shop.home.index'));
    $homeResponse->assertOk();
    $homeResponse->assertSee('PurNatureK', false);
    $homeResponse->assertSee('wholesale@purnaturek.com', false);
    $homeResponse->assertSee('Purnature Khatulistiwa LLC', false);
    $homeResponse->assertSee('505 N Lake Shore Dr', false);
    $homeResponse->assertSee('logo-purnaturek-on-dark.webp', false);
    $homeResponse->assertSee('theme.css?v=', false);
    $homeResponse->assertDontSee('wholesale@purnaturekbulk.com', false);
    $homeResponse->assertDontSee('wholesales@purnaturek.com', false);
    $homeResponse->assertDontSee('supplement manufacturers', false);
    $homeResponse->assertDontSee('manufacturing facility details', false);

    $this->get(route('shop.customer.session.index'))
        ->assertOk()
        ->assertSee('pk-auth-shell', false)
        ->assertSee('Sign In', false);

    $this->get(route('shop.customers.register.index'))
        ->assertOk()
        ->assertSee('pk-auth-shell', false);

    $this->get(route('shop.customers.register.index', ['type' => 'company']))
        ->assertOk()
        ->assertSee('pk-auth-shell', false);

    $this->get(route('shop.customers.forgot_password.create'))
        ->assertOk()
        ->assertSee('pk-auth-shell', false);
});

test('guests are blocked from private member portal routes and storefront product APIs', function () {
    $privateRoutes = [
        route('shop.customers.account.index'),
        route('shop.customers.account.profile.index'),
        route('shop.customers.account.buyer_verification.index'),
        route('shop.customers.account.business_documents.index'),
        route('shop.customers.account.contracts.index'),
        route('shop.customers.account.contracts.templates'),
        route('shop.customers.account.conversations.index'),
        route('shop.customers.account.conversations.create'),
        route('shop.customers.account.company_supply.index'),
        route('shop.customers.account.partnership_information.index'),
    ];

    foreach ($privateRoutes as $url) {
        $this->get($url)->assertRedirect(route('shop.customer.session.index'));
    }

    if ($product = Product::first()) {
        $this->get('/' . $product->url_key)->assertRedirect(route('shop.customer.session.index'));
    }

    $this->getJson(route('shop.api.products.index'))->assertStatus(401);
});

test('authenticated members can view all modernized workspace, catalog, service, and cart views', function () {
    $customer = makePortalCustomer();

    $memberUrls = [
        route('shop.home.index'),
        route('shop.customers.account.index'),
        route('shop.customers.account.profile.index'),
        route('shop.customers.account.buyer_verification.index'),
        route('shop.customers.account.business_documents.index'),
        route('shop.customers.account.contracts.index'),
        route('shop.customers.account.contracts.templates'),
        route('shop.customers.account.conversations.index'),
        route('shop.customers.account.conversations.create'),
        route('shop.customers.account.company_supply.index'),
        route('shop.customers.account.partnership_information.index'),
        route('shop.checkout.cart.index'),
    ];

    foreach ($memberUrls as $url) {
        $response = $this->actingAs($customer, 'customer')->get($url);
        $response->assertOk();
        $response->assertDontSee('wholesale@purnaturekbulk.com', false);
        $response->assertDontSee('wholesales@purnaturek.com', false);
        $response->assertDontSee('supplement manufacturers', false);
        $response->assertDontSee('manufacturing facility details', false);
        $response->assertDontSee('shopTheme::', false);
    }

    $accountResponse = $this->actingAs($customer, 'customer')->get(route('shop.customers.account.index'));
    $accountResponse->assertSee('pk-mobile-drawer', false);
    $accountResponse->assertSee('pk-shortcut-card', false);

    $this->actingAs($customer, 'customer')
        ->get(route('shop.customers.account.buyer_verification.index'))
        ->assertSee('pk-stepper', false);

    if ($product = Product::first()) {
        $pdpResponse = $this->actingAs($customer, 'customer')->get('/' . $product->url_key);
        $pdpResponse->assertOk();
        $pdpResponse->assertSee('pk-product__hero', false);
        $pdpResponse->assertSee('pk_recent_' . $customer->id, false);
    }
});

test('tier 1 unverified members are blocked from verified-buyer transaction routes while tier 2 approved members pass', function () {
    $tier1Customer = makePortalCustomer();

    // Unverified member redirected to buyer verification on web GET and receives 403 BUYER_VERIFICATION_REQUIRED on JSON
    $this->actingAs($tier1Customer, 'customer')
        ->get(route('shop.checkout.onepage.index'))
        ->assertRedirect(route('shop.customers.account.buyer_verification.index'));

    $this->actingAs($tier1Customer, 'customer')
        ->getJson(route('shop.checkout.onepage.index'))
        ->assertStatus(403)
        ->assertJsonPath('code', 'BUYER_VERIFICATION_REQUIRED');

    // Approve Tier 2 status
    BuyerVerification::create([
        'customer_id'      => $tier1Customer->id,
        'company_name'     => 'Verified Botanical Importers LLC',
        'tax_id'           => 'US-EIN-99-1122334',
        'business_type'    => 'distributor',
        'business_phone'   => '+1 312 555 0100',
        'business_address' => '100 Trade Plaza, Chicago, IL 60611',
        'status'           => BuyerVerification::STATUS_APPROVED,
        'submitted_at'     => now()->subDay(),
        'reviewed_at'      => now(),
    ]);

    // Tier 2 approved member passes EnsureVerifiedBuyer middleware (no longer sent to buyer-verification; OnepageController redirects empty cart to cart)
    $this->actingAs($tier1Customer, 'customer')
        ->get(route('shop.checkout.onepage.index'))
        ->assertRedirect(route('shop.checkout.cart.index'));
});

test('cross-customer ownership isolation blocks unauthorized access to contracts, conversations, and credentials', function () {
    Storage::fake('private');

    $owner = makePortalCustomer();
    $intruder = makePortalCustomer();

    // Create owner's conversation via manager
    $conversation = MemberConversationManager::createConversation($owner, [
        'subject'  => 'Private Owner Lot Inquiry',
        'category' => MemberConversation::CATEGORY_PRODUCT_INQUIRY,
        'message'  => 'Confirming COA availability for Lot #2026-01.',
    ]);

    // Create owner's contract record
    Storage::disk('private')->put('contracts/individual/test-owner-msa.pdf', '%PDF-1.4 test');
    $contract = MemberContract::create([
        'reference_number'  => 'PK-CNT-' . strtoupper(Str::random(6)),
        'title'             => 'Confidential Wholesale Supply Agreement',
        'customer_id'       => $owner->id,
        'original_filename' => 'test-owner-msa.pdf',
        'storage_path'      => 'contracts/individual/test-owner-msa.pdf',
        'mime_type'         => 'application/pdf',
        'file_size'         => 13,
        'file_hash'         => hash('sha256', '%PDF-1.4 test'),
        'status'            => MemberContract::STATUS_SENT,
        'issued_date'       => now()->toDateString(),
        'sent_at'           => now(),
    ]);

    // Create owner's verification document
    $verification = BuyerVerification::create([
        'customer_id'      => $owner->id,
        'company_name'     => 'Owner Entity LLC',
        'tax_id'           => 'EIN-77-8889999',
        'business_type'    => 'distributor',
        'business_phone'   => '+1 312 555 0144',
        'business_address' => '200 Market St, Chicago, IL 60611',
        'status'           => BuyerVerification::STATUS_PENDING,
        'submitted_at'     => now(),
    ]);

    Storage::disk('private')->put('buyer-verifications/doc-secret.pdf', '%PDF-1.4 secret');
    $doc = BuyerVerificationDocument::create([
        'buyer_verification_id' => $verification->id,
        'document_type'         => BuyerVerificationDocument::TYPE_BUSINESS_LICENSE,
        'original_filename'     => 'license.pdf',
        'storage_path'          => 'buyer-verifications/doc-secret.pdf',
        'mime_type'             => 'application/pdf',
        'file_size'             => 15,
    ]);

    // Owner can access their own conversation, contract view, and contract acknowledgment
    $this->actingAs($owner, 'customer')
        ->get(route('shop.customers.account.conversations.view', $conversation->id))
        ->assertOk()
        ->assertSee('Private Owner Lot Inquiry', false);

    $this->actingAs($owner, 'customer')
        ->get(route('shop.customers.account.contracts.view', $contract->id))
        ->assertOk()
        ->assertSee($contract->reference_number, false);

    // Intruder cannot read or mutate owner's records
    $this->actingAs($intruder, 'customer')
        ->get(route('shop.customers.account.conversations.view', $conversation->id))
        ->assertNotFound();

    $this->actingAs($intruder, 'customer')
        ->post(route('shop.customers.account.conversations.reply', $conversation->id), [
            'message' => 'Intruder attempt',
        ])
        ->assertNotFound();

    $this->actingAs($intruder, 'customer')
        ->get(route('shop.customers.account.contracts.view', $contract->id))
        ->assertNotFound();

    $this->actingAs($intruder, 'customer')
        ->post(route('shop.customers.account.contracts.action', $contract->id), [
            'action' => 'accept',
        ])
        ->assertNotFound();

    $this->actingAs($intruder, 'customer')
        ->get(route('shop.customers.account.contracts.download', $contract->id))
        ->assertNotFound();

    $this->actingAs($intruder, 'customer')
        ->get(route('shop.customers.account.business_documents.download', $doc->id))
        ->assertForbidden();
});

test('contract acknowledgment workflow and wholesale pricing model b gating work as specified', function () {
    Storage::fake('private');
    $owner = makePortalCustomer();

    Storage::disk('private')->put('contracts/individual/happy-msa.pdf', '%PDF-1.4 happy');
    $contract = MemberContract::create([
        'reference_number'  => 'PK-CNT-' . strtoupper(Str::random(6)),
        'title'             => 'Master Supply Agreement 2026',
        'customer_id'       => $owner->id,
        'original_filename' => 'happy-msa.pdf',
        'storage_path'      => 'contracts/individual/happy-msa.pdf',
        'mime_type'         => 'application/pdf',
        'file_size'         => 14,
        'file_hash'         => hash('sha256', '%PDF-1.4 happy'),
        'status'            => MemberContract::STATUS_SENT,
        'issued_date'       => now()->toDateString(),
        'sent_at'           => now(),
    ]);

    $this->actingAs($owner, 'customer')
        ->post(route('shop.customers.account.contracts.action', $contract->id), [
            'action' => 'accept',
            'notes'  => 'Approved for Q4 wholesale allocation.',
        ])
        ->assertRedirect(route('shop.customers.account.contracts.view', $contract->id));

    expect($contract->fresh()->status)->toBe(MemberContract::STATUS_ACCEPTED);

    // Model B wholesale pricing masking guarantees no raw range/cost leak in Contact mode
    $mockContactProduct = (object) [
        'price_display_mode'  => WholesalePricingManager::MODE_CONTACT,
        'wholesale_min_price' => 120.00,
        'wholesale_max_price' => 240.00,
        'wholesale_moq'       => 25,
        'wholesale_unit'      => 'kg',
        'supply_availability' => 'Available',
    ];

    $formatted = WholesalePricingManager::formatForMember($mockContactProduct);
    expect($formatted['is_contact'])->toBeTrue()
        ->and($formatted['is_range'])->toBeFalse()
        ->and($formatted['min_price_formatted'])->toBeNull()
        ->and($formatted['max_price_formatted'])->toBeNull()
        ->and($formatted['price_display'])->toBe('Contact for Price')
        ->and($formatted['moq_display'])->toBe('25 kg');

    // Model A product (if seeded) passes quotation check cleanly; EnsureQuotationForCheckout contract verified
    if ($product = Product::first()) {
        $violations = WholesalePricingManager::quotationViolations([
            [
                'product_id'   => $product->id,
                'name'         => $product->name,
                'qty'          => 1,
                'base_price'   => 10,
                'custom_price' => null,
                'additional'   => [],
            ],
        ], $owner->id);

        if (! WholesalePricingManager::usesWholesalePricing($product)) {
            expect($violations)->toBe([]);
        }
    }
});

test('ai assistant public config endpoint and pricing gating behave consistently', function () {
    $coreConfigRepository = app(CoreConfigRepository::class);
    $channelCode = core()->getRequestedChannelCode() ?: 'default';
    $localeCode = core()->getRequestedLocaleCode() ?: 'en';

    $coreConfigRepository->updateOrCreate(
        [
            'code'         => 'ai_assistant.general.status',
            'channel_code' => $channelCode,
            'locale_code'  => $localeCode,
        ],
        ['value' => '1']
    );

    $this->getJson(route('api.ai_assistant.config'))
        ->assertOk()
        ->assertJsonStructure([
            'enabled',
            'title',
            'welcome_message',
            'placeholder',
            'position',
            'theme_color',
            'auto_open',
            'faqs',
        ])
        ->assertJsonPath('enabled', true)
        ->assertJsonPath('title', 'Assistant');

    // Guest pricing question triggers Model B private pricing gate cleanly
    $this->postJson(route('api.ai_assistant.chat'), [
        'message' => 'What is your wholesale pricing rate for 100kg?',
    ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('gated', true);

    // Disabled config check via repository so Bagisto CacheGeneration bumps cleanly
    $coreConfigRepository->updateOrCreate(
        [
            'code'         => 'ai_assistant.general.status',
            'channel_code' => $channelCode,
            'locale_code'  => $localeCode,
        ],
        ['value' => '0']
    );

    $this->getJson(route('api.ai_assistant.config'))
        ->assertOk()
        ->assertJsonPath('enabled', false);

    $this->postJson(route('api.ai_assistant.chat'), [
        'message' => 'Hello',
    ])
        ->assertStatus(503);

    // Restore enabled status via repository before transaction cleanup
    $coreConfigRepository->updateOrCreate(
        [
            'code'         => 'ai_assistant.general.status',
            'channel_code' => $channelCode,
            'locale_code'  => $localeCode,
        ],
        ['value' => '1']
    );
});

test('footer renders configured copyright when set and PurNatureK fallback when empty across portal and auth pages', function () {
    config(['responsecache.enabled' => false]);

    $channelCode = core()->getRequestedChannelCode() ?: 'default';
    $localeCode = core()->getRequestedLocaleCode() ?: 'en';
    $member = makePortalCustomer();

    CoreConfig::updateOrCreate(
        [
            'code'         => 'general.content.footer.copyright_content',
            'channel_code' => $channelCode,
            'locale_code'  => $localeCode,
        ],
        ['value' => 'Copyright Commercial Audit Ltd. All rights reserved.']
    );
    Cache::flush();

    $guestUrls = [
        route('shop.home.index'),
        route('shop.customer.session.index'),
        route('shop.customers.register.index'),
        route('shop.customers.forgot_password.create'),
        route('shop.customers.reset_password.create', 'test-reset-token'),
    ];

    foreach ($guestUrls as $url) {
        $this->get($url)
            ->assertOk()
            ->assertSee('Copyright Commercial Audit Ltd. All rights reserved.', false)
            ->assertDontSee('Purnature Khatulistiwa LLC. All rights reserved.', false);
    }

    $accountUrls = [
        route('shop.customers.account.index'),
        route('shop.customers.account.profile.index'),
        route('shop.customers.account.contracts.index'),
        route('shop.customers.account.conversations.index'),
    ];

    foreach ($accountUrls as $accountUrl) {
        $this->actingAs($member, 'customer')
            ->get($accountUrl)
            ->assertOk()
            ->assertSee('Copyright Commercial Audit Ltd. All rights reserved.', false);
    }

    auth()->guard('customer')->logout();

    CoreConfig::updateOrCreate(
        [
            'code'         => 'general.content.footer.copyright_content',
            'channel_code' => $channelCode,
            'locale_code'  => $localeCode,
        ],
        ['value' => '']
    );
    Cache::flush();

    $expectedFallback = '&copy; ' . date('Y') . ' Purnature Khatulistiwa LLC. All rights reserved.';

    foreach ($guestUrls as $url) {
        $this->get($url)
            ->assertOk()
            ->assertSee($expectedFallback, false)
            ->assertDontSee('Copyright Commercial Audit Ltd. All rights reserved.', false);
    }

    foreach ($accountUrls as $accountUrl) {
        $this->actingAs($member, 'customer')
            ->get($accountUrl)
            ->assertOk()
            ->assertSee($expectedFallback, false);
    }

    auth()->guard('customer')->logout();
});

test('buyer verification submission, validation, rejection resubmit, and approval work cleanly without request memoization', function () {
    Mail::fake();
    Storage::fake('private');

    $member = makePortalCustomer();
    $admin = Admin::first() ?? Admin::factory()->create();

    expect(BuyerVerificationManager::isVerifiedBuyer($member))->toBeFalse()
        ->and(BuyerVerificationManager::getVerificationStatus($member))->toBe('unverified')
        ->and(BuyerVerificationManager::canSubmitApplication($member))->toBeTrue();

    // Missing required credentials/documents triggers validation redirect
    $this->actingAs($member, 'customer')
        ->from(route('shop.customers.account.buyer_verification.index'))
        ->post(route('shop.customers.account.buyer_verification.store'), [])
        ->assertRedirect(route('shop.customers.account.buyer_verification.index'))
        ->assertSessionHasErrors(['company_name', 'tax_id', 'business_type', 'business_phone', 'business_address', 'documents']);

    // Submit valid verification application
    $licenseFile = UploadedFile::fake()->create('state-license.pdf', 120, 'application/pdf');

    $this->actingAs($member, 'customer')
        ->post(route('shop.customers.account.buyer_verification.store'), [
            'company_name'     => 'Midwest Botanical Supply LLC',
            'tax_id'           => 'EIN-36-7654321',
            'business_type'    => 'distributor',
            'business_phone'   => '+1 312 555 0199',
            'business_address' => '505 N Lake Shore Dr, Chicago, IL 60611',
            'documents'        => [$licenseFile],
            'document_types'   => [BuyerVerificationDocument::TYPE_BUSINESS_LICENSE],
        ])
        ->assertRedirect(route('shop.customers.account.buyer_verification.index'))
        ->assertSessionHas('success');

    expect(BuyerVerificationManager::getVerificationStatus($member))->toBe(BuyerVerification::STATUS_PENDING)
        ->and(BuyerVerificationManager::isVerifiedBuyer($member))->toBeFalse()
        ->and(BuyerVerificationManager::canSubmitApplication($member))->toBeFalse();

    $verification = BuyerVerificationManager::getLatestVerification($member);
    $doc = $verification?->documents()->first();
    expect($verification)->not->toBeNull()
        ->and($doc)->not->toBeNull();

    $this->actingAs($member, 'customer')
        ->get(route('shop.customers.account.buyer_verification.index'))
        ->assertOk()
        ->assertSee('Midwest Botanical Supply LLC', false)
        ->assertSee('Pending', false);

    $this->actingAs($member, 'customer')
        ->get(route('shop.customers.account.business_documents.index'))
        ->assertOk()
        ->assertSee('state-license.pdf', false);

    $this->actingAs($member, 'customer')
        ->get(route('shop.customers.account.business_documents.download', $doc->id))
        ->assertOk();

    // Admin rejects -> immediately eligible to resubmit without manual cache clearing
    $verification = BuyerVerificationManager::rejectApplication($verification, $admin, 'Please provide current fiscal year certificate.');
    expect(BuyerVerificationManager::getVerificationStatus($member))->toBe(BuyerVerification::STATUS_REJECTED)
        ->and(BuyerVerificationManager::canSubmitApplication($member))->toBeTrue();

    // Member resubmits and admin approves -> immediately unlocks Tier 2 routes without manual cache clearing
    $resubmitFile = UploadedFile::fake()->create('updated-resale.pdf', 90, 'application/pdf');
    $this->actingAs($member, 'customer')
        ->post(route('shop.customers.account.buyer_verification.store'), [
            'company_name'     => 'Midwest Botanical Supply LLC',
            'tax_id'           => 'EIN-36-7654321',
            'business_type'    => 'distributor',
            'business_phone'   => '+1 312 555 0199',
            'business_address' => '505 N Lake Shore Dr, Chicago, IL 60611',
            'documents'        => [$resubmitFile],
            'document_types'   => [BuyerVerificationDocument::TYPE_RESALE_PERMIT],
        ])
        ->assertRedirect(route('shop.customers.account.buyer_verification.index'));

    $latest = BuyerVerificationManager::getLatestVerification($member);
    expect($latest->id)->not->toBe($verification->id);

    BuyerVerificationManager::approveApplication($latest, $admin);
    expect(BuyerVerificationManager::isVerifiedBuyer($member))->toBeTrue()
        ->and(BuyerVerificationManager::getVerificationStatus($member))->toBe(BuyerVerification::STATUS_APPROVED);

    $this->actingAs($member, 'customer')
        ->get(route('shop.checkout.onepage.index'))
        ->assertRedirect(route('shop.checkout.cart.index'));
});

test('message admin conversation flow tracks unread counts across admin reply and member view cleanly', function () {
    Mail::fake();

    $member = makePortalCustomer();
    $admin = Admin::first() ?? Admin::factory()->create();

    expect(MemberConversationManager::getUnreadCountForMember($member))->toBe(0);

    // Member creates conversation through HTTP controller
    $createResponse = $this->actingAs($member, 'customer')
        ->post(route('shop.customers.account.conversations.store'), [
            'subject'  => 'Lot 2026-Q4 Spec Sheet & COA Request',
            'category' => MemberConversation::CATEGORY_PRODUCT_DOCUMENTATION,
            'message'  => 'Requesting heavy metals COA for Red Bali standardized powder.',
        ]);

    $conversation = MemberConversation::where('customer_id', $member->id)->latest('id')->first();
    expect($conversation)->not->toBeNull();
    $createResponse->assertRedirect(route('shop.customers.account.conversations.view', $conversation->id));
    expect(MemberConversationManager::getUnreadCountForMember($member))->toBe(0);

    // Admin views (marks read) and replies -> member unread count immediately updates in workspace, index, and manager
    MemberConversationManager::markAsReadByAdmin($conversation);
    expect($conversation->fresh()->unread_for_admin_count)->toBe(0);

    MemberConversationManager::replyAsAdmin($conversation->fresh(), $admin, 'COA packet uploaded and attached to lot documentation.');

    expect(MemberConversationManager::getUnreadCountForMember($member))->toBe(1);

    $this->actingAs($member, 'customer')
        ->get(route('shop.customers.account.index'))
        ->assertOk()
        ->assertSee('1 unread', false);

    $this->actingAs($member, 'customer')
        ->get(route('shop.customers.account.conversations.index'))
        ->assertOk()
        ->assertSee('1 Unread Reply', false)
        ->assertSee('Lot 2026-Q4 Spec Sheet &amp; COA Request', false);

    // Member opens thread -> unread count clears to 0 immediately
    $this->actingAs($member, 'customer')
        ->get(route('shop.customers.account.conversations.view', $conversation->id))
        ->assertOk()
        ->assertSee('COA packet uploaded and attached to lot documentation.', false);

    expect(MemberConversationManager::getUnreadCountForMember($member))->toBe(0);

    // Member posts follow-up reply -> status updates back to waiting_admin
    $this->actingAs($member, 'customer')
        ->post(route('shop.customers.account.conversations.reply', $conversation->id), [
            'message' => 'Confirmed receipt of the COA. Thank you.',
        ])
        ->assertRedirect(route('shop.customers.account.conversations.view', $conversation->id));

    expect($conversation->fresh()->status)->toBe(MemberConversation::STATUS_WAITING_ADMIN)
        ->and($conversation->fresh()->unread_for_admin_count)->toBe(1);
});

test('contracts pending counters, templates library, reject action, and download reflect live database state', function () {
    Mail::fake();
    Storage::fake('private');

    $owner = makePortalCustomer();

    $this->actingAs($owner, 'customer')
        ->get(route('shop.customers.account.contracts.templates'))
        ->assertOk();

    Storage::disk('private')->put('contracts/individual/contract-a.pdf', '%PDF-1.4 contract A');
    Storage::disk('private')->put('contracts/individual/contract-b.pdf', '%PDF-1.4 contract B');

    $contractA = MemberContract::create([
        'reference_number'  => 'PK-CNT-' . strtoupper(Str::random(6)),
        'title'             => 'Regional Distribution Addendum A',
        'customer_id'       => $owner->id,
        'original_filename' => 'contract-a.pdf',
        'storage_path'      => 'contracts/individual/contract-a.pdf',
        'mime_type'         => 'application/pdf',
        'file_size'         => 19,
        'file_hash'         => hash('sha256', '%PDF-1.4 contract A'),
        'status'            => MemberContract::STATUS_SENT,
        'issued_date'       => now()->toDateString(),
        'sent_at'           => now(),
    ]);

    $contractB = MemberContract::create([
        'reference_number'  => 'PK-CNT-' . strtoupper(Str::random(6)),
        'title'             => 'Packaging Rider Agreement B',
        'customer_id'       => $owner->id,
        'original_filename' => 'contract-b.pdf',
        'storage_path'      => 'contracts/individual/contract-b.pdf',
        'mime_type'         => 'application/pdf',
        'file_size'         => 19,
        'file_hash'         => hash('sha256', '%PDF-1.4 contract B'),
        'status'            => MemberContract::STATUS_SENT,
        'issued_date'       => now()->toDateString(),
        'sent_at'           => now(),
    ]);

    $this->actingAs($owner, 'customer')
        ->get(route('shop.customers.account.index'))
        ->assertOk()
        ->assertSee('2 pending', false);

    $this->actingAs($owner, 'customer')
        ->get(route('shop.customers.account.contracts.index'))
        ->assertOk()
        ->assertSee('Action Required: 2 Agreement(s) Pending Review', false);

    // Reject contract B -> pending counter drops to 1 immediately without manual cache clearing
    $this->actingAs($owner, 'customer')
        ->post(route('shop.customers.account.contracts.action', $contractB->id), [
            'action' => 'reject',
            'notes'  => 'Requesting revised quarterly volume schedule.',
        ])
        ->assertRedirect(route('shop.customers.account.contracts.view', $contractB->id));

    expect($contractB->fresh()->status)->toBe(MemberContract::STATUS_REJECTED);

    $this->actingAs($owner, 'customer')
        ->get(route('shop.customers.account.contracts.index'))
        ->assertOk()
        ->assertSee('Action Required: 1 Agreement(s) Pending Review', false);

    // Download contract A securely
    $downloadResponse = $this->actingAs($owner, 'customer')
        ->get(route('shop.customers.account.contracts.download', $contractA->id));

    $downloadResponse->assertOk();
    expect($downloadResponse->streamedContent())->toBe('%PDF-1.4 contract A');
});

test('group a shop namespace routes and views resolve and render cleanly when default theme is active', function () {
    config(['responsecache.enabled' => false]);

    $channel = core()->getCurrentChannel() ?? Channel::first();
    $originalTheme = $channel->theme ?: 'purnaturek';
    $owner = makePortalCustomer();

    $conversation = MemberConversation::create([
        'customer_id'             => $owner->id,
        'subject'                 => 'Default Theme Fallback Check',
        'category'                => MemberConversation::CATEGORY_GENERAL_INQUIRY,
        'status'                  => MemberConversation::STATUS_WAITING_ADMIN,
        'last_message_at'         => now(),
        'last_sender_type'        => 'customer',
        'unread_for_admin_count'  => 1,
        'unread_for_member_count' => 0,
    ]);

    $contract = MemberContract::create([
        'reference_number'  => 'CT-DEF-' . strtoupper(Str::random(5)),
        'title'             => 'Default Theme Fallback Agreement',
        'customer_id'       => $owner->id,
        'original_filename' => 'default-fallback.pdf',
        'storage_path'      => 'contracts/individual/default-fallback.pdf',
        'mime_type'         => 'application/pdf',
        'file_size'         => 16,
        'file_hash'         => hash('sha256', '%PDF-1.4 default'),
        'status'            => MemberContract::STATUS_SENT,
        'issued_date'       => now()->toDateString(),
        'sent_at'           => now(),
    ]);

    try {
        Channel::where('id', $channel->id)->update(['theme' => 'default']);
        $updatedChannel = Channel::find($channel->id);
        core()->setCurrentChannel($updatedChannel);
        themes()->set('default');

        // Confirm view finder resolves Group A views via legacy fallback directory
        $finder = app('view')->getFinder();
        expect($finder->find('shop::customers.account.company_supply.index'))
            ->toContain('resources/views/vendor/shop/customers/account/company_supply/index.blade.php')
            ->and($finder->find('shop::customers.account.conversations.view'))
            ->toContain('resources/views/vendor/shop/customers/account/conversations/view.blade.php')
            ->and($finder->find('shop::customers.account.contracts.view'))
            ->toContain('resources/views/vendor/shop/customers/account/contracts/view.blade.php');

        // Guest routes render cleanly under default theme
        foreach ([
            route('shop.home.index'),
            route('shop.customer.session.index'),
            route('shop.customers.register.index'),
            route('shop.customers.forgot_password.create'),
            route('shop.customers.reset_password.create', 'default-theme-token'),
        ] as $guestUrl) {
            $this->get($guestUrl)->assertOk();
        }

        // Member & Group A routes render cleanly under default theme
        $memberRoutes = [
            route('shop.customers.account.index'),
            route('shop.customers.account.profile.index'),
            route('shop.customers.account.buyer_verification.index'),
            route('shop.customers.account.business_documents.index'),
            route('shop.customers.account.company_supply.index'),
            route('shop.customers.account.partnership_information.index'),
            route('shop.customers.account.conversations.index'),
            route('shop.customers.account.conversations.create'),
            route('shop.customers.account.conversations.view', $conversation->id),
            route('shop.customers.account.contracts.index'),
            route('shop.customers.account.contracts.templates'),
            route('shop.customers.account.contracts.view', $contract->id),
        ];

        if ($product = Product::first()) {
            $memberRoutes[] = '/' . $product->url_key;
        }

        foreach ($memberRoutes as $url) {
            $this->actingAs($owner, 'customer')
                ->get($url)
                ->assertOk();
        }
    } finally {
        Channel::where('id', $channel->id)->update(['theme' => $originalTheme]);
        core()->setCurrentChannel(Channel::find($channel->id));
        themes()->set($originalTheme);
    }
});

test('admin ai assistant, magic ai, and generic configuration routes resolve without wildcard capture', function () {
    $router = app('router');

    expect($router->getRoutes()->match(\Illuminate\Http\Request::create('/admin/configuration/ai-assistant', 'GET'))->getName())
        ->toBe('admin.ai_assistant.index')
        ->and($router->getRoutes()->match(\Illuminate\Http\Request::create('/admin/configuration/ai-assistant/test-prompt', 'POST'))->getName())
        ->toBe('admin.ai_assistant.test_prompt')
        ->and($router->getRoutes()->match(\Illuminate\Http\Request::create('/admin/configuration/magic_ai/general', 'GET'))->getName())
        ->toBe('admin.configuration.magic_ai')
        ->and($router->getRoutes()->match(\Illuminate\Http\Request::create('/admin/configuration/general/general', 'GET'))->getName())
        ->toBe('admin.configuration.index');

    $admin = Admin::first() ?? Admin::factory()->create();

    $aiResponse = $this->actingAs($admin, 'admin')
        ->get(route('admin.ai_assistant.index'))
        ->assertOk();

    foreach ([
        'Provider & Model',
        'Knowledge Base',
        'Welcome & FAQs',
        'Widget & Embed',
        'Chat & Security',
        'Conversation Logs (',
    ] as $tabLabel) {
        $aiResponse->assertSee($tabLabel, false);
    }

    $this->actingAs($admin, 'admin')
        ->get(route('admin.configuration.magic_ai'))
        ->assertOk();

    $this->actingAs($admin, 'admin')
        ->get('/admin/configuration/general/general')
        ->assertOk();
});
