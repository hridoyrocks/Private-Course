<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Services\VideoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VideoController extends Controller
{
    public function __construct(protected VideoService $videoService)
    {
    }

    public function watch(Request $request, Video $video): Response
    {
        $user = $request->user();
        $course = $video->course;

        if (!$user->hasAccessToCourse($course)) {
            abort(403, 'এই কোর্সে আপনার এক্সেস নেই অথবা মেয়াদ শেষ হয়ে গেছে।');
        }

        $course->load(['activeVideos']);

        return Inertia::render('user/Videos/Watch', [
            'video' => $video,
            'course' => $course,
            'allVideos' => $course->activeVideos,
        ]);
    }

    public function getStreamUrl(Request $request, Video $video): JsonResponse
    {
        $user = $request->user();
        $course = $video->course;

        if (!$user->hasAccessToCourse($course)) {
            return response()->json([
                'error' => 'এই কোর্সে আপনার এক্সেস নেই অথবা মেয়াদ শেষ হয়ে গেছে।'
            ], 403);
        }

        // Get signed URL valid for 2 hours for uninterrupted playback
        $signedUrl = $this->videoService->getSignedUrl($video, 120);

        return response()->json([
            'url' => $signedUrl,
            'expires_in' => 120 * 60, // 2 hours in seconds
        ]);
    }
}
