<?php

namespace App\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see https://symfony.com/doc/current/reference/constraints.html
 */
final class SigninRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public readonly ?string $email,

        #[Assert\NotBlank]
        public readonly ?string $password,
    ) {
    }

    public static function createFromArray(array $data): static
    {
        return new static($data['username'] ?? null,$data['password'] ?? null);
    }
}
