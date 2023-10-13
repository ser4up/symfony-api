<?php

namespace App\Mapper\Request;

use App\Dto\Request\SignupRequestDto;
use App\Entity\User;
use AutoMapperPlus\AutoMapper;
use AutoMapperPlus\Configuration\AutoMapperConfig;

class UserRequestMapper
{
    private static function getMapper(): AutoMapper
    {
        $config = new AutoMapperConfig();
        $config
            ->registerMapping(User::class, SignupRequestDto::class)
            ->reverseMap();

        return new AutoMapper($config);
    }

    public static function mapToUser(SignupRequestDto $signupRequestDto): User
    {
        return self::getMapper()->map($signupRequestDto, User::class);
    }
}
