<?php

namespace App\Http\Controllers\Admin\Education;

use App\Http\Controllers\Controller;
use App\Models\EducationCategory;
use App\Services\Education\EducationMediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class CategoryController extends Controller
{
    public function __construct(
        private readonly EducationMediaService $media
    ) {}

    public function index(Request $request): View
    {
        try {
            $query = EducationCategory::query()
                ->withCount('modules');

            // Search by category name or slug
            if ($request->filled('search')) {
                $term = trim((string) $request->input('search'));

                $query->where(function ($builder) use ($term) {
                    $builder->where('name', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%");
                });
            }

            // Filter by active/inactive status
            if ($request->input('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->input('status') === 'inactive') {
                $query->where('is_active', false);
            }

            // Paginated categories
            $categories = $query
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->paginate(10)
                ->withQueryString();

            // Dashboard statistics
            $totalCategories = EducationCategory::count();

            $activeCategories = EducationCategory::where(
                'is_active',
                true
            )->count();

            $inactiveCategories = EducationCategory::where(
                'is_active',
                false
            )->count();

            $totalModules = \App\Models\EducationModule::count();

            return view('admin.education.categories.index', compact(
                'categories',
                'totalCategories',
                'totalModules',
                'activeCategories',
                'inactiveCategories'
            ));
        } catch (Throwable $exception) {
            report($exception);

            throw $exception;
        }
    }

    public function create(): View
    {
        try {
            return view('admin.education.categories.create');
        } catch (Throwable $exception) {
            report($exception);

            throw $exception;
        }
    }

    public function show(EducationCategory $category): View
    {
        try {
            $category->load([
                'modules' => fn ($query) => $query->orderBy('sort_order'),
            ]);

            $category->loadCount('modules');

            return view('admin.education.categories.show', compact('category'));
        } catch (Throwable $exception) {
            report($exception);

            throw $exception;
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180', 'alpha_dash', 'unique:education_categories,slug'],
            'description' => ['nullable', 'string', 'max:5000'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $path = null;

        try {
            DB::transaction(function () use ($data, $request, &$path) {
                if ($request->hasFile('thumbnail')) {
                    $path = $this->media->store(
                        $request->file('thumbnail'),
                        'education/categories'
                    );
                }

                EducationCategory::create([
                    'name' => $data['name'],
                    'slug' => $data['slug'] ?: Str::slug($data['name']),
                    'description' => $data['description'] ?? null,
                    'thumbnail_path' => $path,
                    'sort_order' => $data['sort_order'] ?? 0,
                    'is_active' => $request->boolean('is_active'),
                ]);
            });

            return redirect()
                ->route('admin.education.categories.index')
                ->with('success', 'Education category created successfully.');
        } catch (Throwable $exception) {
            if ($path) {
                $this->media->delete($path);
            }

            report($exception);

            return back()
                ->withInput($request->except('thumbnail'))
                ->with('error', 'Unable to create the category. Please try again.');
        }
    }

    public function edit(EducationCategory $category): View
    {
        try {
            return view('admin.education.categories.edit', compact('category'));
        } catch (Throwable $exception) {
            report($exception);

            throw $exception;
        }
    }

    public function update(
        Request $request,
        EducationCategory $category
    ): RedirectResponse {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => [
                'nullable',
                'string',
                'max:180',
                'alpha_dash',
                Rule::unique('education_categories', 'slug')->ignore($category->id),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $newPath = null;

        try {
            DB::transaction(function () use ($request, $data, $category, &$newPath) {
                $newPath = $request->hasFile('thumbnail')
                    ? $this->media->store(
                        $request->file('thumbnail'),
                        'education/categories'
                    )
                    : null;

                $category->update([
                    'name' => $data['name'],
                    'slug' => $data['slug'] ?: Str::slug($data['name']),
                    'description' => $data['description'] ?? null,
                    'thumbnail_path' => $newPath ?? $category->thumbnail_path,
                    'sort_order' => $data['sort_order'] ?? 0,
                    'is_active' => $request->boolean('is_active'),
                ]);
            });

            if ($newPath && $category->getOriginal('thumbnail_path') !== $newPath) {
                // Old-file cleanup can be moved to a queued job if needed.
            }

            return redirect()
                ->route('admin.education.categories.index')
                ->with('success', 'Education category updated successfully.');
        } catch (Throwable $exception) {
            if ($newPath) {
                $this->media->delete($newPath);
            }

            report($exception);

            return back()
                ->withInput($request->except('thumbnail'))
                ->with('error', 'Unable to update the category. Please try again.');
        }
    }

    public function destroy(EducationCategory $category): RedirectResponse
    {
        if ($category->modules()->exists()) {
            return back()->with(
                'error',
                'This category has modules. Move or remove those modules before deleting it.'
            );
        }

        try {
            $thumbnail = $category->thumbnail_path;

            $category->delete();

            if ($thumbnail) {
                $this->media->delete($thumbnail);
            }

            return redirect()
                ->route('admin.education.categories.index')
                ->with('success', 'Education category deleted successfully.');
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'Unable to delete the category. Please try again.'
            );
        }
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => [
                'required',
                'integer',
                'min:1',
                'distinct',
                'exists:education_categories,id',
            ],
        ]);

        $categories = EducationCategory::query()
            ->whereIn('id', $validated['ids'])
            ->withCount('modules')
            ->get();

        if ($categories->count() !== count($validated['ids'])) {
            return back()->with(
                'error',
                'One or more selected categories could not be found.'
            );
        }

        $deletedCount = 0;
        $skippedNames = [];

        try {
            foreach ($categories as $category) {
                // Preserve the existing destroy() rule.
                if ($category->modules_count > 0) {
                    $skippedNames[] = $category->name;
                    continue;
                }

                $thumbnail = $category->thumbnail_path;

                DB::transaction(function () use ($category) {
                    $category->delete();
                });

                if ($thumbnail) {
                    try {
                        $this->media->delete($thumbnail);
                    } catch (Throwable $exception) {
                        report($exception);
                    }
                }

                $deletedCount++;
            }

            if ($deletedCount === 0) {
                return back()->with(
                    'error',
                    'No categories were deleted. All selected categories have linked modules.'
                );
            }

            $message = "{$deletedCount} category(s) deleted successfully.";

            if (!empty($skippedNames)) {
                $message .= ' Skipped categories with linked modules: '
                    . implode(', ', $skippedNames) . '.';
            }

            return redirect()
                ->route('admin.education.categories.index')
                ->with('success', $message);

        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'An error occurred while deleting the selected categories. Please review the result and try again.'
            );
        }
    }
}
