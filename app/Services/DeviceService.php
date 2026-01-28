<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;

class DeviceService
{
    public function generateDeviceHash(Request $request): string
    {
        $userAgent = $request->userAgent() ?? 'unknown';
        $acceptLanguage = $request->header('Accept-Language', '');
        $acceptEncoding = $request->header('Accept-Encoding', '');

        // Use cookie-based device token that survives logout
        $deviceToken = $request->cookie('device_token');
        if (!$deviceToken) {
            $deviceToken = bin2hex(random_bytes(32));
            // Store in cookie for 2 years - survives logout/session destroy
            cookie()->queue(cookie('device_token', $deviceToken, 60 * 24 * 365 * 2, '/', null, false, true));
        }

        return hash('sha256', $userAgent . $acceptLanguage . $acceptEncoding . $deviceToken);
    }

    public function getDeviceName(Request $request): string
    {
        $userAgent = $request->userAgent() ?? 'Unknown Device';

        if (str_contains($userAgent, 'iPhone')) {
            return 'iPhone';
        }
        if (str_contains($userAgent, 'iPad')) {
            return 'iPad';
        }
        if (str_contains($userAgent, 'Android')) {
            return 'Android';
        }
        if (str_contains($userAgent, 'Windows')) {
            return 'Windows PC';
        }
        if (str_contains($userAgent, 'Macintosh')) {
            return 'Mac';
        }
        if (str_contains($userAgent, 'Linux')) {
            return 'Linux';
        }

        return 'Unknown Device';
    }

    public function registerDevice(User $user, Request $request): UserDevice|false
    {
        $deviceHash = $this->generateDeviceHash($request);

        $existingDevice = $user->devices()->where('device_hash', $deviceHash)->first();

        if ($existingDevice) {
            $existingDevice->update([
                'last_active_at' => now(),
                'ip_address' => $request->ip(),
            ]);
            return $existingDevice;
        }

        if (!$user->canAddDevice()) {
            return false;
        }

        return $user->devices()->create([
            'device_name' => $this->getDeviceName($request),
            'device_hash' => $deviceHash,
            'ip_address' => $request->ip(),
            'last_active_at' => now(),
        ]);
    }

    public function validateDevice(User $user, Request $request): bool
    {
        $deviceHash = $this->generateDeviceHash($request);

        $device = $user->devices()->where('device_hash', $deviceHash)->first();

        if ($device) {
            $device->update(['last_active_at' => now()]);
            return true;
        }

        return false;
    }

    public function removeDevice(UserDevice $device): bool
    {
        return $device->delete();
    }

    public function removeAllDevices(User $user): int
    {
        return $user->devices()->delete();
    }
}
