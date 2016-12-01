<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">

        <style>
          .container {
            text-align:center;
          }

          .signButton {
            border-radius:20px;
            padding:15px 10px;
            background:#1E90FF;
            color:#FFFFFF;
            text-decoration:none;
            font-variant:small-caps;
            font-size:2em;
          }

          h2 {
            font-variant:small-caps;
            padding-bottom:15px;
            border-bottom:1px solid #DDDDDD;
          }

          p {
            font-size:1em;
          }

          span {
            font-size:0.9em;
          }

          span a {
            color:#555555;
          }

        </style>
    </head>
    <body>
        <h2>{{$website}}</h2>

        <div class="container">
          <p>Click and confirm that you want to sign in to {{$website}}. This link will expire in fifteen minutes and can only be used once.</p>
          <br/>
          <br/>
          <a class="signButton" href="{{ URL::to('confirm/' . $token) }}">Sign in to {{$website}}</a>
          <br/>
          <br/>
          <br/>
          <p>
            <span>Or sign in using this link:
              {{ URL::to('confirm/' . $token) }}
            </span>
          </p>

        </div>

    </body>
</html>
