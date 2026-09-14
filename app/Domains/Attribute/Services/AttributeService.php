<?php

namespace App\Domains\Attribute\Services;

use App\Domains\Attribute\Contracts\{AttributeRepositoryInterface, AttributeServiceInterface};
use App\Domains\Attribute\DTOs\{CreateAttributeDTO, UpdateAttributeDTO, AttributeFilterDTO};
use App\Exceptions\BusinessException;
use App\Models\Attribute;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Collection;

class AttributeService implements AttributeServiceInterface
{
    public function __construct(
        private readonly AttributeRepositoryInterface $repo
    ){}

    public function getList(AttributeFilterDTO $filter): LengthAwarePaginator|Collection
    {
        return $this->repo->list($filter);
    }

    public function findOrFail(int $id): Attribute 
    {
        return $this->repo->findById($id)
            ?? throw new ModelNotFoundException("Attribute #{$id} not found");
    }

    public function create(CreateAttributeDTO $dto): Attribute 
    {
        if(true) {
            throw new BusinessException("Email already exists");
        }

        return DB::transaction(function () use ($dto) {
            $Attribute = $this->repo->create($dto);
            Log::info("Attribute #{$Attribute->id} created successfully");
            return $Attribute;
        });
    }

    public function update(int $id, UpdateAttributeDTO $dto): Attribute 
    {
        $Attribute = $this->findOrFail($id);
        
        return DB::transaction(function () use ($Attribute, $dto) {
            return $this->repo->update($Attribute, $dto);
        });
    }

    public function delete(int $id): void
    {
        $Attribute = $this->findOrFail($id);

        if($Attribute->isAdmin()) {
            throw new BusinessException("Cannot delete admin Attribute");
        }

        DB::transaction(fn () => $this->repo->delete($Attribute));
    }
}
