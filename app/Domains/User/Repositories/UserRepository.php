<?php

namespace App\Domains\User\Repositories;

use App\Domains\User\Contracts\UserRepositoryInterface;
use App\Domains\User\DTOs\{CreateUserDTO, UpdateUserDTO, UserFilterDTO};
use App\Models\User;
use App\Support\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    protected function model(): Model
    {
        return User::class;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->query()->where('email', $email)->first();
    }

    public function list(UserFilterDTO $filter): LengthAwarePaginator
    {
        $query = $this->query();

        if ($filter->search) {
            $term = '%' . $filter->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term);
            });
        }

        if ($filter->status) {
            $query->where('status', $filter->status->value);
        }

        return $query
            ->orderBy($filter->sortBy, $filter->sortDir)
            ->paginate($filter->perPage);
    }

    public function findById(int $id): ?User
    {
        return $this->query()->find($id);
    }

    public function existsByEmail(string $email, ?int $excludeId = null): bool
    {
        return $this->query()
            ->where('email', $email)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }

    public function create(CreateUserDTO $dto): User
    {
        return User::create([
            'name'     => $dto->name,
            'email'    => $dto->email,
            'password' => $dto->password,
            'status'   => $dto->status->value,
            'phone'    => $dto->phone,
        ]);
    }

    public function update(User $user, UpdateUserDTO $dto): User
    {
        $user->fill($dto->toArray());
        $user->save();

        return $user->fresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
