<?php

namespace App\Domains\Attribute\Repositories;

use App\Domains\Attribute\Contracts\AttributeRepositoryInterface;
use App\Domains\Attribute\DTOs\{CreateAttributeDTO, UpdateAttributeDTO, AttributeFilterDTO};
use App\Models\Attribute;
use App\Models\AttributeValue;
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
            'code'    => $dto->code,
            'is_active' => $dto->isActive,
        ]);
    }

    public function update(Attribute $attribute, UpdateAttributeDTO $dto): Attribute
    {
        $attribute->fill($dto->toArray());
        $attribute->save();

        return $attribute->fresh();
    }

    public function delete(Attribute $attribute): void
    {
        $attribute->delete();
    }

    public function upsertValues(int $attributeId, array $entries): void
    {
        if ($entries === []) {
            return;
        }

        $now = now();
        $rows = array_map(fn (array $entry) => [
            'attribute_id'     => $attributeId,
            'value'            => $entry['value'],
            'normalized_value' => $entry['normalized_value'],
            'is_active'        => true,
            'deleted_at'       => null,
            'created_at'       => $now,
            'updated_at'       => $now,
        ], $entries);

        AttributeValue::upsert(
            $rows,
            ['attribute_id', 'normalized_value'],
            ['value', 'is_active', 'deleted_at', 'updated_at'],
        );
    }

    public function deactivateValuesExcept(int $attributeId, array $normalizedKeys): void
    {
        $query = AttributeValue::query()->where('attribute_id', $attributeId);

        if ($normalizedKeys !== []) {
            $query->whereNotIn('normalized_value', $normalizedKeys);
        }

        $query->update(['is_active' => false]);
    }

    public function hasProducts(Attribute $attribute): bool
    {
        return $attribute->products()->exists();
    }

    public function hasValuesInUse(Attribute $attribute): bool 
    {
        return AttributeValue::query()
            ->where('attribute_id', $attribute->id)
            ->where(function ($query) {
                $query->whereHas('products')
                    ->orWhereHas('productVariants');
            })
            ->exists();
    }

    public function deleteValues(Attribute $attribute): void
    {
        AttributeValue::query()
            ->where('attribute_id', $attribute->id)
            ->delete();
    }
}
