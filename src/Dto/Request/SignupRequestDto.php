<?php

namespace App\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see https://symfony.com/doc/current/reference/constraints.html
 */
final class SignupRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly ?string $name,

        #[Assert\NotBlank]
        #[Assert\Email]
        public readonly ?string $email,

        #[Assert\NotBlank]
        public readonly ?string $password,
    ) {
    }
}
