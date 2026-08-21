<?php

namespace App\Domains\User\Repositories;

use App\Domains\User\Contracts\UserRepositoryInterface;
use App\Domains\User\DTOs\{CreateUserDTO, UpdateUserDTO, UserFilterDTO};
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class CachedUserRepository implements UserRepositoryInterface
{
    private const TTL = 3600;

    public function __construct(
        private readonly UserRepositoryInterface $inner,
    ) {}

    public function findById(int $id): ?User
    {
        return Cache::remember("user:{$id}", self::TTL, fn () => $this->inner->findById($id));
    }

    public function existsByEmail(string $email, ?int $excludeId = null): bool
    {
        return $this->inner->existsByEmail($email, $excludeId);
    }

    public function list(UserFilterDTO $filter): LengthAwarePaginator
    {
        // List không cache vì filter thay đổi liên tục
        return $this->inner->list($filter);
    }

    public function create(CreateUserDTO $dto): User
    {
        $user = $this->inner->create($dto);
        Cache::forget("user:{$user->id}");

        return $user;
    }

    public function update(User $user, UpdateUserDTO $dto): User
    {
        $updated = $this->inner->update($user, $dto);
        Cache::forget("user:{$user->id}");

        return $updated;
    }

    public function delete(User $user): void
    {
        Cache::forget("user:{$user->id}");
        $this->inner->delete($user);
    }
}
