# Upgrade from 1.x to 2.0

## UriSignerFactory

- The `UriSignerFactory` became `@internal` & `@final`. This class should not be
used.

## VerifyEmailSignatureComponents

- Providing an `int` to the constructor parameter `$generatedAt` is now required
when instantiating a new `VerifyEmailSignatureComponents` instance.

```diff
- public function __construct(\DateTimeInterface $expiresAt, string $uri, ?int $generatedAt = null)
+ public function __construct(\DateTimeInterface $expiresAt, string $uri, int $generatedAt)
```

- Method's `getExpirationMessageKey`, `getExpirationMessageData`, & `getExpiresAtIntervalInstance`
no longer potentially throw a `LogicException`.
