<?php

namespace App\Domains\Category\Services;

use App\Domains\Category\Contracts\{CategoryRepositoryInterface, CategoryServiceInterface};
use App\Domains\Category\DTOs\{CreateCategoryDTO, UpdateCategoryDTO, CategoryFilterDTO};
use App\Exceptions\BusinessException;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CategoryService implements CategoryServiceInterface
{
    public function __construct(
        private readonly CategoryRepositoryInterface $repo
    ){}

    public function getList(CategoryFilterDTO $filter): LengthAwarePaginator|Collection
    {
        return $this->repo->list($filter);
    }

    /**
     * @return Collection<int, Category>
     */
    public function build(): Collection
    {
        $all = $this->repo->list(CategoryFilterDTO::fromRequest([
            'with_count' => ['products'],
            'sort' => ['sort_order' => 'asc', 'id' => 'asc'],
            'is_active' => true,
            'paginate' => false,
        ]));

        $grouped = $all->groupBy(fn (Category $cat) => (string) ($cat->parent_id ?? ''));

        return $this->nest($grouped, '');
    }

    /**
     * @param  Collection<string, Collection<int, Category>>  $grouped
     * @return Collection<int, Category>
     */
    private function nest(Collection $grouped, string $parentKey): Collection
    {
        return ($grouped->get($parentKey) ?? collect())->map(function (Category $category) use ($grouped) {
            $childKey = (string) $category->id;
            $category->setRelation(
                'childrenTree',
                $this->nest($grouped, $childKey)
            );

            return $category;
        })->values();
    }

    public function findOrFail(int $id): Category 
    {
        return $this->repo->findById($id)
            ?? throw new ModelNotFoundException("Category #{$id} not found");
    }

    public function create(CreateCategoryDTO $dto): Category 
    {
        $slug = $this->resolveSlug($dto->slug ?: $dto->name);
        $sortOrder = $this->getNextSortOrder($dto->parentId);
        return DB::transaction(function () use ($dto, $slug, $sortOrder) {
            $category = $this->repo->create(new CreateCategoryDTO(
                name: $dto->name,
                slug: $slug,
                level: $dto->level,
                parentId: $dto->parentId,
                sortOrder: $sortOrder,
                isActive: $dto->isActive,
            ));
            Log::info("Category #{$category->id} created successfully");
            return $category;
        });
    }

    public function update(int $id, UpdateCategoryDTO $dto): Category 
    {
        $category = $this->findOrFail($id);
        $slug = $this->resolveSlug($dto->slug ?: $dto->name, $category->id);
        
        return DB::transaction(function () use ($category, $dto, $slug) {
            return $this->repo->update($category, new UpdateCategoryDTO(
                name: $dto->name,
                slug: $slug,
            ));
        });
    }

    public function delete(int $id): void
    {
        $category = $this->findOrFail($id);
        if($this->repo->hasChildren($category)) {
            throw new BusinessException('Danh mục có chứa danh mục con, không thể xóa');
        } 

        if($this->repo->hasProducts($category)) {
            throw new BusinessException('Danh mục có chứa sản phẩm, để xóa cần di chuyển các sản phẩm thuộc danh mục này sang 1 danh mục khác');
        }

        DB::transaction(fn () => $this->repo->delete($category));
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
            $this->repo->existsBySlug($candidate, $ignoreId)
        ) {
            $candidate = $base . '-' . $suffix++;
        }

        return $candidate;
    }

    private function getNextSortOrder(?int $parentId): int 
    {
        return ($this->repo->maxSortOrder($parentId) ?? -1) + 1;
    }

    public function reorder(array $nodes):void 
    {
        $ids = $this->flattenTreeIds($nodes);

        if($ids === []) {
            throw new BusinessException('Không có danh mục để cập nhật');
        }

        if(count($ids) !== count(array_unique($ids))) {
            throw new BusinessException('Danh mục có ID trùng lặp, vui lòng kiểm tra lại');
        }

        if($this->repo->countByIds($ids) !== count($ids)) {
            throw new BusinessException('Danh mục có ID không tồn tại, vui lòng kiểm tra lại');
        }

        DB::transaction(function() use ($nodes) {
            $this->applyReorder($nodes, null, 0);
        });
    }

    private function applyReorder(array $nodes, ?int $parentId, int $depth): void 
    {
        foreach($nodes as $index => $node) {
            $id = (int) $node['id'];

            $updated = $this->repo->updatePosition($id, $parentId, $index, $depth);

            if($updated === 0) {
                throw new BusinessException("Không cập nhật được danh mục #{$id}.");
            }

            if (! empty($node['children']) && is_array($node['children'])) {
                $this->applyReorder($node['children'], $id, $depth + 1);
            }
        }
    }

    private function flattenTreeIds(array $nodes): array 
    {
        $ids = [];

        foreach($nodes as $node) {
            if(! empty($node['id'])) {
                $ids[] = (int) $node['id'];
            }

            if(! empty($node['children']) && is_array($node['children'])) {
                $ids = array_merge($ids, $this->flattenTreeIds($node['children']));
            }
        }

        return $ids;
    }
}
