<?php

namespace App\Controller\Api;

use App\Dto\Request\PostAddRequestDto;
use App\Dto\Request\PostUpdateRequestDto;
use App\Dto\Response\PostResponseDto;
use App\Mapper\Request\PostAddRequestMapper;
use App\Mapper\Request\PostUpdateRequestMapper;
use App\Mapper\Response\PostResponseMapper;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    #[OA\Get(
        summary: 'List of posts.',
        description: 'It returns a list of posts.',
        tags: ['posts']
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful operation.',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: PostResponseDto::class
            ))),
    )]
    #[OA\Response(
        response: 401,
        description: 'Invalid credentials.',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'status', type: 'integer', example: 401),
            new OA\Property(property: 'message', type: 'string', example: 'string'),
        ])
    )]
    #[Route('/posts', name: 'posts.list', methods: ['GET'])]
    public function list(PostRepository $postRepo): Response
    {
        $responseData = PostResponseMapper::mapList($postRepo->getAll());

        return $this->json($responseData);
    }

    #[OA\Get(
        summary: 'One post.',
        description: 'It returns one post.',
        tags: ['posts'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Post id.'),
        ]
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful operation.',
        content: new OA\JsonContent(ref: new Model(type: PostResponseDto::class))
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
        response: 404,
        description: 'Post not found.',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'status', type: 'integer', example: 404),
            new OA\Property(property: 'message', type: 'string', example: 'string'),
        ])
    )]
    #[Route('/posts/{id}', name: 'posts.one', methods: ['GET'])]
    public function one(int $id, PostRepository $postRepo): Response
    {
        $post = $postRepo->byId($id);
        if (is_null($post)) {
            return $this->postNotFoundResponse();
        }

        $responseData = PostResponseMapper::map($post);

        return $this->json($responseData);
    }

    #[OA\Post(
        summary: 'Add post.',
        description: 'It adds a new post to the DB.',
        tags: ['posts'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: PostAddRequestDto::class))
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful operation.',
        content: new OA\JsonContent(ref: new Model(type: PostResponseDto::class))
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
    #[Security(name: 'Bearer')]
    #[Route('/posts', name: 'posts.add', methods: ['POST'])]
    public function add(
        #[MapRequestPayload] PostAddRequestDto $postAddDto,
        EntityManagerInterface $em
    ): Response {
        $post = PostAddRequestMapper::mapToPost($postAddDto);

        $em->persist($post);
        $em->flush();

        $responseData = PostResponseMapper::map($post);

        return $this->json($responseData);
    }

    #[OA\Patch(
        summary: 'Update post.',
        description: 'It updates the post in the DB.',
        tags: ['posts'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Post id.'),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(ref: new Model(type: PostUpdateRequestDto::class))
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful operation.',
        content: new OA\JsonContent(ref: new Model(type: PostResponseDto::class))
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
        response: 404,
        description: 'Post not found.',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'status', type: 'integer', example: 404),
            new OA\Property(property: 'message', type: 'string', example: 'string'),
        ])
    )]
    #[Security(name: 'Bearer')]
    #[Route('/posts/{id}', name: 'posts.update', methods: ['PATCH'])]
    public function update(
        int $id,
        #[MapRequestPayload] PostUpdateRequestDto $postUpdateDto,
        EntityManagerInterface $em,
        PostRepository $postRepo
    ): Response {
        $post = $postRepo->byId($id);
        if (is_null($post)) {
            return $this->postNotFoundResponse();
        }

        $updatedPost = PostUpdateRequestMapper::mapToPost($postUpdateDto, $post);

        $em->persist($updatedPost);
        $em->flush();

        $responseData = PostResponseMapper::map($updatedPost);

        return $this->json($responseData);
    }

    #[OA\Delete(
        summary: 'Delete post.',
        description: 'It deletes the post from the DB.',
        tags: ['posts'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Post id.'),
        ]
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
        response: 401,
        description: 'Invalid credentials.',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'status', type: 'integer', example: 401),
            new OA\Property(property: 'message', type: 'string', example: 'string'),
        ])
    )]
    #[OA\Response(
        response: 404,
        description: 'Post not found.',
        content: new OA\JsonContent(type: 'object', properties: [
            new OA\Property(property: 'status', type: 'integer', example: 404),
            new OA\Property(property: 'message', type: 'string', example: 'string'),
        ])
    )]
    #[Security(name: 'Bearer')]
    #[Route('/posts/{id}', name: 'posts.delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        EntityManagerInterface $em,
        PostRepository $postRepo
    ): Response {
        $post = $postRepo->byId($id);
        if (is_null($post)) {
            return $this->postNotFoundResponse();
        }

        $em->remove($post);
        $em->flush();

        return $this->json([
            'status' => '200',
            'message' => 'Successful operation.',
        ]);
    }

    /**
     * Prepare post Not Found response.
     */
    private function postNotFoundResponse(): JsonResponse
    {
        return $this->json([
            'status' => '404',
            'message' => 'Post not found.',
        ], 404);
    }
}
