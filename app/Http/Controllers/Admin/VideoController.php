<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Video;
use App\Services\VideoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class VideoController extends Controller
{
    public function __construct(protected VideoService $videoService)
    {
    }

    public function create(Course $course): Response
    {
        return Inertia::render('admin/Videos/Create', [
            'course' => $course,
        ]);
    }

    /**
     * Get presigned URL for direct R2 upload
     */
    public function getUploadUrl(Request $request, Course $course): JsonResponse
    {
        $validated = $request->validate([
            'filename' => ['required', 'string'],
            'content_type' => ['required', 'string'],
        ]);

        $result = $this->videoService->getPresignedUploadUrl(
            $course->id,
            $validated['filename'],
            $validated['content_type']
        );

        return response()->json($result);
    }

    /**
     * Store video after direct upload to R2
     */
    public function store(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'video_path' => ['required', 'string'],
            'duration' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $maxOrder = $course->videos()->max('order') ?? 0;

        Video::create([
            'course_id' => $course->id,
            'title' => $validated['title'],
            'video_path' => $validated['video_path'],
            'duration' => $validated['duration'] ?? null,
            'order' => $validated['order'] ?? $maxOrder + 1,
            'is_active' => true,
        ]);

        return redirect()->route('admin.courses.edit', $course)
            ->with('success', 'ভিডিও সফলভাবে আপলোড হয়েছে।');
    }

    public function edit(Video $video): Response
    {
        $videoData = $video->load('course')->toArray();

        // Try to get signed URL for preview
        try {
            $videoData['preview_url'] = $this->videoService->getSignedUrl($video, 60);
        } catch (\Exception $e) {
            $videoData['preview_url'] = null;
        }

        return Inertia::render('admin/Videos/Edit', [
            'video' => $videoData,
        ]);
    }

    /**
     * Get presigned URL for replacing video in edit
     */
    public function getEditUploadUrl(Request $request, Video $video): JsonResponse
    {
        $validated = $request->validate([
            'filename' => ['required', 'string'],
            'content_type' => ['required', 'string'],
        ]);

        $result = $this->videoService->getPresignedUploadUrl(
            $video->course_id,
            $validated['filename'],
            $validated['content_type']
        );

        return response()->json($result);
    }

    public function update(Request $request, Video $video): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'video_path' => ['nullable', 'string'],
            'duration' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $updateData = [
            'title' => $validated['title'],
            'duration' => $validated['duration'] ?? $video->duration,
            'order' => $validated['order'] ?? $video->order,
            'is_active' => $validated['is_active'] ?? true,
        ];

        // If new video path provided (from direct upload), replace the old one
        if (!empty($validated['video_path'])) {
            // Delete old video
            $this->videoService->delete($video);
            $updateData['video_path'] = $validated['video_path'];
        }

        $video->update($updateData);

        return redirect()->route('admin.courses.edit', $video->course_id)
            ->with('success', 'Video updated successfully.');
    }

    public function destroy(Video $video): RedirectResponse
    {
        $courseId = $video->course_id;

        $this->videoService->delete($video);
        $video->delete();

        return redirect()->route('admin.courses.edit', $courseId)
            ->with('success', 'ভিডিও সফলভাবে মুছে ফেলা হয়েছে।');
    }

    public function reorder(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'videos' => ['required', 'array'],
            'videos.*.id' => ['required', 'exists:videos,id'],
            'videos.*.order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['videos'] as $item) {
            Video::where('id', $item['id'])
                ->where('course_id', $course->id)
                ->update(['order' => $item['order']]);
        }

        return back()->with('success', 'ভিডিও অর্ডার আপডেট হয়েছে।');
    }
}
