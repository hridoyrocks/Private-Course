<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Models\Video;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'totalUsers' => User::where('role', 'user')->count(),
                'activeUsers' => User::where('role', 'user')->where('is_active', true)->count(),
                'totalCourses' => Course::count(),
                'totalVideos' => Video::count(),
            ],
            'recentUsers' => User::where('role', 'user')
                ->latest()
                ->take(5)
                ->get(['id', 'name', 'email', 'created_at', 'is_active']),
        ]);
    }
}
