<?php

namespace App\Domains\User\DTOs;

use App\Support\Enums\UserStatusEnum;

final class UpdateUserDTO 
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?UserStatusEnum $status = null,
    ){}

    public static function fromRequest(array $data): self 
    {
        return new self(
            name: $data['name'] ?? null,
            phone: $data['phone'] ?? null,
            status: isset($data['status']) ? UserStatusEnum::from($data['status']) : null,
        );
    }

    // Chỉ trả field khác null -> phục vụ partial update
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'phone' => $this->phone,
            'status' => $this->status?->value,
        ], fn($value) => $value !== null);
    }
}