<?php

namespace SymfonyCasts\Bundle\VerifyUser\Tests\FunctionalTests;

use PHPUnit\Framework\TestCase;
use SymfonyCasts\Bundle\VerifyUser\Util\QueryUtility;
use SymfonyCasts\Bundle\VerifyUser\Util\UriSigningWrapper;
use SymfonyCasts\Bundle\VerifyUser\VerifyHelper;

class VerifyHelperFunctionalTest extends TestCase
{
    public function testGenerateSignature(): void
    {
        $signingWrapper = new UriSigningWrapper('superSecret');
        $queryUtil = new QueryUtility();
        $lifetime = 3600;
        $helper = new VerifyHelper($signingWrapper, $queryUtil, $lifetime);

        $userId = '1234';
        $email = 'jr@rushlow.dev';

        $result = $helper->generateSignature($userId, $email);
        $parsedUri = parse_url($result->getSignature());
        parse_str($parsedUri['query'], $queryParams);

        $expectedQueryParams['id'] = $userId;
        $expectedQueryParams['email'] = $email;
        $expectedQueryParams['expires'] = $queryParams['expires'];
        ksort($expectedQueryParams);
        $expectedQueryString = http_build_query($expectedQueryParams);

        $expectedUri = '/?'.$expectedQueryString;
        $expectedHash = \base64_encode(\hash_hmac('sha256', $expectedUri, 'superSecret', true));

        $resultHash = $queryParams['signature'];

        self::assertTrue(\hash_equals($expectedHash, $resultHash));
    }

    public function testValidSignature(): void
    {
        $signingWrapper = new UriSigningWrapper('superSecret');
        $queryUtil = new QueryUtility();
        $lifetime = 3600;
        $helper = new VerifyHelper($signingWrapper, $queryUtil, $lifetime);

        $userId = '1234';
        $email = 'jr@rushlow.dev';

        $expiresAt = (new \DateTimeImmutable('+1 hours'))->getTimestamp();

        $queryParams['id'] = $userId;
        $queryParams['email'] = $email;
        $queryParams['expires'] = $expiresAt;

        ksort($queryParams);
        $queryString = http_build_query($queryParams);

        $uriToSign = '/?'.$queryString;
        $signature = \base64_encode(\hash_hmac('sha256', $uriToSign, 'superSecret', true));
        $queryParams['signature'] = $signature;
        unset($queryParams['id']);
        unset($queryParams['email']);

        ksort($queryParams);
        $expectedQueryString = http_build_query($queryParams);
        $expectedSignedUri = '/?'.$expectedQueryString;

        self::assertTrue($helper->isValidSignature($expectedSignedUri, $userId, $email));
    }
}
