<?php

namespace App\Http\Responses;

use App\Services\DeviceService;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();

            return $request->wantsJson()
                ? response()->json(['message' => 'আপনার অ্যাকাউন্ট নিষ্ক্রিয় করা হয়েছে।'], 403)
                : redirect()->route('login')->withErrors(['email' => 'আপনার অ্যাকাউন্ট নিষ্ক্রিয় করা হয়েছে।']);
        }

        if (!$user->isAdmin()) {
            $deviceService = app(DeviceService::class);
            $device = $deviceService->registerDevice($user, $request);

            if ($device === false) {
                Auth::logout();

                return $request->wantsJson()
                    ? response()->json(['message' => 'আপনার ডিভাইস লিমিট শেষ।'], 403)
                    : redirect()->route('login')->withErrors(['email' => 'আপনার ডিভাইস লিমিট শেষ। অনুগ্রহ করে অ্যাডমিনের সাথে যোগাযোগ করুন।']);
            }
        }

        $redirectTo = $user->isAdmin() ? route('admin.dashboard') : route('user.courses.index');

        return $request->wantsJson()
            ? response()->json(['two_factor' => false, 'redirect' => $redirectTo])
            : redirect()->intended($redirectTo);
    }
}
