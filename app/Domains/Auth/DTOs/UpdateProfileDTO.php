<?php

namespace App\Domains\Auth\DTOs;

final class UpdateProfileDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
        );
    }
}
