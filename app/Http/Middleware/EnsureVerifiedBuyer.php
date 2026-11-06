<?php

namespace App\Http\Middleware;

use App\Support\BuyerVerificationManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVerifiedBuyer
{
    /**
     * Handle an incoming request.
     *
     * Protects transaction and commercial endpoints (RFQs, quotations, purchase orders)
     * so that only administrators-approved Verified Wholesale Buyers can initiate transactions.
     * Ordinary browsing, catalog viewing, and login are NEVER blocked.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $customer = auth()->guard('customer')->user();

        if (! $customer) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Authentication required.',
                ], 401);
            }

            return redirect()->route('shop.customer.session.index');
        }

        if (! BuyerVerificationManager::isVerifiedBuyer($customer)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Verified Wholesale Buyer status is required to access official wholesale transactions.',
                    'code'    => 'BUYER_VERIFICATION_REQUIRED',
                ], 403);
            }

            session()->flash('warning', 'Official wholesale transactions and RFQs require Verified Wholesale Buyer status. Please submit your business credentials for verification.');

            return redirect()->route('shop.customers.account.buyer_verification.index');
        }

        return $next($request);
    }
}

