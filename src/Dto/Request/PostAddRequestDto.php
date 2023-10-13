<?php

namespace App\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see https://symfony.com/doc/current/reference/constraints.html
 */
final class PostAddRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        public readonly ?string $title,
    ) {
    }
}
