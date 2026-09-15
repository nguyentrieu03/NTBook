<?php

namespace App\Domains\Attribute\DTOs;
use Illuminate\Support\Str;

final class CreateAttributeDTO 
{
    public function __construct(
        public readonly string $name,
        public readonly string $code,
        public readonly ?bool $isActive = true,
        public readonly array $values = [],
    ){}

    public static function fromRequest(array $data): self 
    {
        return new self(
            name: $data['name'],
            code: Str::lower($data['code']),
            isActive: (bool) ($data['is_active'] ?? true),
            values: $data['values'] ?? [],
        );
    }
}