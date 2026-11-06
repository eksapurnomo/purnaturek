<?php

use App\Http\Controllers\Admin\BuyerVerificationController as AdminBuyerVerificationController;
use App\Http\Controllers\Admin\CompanySupplyController as AdminCompanySupplyController;
use App\Http\Controllers\Admin\ContractTemplateController as AdminContractTemplateController;
use App\Http\Controllers\Admin\MemberContractController as AdminMemberContractController;
use App\Http\Controllers\Admin\MemberConversationController as AdminMemberConversationController;
use App\Http\Controllers\Admin\ProductDocumentController as AdminProductDocumentController;
use App\Http\Controllers\Admin\WholesalePartnershipController as AdminWholesalePartnershipController;
use App\Http\Controllers\WholesaleBuyerVerificationController;
use App\Http\Controllers\WholesaleCompanySupplyController;
use App\Http\Controllers\WholesaleContractController;
use App\Http\Controllers\WholesaleConversationController;
use App\Http\Controllers\WholesalePartnershipController;
use App\Http\Controllers\WholesaleProductDocumentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PurNatureK Members Portal — Member Routes
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['web', 'theme', 'locale', 'currency', 'customer']], function () {
    Route::prefix('customer/account')->group(function () {
        // Buyer Verification Application & Status
        Route::get('buyer-verification', [WholesaleBuyerVerificationController::class, 'index'])
            ->name('shop.customers.account.buyer_verification.index');

        Route::post('buyer-verification', [WholesaleBuyerVerificationController::class, 'store'])
            ->name('shop.customers.account.buyer_verification.store');

        // Member Uploaded Business Documents
        Route::get('business-documents', [WholesaleBuyerVerificationController::class, 'documents'])
            ->name('shop.customers.account.business_documents.index');

        Route::get('business-documents/{id}/download', [WholesaleBuyerVerificationController::class, 'downloadDocument'])
            ->name('shop.customers.account.business_documents.download');

        // Product Documentation Access (Published Documents)
        Route::get('products/{productId}/documents/{documentId}/download', [WholesaleProductDocumentController::class, 'download'])
            ->name('shop.customers.account.product_documents.download');

        Route::get('products/{productId}/documents/{documentId}/preview', [WholesaleProductDocumentController::class, 'preview'])
            ->name('shop.customers.account.product_documents.preview');

        // Member Internal Messaging (Module 2.6)
        Route::get('conversations', [WholesaleConversationController::class, 'index'])
            ->name('shop.customers.account.conversations.index');

        Route::get('conversations/create', [WholesaleConversationController::class, 'create'])
            ->name('shop.customers.account.conversations.create');

        Route::post('conversations/create', [WholesaleConversationController::class, 'store'])
            ->name('shop.customers.account.conversations.store');

        Route::get('conversations/{id}', [WholesaleConversationController::class, 'view'])
            ->name('shop.customers.account.conversations.view');

        Route::post('conversations/{id}/reply', [WholesaleConversationController::class, 'reply'])
            ->name('shop.customers.account.conversations.reply');

        // Contracts & Commercial Agreements (Module 2.7)
        Route::get('contracts', [WholesaleContractController::class, 'index'])
            ->name('shop.customers.account.contracts.index');

        Route::get('contracts/templates', [WholesaleContractController::class, 'templates'])
            ->name('shop.customers.account.contracts.templates');

        Route::get('contracts/templates/{id}/download', [WholesaleContractController::class, 'downloadTemplate'])
            ->name('shop.customers.account.contracts.templates.download');

        Route::get('contracts/{id}', [WholesaleContractController::class, 'view'])
            ->name('shop.customers.account.contracts.view');

        Route::post('contracts/{id}/action', [WholesaleContractController::class, 'action'])
            ->name('shop.customers.account.contracts.action');

        Route::get('contracts/{id}/download', [WholesaleContractController::class, 'download'])
            ->name('shop.customers.account.contracts.download');

        // Company & Supply Information (Module 2.3)
        Route::get('company-supply', [WholesaleCompanySupplyController::class, 'index'])
            ->name('shop.customers.account.company_supply.index');

        // Wholesale Partnership Information (Module 2.4)
        Route::get('partnership-information', [WholesalePartnershipController::class, 'index'])
            ->name('shop.customers.account.partnership_information.index');
    });
});

