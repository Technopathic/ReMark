<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use JWTAuth;
use Auth;
use App\Mtopic;
use App\Mreply;
use App\User;
use App\Role;
use App\Mchannel;
use App\Option;
use App\Message;
use App\Notification;
use \Input;
use \Response;
use \Image;
use \Session;
use \Redirect;
use \DB;

class RemarkAdminsController extends Controller
{
  public function __construct()
  {
    $this->middleware('jwt.auth');
  }

  public function getContent()
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $channels = Mchannel::select('id', 'channelTitle', 'channelDesc', 'channelImg', 'channelTopics', 'channelSlug', 'created_at')->paginate(10);
      $topics = Mtopic::join('mchannels', 'mtopics.topicChannel', '=', 'mchannels.id')->select('mtopics.id', 'mtopics.topicTitle', 'mtopics.topicThumbnail', 'mtopics.topicChannel', 'mtopics.topicSlug', 'mtopics.topicViews', 'mtopics.topicReplies', 'mtopics.topicVotes', 'mtopics.topicFeature', 'mtopics.topicTags', 'mtopics.topicType', 'mtopics.pageMenu', 'mtopics.allowReplies', 'mtopics.showImage', 'mtopics.topicStatus', 'mtopics.created_at', 'mchannels.channelTitle')->orderBy('mtopics.created_at', 'DESC')->paginate(10);

