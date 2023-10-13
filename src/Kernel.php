<?php

namespace App;

use App\Security\CustomJsonLoginAuthenticator;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    public function process(ContainerBuilder $container): void
    {
        $container
            ->getDefinition('security.authenticator.json_login')
            ->setClass(CustomJsonLoginAuthenticator::class)
            ->setArgument(6, new Reference('validator'))
        ;
    }
}
