<?php

namespace App\Domains\Attribute\Repositories;

use App\Domains\Attribute\Contracts\AttributeRepositoryInterface;
use App\Domains\Attribute\DTOs\{CreateAttributeDTO, UpdateAttributeDTO, AttributeFilterDTO};
use App\Models\Attribute;
use App\Support\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AttributeRepository extends BaseRepository implements AttributeRepositoryInterface
{
    protected function model(): string
    {
        return Attribute::class;
    }

    public function list(AttributeFilterDTO $filter): LengthAwarePaginator|Collection
    {
        $query = $this->query();

        if ($filter->search) {
            $term = '%' . $filter->search . '%';
            $query->where('name', 'like', $term);
        }

        $query->where('is_active', $filter->isActive);

        if($filter->withCount) {
            $query->withCount($filter->withCount);
        }

        if($filter->relations) {
            foreach($filter->relations as $relation => $conditions) {
                $query->with($relation, function($q) use ($conditions) {
                    foreach($conditions['where'] as $where) {
                        $q->where($where[0], $where[1], $where[2]);
                    }
                    foreach($conditions['sort'] as $sortBy => $sortDir) {
                        $q->orderBy($sortBy, $sortDir);
                    }
                });
            }
        }

        foreach ($filter->sort as $field => $direction) {
            $query->orderBy($field, $direction);
        }

        return $filter->paginate ? $query
            ->paginate($filter->perPage) : $query->get();
    }

    public function create(CreateAttributeDTO $dto): Attribute
    {
        return Attribute::create([
            'name'     => $dto->name,
            'slug'    => $dto->slug,
            'level' => $dto->level,
            'sort_order' => $dto->sortOrder,
            'is_active' => $dto->isActive,
            'parent_id' => $dto->parentId,
        ]);
    }

    public function update(Attribute $Attribute, UpdateAttributeDTO $dto): Attribute
    {
        $Attribute->fill($dto->toArray());
        $Attribute->save();

        return $Attribute->fresh();
    }

    public function delete(Attribute $Attribute): void
    {
        $Attribute->delete();
    }
}
