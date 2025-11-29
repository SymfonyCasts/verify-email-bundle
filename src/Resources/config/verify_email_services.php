<?php

/*
 * This file is part of the SymfonyCasts VerifyEmailBundle package.
 * Copyright (c) SymfonyCasts <https://symfonycasts.com/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\HttpFoundation\UriSigner;
use SymfonyCasts\Bundle\VerifyEmail\Factory\UriSignerFactory;
use SymfonyCasts\Bundle\VerifyEmail\Generator\VerifyEmailTokenGenerator;
use SymfonyCasts\Bundle\VerifyEmail\Util\VerifyEmailQueryUtility;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelper;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();
    $services->set('symfonycasts.verify_email.token_generator', VerifyEmailTokenGenerator::class)
        ->args(['%kernel.secret%'])
        ->private();

    $services->set('symfonycasts.verify_email.query_utility', VerifyEmailQueryUtility::class)
        ->private();

    $services->set('symfonycasts.verify_email.uri_signer_factory', UriSignerFactory::class)
        ->args([
            '%kernel.secret%',
            'signature',
        ])
        ->private();

    $services->set('symfonycasts.verify_email.uri_signer', UriSigner::class)
        ->factory([
            service('symfonycasts.verify_email.uri_signer_factory'),
            'createUriSigner',
        ]);

    $services->alias(VerifyEmailHelperInterface::class, 'symfonycasts.verify_email.helper');

    $services->set('symfonycasts.verify_email.helper', VerifyEmailHelper::class)
        ->args([
            service('router'),
            service('symfonycasts.verify_email.uri_signer'),
            service('symfonycasts.verify_email.query_utility'),
            service('symfonycasts.verify_email.token_generator'),
            null, // verify user signature lifetime
        ]);
};
