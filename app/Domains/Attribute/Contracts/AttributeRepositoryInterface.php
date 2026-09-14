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
}
