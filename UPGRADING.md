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
