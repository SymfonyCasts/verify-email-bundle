# VerifyEmailBundle

Don't know if your user's have a valid email address? The VerifyEmailBundle can
help! 

Because it's stateless, Verify Email Bundle will generate and validated
 fully qualified and secure signed URL's that can be emailed to your users using
 your existing entities with only minor modifications.

## Installation

The bundle can be installed using Composer or the [Symfony binary](https://symfony.com/download):

```
composer require symfonycasts/verify-email-bundle
```

## What this bundle provides

1) A generator to create secure fully qualified signed URL's comprised of a unique
 user identifier, unique user email address, and any other query params your
 business logic requires.
 This URL should be emailed to the user for validation.

2) A signed URL validator.

3) Peace of mind knowing user credentials, personally identified information, and
design principles of your app will not be leaked in to server logs or emails.

## Usage

We strongly suggest using Symfony Maker Bundle's `make:registration-form` to get
 a feel for how the bundle should be used. It's super simple! Answer a couple 
 questions, and you'll have a fully functional secure registration system with
 email verification.
 
_If your business logic requires additonal query parameters to be used in the signed
 URL for email validation - please note that the `expires`, `signature`, & `token` 
 query parameter keys are reserved for use by the Verify Email Bundle._

## Setting things up manually

_Implementing this bundle manually without fully understanding the design principles
 behind it - may result in security vulnerabilities within your application._
 
 _You really should try `make:registration-form` first before rolling-your-own
 implementation._

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

It is _critical_ that - 

1) The user identifier and email address should be retrieved from within your
application, not from the user when validating the signed URL. e.g. Require user
to be logged in and retrieve user identifier from the session.

2) The URL being signed then validated should be fully qualified. e.g. 
`https://your-domain.com/verify/user` not just `/verify/user`

Failure to follow the above guidelines will circumvent the security features this
bundle provides.

## Configuration

You can change the default configuration parameters for the bundle in a 
`config/packages/verify_email.yaml` config file.

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