/*
|--------------------------------------------------------------------------
| PurNatureK Members Portal — Admin Management Routes
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['web', 'admin']], function () {
    // Buyer Verification Management
    Route::prefix('admin/b2b/buyer-verifications')->group(function () {
        Route::get('', [AdminBuyerVerificationController::class, 'index'])
            ->name('admin.b2b.buyer_verifications.index');

        Route::get('{id}', [AdminBuyerVerificationController::class, 'view'])
            ->name('admin.b2b.buyer_verifications.view');

        Route::post('{id}/approve', [AdminBuyerVerificationController::class, 'approve'])
            ->name('admin.b2b.buyer_verifications.approve');

        Route::post('{id}/reject', [AdminBuyerVerificationController::class, 'reject'])
            ->name('admin.b2b.buyer_verifications.reject');

        Route::get('documents/{id}/download', [AdminBuyerVerificationController::class, 'downloadDocument'])
            ->name('admin.b2b.buyer_verifications.download_document');
    });

    // Member Message Inbox Management (Module 2.6)
    Route::prefix('admin/b2b/conversations')->group(function () {
        Route::get('', [AdminMemberConversationController::class, 'index'])
            ->name('admin.b2b.conversations.index');

        Route::get('{id}', [AdminMemberConversationController::class, 'view'])
            ->name('admin.b2b.conversations.view');

        Route::post('{id}/reply', [AdminMemberConversationController::class, 'reply'])
            ->name('admin.b2b.conversations.reply');

        Route::post('{id}/status', [AdminMemberConversationController::class, 'updateStatus'])
            ->name('admin.b2b.conversations.status');
    });

    // Product Documentation Management
    Route::prefix('admin/catalog/product-documents')->group(function () {
        Route::get('', [AdminProductDocumentController::class, 'index'])
            ->name('admin.catalog.product_documents.index');

        Route::get('create', [AdminProductDocumentController::class, 'create'])
            ->name('admin.catalog.product_documents.create');

        Route::post('create', [AdminProductDocumentController::class, 'store'])
            ->name('admin.catalog.product_documents.store');

        Route::get('{id}/edit', [AdminProductDocumentController::class, 'edit'])
            ->name('admin.catalog.product_documents.edit');

        Route::post('{id}/edit', [AdminProductDocumentController::class, 'update'])
            ->name('admin.catalog.product_documents.update');

        Route::post('{id}/delete', [AdminProductDocumentController::class, 'destroy'])
            ->name('admin.catalog.product_documents.delete');

        Route::get('{id}/download', [AdminProductDocumentController::class, 'download'])
            ->name('admin.catalog.product_documents.download');

        Route::get('{id}/preview', [AdminProductDocumentController::class, 'preview'])
            ->name('admin.catalog.product_documents.preview');
    });

    // Contract Templates Management (Module 2.7)
    Route::prefix('admin/b2b/contract-templates')->group(function () {
        Route::get('', [AdminContractTemplateController::class, 'index'])
            ->name('admin.b2b.contract-templates.index');

        Route::get('create', [AdminContractTemplateController::class, 'create'])
            ->name('admin.b2b.contract-templates.create');

        Route::post('create', [AdminContractTemplateController::class, 'store'])
            ->name('admin.b2b.contract-templates.store');

        Route::get('{id}/edit', [AdminContractTemplateController::class, 'edit'])
            ->name('admin.b2b.contract-templates.edit');

        Route::post('{id}/edit', [AdminContractTemplateController::class, 'update'])
            ->name('admin.b2b.contract-templates.update');

        Route::post('{id}/delete', [AdminContractTemplateController::class, 'destroy'])
            ->name('admin.b2b.contract-templates.delete');

        Route::get('{id}/download', [AdminContractTemplateController::class, 'download'])
            ->name('admin.b2b.contract-templates.download');
    });

    // Member Contracts & Agreements Management (Module 2.7)
    Route::prefix('admin/b2b/member-contracts')->group(function () {
        Route::get('', [AdminMemberContractController::class, 'index'])
            ->name('admin.b2b.member-contracts.index');

        Route::get('create', [AdminMemberContractController::class, 'create'])
            ->name('admin.b2b.member-contracts.create');

        Route::post('create', [AdminMemberContractController::class, 'store'])
            ->name('admin.b2b.member-contracts.store');

        Route::get('{id}', [AdminMemberContractController::class, 'view'])
            ->name('admin.b2b.member-contracts.view');

        Route::post('{id}/send', [AdminMemberContractController::class, 'send'])
            ->name('admin.b2b.member-contracts.send');

        Route::post('{id}/cancel', [AdminMemberContractController::class, 'cancel'])
            ->name('admin.b2b.member-contracts.cancel');

        Route::get('{id}/download', [AdminMemberContractController::class, 'download'])
            ->name('admin.b2b.member-contracts.download');
    });

    // Company & Supply Information Management (Module 2.3)
    Route::prefix('admin/b2b/company-supply')->group(function () {
        Route::get('', [AdminCompanySupplyController::class, 'edit'])
            ->name('admin.b2b.company_supply.edit');

        Route::post('', [AdminCompanySupplyController::class, 'update'])
            ->name('admin.b2b.company_supply.update');

        Route::get('preview', [AdminCompanySupplyController::class, 'preview'])
            ->name('admin.b2b.company_supply.preview');
    });

    // Wholesale Partnership Information Management (Module 2.4)
    Route::prefix('admin/b2b/partnership-information')->group(function () {
        Route::get('', [AdminWholesalePartnershipController::class, 'edit'])
            ->name('admin.b2b.partnership_information.edit');

        Route::post('', [AdminWholesalePartnershipController::class, 'update'])
            ->name('admin.b2b.partnership_information.update');

        Route::get('preview', [AdminWholesalePartnershipController::class, 'preview'])
            ->name('admin.b2b.partnership_information.preview');
    });
});
