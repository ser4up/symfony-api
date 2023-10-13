<?php

namespace App\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see https://symfony.com/doc/current/reference/constraints.html
 */
final class PostUpdateRequestDto
{
    public function __construct(
        #[Assert\Type(type: 'string')]
        public readonly ?string $title,

        #[Assert\Type(type: 'integer')]
        public readonly ?int $likes,
    ) {
    }
}
