<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use App\Domains\Category\Contracts\CategoryServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Domains\Attribute\Contracts\AttributeServiceInterface;
use App\Domains\Attribute\DTOs\AttributeFilterDTO;
use App\Domains\Category\DTOs\CreateCategoryDTO;
use App\Http\Requests\Category\CreateCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Domains\Category\DTOs\UpdateCategoryDTO;
use App\Http\Requests\Category\ReorderCategoryRequest;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryServiceInterface $categoryService,
        private readonly AttributeServiceInterface $attributeService
    ) {}

    public function index(): View
    {
        $categoryTree = $this->categoryService->build();
        $attributes = $this->attributeService->getList(AttributeFilterDTO::fromRequest([
            'paginate' => false,
            'relations' => [
                'attributeValues' => [
                    'where'=> [
                        ['is_active', '=', true],
                    ],
                    'sort' => ['id' => 'desc'],
                ]
            ]
        ]));

        return view('categories.index', compact('categoryTree', 'attributes'));
    }

    public function store(CreateCategoryRequest $request): RedirectResponse
    {
        $category = $this->categoryService->create(CreateCategoryDTO::fromRequest($request->validated()));

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Đã thêm danh mục "'.$category->name.'".');
    }

    public
     function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category = $this->categoryService->update($category->id, UpdateCategoryDTO::fromRequest($request->validated()));

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Đã cập nhật danh mục "'.$category->name.'".');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $name = $category->name;
        $this->categoryService->delete($category->id);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Đã xóa danh mục "'.$name.'".');
    }

    public function reorder(ReorderCategoryRequest $request): JsonResponse
    {
        $this->categoryService->reorder($request->validated('tree'));

        return response()->json(['ok' => true]);
    }
}
