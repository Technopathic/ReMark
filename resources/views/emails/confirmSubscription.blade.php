<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <h2>Confirm your Subscription</h2>

        <div>
            Hi!
            Thanks for following us @{{website}}, we'll be sure to bring your great content, weekly.
            Please follow the link below to verify your email address.
            {{ URL::to('notify/confirmSubscription/' . $subscriptionID) }}.<br/>
        </div>

    </body>
</html>
