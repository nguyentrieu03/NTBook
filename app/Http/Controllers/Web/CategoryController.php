<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Category;
use App\Support\Categories\CategoryTreeBuilder;
use Illuminate\Http\JsonResponse;
use App\Domains\Category\Contracts\CategoryServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Domains\Category\DTOs\CategoryFilterDTO;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryServiceInterface $categoryService
    ) {}

    public function index(Request $request): View
    {
        $categoryTree = $this->categoryService->build();
        $attributes = Attribute::query()
            ->with(['attributeValues' => fn ($q) => $q->where('is_active', true)->orderBy('id')])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categoryTree', 'attributes'));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.categories.index');
    }

    public function edit(Category $category): RedirectResponse
    {
        return redirect()->route('admin.categories.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:150'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $slug = $this->resolveSlug($validated['slug'] ?: $validated['name']);
        $parentId = $validated['parent_id'] ?? null;
        $sortOrder = Category::query()
            ->where('parent_id', $parentId)
            ->max('sort_order');

        Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'parent_id' => $parentId,
            'sort_order' => ($sortOrder ?? -1) + 1,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Đã thêm danh mục "'.$validated['name'].'".');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:150'],
        ]);

        $slug = $this->resolveSlug($validated['slug'] ?: $validated['name'], $category->id);

        $category->update([
            'name' => $validated['name'],
            'slug' => $slug,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Đã cập nhật danh mục "'.$category->name.'".');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $name = $category->name;
        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Đã xóa danh mục "'.$name.'".');
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tree' => ['required', 'array'],
        ]);

        $this->applyTreeOrder($validated['tree'], null);

        return response()->json(['ok' => true]);
    }

    private function resolveSlug(string $input, ?int $ignoreId = null): string
    {
        $base = Str::slug($input);
        if ($base === '') {
            $base = 'danh-muc';
        }

        $candidate = $base;
        $suffix = 2;

        while (
            Category::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }

    /**
     * @param  array<int, array{id:int, children?:array}>  $nodes
     */
    private function applyTreeOrder(array $nodes, ?int $parentId): void
    {
        foreach ($nodes as $index => $node) {
            if (empty($node['id'])) {
                continue;
            }

            Category::query()
                ->whereKey($node['id'])
                ->update([
                    'parent_id' => $parentId,
                    'sort_order' => $index,
                ]);

            if (! empty($node['children']) && is_array($node['children'])) {
                $this->applyTreeOrder($node['children'], (int) $node['id']);
            }
        }
    }
}
