<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\VerifyEmail\Tests\IntegrationTests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use SymfonyCasts\Bundle\VerifyEmail\Tests\VerifyEmailTestKernel;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver   <ryan@symfonycasts.com>
 */
final class VerifyEmailServiceDefinitionTest extends TestCase
{
    public function bundleServiceDefinitionDataProvider(): \Generator
    {
        $prefix = 'symfonycasts.verify_email.';

        yield [$prefix.'query_utility'];
        yield [$prefix.'uri_signer'];
        yield [$prefix.'helper'];
        yield [$prefix.'token_generator'];
    }

    /**
     * @dataProvider bundleServiceDefinitionDataProvider
     */
    public function testBundleServiceDefinitions(string $definition): void
    {
        $pass = new DefinitionPublicCompilerPass();
        $pass->definition = $definition;

        $kernel = new VerifyEmailDefinitionTestKernel();
        $kernel->compilerPass = $pass;
        $kernel->boot();

        $container = $kernel->getContainer();
        $container->get($definition);

        $this->expectNotToPerformAssertions();
    }
}

final class DefinitionPublicCompilerPass implements CompilerPassInterface
{
    public $definition;

    public function process(ContainerBuilder $container)
    {
        $container->getDefinition($this->definition)
            ->setPublic(true)
        ;
    }
}

final class VerifyEmailDefinitionTestKernel extends VerifyEmailTestKernel
{
    public $compilerPass;

    protected function build(ContainerBuilder $container)
    {
        $container->addCompilerPass($this->compilerPass);
    }
}
