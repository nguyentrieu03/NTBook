<?php

namespace App\Domains\Attribute\DTOs;
use Illuminate\Support\Str;

final class UpdateAttributeDTO 
{
    public function __construct(
        public readonly string $name,
        public readonly string $code,
        public readonly ?bool $isActive = null,
        public readonly array $values = [],
    ){}

    public static function fromRequest(array $data): self 
    {
        return new self(
            name: $data['name'],
            code: Str::lower($data['code']),
            isActive: $data['is_active'] ?? null,
            values: $data['values'] ?? [],
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'code' => $this->code,
            'is_active' => $this->isActive,
        ], fn($value) => $value !== null);
    }
}