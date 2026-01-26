<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\DeviceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __construct(protected DeviceService $deviceService)
    {
    }

    public function authenticated(Request $request)
    {
        $user = $request->user();

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'আপনার অ্যাকাউন্ট নিষ্ক্রিয় করা হয়েছে।']);
        }

        if (!$user->isAdmin()) {
            // First check if this device is already registered
            if ($this->deviceService->validateDevice($user, $request)) {
                // Device already registered, allow login
                if ($user->isAdmin()) {
                    return redirect()->intended(route('admin.dashboard'));
                }
                return redirect()->intended(route('user.courses.index'));
            }

            // Device not registered, check if we can add new device
            $currentDeviceCount = $user->devices()->count();
            $maxDevices = $user->max_devices;

            if ($currentDeviceCount >= $maxDevices) {
                Auth::logout();
                $request->session()->invalidate();
                return redirect()->route('login')
                    ->withErrors(['email' => "আপনার ডিভাইস লিমিট ({$maxDevices}) শেষ। অন্য ডিভাইস থেকে লগআউট করুন অথবা অ্যাডমিনের সাথে যোগাযোগ করুন।"]);
            }

            // Try to register new device
            $device = $this->deviceService->registerDevice($user, $request);

            if ($device === false) {
                Auth::logout();
                $request->session()->invalidate();
                return redirect()->route('login')
                    ->withErrors(['email' => 'ডিভাইস রেজিস্টার করা যায়নি। অনুগ্রহ করে অ্যাডমিনের সাথে যোগাযোগ করুন।']);
            }
        }

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('user.courses.index'));
    }
}
