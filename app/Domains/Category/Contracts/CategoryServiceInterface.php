<?php

namespace App\Domains\Category\Contracts;

use App\Domains\Category\DTOs\{CreateCategoryDTO, UpdateCategoryDTO, CategoryFilterDTO};
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CategoryServiceInterface
{
    public function getList(CategoryFilterDTO $filter): LengthAwarePaginator|Collection;
    public function build(): Collection;
    public function findOrFail(int $id): Category;
    public function create(CreateCategoryDTO $dto): Category;
    public function update(int $id, UpdateCategoryDTO $dto): Category;
    public function delete(int $id): void;
    public function reorder(array $nodes): void;
}