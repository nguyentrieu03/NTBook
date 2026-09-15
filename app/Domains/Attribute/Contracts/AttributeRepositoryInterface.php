<?php

namespace App\Domains\Attribute\Contracts;

use App\Domains\Attribute\DTOs\{CreateAttributeDTO, UpdateAttributeDTO, AttributeFilterDTO};
use App\Models\Attribute;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Support\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Support\Collection;

interface AttributeRepositoryInterface extends BaseRepositoryInterface
{
    public function list(AttributeFilterDTO $filter): LengthAwarePaginator|Collection;

    public function create(CreateAttributeDTO $dto): Attribute;

    public function update(Attribute $attribute, UpdateAttributeDTO $dto): Attribute;

    public function delete(Attribute $attribute): void;

    /**
     * @param  list<array{value: string, normalized_value: string}>  $entries
     */
    public function upsertValues(int $attributeId, array $entries): void;

    /**
     * @param  list<string>  $normalizedKeys
     */
    public function deactivateValuesExcept(int $attributeId, array $normalizedKeys): void;

    public function hasProducts(Attribute $attribute): bool;

    public function hasValuesInUse(Attribute $attribute): bool;

    public function deleteValues(Attribute $attribute): void;
}
