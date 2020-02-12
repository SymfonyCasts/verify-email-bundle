# Goal for the verification bundle:
Provide a simple, yet flexible, means of user email verification after registration.
The bundle's simplicity must not be so much where it is useless without heavy modification.
That said, the flexibility of the bundle should not be to the point that it covers
every use case. But flexible enough to cover use cases that require session storage
and persistence storage of the verification components. 

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