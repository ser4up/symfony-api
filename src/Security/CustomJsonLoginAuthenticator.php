<?php

namespace App\Security;

use App\Dto\Request\SigninRequestDto;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\InteractiveAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PasswordUpgradeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @see Symfony\Component\Security\Http\Authenticator\JsonLoginAuthenticator
 */
class CustomJsonLoginAuthenticator implements InteractiveAuthenticatorInterface
{
    private array $options;
    private HttpUtils $httpUtils;
    private UserProviderInterface $userProvider;
    private PropertyAccessorInterface $propertyAccessor;
    private ?AuthenticationSuccessHandlerInterface $successHandler;
    private ?AuthenticationFailureHandlerInterface $failureHandler;
    private ?TranslatorInterface $translator = null;
    private ValidatorInterface $validator;

    public function __construct(
        HttpUtils $httpUtils,
        UserProviderInterface $userProvider,
        AuthenticationSuccessHandlerInterface $successHandler = null,
        AuthenticationFailureHandlerInterface $failureHandler = null,
        array $options = [],
        PropertyAccessorInterface $propertyAccessor = null,
        ValidatorInterface $validator = null
    ) {
        $this->options = array_merge(['username_path' => 'username', 'password_path' => 'password'], $options);
        $this->httpUtils = $httpUtils;
        $this->successHandler = $successHandler;
        $this->failureHandler = $failureHandler;
        $this->userProvider = $userProvider;
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessor();

        $this->validator = $validator;
    }

    public function supports(Request $request): ?bool
    {
        if (
            !str_contains($request->getRequestFormat() ?? '', 'json')
            && !str_contains((method_exists(Request::class, 'getContentTypeFormat') ? $request->getContentTypeFormat() : $request->getContentType()) ?? '', 'json')
        ) {
            return false;
        }

        if (isset($this->options['check_path']) && !$this->httpUtils->checkRequestPath($request, $this->options['check_path'])) {
            return false;
        }

        return true;
    }

    public function authenticate(Request $request): Passport
    {
        try {
            $data = json_decode($request->getContent());
            if (!$data instanceof \stdClass) {
                throw new BadRequestHttpException('Invalid JSON.');
            }

            $credentials = $this->getCredentials($data);
        } catch (BadRequestHttpException $e) {
            $request->setRequestFormat('json');

            throw $e;
        }

        $userBadge = new UserBadge($credentials['username'], $this->userProvider->loadUserByIdentifier(...));
        $passport = new Passport($userBadge, new PasswordCredentials($credentials['password']), [new RememberMeBadge((array) $data)]);

        if ($this->userProvider instanceof PasswordUpgraderInterface) {
            $passport->addBadge(new PasswordUpgradeBadge($credentials['password'], $this->userProvider));
        }

        return $passport;
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        return new UsernamePasswordToken($passport->getUser(), $firewallName, $passport->getUser()->getRoles());
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if (null === $this->successHandler) {
            return null; // let the original request continue
        }

        return $this->successHandler->onAuthenticationSuccess($request, $token);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if (null === $this->failureHandler) {
            if (null !== $this->translator) {
                $errorMessage = $this->translator->trans($exception->getMessageKey(), $exception->getMessageData(), 'security');
            } else {
                $errorMessage = strtr($exception->getMessageKey(), $exception->getMessageData());
            }

            return new JsonResponse(['error' => $errorMessage], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return $this->failureHandler->onAuthenticationFailure($request, $exception);
    }

    public function isInteractive(): bool
    {
        return true;
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    private function getCredentials(\stdClass $data): array
    {
        $credentials = [];

        try {
            $credentials['username'] = $this->propertyAccessor->getValue($data, $this->options['username_path']);
        } catch (AccessException $e) {
            $credentials['username'] = '';
        }

        try {
            $credentials['password'] = $this->propertyAccessor->getValue($data, $this->options['password_path']);
            $this->propertyAccessor->setValue($data, $this->options['password_path'], null);
        } catch (AccessException $e) {
            $credentials['password'] = '';
        }

        $signinDto = SigninRequestDto::createFromArray($credentials);

        $violations = $this->validator->validate($signinDto);
        if ($violations->count() > 0) {
            throw new HttpException(422, 'Validation error.', new ValidationFailedException('', $violations));
        }

        return $credentials;
    }
}
