<?php

namespace App\Http\Middleware;

use App\Support\WholesalePricingManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Webkul\Checkout\Facades\Cart;

/**
 * Blocks the standard checkout steps while the active cart holds Model B (wholesale indicative
 * pricing) items that are not backed by a valid, approved B2B quotation.
 *
 * Model A (native Bagisto pricing) carts pass through untouched. The cart page itself is NOT
 * guarded so buyers can still request quotations from a cart.
 *
 * This is the user-facing gate; the same verification also runs on `checkout.order.save.before`
 * (see AppServiceProvider) so payment-gateway routes cannot bypass it.
 */
class EnsureQuotationForCheckout
{
    public function handle(Request $request, Closure $next): Response
    {
        $violations = WholesalePricingManager::cartQuotationViolations(
            Cart::getCart(),
            auth()->guard('customer')->id()
        );

        if (empty($violations)) {
            return $next($request);
        }

        $message = 'An approved quotation is required to check out wholesale products. '.implode(' ', $violations);

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message'      => $message,
                'redirect_url' => route('shop.checkout.cart.index'),
            ], 403);
        }

        session()->flash('error', $message);

        return redirect()->route('shop.checkout.cart.index');
    }
}
