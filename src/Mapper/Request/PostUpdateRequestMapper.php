<?php

namespace App\Mapper\Request;

use App\Dto\Request\PostUpdateRequestDto;
use App\Entity\Post;
use AutoMapperPlus\AutoMapper;
use AutoMapperPlus\Configuration\AutoMapperConfig;

class PostUpdateRequestMapper
{
    private static function getMapper(): AutoMapper
    {
        $config = new AutoMapperConfig();
        $config
            ->getOptions()
            ->ignoreNullProperties()
        ;
        $config
            ->registerMapping(PostUpdateRequestDto::class, Post::class)
        ;

        return new AutoMapper($config);
    }

    public static function mapToPost(PostUpdateRequestDto $PostUpdateDto, Post $post): Post
    {
        return self::getMapper()->mapToObject($PostUpdateDto, $post);
    }
}
