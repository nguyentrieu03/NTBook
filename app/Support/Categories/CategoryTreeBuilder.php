<?php

namespace App\Support\Categories;

use App\Models\Category;
use Illuminate\Support\Collection;
use App\Domains\Category\Contracts\CategoryRepositoryInterface;
use App\Domains\Category\DTOs\CategoryFilterDTO;

final class CategoryTreeBuilder
{
    /**
     * @return Collection<int, Category>
     */
    public static function build(): Collection
    {
        $all = app(CategoryRepositoryInterface::class)->list(CategoryFilterDTO::fromRequest([
            'with_count' => ['products'],
            'sort' => ['sort_order' => 'asc', 'id' => 'asc'],
            'is_active' => true,
            'paginate' => false,
        ]));

        $grouped = $all->groupBy(fn (Category $cat) => (string) ($cat->parent_id ?? ''));

        return self::nest($grouped, '');
    }

    /**
     * @param  Collection<string, Collection<int, Category>>  $grouped
     * @return Collection<int, Category>
     */
    private static function nest(Collection $grouped, string $parentKey): Collection
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
}
