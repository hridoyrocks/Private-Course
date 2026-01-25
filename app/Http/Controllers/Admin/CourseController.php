<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CourseController extends Controller
{
    public function index(): Response
    {
        $courses = Course::withCount(['videos', 'users'])
            ->latest()
            ->paginate(15);

        return Inertia::render('admin/Courses/Index', [
            'courses' => $courses,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/Courses/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
        ]);

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        Course::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'thumbnail' => $thumbnailPath,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.courses.index')
            ->with('success', 'কোর্স সফলভাবে তৈরি হয়েছে।');
    }

    public function edit(Course $course): Response
    {
        $course->load('videos');

        return Inertia::render('admin/Courses/Edit', [
            'course' => $course,
        ]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
        ]);

        $thumbnailPath = $course->thumbnail;
        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $course->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'thumbnail' => $thumbnailPath,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.courses.index')
            ->with('success', 'কোর্স সফলভাবে আপডেট হয়েছে।');
    }

    public function destroy(Course $course): RedirectResponse
    {
        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }

        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', 'কোর্স সফলভাবে মুছে ফেলা হয়েছে।');
    }
}
