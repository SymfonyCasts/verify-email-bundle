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

The example below demonstrates the basic steps to generate a signed URL
that is to be emailed to a user after they have registered. The URL is then 
validated once the user "clicks" the link in their email. 

The example below utilizes Symfony's `AbstractController` available in the 
[Framework Bundle](https://github.com/symfony/framework-bundle)

```php
// RegistrationController.php

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
...

class RegistrationController extends AbstractController
{
    private $verifyEmailHelper;
    private $mailer;
    
    public function __construct(VerifyEmailHelperInterface $helper, MailerInterface $mailer)
    {
        $this->verifyEmailHelper = $helper;
        $this->mailer = $mailer;
    }
    
    /**
     * @Route("/register", name="register-user")
     */
    public function register(): Response
    {
        $user = new User();
    
        // handle the user registration form and persist the new user...
    
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
                'registration_confirmation_route',
                $user->getId(),
                $user->getEmail()
            );
        
        $email = new TemplatedEmail();
        $email->to($user->getEmail());
        $email->htmlTemplate('registration/confirmation_email.html.twig');
        $email->context(['signedUrl' => $signatureComponents->getSignedUrl()]);
        
        $this->mailer->send($email)
    
        // generate and return a response for the browser
    }
...

```

Once the user has received their email and clicked on the link, the RegistrationController
would then validate the signed URL in following method:

```php
// RegistrationController.php

use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
...

    /**
     * @Route("/verify", name="registration_confirmation_route")
     */
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Do not get the User's Id or Email Address from the Request object
        try {
            $this->helper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail())
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('verify_email_error', $e->getReason());

            return $this->redirectToRoute('app_register');
        }

        // Mark your user as verified. e.g. switch a User::verified property to true

        $this->addFlash('success', 'Your e-mail address has been verified.');

        return $this->redirectToRoute('app_home');
    }
}
```

It is _critical_ that you require the user to be logged in and fetch the
user identifier and email (e.g. `$user->getid()` and `$user->getEmail()`)
from that authenticated user (not from anywhere in the URL).

## Configuration

You can change the default configuration parameters for the bundle by creating
a `config/packages/verify_email.yaml` config file.

```yml
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
