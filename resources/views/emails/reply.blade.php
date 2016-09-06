<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <h2>{{$replyAuthor->displayName}} has sent you a reply.</h2>

        <div>
          {{$reply->replyBody}}
          <br/>
          You can view the reply here: {{ URL::to('topic/'.$topic->topicSlug.'#'.$reply->id) }}
        </div>

    </body>
</html>
