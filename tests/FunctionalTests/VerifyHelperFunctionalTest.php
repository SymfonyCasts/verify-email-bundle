<?php

namespace SymfonyCasts\Bundle\VerifyUser\Tests\FunctionalTests;

use PHPUnit\Framework\TestCase;
use SymfonyCasts\Bundle\VerifyUser\Util\QueryUtility;
use SymfonyCasts\Bundle\VerifyUser\Util\UriSigningWrapper;
use SymfonyCasts\Bundle\VerifyUser\VerifyHelper;
use SymfonyCasts\Bundle\VerifyUser\VerifyHelperInterface;

class VerifyHelperFunctionalTest extends TestCase
{
    private const FAKE_SIGNING_KEY = 'superSecret';

    /** @var VerifyHelperInterface */
    private $helper;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->helper = new VerifyHelper(
            new UriSigningWrapper(self::FAKE_SIGNING_KEY),
            new QueryUtility(),
            3600
        );
    }

    public function testGenerateSignature(): void
    {
        $userId = '1234';
        $email = 'jr@rushlow.dev';

        $result = $this->helper->generateSignature($userId, $email);

        $parsedUri = parse_url($result->getSignature());
        parse_str($parsedUri['query'], $queryParams);

        $expectedQueryParams['email'] = $email;
        $expectedQueryParams['expires'] = $queryParams['expires'];
        $expectedQueryParams['id'] = $userId;

        ksort($expectedQueryParams);
        $expectedQueryString = http_build_query($expectedQueryParams);

        $expectedUri = '/?'.$expectedQueryString;
        $expectedHash = \base64_encode(\hash_hmac('sha256', $expectedUri, self::FAKE_SIGNING_KEY, true));

        self::assertTrue(\hash_equals($expectedHash, $queryParams['signature']));
    }

    public function testValidSignature(): void
    {
        $userId = '1234';
        $email = 'jr@rushlow.dev';

        $queryParams['email'] = $email;
        $queryParams['expires'] = (new \DateTimeImmutable('+1 hours'))->getTimestamp();
        $queryParams['id'] = $userId;

        $queryString = http_build_query($queryParams);
        $uriToSign = '/?'.$queryString;

        $signature = \base64_encode(\hash_hmac('sha256', $uriToSign, self::FAKE_SIGNING_KEY, true));
        $queryParams['signature'] = $signature;

        unset($queryParams['id'], $queryParams['email']);
        \ksort($queryParams);

        $expectedSignedUri = '/?'.\http_build_query($queryParams);

        self::assertTrue($this->helper->isValidSignature($expectedSignedUri, $userId, $email));
    }
}
