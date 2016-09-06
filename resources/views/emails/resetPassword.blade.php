<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <h2>Reset Your Password</h2>

        <div>
          Hi!
          This password reset was sent to you because you claimed to have forgotten your password for {{$website}}. To reset your password and create a new one,
          click this link:  {{ URL::to('resetPassword/' . $token) }}
          This form will only be available for 24 hours.

          If you did not ask for a password reset, ignore this email and delete it.<br/>
        </div>

    </body>
</html>
