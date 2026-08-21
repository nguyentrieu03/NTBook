<?php

namespace App\Domains\User\Contracts;

use App\Domains\User\DTOs\{CreateUserDTO, UpdateUserDTO, UserFilterDTO};
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function list(UserFilterDTO $filter): LengthAwarePaginator;

    public function findById(int $id): ?User;

    public function existsByEmail(string $email, ?int $excludeId = null): bool;

    public function create(CreateUserDTO $dto): User;

    public function update(User $user, UpdateUserDTO $dto): User;

    public function delete(User $user): void;
}
