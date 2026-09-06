<?php

namespace App\Domains\User\Contracts;

use App\Domains\User\DTOs\{CreateUserDTO, UpdateUserDTO, UserFilterDTO};
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserServiceInterface
{
    public function getList(UserFilterDTO $filter): LengthAwarePaginator;
    public function findOrFail(int $id): User;
    public function create(CreateUserDTO $dto): User;
    public function update(int $id, UpdateUserDTO $dto): User;
    public function delete(int $id): void;
}