      return Response::json(['channels' => $channels, 'topics' => $topics]);
    } else {
      return Response::json(403);
    }
  }

  public function getTopics($id)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      if($id == 0)
      {
        $topics = Mtopic::join('mchannels', 'mtopics.topicChannel', '=', 'mchannels.id')->select('mtopics.id', 'mtopics.topicTitle', 'mtopics.topicThumbnail', 'mtopics.topicChannel', 'mtopics.topicSlug', 'mtopics.topicViews', 'mtopics.topicReplies', 'mtopics.topicVotes', 'mtopics.topicFeature', 'mtopics.topicTags', 'mtopics.topicType', 'mtopics.pageMenu', 'mtopics.allowReplies', 'mtopics.showImage', 'mtopics.topicStatus', 'mtopics.created_at', 'mchannels.channelTitle')->orderBy('mtopics.created_at', 'DESC')->paginate(10);
      } else {
        $topics = Mtopic::where('mtopics.topicChannel', '=', $id)->join('mchannels', 'mtopics.topicChannel', '=', 'mchannels.id')->select('mtopics.id', 'mtopics.topicTitle', 'mtopics.topicThumbnail', 'mtopics.topicChannel', 'mtopics.topicSlug', 'mtopics.topicViews', 'mtopics.topicReplies', 'mtopics.topicVotes', 'mtopics.topicFeature', 'mtopics.topicTags', 'mtopics.topicType', 'mtopics.pageMenu', 'mtopics.allowReplies', 'mtopics.showImage', 'mtopics.topicStatus', 'mtopics.created_at', 'mchannels.channelTitle')->orderBy('mtopics.created_at', 'DESC')->paginate(10);
      }

      return Response::json($topics);
    } else {
      return Response::json(403);
    }
  }

  public function getTopic($id)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $topic = Mtopic::where('mtopics.id', '=', $id)->join('mchannels', 'mtopics.topicChannel', '=', 'mchannels.id')->select('mtopics.id', 'mtopics.topicTitle', 'mtopics.topicBody', 'mtopics.topicImg', 'mtopics.topicAudio', 'mtopics.topicVideo', 'mtopics.topicThumbnail', 'mtopics.topicChannel', 'mtopics.topicSlug', 'mtopics.topicViews', 'mtopics.topicReplies', 'mtopics.topicAuthor', 'mtopics.topicFeature', 'mtopics.topicTags', 'mtopics.topicType', 'mtopics.allowReplies', 'mtopics.showImage', 'mtopics.created_at', 'mchannels.channelTitle')->first();

      $replies = Mreply::where('mreplies.topicID', '=', $id)->where('mreplies.replyParent', '=', 0)->join('users', 'mreplies.replyAuthor', '=', 'users.id')->orderBy('mreplies.created_at', 'ASC')->select('mreplies.id', 'mreplies.replyParent', 'mreplies.created_at', 'mreplies.replyBody', 'mreplies.childCount', 'mreplies.replyFlagged', 'mreplies.replyFeature', 'mreplies.replyApproved', 'users.avatar', 'users.name', 'users.displayName')->get()->toArray();
      $childReplies = Mreply::where('mreplies.topicID', '=', $id)->where('mreplies.replyParent', '!=', 0)->join('users', 'mreplies.replyAuthor', '=', 'users.id')->orderBy('mreplies.created_at', 'ASC')->select('mreplies.id', 'mreplies.replyParent', 'mreplies.created_at', 'mreplies.replyBody', 'users.avatar', 'mreplies.replyFlagged', 'mreplies.replyFeature', 'mreplies.replyApproved', 'users.name', 'users.displayName')->get()->toArray();

      foreach($replies as $key => $reply)
      {
        $replies[$key]['showChildren'] = 0;
        $replies[$key]['childReplies'] = array();
        foreach($childReplies as $key2 => $child)
        {
          if($reply['id'] == $child['replyParent'])
          {
            $replies[$key]['childReplies'][] = $child;
          }
        }
      }

      return Response::json(['topic' => $topic, 'replies' => $replies]);
    } else {
      return Response::json(403);
    }
  }

  public function getChannels()
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $channels = Mchannel::select('id', 'channelTitle', 'channelDesc', 'channelImg', 'channelTopics', 'channelSlug', 'created_at')->paginate(10);
      return Response::json($channels);
    } else {
      return Response::json(403);
    }
  }

  public function createTopic()
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $channels = Mchannel::where('channelArchived', '=', 0)->select('id', 'channelTitle')->get();

      return Response::json($channels);

      return Response::json($channels);
    }
    else {
      return Response::json(403);
    }
  }

  public function storeTopic(Request $request)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $validator = Validator::make($request->all(), [
        'topicTitle'  =>  'required',
        'topicChannel' => 'required'
      ]);

      if ($validator->fails()) {
        return Response::json(0);
      }
      else {

        $topicTitle = $request->input('topicTitle');
        $topicBody = $request->input('topicBody');
        $topicChannel = $request->input('topicChannel');
        $topicTags = $request->input('topicTags');
        $topicStatus = $request->input('topicStatus');
        $topicType = $request->input('topicType');
        $allowReplies = $request->input('allowReplies');
        $showImage = $request->input('showImage');
        $topicImgSize = "Large";

        if (preg_match('/[A-Za-z]/', $topicTitle) || preg_match('/[0-9]/', $topicTitle))
        {
          $topicSlug = str_replace(' ', '-', $topicTitle);
          $topicSlug = preg_replace('/[^A-Za-z0-9\-]/', '', $topicSlug);
          $topicSlug = preg_replace('/-+/', '-', $topicSlug);

          if(strlen($topicSlug > 15))
          {
            $topicSlug = substr($topicSlug, 0, 15);
          }

          if (Mtopic::where('topicSlug', '=', $topicSlug)->exists()) {
             $topicSlug = $topicSlug.'_'.mt_rand(1, 9999);
          }

          $imageFile = 'storage/media/topics/image';
          if (!is_dir($imageFile)) {
            mkdir($imageFile,0777,true);
          }

          if(!$request->has('topicImg'))
          {
            if($request->file('topicImg'))
            {
              $topicImg = $request->file('topicImg');
              $ext = $topicImg->getClientOriginalExtension();
              $topicImg->move($imageFile, $topicSlug.'.'.$ext);

              $topicImg = $imageFile.'/'.$topicSlug.'.'.$ext;

              $topicThumbnail = 'storage/media/topics/image/thumbnails/'.$topicSlug.'_thumbnail.png';
              $img = Image::make($topicImg);

              list($width, $height) = getimagesize($topicImg);
              if($width > 500)
              {
                $img->resize(500, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                if($height > 300)
                {
                  $img->crop(500, 300);
                }
              }
              $img->save($topicThumbnail);
            }
            else {
              $topicImg = 0;
              $topicThumbnail = 0;
            }
          }
          elseif($request->has('topicImg'))
          {

            if(filter_var($request->input('topicImg'), FILTER_VALIDATE_URL) == true)
            {
              $topicImg = $request->input('topicImg');
              $topicImg = Image::make($topicImg);
              $ext = 'png';
              $topicImg->save($imageFile.'/'.$topicSlug.'.'.$ext);

              $topicImg = $imageFile.'/'.$topicSlug.'.'.$ext;

              $topicThumbnail = 'storage/media/topics/image/thumbnails/'.$topicSlug.'_thumbnail.png';
              $img = Image::make($topicImg);

              list($width, $height) = getimagesize($topicImg);
              if($width > 500)
              {
                $img->resize(500, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                if($height > 300)
                {
                  $img->crop(500, 300);
                }
              }
              if($width < 800)
              {
                $topicImgSize = "Small";
              }
              $img->save($topicThumbnail);
            } else {
              $topicImg = 0;
              $topicThumbnail = 0;
            }
          }

          $topic = new Mtopic;

          $topic->topicTitle = $topicTitle;
          $topic->topicBody = $topicBody;
          $topic->topicImg = $topicImg;
          $topic->topicThumbnail = $topicThumbnail;
          $topic->topicImgSize = $topicImgSize;
          $topic->topicAudio = 0;
          $topic->topicVideo = 0;
          $topic->topicChannel = $topicChannel;
          $topic->topicSlug = $topicSlug;
          $topic->topicViews = 0;
          $topic->topicReplies = 0;
          $topic->topicAuthor = Auth::user()->id;
          $topic->topicStatus = $topicStatus;
          $topic->topicArchived = 0;
          $topic->topicFeature = 0;
          $topic->topicTags = $topicTags;
          $topic->topicType = $topicType;
          $topic->allowReplies = $allowReplies;
          $topic->showImage = $showImage;
          $topic->save();

          $channelCount = Mchannel::where('id', '=', $topicChannel)->increment('channelTopics');
          $userCount = User::where('name', '=', $user->name)->increment('topics');

          $notifyUsers = User::where('role', '=', 'Administrator')->where('id', '!=', Auth::user()->id)->get();
          foreach($notifyUsers as $key => $value)
          {
            DB::table('notifications')->insert(array('userID' => $notifyUsers->id, 'notifierID' => $topicAuthor, 'contentID' => $topic->id, 'notificationType' => 'Alert', 'notificationSubType' => 'Topic', 'notificationRead' => 0));
          }

          $topicData = Mtopic::where('mtopics.id', '=', $topic->id)->join('mchannels', 'mtopics.topicChannel', '=', 'mchannels.id')->select('mtopics.id', 'mtopics.topicTitle', 'mtopics.topicBody', 'mtopics.topicImg', 'mtopics.topicThumbnail', 'mtopics.topicChannel', 'mtopics.topicSlug', 'mtopics.topicViews', 'mtopics.topicReplies', 'mtopics.topicAuthor', 'mtopics.topicFeature', 'mtopics.topicTags', 'mtopics.topicStatus', 'mtopics.topicType', 'mtopics.allowReplies', 'mtopics.showImage', 'mtopics.created_at', 'mchannels.channelTitle')->first();
          return Response::json($topicData);
        } else {
          return Response::json(0);
        }
      }
    } else {
      return Response::json(403);
    }
  }

  public function editTopic(Request $request, $id)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $topic = Mtopic::find($id);
      $channels = Mchannel::where('channelArchived', '=', 0)->select('id', 'channelTitle')->get();

      return Response::json(['topic' => $topic, 'channels' => $channels]);
    } else {
      return Response::json(403);
    }
  }

  public function updateTopic(Request $request, $id)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $rules = array(
        'topicTitle'		=> 	'required',
        'topicChannel' => 'required'
      );
      $validator = Validator::make($request->all(), $rules);

      if ($validator->fails()) {
          return Response::json(0);
      } else {

        $topic = Mtopic::find($id);

        $topicTitle = $request->input('topicTitle');
        $topicBody = $request->input('topicBody');
        $topicChannel = $request->input('topicChannel');
        $topicTags = $request->input('topicTags');
        $topicStatus = $request->input('topicStatus');
        $topicType = $request->input('topicType');
        $allowReplies = $request->input('allowReplies');
        $showImage = $request->input('showImage');
        $topicImgSize = "Large";

        if($topicTitle != NULL)
        {
          if (preg_match('/[A-Za-z]/', $topicTitle) || preg_match('/[0-9]/', $topicTitle)) {
            $topicSlug = str_replace(' ', '-', $topicTitle);
            $topicSlug = preg_replace('/[^A-Za-z0-9\-]/', '', $topicSlug);
            $topicSlug = preg_replace('/-+/', '-', $topicSlug);

            if (Mtopic::where('topicSlug', '=', $topicSlug)->where('id', '!=', $topic->id)->exists()) {
               $topicSlug = $topicSlug.'_'.mt_rand(1, 9999);
            }

            $topic->topicTitle = $topicTitle;
            $topic->topicSlug = $topicSlug;
          } else {
            return Response::json(0);
          }
        }
        if($topicBody != NULL)
        {
          $topic->topicBody = $topicBody;
        }
        if($topicChannel != NULL)
        {
          if($topic->topicChannel != $topicChannel)
          {
            Mchannel::where('id', '=', $topic->topicChannel)->decrement('channelTopics');
            Mchannel::where('id', '=', $topicChannel)->increment('channelTopics');
          }
          $topic->topicChannel = $topicChannel;
        }
        if($topicTags != NULL)
        {
          $topic->topicTags = $topicTags;
        }

        $imageFile = 'storage/media/topics/image';

        if (!is_dir($imageFile)) {
          mkdir($imageFile,0777,true);
        }

        if($request->file('topicImg'))
        {
          $topicImg = $request->file('topicImg');
          $ext = $topicImg->getClientOriginalExtension();
          $topicImg->move($imageFile, $topicSlug.'.'.$ext);

          $topicImg = $imageFile.'/'.$topicSlug.'.'.$ext;

          $topicThumbnail = 'storage/media/topics/image/thumbnails/'.$topicSlug.'_thumbnail.png';
          $img = Image::make($topicImg);

          list($width, $height) = getimagesize($topicImg);
          if($width > 500)
          {
            $img->resize(500, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            if($height > 300)
            {
              $img->crop(500, 300);
            }
          }
          if($width < 800)
          {
            $topicImgSize = "Small";
          }
          $img->save($topicThumbnail);
        }
        else {
          $topicImg = $topic->topicImg;
          $topicThumbnail = $topic->topicThumbnail;
        }

        $topic->topicImg = $topicImg;
        $topic->topicThumbnail = $topicThumbnail;
        $topic->topicImgSize = $topicImgSize;
        $topic->allowReplies = $allowReplies;
        $topic->showImage = $showImage;
        $topic->topicStatus = $topicStatus;
        $topic->topicType = $topicType;
        $topic->save();

        $topicData = Mtopic::where('mtopics.id', '=', $topic->id)->join('mchannels', 'mtopics.topicChannel', '=', 'mchannels.id')->select('mtopics.id', 'mtopics.topicTitle', 'mtopics.topicBody', 'mtopics.topicImg', 'mtopics.topicThumbnail', 'mtopics.topicChannel', 'mtopics.topicSlug', 'mtopics.topicViews', 'mtopics.topicReplies', 'mtopics.topicAuthor', 'mtopics.topicFeature', 'mtopics.topicTags', 'mtopics.topicStatus', 'mtopics.topicType', 'mtopics.allowReplies', 'mtopics.showImage', 'mtopics.created_at', 'mchannels.channelTitle')->first();
        return Response::json($topicData);
      }
    } else {
      return Response::json(403);
    }
  }

  public function deleteTopic($id)
  {
    $user = Auth::user();

    if($user->role == 1)
    {
      $topic = Mtopic::find($id);
      $replies = Mreply::where('topicID', '=', $topic->id)->get();
      $user = User::where('id', '=', $topic->topicAuthor)->first();
      $channel = Mchannel::where('id', '=', $topic->topicChannel)->first();

      if($user->topics > 0)
      {
        $user->topics = $user->topics - 1;
        $user->save();
      }

      if($channel->channelTopics > 0)
      {
        $channel->channelTopics = $channel->channelTopics - 1;
        $channel->save();
      }

      $notification = Notification::where('contentID', '=', $topic->id)->where('notificationType', '=', 'Alert')->where('notificationSubType', '=', 'Topic')->first();
      if(!empty($notification)) {
        $notification->delete();
      }

      if(!empty($replies))
      {
        foreach($replies as $key => $value)
        {

          $user = User::where('id', '=', $value->replyAuthor)->first();
          if($user->replies > 0)
          {
            $user->replies = $user->replies - 1;
            $user->save();
          }

          $notification = Notification::where('contentID', '=', $value->id)->where('notificationType', '=', 'Alert')->where('notificationSubType', '=', 'Reply')->first();
          if(!empty($notification)) {
            $notification->delete();
          }

          if($value->replyParent == 0) {
            $child = Mreply::where('replyParent', '=', $reply->id)->get();
            if(!$child->isEmpty()){
              foreach($child as $Ckey => $c)
              {
                $notification = Notification::where('contentID', '=', $c->id)->where('notificationType', '=', 'Alert')->where('notificationSubType', '=', 'Reply')->first();
                if(!empty($notification)) {
                  $notification->delete();
                }

                $user = User::where('id', '=', $c->replyAuthor)->first();
                if($user->replies > 0)
                {
                  $user->replies = $user->replies - 1;
                  $user->save();
                }
                $c->delete();
              }
            }
          }
          $value->delete();
        }
      }
      $topic->delete();

      return Response::json(1);
    } else {
      return Response::json(403);
    }
  }

  public function setFeature($id)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $topic = Mtopic::find($id);
      if($topic->topicImg != '0')
      {
        if($topic->topicFeature == 0)
        {
          $topic->topicFeature = 1;
          $topic->save();
          //Feature
          return Response::json(1);
        }
        else if($topic->topicFeature == 1)
        {
          $topic->topicFeature = 0;
          $topic->save();
          //Unfeature
          return Response::json(0);
        }
      } else {
        //No Image
        return Response::json(2);
      }
    } else {
      return Response::json(403);
    }
  }

  public function createChannel()
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      return Response::json(1);
    } else {
      return Response::json(403);
    }
  }

  public function storeChannel(Request $request)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $rules = array(
        'channelTitle'	=> 	'required'
      );
      $validator = Validator::make($request->all(), $rules);

      if ($validator->fails()) {
          return Response::json(0);
      } else {

        $channelTitle = $request->input('channelTitle');
        $channelDesc = $request->input('channelDesc');
        $channelImg = $request->file('channelImg');

        if (preg_match('/[A-Za-z]/', $channelTitle) || preg_match('/[0-9]/', $channelTitle)) {
          $channelSlug = str_replace(' ', '-', $channelTitle);
          $channelSlug = preg_replace('/[^A-Za-z0-9\-]/', '', $channelSlug);
          $channelSlug = preg_replace('/-+/', '-', $channelSlug);

          if (Mchannel::where('channelSlug', '=', $channelSlug)->exists()) {
             $channelSlug = $channelSlug.'_'.mt_rand(1, 9999);
          }
          if(empty($channelDesc))
          {
            $channelDesc = "No Description";
          }
          if(empty($channelImg))
          {
            $channelImg = preg_replace('/[^A-Z]/i', "" ,$channelTitle);
            $channelImg = substr($channelImg, 0, 2);
            $channelImg = "https://invatar0.appspot.com/svg/".$channelImg.".jpg?s=100";
          }
          elseif(!empty($channelImg))
          {
            $imageFile = 'storage/media/channels';

            if (!is_dir($imageFile)) {
              mkdir($imageFile,0777,true);
            }

            $ext = $channelImg->getClientOriginalExtension();
            $channelImg->move($imageFile, $channelSlug.'.png');
            $channelImg = $imageFile.'/'.$channelSlug.'.png';

            if (extension_loaded('fileinfo')) {
              $img = Image::make($channelImg);
              list($width, $height) = getimagesize($channelImg);
              if($width > 400)
              {
                $img->resize(400, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
              }
              $img->save($channelImg);
            }
          }

          $channel = new Mchannel;

          $channel->channelTitle = $channelTitle;
          $channel->channelDesc = $channelDesc;
          $channel->channelImg = $channelImg;
          $channel->channelSlug = $channelSlug;
          $channel->channelArchived = 0;
          $channel->channelFeatured = 0;
          $channel->channelTopics = 0;
          $channel->save();

          $channelData = Mchannel::where('id', '=', $channel->id)->select('id', 'channelTitle', 'channelDesc', 'channelImg', 'channelTopics', 'channelSlug', 'created_at')->first();
          return Response::json($channelData);
        } else {
          return Response::json(0);
        }
      }
    } else {
      return Response::json(403);
    }
  }

  public function editChannel($id)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $channel = Mchannel::find($id);

      return Response::json($channel);
    } else {
      return Response::json(403);
    }
  }

  public function updateChannel(Request $request, $id)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $rules = array(
        'channelTitle'		=> 	'required'
      );
      $validator = Validator::make($request->all(), $rules);

      if ($validator->fails()) {
          return Response::json(0);
      } else {

        $channel = Mchannel::find($id);

        $channelTitle = $request->input('channelTitle');
        $channelDesc = $request->input('channelDesc');
        $channelImg = $request->file('channelImg');

        if($channelTitle != NULL)
        {
          if (preg_match('/[A-Za-z]/', $channelTitle) || preg_match('/[0-9]/', $channelTitle)) {
            $channelSlug = str_replace(' ', '-', $channelTitle);
            $channelSlug = preg_replace('/[^A-Za-z0-9\-]/', '', $channelSlug);
            $channelSlug = preg_replace('/-+/', '-', $channelSlug);

            if (Mchannel::where('channelSlug', '=', $channelSlug)->exists()) {
               $channelSlug = $channelSlug.'_'.mt_rand(1, 9999);
            }

            $channel->channelTitle = $channelTitle;
            $channel->channelSlug = $channelSlug;
          } else {
            return Response::json(0);
          }
        }

        if($channelDesc != NULL)
        {
          $channel->channelDesc = $channelDesc;
        }
        else {
          $channel->channelDesc = "No Description.";
        }

        if($channelImg != NULL)
        {

          $imageFile = 'storage/media/channels';

          if (!is_dir($imageFile)) {
            mkdir($imageFile,0777,true);
          }

          $ext = $channelImg->getClientOriginalExtension();
          $channelImg->move($imageFile, $channelSlug.'.'.$ext);
          $channelImg = $imageFile.'/'.$channelSlug.'.'.$ext;

          if (extension_loaded('fileinfo')) {
            $img = Image::make($channelImg);
            list($width, $height) = getimagesize($channelImg);
            if($width > 400)
            {
              $img->resize(400, null, function ($constraint) {
                  $constraint->aspectRatio();
              });
            }
            $img->save($channelImg);
          }

          $channel->channelImg = $channelImg;
        }
        elseif(empty($channelImg) && $channel->channelImg == NULL)
        {
          {
            $channelImg = preg_replace('/[^A-Z]/i', "" ,$channelTitle);
            $channelImg = substr($channelImg, 0, 2);
            $channelImg = "https://invatar0.appspot.com/svg/".$channelImg.".jpg?s=100";
          }
        }

        $channel->save();

        $channelData = Mchannel::where('id', '=', $channel->id)->select('id', 'channelTitle', 'channelDesc', 'channelImg', 'channelTopics', 'channelSlug', 'created_at')->first();
        return Response::json($channelData);
      }
    } else {
      return Response::json(403);
    }
  }

  public function deleteChannel($id)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $channel = Mchannel::find($id);

      if($channel->id != 1)
      {
        $topics = Mtopic::where('topicChannel', '=', $channel->id)->get();
        if(!$topics->isEmpty())
        {
          foreach($topics as $key => $value)
          {
            $value->topicChannel = 1;
            $value->save();
          }
        }
        $channel->delete();

        return Response::json(1);
      }
      else {
        return Response::json(0);
      }
    } else {
      return Response::json(403);
    }
  }

  public function unflagReply($id)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $reply = Mreply::find($id);
      if($reply->replyFlagged == 1)
      {
        $reply->replyFlagged = 0;
        $reply->save();
        return Response::json(1);
      }
      else {
        return Response::json(0);
      }
    } else {
      return Response::json(403);
    }
  }

  public function editReply($id)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $reply = Mreply::find($id);

      return Response::json($reply);
    } else {
      return Response::json(403);
    }
  }

  public function updateReply(Request $request, $id)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $replyBody = $request->json('replyBody');

      if($replyBody != NULL)
      {
        $reply = Mreply::find($id);
        $reply->replyBody = $replyBody;
        $reply->save();

        $replyData = Mreply::where('mreplies.id', '=', $id)->join('users', 'mreplies.replyAuthor', '=', 'users.id')->orderBy('mreplies.created_at', 'ASC')->select('mreplies.id', 'mreplies.replyParent', 'mreplies.created_at', 'mreplies.replyBody', 'mreplies.childCount', 'mreplies.replyFlagged', 'mreplies.replyFeature', 'mreplies.replyApproved', 'users.avatar', 'users.name', 'users.displayName')->first();

        return Response::json($replyData);
      }
      else {
        return Response::json(0);
      }
    } else {
      return Response::json(403);
    }
  }

  public function deleteReply(Request $request)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $id = $request->json('replyID');
      $reply = Mreply::find($id);
      $user = User::where('id', '=', $reply->replyAuthor)->first();
      $topic = Mtopic::find($reply->topicID);

      if($user->replies > 0)
      {
        $user->replies = $user->replies - 1;
        $user->save();
      }

      if($topic->topicReplies > 0)
      {
        $topic->topicReplies = $topic->topicReplies - 1;
        $topic->save();
      }

      $notification = Notification::where('contentID', '=', $id)->where('notificationType', '=', 'Alert')->where('notificationSubType', '=', 'Reply')->first();
      if(!empty($notification))
      {
        $notification->delete();
      }

      if($reply->replyParent != 0)
      {
        $parent = Mreply::find($reply->replyParent);
        $parent->childCount = $parent->childCount - 1;
        $parent->save();
      }

      if($reply->replyParent == 0) {
        $child = Mreply::where('replyParent', '=', $reply->id)->get();
        if(!$child->isEmpty()){
          foreach($child as $key => $value)
          {
            $notification = Notification::where('contentID', '=', $value->id)->where('notificationType', '=', 'Alert')->where('notificationSubType', '=', 'Reply')->first();
            if(!empty($notification)) {
              $notification->delete();
            }

            $user = User::where('id', '=', $value->replyAuthor)->first();
            if($user->replies > 0)
            {
              $user->replies = $user->replies - 1;
              $user->save();
            }

            if($topic->topicReplies > 0)
            {
              $topic->topicReplies = $topic->topicReplies - 1;
              $topic->save();
            }

            $value->delete();
          }
        }
      }

      $reply->delete();

      return Response::json(1);
    } else {
      return Response::json(403);
    }
  }

  public function featureReply($id)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $reply = Mreply::find($id);
      if($reply->replyFeature == 0)
      {
        $reply->replyFeature = 1;
        $reply->save();
        return Response::json(1);
      }
      else {
        $reply->replyFeature = 0;
        $reply->save();
        return Response::json(0);
      }
    } else {
      return Response::json(403);
    }
  }

  public function approveReply($id)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $reply = Mreply::find($id);
      if($reply->replyApproved == 0)
      {
        $reply->replyApproved = 1;
        $reply->save();
        return Response::json(1);
      }
    } else {
      return Response::json(403);
    }
  }

  public function pageMenu($id)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $page = Mtopic::find($id);
      if($page->pageMenu == 0)
      {
        $page->pageMenu = 1;
        $page->save();
        return Response::json(1);
      }
      else {
        $page->pageMenu = 0;
        $page->save();
        return Response::json(0);
      }
    } else {
      return Response::json(403);
    }
  }

  public function messages()
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $inbox = Message::where('recipientID', '=', Auth::user()->id)->get();

      return Response::json($inbox);
    } else {
      return Response::json(403);
    }
  }


  public function deleteMessage($id)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $message = Message::find($id);
      $message->messageArchived = 1;
      $message->save();

      return Response::json(1);
    } else {
      return Response::json(403);
    }
  }

  public function showMessage($id)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $message = Message::where('messages.id', '=', $id)->join('users', 'messages.senderID', '=', 'users.id')->select('messages.id', 'messages.senderID', 'messages.recipientID', 'messages.messageTitle', 'messages.messageBody', 'messages.messageRead', 'messages.created_at', 'users.name', 'users.displayName', 'users.avatar')->first();
      $user = Auth::user()->id;

      if($message->messageRead == 0)
      {
        if(!empty($user) && $user == $message->recipientID)
        {
          $message->messageRead = 1;
          $message->save();
        }
      }

      return Response::json($message);
    } else {
      return Response::json(403);
    }
  }
}
