# VerifyEmailBundle: Love Confirming Emails

Don't know if your users have a valid email address? The VerifyEmailBundle can
help! 

VerifyEmailBundle generates - and validates - a secure, signed URL
that can be emailed to users to confirm their email address. It
does this without needing any storage, so you can use your existing
entities with minor modifications. This bundle provides:

- A generator to create a signed URL that should be emailed to the user.
- A signed URL validator.
- Peace of mind knowing that this is done without leaking the user's
   email address into your server logs (avoiding PII problems).

## Installation

Using Composer of course!

```bash
composer require symfonycasts/verify-email-bundle
```

## Usage

We strongly suggest using Symfony MakerBundle's `make:registration-form` command
to get a feel for how the bundle should be used. It's super simple! Answer a couple
questions, and you'll have a fully functional secure registration system with
email verification.

```bash
bin/console make:registration-form
```

## Setting Things Up Manually

If you want to set things up manually, you can! But do so carefully: email
verification is a sensitive, security process. We'll guide you through the
important stuff. Using `make:registration-form` is still the easiest and
simplest way.

The example below demonstrates the basic steps to generate a signed URL
that is to be emailed to a user after they have registered. The URL is then 
validated once the user "clicks" the link in their email. 

The example below utilizes Symfony's `AbstractController` available in the 
[FrameworkBundle](https://github.com/symfony/framework-bundle):

```php
// RegistrationController.php

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
// ...

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
        $email->from('send@example.com');
        $email->to($user->getEmail());
        $email->htmlTemplate('registration/confirmation_email.html.twig');
        $email->context(['signedUrl' => $signatureComponents->getSignedUrl()]);
        
        $this->mailer->send($email);
    
        // generate and return a response for the browser
    }
}
```

Once the user has received their email and clicked on the link, the `RegistrationController`
would then validate the signed URL in following method:

```php
// RegistrationController.php

use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
// ...

class RegistrationController extends AbstractController
{
    // ...
    /**
     * @Route("/verify", name="registration_confirmation_route")
     */
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        // Do not get the User's Id or Email Address from the Request object
        try {
            $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());
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

## Anonymous Validation

It is also possible to allow users to verify their email address without having
to be authenticated. A use case for this would be if a user registers on their laptop,
but clicks the verification link on their phone. Normally, the user would be
required to log in before their email was verified. 

We can overcome this by passing a user identifier as a query parameter in the
signed url. The diff below demonstrate how this is done based off of the previous
examples:

```diff
// RegistrationController.php

class RegistrationController extends AbstractController
{
    public function register(): Response
    {
        $user = new User();
    
        // handle the user registration form and persist the new user...
    
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
                'registration_confirmation_route',
                $user->getId(),
-               $user->getEmail()
+               $user->getEmail(),
+               ['id' => $user->getId()] // add the user's id as an extra query param
            );
    }
}
```

Once the user has received their email and clicked on the link, the RegistrationController
would then validate the signed URL in the following method:

```diff
// RegistrationController.php

+use App\Repository\UserRepository;

class RegistrationController extends AbstractController
{
-   public function verifyUserEmail(Request $request): Response
+   public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {
-       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
-       $user = $this->getUser();

+       $id = $request->get('id'); // retrieve the user id from the url
+
+       // Verify the user id exists and is not null
+       if (null === $id) {
+           return $this->redirectToRoute('app_home');
+       }
+
+       $user = $userRepository->find($id);
+
+       // Ensure the user exists in persistence
+       if (null === $user) {
+           return $this->redirectToRoute('app_home');
+       }

        try {
            $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());
        } catch (VerifyEmailExceptionInterface $e) {
        // ...
    }
}
```

## Configuration

You can change the default configuration parameters for the bundle by creating
a `config/packages/verify_email.yaml` config file:

```yaml
symfonycasts_verify_email:
    lifetime: 3600
```

#### `lifetime`

_Optional_ - Defaults to `3600` seconds

This is the length of time a signed URL is valid for in seconds after it has
been created. 

## Reserved Query Parameters

If you add any extra query parameters in the 5th argument of `verifyEmailHelper::generateSignature()`,
such as we did for `id` above, take note that you cannot use the following query parameters, because
they will be overwritten by this bundle:

- `token`
- `expires`
- `signature`

## Support

Feel free to open an issue for questions, problems, or suggestions with our bundle.
Issues pertaining to Symfony's MakerBundle, specifically `make:registration-form`,
should be addressed in the [Symfony Maker repository](https://github.com/symfony/maker-bundle).

## Security Issues
For **security related vulnerabilities**, we ask that you send an email to 
`ryan [at] symfonycasts.com` instead of creating an issue. 

This will give us the opportunity to address the issue without exposing the
vulnerability before a fix can be published.
