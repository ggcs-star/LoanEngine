<?php

namespace App\Http\Controllers\Admin\Education;

use App\Http\Controllers\Controller;
use App\Models\EducationCategory;
use App\Models\EducationModule;
use App\Services\Education\EducationMediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class ModuleController extends Controller
{
    private const PER_PAGE = 15;

    private const STATUSES = [
        'draft',
        'published',
    ];

    private const SORT_OPTIONS = [
        'default',
        'latest',
        'oldest',
        'title',
    ];

    public function __construct(
        private readonly EducationMediaService $media
    ) {
    }

    public function index(Request $request): View
    {
        try {
            $filters = $request->validate([
                'search' => ['nullable', 'string', 'max:180'],
                'category' => ['nullable', 'integer', 'min:1'],
                'status' => [
                    'nullable',
                    Rule::in(self::STATUSES),
                ],
                'sort' => [
                    'nullable',
                    Rule::in(self::SORT_OPTIONS),
                ],
            ]);

            $modules = EducationModule::query()
                ->with('category')
                ->withCount('lessons')
                ->when(
                    filled($filters['search'] ?? null),
                    function ($query) use ($filters): void {
                        $search = trim($filters['search']);

                        $query->where(function ($subQuery) use ($search): void {
                            $subQuery
                                ->where('title', 'like', "%{$search}%")
                                ->orWhere('slug', 'like', "%{$search}%");
                        });
                    }
                )
                ->when(
                    filled($filters['category'] ?? null),
                    fn ($query) => $query->where(
                        'education_category_id',
                        $filters['category']
                    )
                )
                ->when(
                    filled($filters['status'] ?? null),
                    fn ($query) => $query->where(
                        'status',
                        $filters['status']
                    )
                )
                ->when(
                    ($filters['sort'] ?? 'default') === 'latest',
                    fn ($query) => $query->orderByDesc('created_at')
                )
                ->when(
                    ($filters['sort'] ?? 'default') === 'oldest',
                    fn ($query) => $query->orderBy('created_at')
                )
                ->when(
                    ($filters['sort'] ?? 'default') === 'title',
                    fn ($query) => $query->orderBy('title')
                )
                ->when(
                    ($filters['sort'] ?? 'default') === 'default',
                    fn ($query) => $query
                        ->orderBy('sort_order')
                        ->orderByDesc('id')
                )
                ->paginate(self::PER_PAGE)
                ->withQueryString();

            $categories = $this->getActiveCategories();

            return view(
                'admin.education.modules.index',
                compact('modules', 'categories', 'filters')
            );
    
        } catch (Throwable $exception) {
            Log::error('Education module listing failed.', [
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function create(): View
    {
        try {
            $categories = $this->getActiveCategories();

            return view(
                'admin.education.modules.create',
                compact('categories')
            );
    
        } catch (Throwable $exception) {
            Log::error('Education module create form failed.', [
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateModule($request);

        $thumbnailPath = null;

        try {
            DB::transaction(function () use (
                &$data,
                &$thumbnailPath
            ): void {
                if (! empty($data['slug'])) {
                    $data['slug'] = $this->generateSlug($data['slug']);
                } else {
                    $data['slug'] = $this->generateSlug($data['title']);
                }

                if (isset($data['thumbnail'])) {
                    $thumbnailPath = $this->storeThumbnail(
                        $data['thumbnail']
                    );

                    unset($data['thumbnail']);
                }

                $data['thumbnail_path'] = $thumbnailPath;

                EducationModule::create($data);
            });
        } catch (Throwable $exception) {
            $this->deleteThumbnail($thumbnailPath);

            Log::error('Education module creation failed.', [
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput($request->except('thumbnail'))
                ->withErrors([
                    'module' => 'Unable to create the module. Please try again.',
                ]);
        }

        return redirect()
            ->route('admin.education.modules.index')
            ->with('success', 'Education module created successfully.');
    }

    public function show(EducationModule $module): View
    {
        try {
            $module->load('category');
            $module->loadCount('lessons');

            return view(
                'admin.education.modules.show',
                compact('module')
            );
    
        } catch (Throwable $exception) {
            Log::error('Education module details failed.', [
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function edit(EducationModule $module): View
    {
        try {
            $categories = $this->getActiveCategories();

            return view(
                'admin.education.modules.edit',
                compact('module', 'categories')
            );
    
        } catch (Throwable $exception) {
            Log::error('Education module edit form failed.', [
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function update(
        Request $request,
        EducationModule $module
    ): RedirectResponse {
        $data = $this->validateModule($request, $module);

        $newThumbnailPath = null;
        $oldThumbnailPath = $module->thumbnail_path;

        try {
            DB::transaction(function () use (
                $module,
                &$data,
                &$newThumbnailPath,
                $oldThumbnailPath
            ): void {
                $module->refresh();

                if (! empty($data['slug'])) {
                    $data['slug'] = $this->generateSlug(
                        $data['slug'],
                        $module->id
                    );
                } else {
                    $data['slug'] = $this->generateSlug(
                        $data['title'],
                        $module->id
                    );
                }

                if (isset($data['thumbnail'])) {
                    $newThumbnailPath = $this->storeThumbnail(
                        $data['thumbnail']
                    );

                    unset($data['thumbnail']);
                    $data['thumbnail_path'] = $newThumbnailPath;
                }

                $module->update($data);
            });
        } catch (Throwable $exception) {
            $this->deleteThumbnail($newThumbnailPath);

            Log::error('Education module update failed.', [
                'module_id' => $module->id,
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput($request->except('thumbnail'))
                ->withErrors([
                    'module' => 'Unable to update the module. Please try again.',
                ]);
        }

        if ($newThumbnailPath !== null) {
            try {
                $this->deleteThumbnail($oldThumbnailPath);
            } catch (Throwable $exception) {
                Log::warning('Old education module thumbnail cleanup failed.', [
                    'module_id' => $module->id,
                    'path' => $oldThumbnailPath,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return redirect()
            ->route('admin.education.modules.index')
            ->with('success', 'Education module updated successfully.');
    }

    public function destroy(
        EducationModule $module
    ): RedirectResponse {
        $thumbnailPath = null;

        try {
            DB::transaction(function () use (
                $module,
                &$thumbnailPath
            ): void {
                $module->refresh();

                if ($module->lessons()->exists()) {
                    throw new \DomainException(
                        'This module has lessons and cannot be deleted.'
                    );
                }

                $thumbnailPath = $module->thumbnail_path;

                $module->delete();
            });
        } catch (\DomainException $exception) {
            return back()->withErrors([
                'module' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Education module deletion failed.', [
                'module_id' => $module->id,
                'message' => $exception->getMessage(),
            ]);

            return back()->withErrors([
                'module' => 'Unable to delete the module. Please try again.',
            ]);
        }

        try {
            $this->deleteThumbnail($thumbnailPath);
        } catch (Throwable $exception) {
            Log::warning('Education module thumbnail cleanup failed.', [
                'module_id' => $module->id,
                'path' => $thumbnailPath,
                'message' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('admin.education.modules.index')
            ->with('success', 'Education module deleted successfully.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'module_ids' => ['required', 'array', 'min:1', 'max:100'],
            'module_ids.*' => [
                'required',
                'integer',
                'distinct',
                'min:1',
                'exists:education_modules,id',
            ],
        ]);

        $deletedCount = 0;
        $skippedCount = 0;
        $thumbnailPaths = [];

        foreach ($validated['module_ids'] as $moduleId) {
            try {
                $result = DB::transaction(
                    function () use ($moduleId): array {
                        $module = EducationModule::query()
                            ->lockForUpdate()
                            ->find($moduleId);

                        if (! $module || $module->lessons()->exists()) {
                            return [
                                'deleted' => false,
                                'thumbnail' => null,
                            ];
                        }

                        $thumbnailPath = $module->thumbnail_path;

                        $module->delete();

                        return [
                            'deleted' => true,
                            'thumbnail' => $thumbnailPath,
                        ];
                    }
                );

                if (! $result['deleted']) {
                    $skippedCount++;
                    continue;
                }

                $deletedCount++;

                if ($result['thumbnail'] !== null) {
                    $thumbnailPaths[] = $result['thumbnail'];
                }
            } catch (Throwable $exception) {
                $skippedCount++;

                Log::warning('Bulk education module deletion failed.', [
                    'module_id' => $moduleId,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        foreach ($thumbnailPaths as $path) {
            try {
                $this->deleteThumbnail($path);
            } catch (Throwable $exception) {
                Log::warning('Bulk thumbnail cleanup failed.', [
                    'path' => $path,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        $message = "{$deletedCount} module(s) deleted successfully.";

        if ($skippedCount > 0) {
            $message .= " {$skippedCount} module(s) skipped because they contain lessons or could not be deleted.";
        }

        return redirect()
            ->route('admin.education.modules.index')
            ->with(
                $skippedCount > 0 ? 'warning' : 'success',
                $message
            );
    }


    private function validateModule(
        Request $request,
        ?EducationModule $module = null
    ): array {
        return $request->validate([
            'education_category_id' => [
                'required',
                'integer',
                Rule::exists('education_categories', 'id'),
            ],

            'title' => ['required', 'string', 'max:180'],

            'slug' => [
                'nullable',
                'string',
                'max:200',
                'alpha_dash',
                Rule::unique('education_modules', 'slug')
                    ->ignore($module?->id),
            ],

            'description' => ['nullable', 'string', 'max:10000'],

            'thumbnail' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:100000',
            ],

            'status' => [
                'required',
                Rule::in(self::STATUSES),
            ],
        ]);
    }


    private function getActiveCategories()
    {
        return EducationCategory::query()
            ->active()
            ->orderBy('name')
            ->get();
    }

    private function generateSlug(
        string $value,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($value);

        if ($baseSlug === '') {
            $baseSlug = 'module';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            EducationModule::query()
                ->where('slug', $slug)
                ->when(
                    $ignoreId !== null,
                    fn ($query) => $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function storeThumbnail(UploadedFile $file): string
    {
        return $this->media->store(
            $file,
            'education/modules'
        );
    }

    private function deleteThumbnail(?string $path): void
    {
        $this->media->delete($path);
    }
}