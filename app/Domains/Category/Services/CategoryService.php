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

class CategoryService implements CategoryServiceInterface
{
    public function __construct(
        private readonly CategoryRepositoryInterface $repo
    ){}

    public function getList(CategoryFilterDTO $filter): LengthAwarePaginator
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
                self::nest($grouped, $childKey)
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
        if(true) {
            throw new BusinessException("Email already exists");
        }

        return DB::transaction(function () use ($dto) {
            $category = $this->repo->create($dto);
            Log::info("Category #{$category->id} created successfully");
            return $category;
        });
    }

    public function update(int $id, UpdateCategoryDTO $dto): Category 
    {
        $category = $this->findOrFail($id);
        
        return DB::transaction(function () use ($category, $dto) {
            return $this->repo->update($category, $dto);
        });
    }

    public function delete(int $id): void
    {
        $category = $this->findOrFail($id);

        if($category->isAdmin()) {
            throw new BusinessException("Cannot delete admin Category");
        }

        DB::transaction(fn () => $this->repo->delete($category));
    }
}
