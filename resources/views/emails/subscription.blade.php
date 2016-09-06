<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
    </head>
    <body>
        <h2>{{$options->website}}</h2>
        <hr/>
        <small>A brief summary of {{$options->website}} since your last visit.</small>

        <h3>Latest Posts</h3>

        @foreach($topics as $key => $value)
          <h3><a href="{{$options->baseurl}}topic/{{$value->topicSlug}}">{{$value->topicTitle}}</a><h3>
          <br/>
          {{Str::words(html_entity_decode($value->topicBody, 300))}}
          <hr/>
        @endforeach      
    </body>
</html>
