<?php

namespace App\Domains\Auth\DTOs;

final class UpdatePasswordDTO
{
    public function __construct(
        public readonly string $password,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(password: $data['password']);
    }
}
