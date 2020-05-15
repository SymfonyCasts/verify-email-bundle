# VerifyEmailBundle: Love Confirming Emails

Don't know if your user's have a valid email address? The VerifyEmailBundle can
help! 

VerifyEmailBundle generates - and validates - a secure, signed URL
that can be emailed to users to confirm their email address. It
does this without needing any storage, so you can use your existing
entities with minor modifications. This bundle provides:

1) A generator to create a signed URL that should be emailed to the user.

2) A signed URL validator.

3) Peace of mind knowing that this is done without leaking the user's
   email address into your server logs (avoiding PII problems).

## Installation

Using Composer of course!

```
composer require symfonycasts/verify-email-bundle
```

## Usage

We strongly suggest using Symfony MakerBundle's `make:registration-form` to get
a feel for how the bundle should be used. It's super simple! Answer a couple
questions, and you'll have a fully functional secure registration system with
email verification.

## Setting things up manually

If you want to set things up manually, you can! But do so carefully: email
verification is a sensitive, security process. We'll guide you through the
important stuff. Using `make:registration-form` is still the easiest and
simplest way.

After running `make:registration-form` and understanding how to use this bundle,
you can validate a users email address anytime. An example would be if the 
user updates their email address.

```
// ExampleUserProfileController.php

class ExampleUserProfileController extends AbstractController
....

/**
 * @Route("/user/new-email", name="update-user-email")
 */
public function updateMyEmailAddress()
{
    $user = $this->getUser();

    // Use a form to change the users email and then persist the updated user object
    
    $helper = new VerifyEmailHelper(....);
    $signatureComponents = $helper->generateSignature(
        'validate-user-email-route-name',
        $user->getId(),
        $user->getEmail()
    );
    
    $signedUrl = $signatureComponents->getSignedUrl();

    // email the $signedUrl to the user
}

/**
 * @Route("/user/validate", name="validate-user-email-route-name")
 */
public function validateSignedUrlEmailedToTheUser(Request $request)
{
    // Deny access to this method if the user is not authenticated

    $user = $this->getUser();
    
    $helper = new VerifyEmailHelper(....)
    $bool = $helper->isValidSignature($request->getUri(), $user->getId(), $user->getEmail());
    
    // If $bool is true, the email address is presumed valid, carry on with your
    // business logic. e.g. mark the user object as verified and persist.
}

....
}
```

It is _critical_ that you require the user to be logged in and fetch the
user identifier and email (e.g. `$user->getid()` and `$user->getEmail()`)
from that authenticated user (not from anywhere in the URL).

## Configuration

You can change the default configuration parameters for the bundle by creating
a `config/packages/verify_email.yaml` config file.

```
symfonycasts_verify_email:
    lifetime: 3600
```

#### `lifetime`

_Optional_ - Defaults to `3600` seconds

This is the length of time a signed URL is valid for in seconds after it has
been created. 

## Support

Feel free to open an issue for questions, problems, or suggestions with our bundle.
Issues pertaining to Symfony's Maker Bundle, specifically `make:registration-form`,
should be addressed in the [Symfony Maker repository](https://github.com/symfony/maker-bundle).

## Security Issues
For **security related vulnerabilities**, we ask that you send an email to 
`ryan [at] symfonycasts.com` instead of creating an issue. 

This will give us the opportunity to address the issue without exposing the
vulnerability before a fix can be published.
