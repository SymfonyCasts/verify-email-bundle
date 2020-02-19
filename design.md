# Verification Bundle:
Provides a simple, yet flexible, means of user email verification after
 registration utilizing signed url's and Symfony's Maker Bundle.

## Synopsis

A user registers with the application providing a password, email, and any
 other required information. At the time of registration, the user is
 authenticated and an email is sent to the user with a signed URL. When the
 user opens the link, the users account is flagged as being verified.
   
## Internal Design Theory

The signed URI segment is a variable length string comprised of 2 parts, an
 embedded timestamp and token. The end user will see a URI segment similar to:

`$uri = 15820587088b382b4961e07f823f853e0117aae8a9ca71aa5c5c028499cc5f11df9b4ee93d`

The first part of the segment is the time the URI expires in the form of
 a UNIX time stamp. As the length of the time stamp can be guessed based on
 the context of it's intended usage, it's safe to assume that the length will
 be the first 10 characters. After the year 2283ish, the timestamp would become
 the first 11 characters. Using the example URI string above, `1582058708` is
  extracted from the URI using PHP's `substr()` function as demonstrated below.
   
```
$expiresAt = (int) substr($uri, 0, 10);
```

The second part of the URI segment is a hashed token. As the bundle knows the
 length of the timestamp as explained above. Extracting the token from the
  URI segment is also done using the `substr()` function. 
  
```
$token = substr($uri, 10);
echo $token;
8b382b4961e07f823f853e0117aae8a9ca71aa5c5c028499cc5f11df9b4ee93d
```
  
The token is created using PHP's `hash_hmac` function. Using the `sha256` encryption
algorithm, a JSON encoded data string comprised of the unix timestamp
and a unique user identifier, and finally the application's `app_secret`. The
token generated is a secure one-way encrypted string perfect, and safe
, for signing a URI. Below is a full example of how the token is generated
within the bundle...
  
```
$signingKey = 'some-super-secret-value';

$expires = (new \DateTimeImmutable('now'))
   ->modify(sprintf('+%d seconds', 900));

$encodedData = \json_encode([$expires->getTimestamp(), $userId]);

$token = \hash_hmac('sha256', $encodedData, $signingKey, false);
```

---
_@TODO - Hold off, lets code out some details_
 
As a developer, you will only need to deal with the bundle's helper method,
 `VerifierHelper::generateSignature()` which returns the `SignatureComponents
 ::class`. which has the `getSignature()` method. Internally this method
  creates a
  signing token and returns the final signed URI segment which is emailed to the
  user as a fully, formed URI.
 
 ---

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

### Reverse Engineering

Care should be taken when considering the utilization of signed URI's to 
access / modify / remove protected resources. If the signing key becomes
 exposed, a bad actor can reverse engineer signed URI's. 

#### Plausible theoretical example:
 User "A" has become aware of the signing key used in the `hash_hmac` function
 that generates the token used in the signed URI. Assuming that the user has
 already created an account with a valid email address, and verified the
 address on your site. The bad actor could create tokens and compare his tokens
 against the token provided within the original verification email. Once the
  user has created a match, he/she could then create fake account's with fake
  email addresses. 

While the above scenario is somewhat far fetched one way to mitigate such
 abuse would be to use UUID's for the user identifier used when encoding the
 JSON data for `hash_hmac()` rather than a int based id generated by MySQL.
 And of course, take great care in safe guarding the signing key used to
 generate tokens.

The above was added to get you thinking about other ways this strategy could be
abused. If you think of possible security vector, please report it to XYZ...

## Going forward
_Idea's not promises..._

- Event based verification for advanced applications using Symfony's Event
 system.

- Verification logging