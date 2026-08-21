<?php

namespace App\Support\Categories;

use App\Models\Category;
use Illuminate\Support\Collection;

final class CategoryTreeBuilder
{
    /**
     * @return Collection<int, Category>
     */
    public static function build(): Collection
    {
        $all = Category::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

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
