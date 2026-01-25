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
            $device = $this->deviceService->registerDevice($user, $request);

            if ($device === false) {
                Auth::logout();
                return redirect()->route('login')
                    ->withErrors(['email' => 'আপনার ডিভাইস লিমিট শেষ। অনুগ্রহ করে অ্যাডমিনের সাথে যোগাযোগ করুন।']);
            }
        }

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('user.courses.index'));
    }
}
