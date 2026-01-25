<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Video;
use App\Services\VideoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'video' => ['required', 'file', 'mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime', 'max:5242880'],
            'duration' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $videoPath = $this->videoService->upload($request->file('video'), $course->id);

        $maxOrder = $course->videos()->max('order') ?? 0;

        Video::create([
            'course_id' => $course->id,
            'title' => $validated['title'],
            'video_path' => $videoPath,
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

    public function update(Request $request, Video $video): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/avi,video/mpeg,video/quicktime', 'max:5242880'],
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

        // If new video uploaded, replace the old one
        if ($request->hasFile('video')) {
            // Delete old video
            $this->videoService->delete($video);

            // Upload new video
            $videoPath = $this->videoService->upload($request->file('video'), $video->course_id);
            $updateData['video_path'] = $videoPath;
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
