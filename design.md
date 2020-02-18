# Goal for the verification bundle:
Provide a simple, yet flexible, means of user email verification after registration.
The bundle's simplicity must not be so much where it is useless without heavy modification.
That said, the flexibility of the bundle should not be to the point that it covers
every use case. But flexible enough to cover use cases that require session storage
and persistence storage of the verification components. 

## Synopsis

A user registers with the application providing a password, email, and any
 other required information. At the time of registration, the user is
  authenticated and an email is sent to the user with a signed URL. When the
   user goes to the link in the email, if not already authenticated, the user is
   redirected to do so. After authentication is complete, the user is directed 
   back to the URL provided, and the account is marked as validated. 
   
## Design theory

The signed URI segment consists of a 2 part variable length string as shown
 below.

`$uri = 15820587088b382b4961e07f823f853e0117aae8a9ca71aa5c5c028499cc5f11df9b4ee93d`

The first part of the URI segment is the time the URI expires in the form of
 a UNIX time stamp. As the length of the time stamp can be guessed based on
  the context of it's intended use, it's safe to assume that the length will
   be 10 characters long until the year 2283? `1582058708` can be extracted from
   the signed URI above using PHP's `substr` function as demonstrated below.
   
```
$timestamp = (int) substr($uri, 0, 10);
```

The second part of the URI segment is a hashed token. The token consists of
 JSON encoded data comprised of the expired unix timestamp and a unique user
 identifier. The JSON encoded data is then passed to PHP's `hash_hmac` function
 using the `sha256` encryption algorithm. Below is a full example of how the
  token is generated.
  
```
$signingKey = 'some-super-secret-valud';

$expires = (new \DateTimeImmutable('now'))
   ->modify(sprintf('+%d seconds', 900));

$encodedData = \json_encode([$expires->getTimestamp(), $userId]);

$token = \hash_hmac('sha256', $encodedData, $signingKey, false);
```

As the expires timestamp is a known length within the context of it's
 intended usage, one can extract the token from the URI by again using the
  `substr` method.
 
```
$token = substr($uri, 10);

echo $token;

8b382b4961e07f823f853e0117aae8a9ca71aa5c5c028499cc5f11df9b4ee93d
```

## In Practice

In Symfony, we can use signed URI's to provide a means of verifying a user's
email address by providing the signed URI to the user via email at the time
of registration. When the user open's the link in the web browser, the
controller check's if the user is authenticated, then check's if
the email address has already been verified by calling `isVerified()` on
the user entity. If it has, then we redirect early. Otherwise, the URI is
passed to the helper class and validated.

### Validation

To validate the token, we first must create a new "verifier" token to compare
 the URI to. To create the verifier token, we extract the timestamp from the
  URI as described  above and get the authenticated user's identifier from
  Symfony. These 2 pieces of information are then passed to the 
  helper's `isValid` method where a token is created, and then compared
   against the URI using `hash_equals()`. If `isValid` returns `true`, the 
controller call's the user entity repositories `markAsVerified()` method and 
the user entity is updated within persistence accordingly.

## Benefits

Using signed URI's as described above create less technical debt in a Symfony
 application as compared to other form's of email validation. No tokens are 
 stored in persistence, 

## Mitigating Abuse
An exit early strategy MUST be used within your business logic with regard's to
token validation. See the list below:

- Is the user currently authenticated at the time of hitting the signed URI
 endpoint? If not, no need to proceed as a unique user identifier is required to validate the signed URI.

- Has the user already been verified? If so, redirect now. No need to verify
 the user again and waste resources.

- Has the signed URI expired? Check the time stamp portion of the signed
 URI against the current time.

- Finally, create and compare the verifier token with token extracted
 from the signed URI.

- If all the above check's have passed, you can now set the user entity
 verified property to true.

As the endpoint for validation will look something like 
`https://domain.com/verify-email/{token}` it will possible for bad actors to
 hit this endpoint with undesired input. Care should be taken in escaping the
 token (signed URI) before passing it onto be verified. Using best practices
 and the method's described above, signed URI's can be safely used to verify
 email addresses and other data.

# Envisioned design for the registration verification prototype

## Event timeline

- create registration event that's fired after user is persisted.
- verification listener is hit by registration event.

Verify-bundle takes over:
- check user is authenticated
- flag user entity in persistence as "unverified account" or something along those lines.
(if not done at time of user creation)
- verification token is generated and stored in current authenticated session.
- email is generated and sent to user w/ verification link

User has X time to verify e-mail (possibly sms, phone, etc.... down the road);

this time needs to be configurable by the app, default to set time, or allow infinite time to verify.
default course of action if user doesnt verify by x time? disable account? probably nothing.
app config option can override the default action.

option to hard delete account if not verified? this would need to be implemented via
garbage collection or something along those lines... getting into outer space here...

Route hit w/ token:

- token is verified much like in the reset password bundle.
- unverified flag removed from user entity..
- destroy any session variables that were set..

What if user doesnt authenticate for a couple of days?
session storage of the token wouldn't work.. would have to revert to a token storage
model along the lines of the reset password bundle...