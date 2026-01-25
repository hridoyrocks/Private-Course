<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $courses = $user->courses()
            ->where('is_active', true)
            ->withPivot('expires_at')
            ->withCount('videos')
            ->get()
            ->map(function ($course) {
                $course->is_expired = $course->pivot->expires_at && $course->pivot->expires_at < now();
                $course->days_remaining = $course->pivot->expires_at
                    ? (int) now()->diffInDays($course->pivot->expires_at, false)
                    : null;
                return $course;
            });

        return Inertia::render('user/Courses/Index', [
            'courses' => $courses,
        ]);
    }

    public function show(Request $request, Course $course): Response
    {
        $user = $request->user();

        if (!$user->hasAccessToCourse($course)) {
            abort(403, 'এই কোর্সে আপনার এক্সেস নেই অথবা মেয়াদ শেষ হয়ে গেছে।');
        }

        $course->load(['activeVideos']);

        $access = $user->courses()->where('course_id', $course->id)->first();

        return Inertia::render('user/Courses/Show', [
            'course' => $course,
            'expiresAt' => $access->pivot->expires_at,
            'daysRemaining' => $access->pivot->expires_at
                ? (int) now()->diffInDays($access->pivot->expires_at, false)
                : null,
        ]);
    }
}
