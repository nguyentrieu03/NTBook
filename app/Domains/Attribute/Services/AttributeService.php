<?php

namespace App\Domains\Attribute\Services;

use App\Domains\Attribute\Contracts\{AttributeRepositoryInterface, AttributeServiceInterface};
use App\Domains\Attribute\DTOs\{CreateAttributeDTO, UpdateAttributeDTO, AttributeFilterDTO};
use App\Exceptions\BusinessException;
use App\Models\Attribute;
use App\Support\Text\ValueNormalizer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Collection;

class AttributeService implements AttributeServiceInterface
{
    public function __construct(
        private readonly AttributeRepositoryInterface $repo
    ) {}

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
        return DB::transaction(function () use ($dto) {
            $attribute = $this->repo->create($dto);
            $this->syncValues($attribute, $dto->values);
            Log::info("Attribute #{$attribute->id} created successfully");

            return $attribute;
        });
    }

    public function update(int $id, UpdateAttributeDTO $dto): Attribute
    {
        // dd($dto->values);
        $attribute = $this->findOrFail($id);

        return DB::transaction(function () use ($attribute, $dto) {
            $attribute = $this->repo->update($attribute, $dto);
            $this->syncValues($attribute, $dto->values);

            return $attribute;
        });
    }

    public function delete(int $id): void
    {
        $attribute = $this->findOrFail($id);

        if($this->repo->hasProducts($attribute)) {
            throw new BusinessException("Không thể xóa thuộc tính đang gắn với sản phẩm");
        }

        if($this->repo->hasValuesInUse($attribute)) {
            throw new BusinessException("Không thể xóa thuộc tính: có giá trị đang được sản phẩm hoặc biến thể sử dụng");
        }

        DB::transaction(function () use ($attribute) {
            $this->repo->deleteValues($attribute);
            $this->repo->delete($attribute);
        });
    }

    /**
     * @param  array<int, string>|string  $raw
     */
    private function syncValues(Attribute $attribute, array|string $raw): void
    {
        $entries = $this->parseValueEntries($raw);
        $normalizedKeys = array_column($entries, 'normalized_value');

        $this->repo->upsertValues($attribute->id, $entries);
        $this->repo->deactivateValuesExcept($attribute->id, $normalizedKeys);
    }

    /**
     * @param  array<int, string>|string  $raw
     * @return list<array{value: string, normalized_value: string}>
     */
    private function parseValueEntries(array|string $raw): array
    {
        $items = is_array($raw)
            ? $raw
            : (preg_split('/\r\n|\r|\n|,/', $raw) ?: []);

        $entries = [];

        foreach ($items as $item) {
            $value = trim((string) $item);
            if ($value === '') {
                continue;
            }

            $normalized = ValueNormalizer::convertToCode($value);
            if ($normalized === '' || isset($entries[$normalized])) {
                continue;
            }

            $entries[$normalized] = [
                'value' => $value,
                'normalized_value' => $normalized,
            ];
        }

        return array_values($entries);
    }
}
