<?php

namespace SymfonyCasts\Bundle\VerifyEmail\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\RouteCollection;

class TestTest extends TestCase
{
    public function testTest(): void
    {
        $kernel = new VerifyEmailTestKernel('test', true);
        $kernel->boot();

        $collection = new RouteCollection();
        $collection->a
        self::assertInstanceOf(Kernel::class, $kernel);
    }
}
