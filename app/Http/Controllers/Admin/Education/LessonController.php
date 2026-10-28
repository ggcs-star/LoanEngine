<?php

namespace App\Http\Controllers\Admin\Education;

use App\Http\Controllers\Controller;
use App\Models\EducationLesson;
use App\Models\EducationModule;
use App\Services\Education\EducationMediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Helpers\ImageHelper;
use Throwable;

class LessonController extends Controller
{
    public function __construct(private readonly EducationMediaService $media) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:180'],
            'module' => ['nullable', 'integer', 'min:1'],
            'type' => ['nullable', Rule::in(EducationLesson::CONTENT_TYPES)],
            'status' => ['nullable', Rule::in(['draft', 'published'])],
            'sort' => ['nullable', Rule::in(['default', 'latest', 'oldest', 'title'])],
        ]);

        $lessons = EducationLesson::query()
            ->with('module.category')
            ->when(filled($filters['search'] ?? null), function ($query) use ($filters): void {
                $term = trim($filters['search']);
                $query->where(function ($builder) use ($term): void {
                    $builder->where('title', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%");
                });
            })
            ->when(filled($filters['module'] ?? null), fn ($query) => $query->where('education_module_id', $filters['module']))
            ->when(filled($filters['type'] ?? null), fn ($query) => $query->where('content_type', $filters['type']))
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->when(($filters['sort'] ?? 'default') === 'latest', fn ($query) => $query->orderByDesc('created_at'))
            ->when(($filters['sort'] ?? 'default') === 'oldest', fn ($query) => $query->orderBy('created_at'))
            ->when(($filters['sort'] ?? 'default') === 'title', fn ($query) => $query->orderBy('title'))
            ->when(($filters['sort'] ?? 'default') === 'default', fn ($query) => $query->orderBy('sort_order')->orderByDesc('id'))
            ->paginate(15)->withQueryString();

        $modules = EducationModule::query()->orderBy('title')->get();
        return view('admin.education.lessons.index', compact('lessons', 'modules', 'filters'));
    }

    public function create(): View
    {
        $modules = EducationModule::query()->orderBy('title')->get();
        return view('admin.education.lessons.create', compact('modules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $mediaPath = $thumbnailPath = null;
        try {
            DB::transaction(function () use ($request, &$data, &$mediaPath, &$thumbnailPath): void {
                
                if ($request->hasFile('media')) {
                    $mediaPath = ImageHelper::store(
                        $request->file('media'),
                        'education/lessons',
                        $data['title']
                    );
                }

                if ($request->hasFile('thumbnail')) {
                    $thumbnailPath = ImageHelper::store(
                        $request->file('thumbnail'),
                        'education/lesson-thumbnails',
                        $data['title']
                    );
                }

                $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['title'], (int) $data['education_module_id']);
                EducationLesson::create([
                    'education_module_id' => $data['education_module_id'], 'title' => $data['title'],
                    'slug' => $data['slug'], 'description' => $data['description'] ?? null,
                    'content' => $data['content'] ?? null, 'content_type' => $data['content_type'],
                    'media_path' => $mediaPath, 'external_media_url' => $data['external_media_url'] ?? null,
                    'thumbnail_path' => $thumbnailPath, 'duration_seconds' => $data['duration_seconds'] ?? 0,
                    'sort_order' => $data['sort_order'] ?? 0, 'status' => $data['status'],
                ]);
            });
        } catch (Throwable $exception) {
            $this->safeDelete($mediaPath); $this->safeDelete($thumbnailPath); report($exception);
            return back()->withInput($request->except(['media', 'thumbnail']))->with('error', 'Unable to create the lesson. Please try again.');
        }
        return redirect()->route('admin.education.lessons.index')->with('success', 'Lesson created successfully.');
    }

    public function show(EducationLesson $lesson): View
    {
        $lesson->load('module.category');
        return view('admin.education.lessons.show', compact('lesson'));
    }

    public function edit(EducationLesson $lesson): View
    {
        $modules = EducationModule::query()->orderBy('title')->get();
        return view('admin.education.lessons.edit', compact('lesson', 'modules'));
    }

    public function update(Request $request, EducationLesson $lesson): RedirectResponse
    {
        $data = $this->validatedData($request, $lesson);
        $oldMediaPath = $lesson->media_path; $oldThumbnailPath = $lesson->thumbnail_path;
        $newMediaPath = $newThumbnailPath = null;
        try {
            DB::transaction(function () use ($request, $lesson, &$data, &$newMediaPath, &$newThumbnailPath): void {
                
                if ($request->hasFile('media')) {
                    $newMediaPath = ImageHelper::store(
                        $request->file('media'),
                        'education/lessons',
                        $data['title']
                    );
                }

                if ($request->hasFile('thumbnail')) {
                    $newThumbnailPath = ImageHelper::store(
                        $request->file('thumbnail'),
                        'education/lesson-thumbnails',
                        $data['title']
                    );
                }

                $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['title'], (int) $data['education_module_id'], $lesson->id);
                $lesson->update([
                    'education_module_id' => $data['education_module_id'], 'title' => $data['title'], 'slug' => $data['slug'],
                    'description' => $data['description'] ?? null, 'content' => $data['content'] ?? null,
                    'content_type' => $data['content_type'], 'media_path' => $newMediaPath ?? $lesson->media_path,
                    'external_media_url' => $data['external_media_url'] ?? null,
                    'thumbnail_path' => $newThumbnailPath ?? $lesson->thumbnail_path,
                    'duration_seconds' => $data['duration_seconds'] ?? 0, 'sort_order' => $data['sort_order'] ?? 0,
                    'status' => $data['status'],
                ]);
            });
        } catch (Throwable $exception) {
            $this->safeDelete($newMediaPath); $this->safeDelete($newThumbnailPath); report($exception);
            return back()->withInput($request->except(['media', 'thumbnail']))->with('error', 'Unable to update the lesson. Please try again.');
        }
        if ($newMediaPath) $this->safeDelete($oldMediaPath);
        if ($newThumbnailPath) $this->safeDelete($oldThumbnailPath);
        return redirect()->route('admin.education.lessons.index')->with('success', 'Lesson updated successfully.');
    }

    public function destroy(EducationLesson $lesson): RedirectResponse
    {
        $mediaPath = $lesson->media_path; $thumbnailPath = $lesson->thumbnail_path;
        try { $lesson->delete(); }
        catch (Throwable $exception) { report($exception); return back()->with('error', 'Unable to delete the lesson. Please try again.'); }
        $this->safeDelete($mediaPath); $this->safeDelete($thumbnailPath);
        return redirect()->route('admin.education.lessons.index')->with('success', 'Lesson deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'lesson_ids' => ['required', 'array', 'min:1', 'max:100'],
            'lesson_ids.*' => ['required', 'integer', 'distinct', 'min:1', 'exists:education_lessons,id'],
        ]);
        $deleted = 0; $skipped = 0; $files = [];
        foreach ($data['lesson_ids'] as $id) {
            try {
                $result = DB::transaction(function () use ($id): ?array {
                    $lesson = EducationLesson::query()->lockForUpdate()->find($id);
                    if (!$lesson) return null;
                    $paths = [$lesson->media_path, $lesson->thumbnail_path];
                    $lesson->delete();
                    return $paths;
                });
                if ($result === null) { $skipped++; continue; }
                $deleted++; $files = array_merge($files, array_filter($result));
            } catch (Throwable $exception) { $skipped++; Log::warning('Bulk lesson delete failed', ['lesson_id' => $id, 'message' => $exception->getMessage()]); }
        }
        foreach ($files as $path) $this->safeDelete($path);
        $message = "{$deleted} lesson(s) deleted successfully.";
        if ($skipped) $message .= " {$skipped} lesson(s) could not be deleted.";
        return redirect()->route('admin.education.lessons.index')->with($skipped ? 'warning' : 'success', $message);
    }

    private function validatedData(Request $request, ?EducationLesson $lesson = null): array
    {
        $moduleId = $request->input('education_module_id');
        $slugRule = Rule::unique('education_lessons', 'slug')->where(fn ($query) => $query->where('education_module_id', $moduleId));
        if ($lesson) $slugRule->ignore($lesson->id);
        return $request->validate([
            'education_module_id' => ['required', 'integer', 'exists:education_modules,id'],
            'title' => ['required', 'string', 'max:180'], 'slug' => ['nullable', 'string', 'max:200', 'alpha_dash', $slugRule],
            'description' => ['nullable', 'string', 'max:5000'], 'content' => ['nullable', 'string', 'max:50000'],
            'content_type' => ['required', Rule::in(EducationLesson::CONTENT_TYPES)],
            'media' => ['nullable', 'file', 'mimes:mp4,webm,mp3,m4a,aac,ogg,wav', 'max:51200'],
            'external_media_url' => ['nullable', 'url', 'max:2048'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'], 'status' => ['required', Rule::in(['draft', 'published'])],
        ]);
    }

    private function uniqueSlug(string $value, int $moduleId, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'lesson'; $slug = $base; $number = 2;
        while (EducationLesson::query()->where('education_module_id', $moduleId)->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $number++;
        }
        return $slug;
    }

    private function safeDelete(?string $path): void
    {
        if (!$path) return;
        try { $this->media->delete($path); }
        catch (Throwable $exception) { Log::warning('Education lesson media cleanup failed', ['path' => $path, 'message' => $exception->getMessage()]); }
    }
}
