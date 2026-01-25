<?php

namespace App\Http\Middleware;

use App\Models\Course;
use App\Models\Video;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCourseAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        $course = $request->route('course');
        $video = $request->route('video');

        if ($video && !$course) {
            if (is_numeric($video)) {
                $video = Video::findOrFail($video);
            }
            $course = $video->course;
        }

        if ($course) {
            if (is_numeric($course)) {
                $course = Course::findOrFail($course);
            }

            if (!$user->hasAccessToCourse($course)) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'message' => 'এই কোর্সে আপনার এক্সেস নেই অথবা মেয়াদ শেষ হয়ে গেছে।',
                        'error' => 'access_denied'
                    ], 403);
                }
                abort(403, 'এই কোর্সে আপনার এক্সেস নেই অথবা মেয়াদ শেষ হয়ে গেছে।');
            }
        }

        return $next($request);
    }
}
