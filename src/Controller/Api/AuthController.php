<?php

namespace App\Controller\Api;

use App\Dto\Request\SignupRequestDto;
use App\Mapper\Request\UserRequestMapper;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/auth', name: 'auth.')]
class AuthController extends AbstractController
{
    #[OA\Post(
        summary: 'Sign up.',
        description: 'User sign up.',
        tags: ['auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: 'object', properties: [
                new OA\Property(property: 'name', type: 'string', example: 'John'),
                new OA\Property(property: 'email', type: 'string', example: 'john-example@mail.mail'),
                new OA\Property(property: 'password', type: 'string', example: '123456'),
            ])
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful operation.',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'status', type: 'integer', example: 200),
            new OA\Property(property: 'message', type: 'string', example: 'string'),
        ])
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation error.',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'status', type: 'integer', example: 422),
            new OA\Property(property: 'message', type: 'string', example: 'string'),
            new OA\Property(property: 'errors', type: 'json', example: '[]'),
        ])
    )]
    #[Route('/signup', name: 'signup', methods: ['POST'])]
    public function signup(
        #[MapRequestPayload] SignupRequestDto $signupDto,
        UserRepository $userRepo,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
    ): Response {
        $user = UserRequestMapper::mapToUser($signupDto);
        if ($userRepo->existsByEmail($user->getEmail())) {
            return $this->json([
                'status' => '422',
                'message' => 'User with such email already exists.',
            ]);
        }

        $user->setPassword($hasher->hashPassword($user, $user->getPassword()));

        $em->persist($user);
        $em->flush();

        return $this->json([
            'status' => '200',
            'message' => 'Successful operation.',
        ]);
    }

    #[OA\Post(
        summary: 'Sign in.',
        description: 'User sign in.',
        tags: ['auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: 'object', properties: [
                new OA\Property(property: 'email', type: 'string', example: 'john-example@mail.mail'),
                new OA\Property(property: 'password', type: 'string', example: '123456'),
            ])
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful operation.',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'token', type: 'string', example: 'string'),
            new OA\Property(property: 'refresh_token', type: 'string', example: 'string'),
        ])
    )]
    #[OA\Response(
        response: 401,
        description: 'Invalid credentials.',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'status', type: 'integer', example: 401),
            new OA\Property(property: 'message', type: 'string', example: 'string'),
        ])
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation error.',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'status', type: 'integer', example: 422),
            new OA\Property(property: 'message', type: 'string', example: 'string'),
            new OA\Property(property: 'errors', type: 'json', example: '[]'),
        ])
    )]
    #[Route('/signin?', name: 'signin', methods: ['POST'])]
    public function signin()
    {
    }

    #[OA\Post(
        summary: 'Refresh.',
        description: 'Refresh a token.',
        tags: ['auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: 'object', properties: [
                new OA\Property(property: 'refresh_token', type: 'string', example: 'string'),
            ])
        )
    )]
    #[Security(name: 'Bearer')]
    #[OA\Response(
        response: 200,
        description: 'Successful operation.',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'token', type: 'string', example: 'string'),
            new OA\Property(property: 'refresh_token', type: 'string', example: 'string'),
        ])
    )]
    #[OA\Response(
        response: 401,
        description: 'Invalid credentials.',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'status', type: 'integer', example: 401),
            new OA\Property(property: 'message', type: 'string', example: 'string'),
        ])
    )]
    #[OA\Response(
        response: 422,
        description: 'Validation error.',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'status', type: 'integer', example: 422),
            new OA\Property(property: 'message', type: 'string', example: 'string'),
            new OA\Property(property: 'errors', type: 'json', example: '[]'),
        ])
    )]
    #[Route('/refresh?', name: 'refresh', methods: ['POST'])]
    public function refresh()
    {
    }
}
