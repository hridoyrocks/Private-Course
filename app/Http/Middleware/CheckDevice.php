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

        // STRICT: Only allow if device is already registered - no auto-registration here
        // Device must be registered during login only
        if ($this->deviceService->validateDevice($user, $request)) {
            return $next($request);
        }

        // Device not registered - reject access
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'এই ডিভাইস থেকে অ্যাক্সেস অনুমোদিত নয়। অনুগ্রহ করে আবার লগইন করুন।',
                'error' => 'device_not_registered'
            ], 403);
        }

        auth()->logout();
        return redirect()->route('login')->withErrors([
            'email' => 'এই ডিভাইস থেকে অ্যাক্সেস অনুমোদিত নয়। অনুগ্রহ করে আবার লগইন করুন।'
        ]);
    }
}
