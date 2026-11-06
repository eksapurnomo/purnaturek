<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Spatie\ResponseCache\Facades\ResponseCache;
use Throwable;
use Webkul\Shop\Http\Controllers\Customer\SessionController as BaseSessionController;

class WholesaleCustomerSessionController extends BaseSessionController
{
    /**
     * Remove the specified resource from storage.
     * Securely invalidates the session and regenerates CSRF token.
     */
    public function destroy(?\Illuminate\Http\Request $request = null): RedirectResponse
    {
        $request = $request ?: request();

        $id = auth()->guard('customer')->user()?->id;

        auth()->guard('customer')->logout();

        if ($id) {
            Event::dispatch('customer.after.logout', $id);
        }

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        try {
            if (class_exists(ResponseCache::class)) {
                ResponseCache::forget(route('shop.home.index'));
            }
        } catch (Throwable) {
            // Silently continue if ResponseCache is not configured
        }

        return redirect()->route('shop.home.index')->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Expires'       => '0',
        ]);
    }
}
