<?php

namespace App\Domains\Attribute\Contracts;

use App\Domains\Attribute\DTOs\{CreateAttributeDTO, UpdateAttributeDTO, AttributeFilterDTO};
use App\Models\Attribute;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface AttributeServiceInterface
{
    public function getList(AttributeFilterDTO $filter): LengthAwarePaginator|Collection;
    public function findOrFail(int $id): Attribute;
    public function create(CreateAttributeDTO $dto): Attribute;
    public function update(int $id, UpdateAttributeDTO $dto): Attribute;
    public function delete(int $id): void;
}