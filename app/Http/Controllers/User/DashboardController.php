<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $courses = $user->courses()
            ->where('is_active', true)
            ->withPivot('expires_at')
            ->withCount(['videos' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get()
            ->map(function ($course) {
                $course->is_expired = $course->pivot->expires_at && $course->pivot->expires_at < now();
                $course->days_remaining = $course->pivot->expires_at
                    ? (int) now()->diffInDays($course->pivot->expires_at, false)
                    : null;
                return $course;
            });

        $activeCourses = $courses->filter(fn($c) => !$c->is_expired);
        $expiredCourses = $courses->filter(fn($c) => $c->is_expired);
        $totalVideos = $activeCourses->sum('videos_count');

        return Inertia::render('user/Dashboard', [
            'courses' => $courses,
            'stats' => [
                'totalCourses' => $activeCourses->count(),
                'expiredCourses' => $expiredCourses->count(),
                'totalVideos' => $totalVideos,
            ],
        ]);
    }
}
