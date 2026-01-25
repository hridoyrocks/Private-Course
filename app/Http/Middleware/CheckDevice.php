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

        if (!$this->deviceService->validateDevice($user, $request)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'ডিভাইস যাচাই ব্যর্থ। অনুগ্রহ করে আবার লগইন করুন।',
                    'error' => 'device_invalid'
                ], 403);
            }

            auth()->logout();
            return redirect()->route('login')->with('error', 'ডিভাইস যাচাই ব্যর্থ। অনুগ্রহ করে আবার লগইন করুন।');
        }

        return $next($request);
    }
}
