<?php

namespace App\Domains\User\DTOs;

use App\Support\Enum\UserStatusEnum;

final class UserFilterDTO
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?UserStatusEnum $status = null,
        public readonly string $sortBy = 'created_at',
        public readonly string $sortDir = 'desc',
        public readonly int $perPage = 15,
    ){}

    public static function fromRequest(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            status: isset($data['status']) ? UserStatusEnum::from($data['status']) : null,
            sortBy: in_array($data['sort_by'] ?? '', ['name', 'email', 'created_at'], true) ? $data['sort_by'] : 'created_at',
            sortDir: in_array($data['sort_dir'] ?? '', ['asc', 'desc'], true) ? $data['sort_dir'] : 'desc',
            perPage: min((int)($data['per_page'] ?? 15), 100),
        );
    }
}