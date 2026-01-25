<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccessController extends Controller
{
    public function index(): Response
    {
        $accesses = User::where('role', 'user')
            ->with(['courses' => function ($query) {
                $query->withPivot('expires_at', 'created_at');
            }])
            ->whereHas('courses')
            ->paginate(15);

        return Inertia::render('admin/Access/Index', [
            'accesses' => $accesses,
        ]);
    }

    public function create(): Response
    {
        $users = User::where('role', 'user')
            ->where('is_active', true)
            ->get(['id', 'name', 'email']);

        $courses = Course::where('is_active', true)
            ->get(['id', 'title']);

        return Inertia::render('admin/Access/Create', [
            'users' => $users,
            'courses' => $courses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'course_id' => ['required', 'exists:courses,id'],
            'expires_at' => ['nullable', 'date', 'after:today'],
        ]);

        $user = User::findOrFail($validated['user_id']);

        $existingAccess = $user->courses()->where('course_id', $validated['course_id'])->exists();

        if ($existingAccess) {
            $user->courses()->updateExistingPivot($validated['course_id'], [
                'expires_at' => $validated['expires_at'] ?? null,
            ]);
            $message = 'এক্সেস সফলভাবে আপডেট হয়েছে।';
        } else {
            $user->courses()->attach($validated['course_id'], [
                'expires_at' => $validated['expires_at'] ?? null,
            ]);
            $message = 'এক্সেস সফলভাবে দেওয়া হয়েছে।';
        }

        return redirect()->route('admin.access.index')
            ->with('success', $message);
    }

    public function grantAccess(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'expires_at' => ['nullable', 'date', 'after:today'],
        ]);

        $existingAccess = $user->courses()->where('course_id', $validated['course_id'])->exists();

        if ($existingAccess) {
            $user->courses()->updateExistingPivot($validated['course_id'], [
                'expires_at' => $validated['expires_at'] ?? null,
            ]);
        } else {
            $user->courses()->attach($validated['course_id'], [
                'expires_at' => $validated['expires_at'] ?? null,
            ]);
        }

        return back()->with('success', 'কোর্স এক্সেস দেওয়া হয়েছে।');
    }

    public function revokeAccess(User $user, Course $course): RedirectResponse
    {
        $user->courses()->detach($course->id);

        return back()->with('success', 'কোর্স এক্সেস বাতিল করা হয়েছে।');
    }
}
