# Upgrade from 1.x to 2.0

## UriSignerFactory

- The `UriSignerFactory` became `@internal` & `@final`. This class should not be
used.

## VerifyEmailHelper

- `__construct()` no longer has a `VerifyEmailQueryUtility $queryUtility` argument

```diff
 public function __construct(
     private UrlGeneratorInterface $router,
     private UriSigner $uriSigner,
-    private VerifyEmailQueryUtility $queryUtility,
     private VerifyEmailTokenGenerator $tokenGenerator,
     private int $lifetime
 ) {
 }
```

- `VerifyEmailHelperInterface::validateEmailConfirmation()` is deprecated since
`v1.17.0` and will be removed in `v2.0.0`. Use `validateEmailConfirmationFromRequest()`
instead.

```diff
// src/Security/EmailVerifier.php
class EmailVerifier
{
    ...
    public function handleEmailConfirmation(Request $request, UserInterface $user): void
    {
-        $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());
+        $this->verifyEmailHelper->validateEmailConfirmationFromRequest($request, $user->getId(), $user->getEmail());
        ...
    }
}
```

- `VerifyEmailHelperInterface::generateSignature(extraParams: [])` added the array
shape expected for the `extraParams` argument.

```diff
- @param array $extraParams
+ @param array<string, int|string> $extraParams
```

- `generateSignature()` now throws a `VerifyEmailRuntimeException` if unable to create a `DateTimeInterface`
instance from the sum of a timestamp and the `$lifetime` value passed to the class constructor


## VerifyEmailSignatureComponents

- Providing an `int` to the constructor parameter `$generatedAt` is now required
when instantiating a new `VerifyEmailSignatureComponents` instance.

```diff
- public function __construct(\DateTimeInterface $expiresAt, string $uri, ?int $generatedAt = null)
+ public function __construct(\DateTimeInterface $expiresAt, string $uri, int $generatedAt)
```

- Method's `getExpirationMessageKey`, `getExpirationMessageData`, & `getExpiresAtIntervalInstance`
no longer potentially throw a `LogicException`. They now throw a `VerifyEmailRuntimeException`
if an invalid `$generatedAt` timestamp is provided to the class constructor.

- Added array shape typehint for the return value of `getExpirationMessageData()`

```diff
- @return array
+ @return array<string, int>
```

## VerifyEmailTokenGenerator

- Method `createToken()` now throws a `VerifyEmailRuntimeException` if unable to `json_encode()` the
  `$userId` & `$email` arguments.
