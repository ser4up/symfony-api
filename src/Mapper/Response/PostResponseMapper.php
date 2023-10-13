<?php

namespace App\Mapper\Response;

use App\Dto\Response\PostResponseDto;
use App\Entity\Post;
use AutoMapperPlus\AutoMapper;
use AutoMapperPlus\Configuration\AutoMapperConfig;

class PostResponseMapper
{
    private static function getMapper(): AutoMapper
    {
        $config = new AutoMapperConfig();
        $config
            ->registerMapping(Post::class, PostResponseDto::class)
            ->forMember('updated_date', fn (Post $post) => $post->getUpdatedDate()?->format('Y.m.d H:i:s'))
        ;

        return new AutoMapper($config);
    }

    public static function map(Post $post): PostResponseDto
    {
        return self::getMapper()->map($post, PostResponseDto::class);
    }

    public static function mapList(array $posts): array
    {
        return array_map(fn ($post) => static::map($post), $posts);
    }
}
