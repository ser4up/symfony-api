<?php

namespace App\Mapper\Request;

use App\Dto\Request\PostAddRequestDto;
use App\Entity\Post;
use AutoMapperPlus\AutoMapper;
use AutoMapperPlus\Configuration\AutoMapperConfig;

class PostAddRequestMapper
{
    private static function getMapper(): AutoMapper
    {
        $config = new AutoMapperConfig();
        $config
            ->registerMapping(PostAddRequestDto::class, Post::class)
        ;

        return new AutoMapper($config);
    }

    public static function mapToPost(PostAddRequestDto $postAddDto): Post
    {
        return self::getMapper()->map($postAddDto, Post::class);
    }
}
