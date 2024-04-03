# Upgrade from 1.x to 2.0

## UriSignerFactory

- The `UriSignerFactory` became `@internal` & `@final`. This class should not be
used.

## VerifyEmailHelper

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


## VerifyEmailSignatureComponents

- Providing an `int` to the constructor parameter `$generatedAt` is now required
when instantiating a new `VerifyEmailSignatureComponents` instance.

```diff
- public function __construct(\DateTimeInterface $expiresAt, string $uri, ?int $generatedAt = null)
+ public function __construct(\DateTimeInterface $expiresAt, string $uri, int $generatedAt)
```

- Method's `getExpirationMessageKey`, `getExpirationMessageData`, & `getExpiresAtIntervalInstance`
no longer potentially throw a `LogicException`.
