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
        // Don't use IP in hash - it changes frequently (mobile networks, VPN, etc.)
        // Use only browser fingerprint for device identification
        $acceptLanguage = $request->header('Accept-Language', '');
        $acceptEncoding = $request->header('Accept-Encoding', '');

        return hash('sha256', $userAgent . $acceptLanguage . $acceptEncoding);
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
