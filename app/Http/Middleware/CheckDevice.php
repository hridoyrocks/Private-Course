<?php

namespace App\Http\Middleware;

use App\Services\DeviceService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDevice
{
    public function __construct(protected DeviceService $deviceService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        // First try to validate existing device
        if ($this->deviceService->validateDevice($user, $request)) {
            return $next($request);
        }

        // If device not found, try to register it (for first-time or new devices)
        $device = $this->deviceService->registerDevice($user, $request);

        if ($device) {
            return $next($request);
        }

        // Device limit reached
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'আপনার ডিভাইস লিমিট শেষ। অনুগ্রহ করে অ্যাডমিনের সাথে যোগাযোগ করুন।',
                'error' => 'device_limit_reached'
            ], 403);
        }

        auth()->logout();
        return redirect()->route('login')->withErrors([
            'email' => 'আপনার ডিভাইস লিমিট শেষ। অনুগ্রহ করে অ্যাডমিনের সাথে যোগাযোগ করুন।'
        ]);
    }
}
