<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Services\DeviceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::where('role', 'user')
            ->withCount(['devices', 'courses'])
            ->latest()
            ->paginate(15);

        return Inertia::render('admin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/Users/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Password::defaults()],
            'max_devices' => ['required', 'integer', 'min:1', 'max:10'],
            'is_active' => ['boolean'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'max_devices' => $validated['max_devices'],
            'is_active' => $validated['is_active'] ?? true,
            'role' => 'user',
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'ইউজার সফলভাবে তৈরি হয়েছে।');
    }

    public function edit(User $user): Response
    {
        $user->load(['devices', 'courses']);

        $allCourses = Course::where('is_active', true)->get(['id', 'title']);

        return Inertia::render('admin/Users/Edit', [
            'user' => $user,
            'allCourses' => $allCourses,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', Password::defaults()],
            'max_devices' => ['required', 'integer', 'min:1', 'max:10'],
            'is_active' => ['boolean'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'max_devices' => $validated['max_devices'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'ইউজার সফলভাবে আপডেট হয়েছে।');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'ইউজার সফলভাবে মুছে ফেলা হয়েছে।');
    }

    public function removeDevice(User $user, int $deviceId, DeviceService $deviceService): RedirectResponse
    {
        $device = $user->devices()->findOrFail($deviceId);
        $deviceService->removeDevice($device);

        return back()->with('success', 'ডিভাইস সফলভাবে মুছে ফেলা হয়েছে।');
    }

    public function removeAllDevices(User $user, DeviceService $deviceService): RedirectResponse
    {
        $deviceService->removeAllDevices($user);

        return back()->with('success', 'সব ডিভাইস সফলভাবে মুছে ফেলা হয়েছে।');
    }
}
