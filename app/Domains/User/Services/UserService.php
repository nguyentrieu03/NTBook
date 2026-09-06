<?php

namespace App\Domains\User\Services;

use App\Domains\User\Contracts\{UserRepositoryInterface, UserServiceInterface};
use App\Domains\User\DTOs\{CreateUserDTO, UpdateUserDTO, UserFilterDTO};
use App\Exceptions\BusinessException;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class UserService implements UserServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $repo
    ){}

    public function getList(UserFilterDTO $filter): LengthAwarePaginator
    {
        return $this->repo->list($filter);
    }

    public function findOrFail(int $id): User 
    {
        return $this->repo->findById($id)
            ?? throw new ModelNotFoundException("User #{$id} not found");
    }

    public function create(CreateUserDTO $dto): User 
    {
        if($this->repo->existsByEmail($dto->email)) {
            throw new BusinessException("Email already exists");
        }

        return DB::transaction(function () use ($dto) {
            $user = $this->repo->create($dto);
            Log::info("User #{$user->id} created successfully");
            return $user;
        });
    }

    public function update(int $id, UpdateUserDTO $dto): User 
    {
        $user = $this->findOrFail($id);
        
        return DB::transaction(function () use ($user, $dto) {
            return $this->repo->update($user, $dto);
        });
    }

    public function delete(int $id): void
    {
        $user = $this->findOrFail($id);

        if($user->isAdmin()) {
            throw new BusinessException("Cannot delete admin user");
        }

        DB::transaction(fn () => $this->repo->delete($user));
    }
}
