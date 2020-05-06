# VerifyEmailBundle
# Bundle is a WIP and not ready for use.

Don't know if your user's have a valid email address? The VerifyEmailBundle can
help!

## Installation

The bundle can be installed using Composer or the [Symfony binary](https://symfony.com/download):

```
composer require symfonycasts/verify-email-bundle
```

## Usage

## Setting things up manually

## Configuration

You can change the default configuration parameters for the bundle in a 
`config/packages/verify_email.yaml` config file.

```
symfonycasts_verify_email:
  lifetime: 3600
```

#### `lifetime`

_Optional_ - Defaults to `3600` seconds

This is the length of time a reset password request is valid for in seconds 
after it has been created. 

## Support

Feel free to open an issue for questions, problems, or suggestions with our bundle.
Issues pertaining to Symfony's Maker Bundle, specifically `make:reset-password`,
should be addressed in the [Symfony Maker repository](https://github.com/symfony/maker-bundle).

## Security Issues
For **security related vulnerabilities**, we ask that you send an email to 
`ryan [at] symfonycasts.com` instead of creating an issue. 

This will give us the opportunity to address the issue without exposing the
vulnerability before a fix can be published.
