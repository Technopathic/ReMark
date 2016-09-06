<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Auth;
use App\Mtopic;
use App\Mreply;
use App\User;
use App\Mchannel;
use App\Option;
use App\Message;
use App\Notification;
use \DB;
use \Response;
use \Input;
use \Image;
use \File;
use \Mail;
use \DateTime;
use \Purifier;
use GrahamCampbell\Markdown\Facades\Markdown;
use League\HTMLToMarkdown\HtmlConverter;

class RemarksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    public function __construct()
    {
      $this->middleware('jwt.auth', ['only' => ['storeReply', 'storeMessage', 'voteTopic', 'updateProfile', 'deactivateUser']]);
    }

    public function index()
    {
        return File::get('index.html');
    }

    public function getInfo(Request $request)
    {
      $info = Option::select('website')->first();

      return Response::json($info)->setCallback($request->input('callback'));
    }

    public function main(Request $request)
    {
      $pages = Mtopic::where('pageMenu', '=', 1)->orderBy('id', 'ASC')->select('id', 'topicTitle', 'topicSlug')->get();
      $options = Option::select('website', 'baseurl', 'siteLogo', 'homeBanner', 'aboutWebsite', 'allowAsk', 'allowSubscription', 'homePage')->first();

      return Response::json(['pages' => $pages, 'options' => $options])->setCallback($request->input('callback'));
    }

    public function getTopics(Request $request, $channel = 0, $count = 6, $length = 500)
    {
      if($channel == '0')
      {
        $topics = Mtopic::where('mtopics.topicStatus', '=', 'Published')->join('mchannels', 'mtopics.topicChannel', '=', 'mchannels.id')->join('users', 'mtopics.topicAuthor', '=', 'users.id')->orderBy('mtopics.created_at', 'DESC')->select('mtopics.id', 'mtopics.topicTitle', 'mtopics.topicSlug', 'mtopics.topicBody', 'mtopics.topicThumbnail', 'mtopics.created_at', 'mtopics.topicReplies', 'mtopics.topicViews', 'mtopics.topicChannel', 'mchannels.channelTitle', 'mtopics.topicType', 'users.displayName', 'users.name', 'users.avatar')->paginate($count);
      }
      else
      {
        $channel = Mchannel::where('channelSlug', '=', $channel)->select('id', 'channelTitle', 'channelDesc', 'channelTopics')->first();
        $topics = Mtopic::where('mtopics.topicStatus', '=', 'Published')->where('mtopics.topicChannel', '=', $channel->id)->join('mchannels', 'mtopics.topicChannel', '=', 'mchannels.id')->join('users', 'mtopics.topicAuthor', '=', 'users.id')->orderBy('mtopics.created_at', 'DESC')->select('mtopics.id', 'mtopics.topicTitle', 'mtopics.topicSlug', 'mtopics.topicBody', 'mtopics.topicThumbnail', 'mtopics.created_at', 'mtopics.topicReplies', 'mtopics.topicViews', 'mtopics.topicChannel', 'mchannels.channelTitle', 'mtopics.topicType', 'users.displayName', 'users.name', 'users.avatar')->paginate($count);
      }

      if(!$topics->isEmpty())
      {
        foreach($topics as $key => $value)
        {
          $topicBody = Markdown::convertToHtml($value->topicBody);
          $topicBody = strip_tags($topicBody);
          if(strlen($topicBody) > $length)
          {
            $bodyCut = substr($topicBody, 0, $length);
            $topicBody = substr($bodyCut, 0, strrpos($bodyCut, ' ')).'...';
            $value['topicBody'] = $topicBody;
          }
        }
      }

      return Response::json($topics)->setCallback($request->input('callback'));
    }

    public function getFeatured(Request $request)
    {
      $features = Mtopic::where('topicStatus', '=', 'Published')->where('topicFeature', '=', 1)->orderBy('created_at', 'DESC')->take(6)->select('id', 'topicTitle', 'topicSlug', 'topicImg', 'created_at')->get();

      return Response::json($features)->setCallback($request->input('callback'));
    }

    public function getChannels(Request $request)
    {
      $channels = Mchannel::select('id', 'channelTitle', 'channelDesc', 'channelImg', 'channelTopics', 'channelSlug', 'created_at')->get();

      return Response::json($channels)->setCallback($request->input('callback'));
    }

    public function getChannel(Request $request, $slug)
    {
      $channel = Mchannel::where('channelSlug', '=', $slug)->select('id', 'channelTitle', 'channelSlug', 'channelDesc', 'channelTopics')->first();

      return Response::json($channel)->setCallback($request->input('callback'));
    }

    public function getDetail(Request $request, $slug)
    {
      $topic = Mtopic::where('mtopics.topicSlug', '=', $slug)->where('mtopics.topicStatus', '=', 'Published')->join('mchannels', 'mtopics.topicChannel', '=', 'mchannels.id')->select('mtopics.id', 'mtopics.topicSlug', 'mtopics.topicTitle', 'mtopics.topicChannel', 'mtopics.topicImg', 'mtopics.topicThumbnail', 'mtopics.topicAudio', 'mtopics.topicVideo', 'mtopics.created_at', 'mtopics.topicReplies', 'mtopics.topicViews', 'mtopics.topicBody', 'mtopics.topicAuthor', 'mtopics.topicTags', 'mtopics.topicVotes', 'mchannels.channelTitle', 'mtopics.topicType', 'mtopics.allowReplies', 'mtopics.showImage')->first();
      $user = User::where('id', '=', $topic->topicAuthor)->select('id', 'name', 'displayName', 'avatar')->first();
      $relates = Mtopic::where('mtopics.id', '!=', $topic->id)->where('mtopics.topicStatus', '=', 'Published')->where('mtopics.topicThumbnail', '!=', 0)->where('mtopics.topicChannel', '=', $topic->topicChannel)->orderBy('mtopics.created_at', 'DESC')->take(4)->select('mtopics.id', 'mtopics.topicTitle', 'mtopics.topicSlug', 'mtopics.topicThumbnail', 'mtopics.created_at', 'mtopics.topicReplies', 'mtopics.topicViews')->get();
      if(count($relates) < 4)
      {
        $relates = Mtopic::where('mtopics.id', '!=', $topic->id)->where('mtopics.topicStatus', '=', 'Published')->where('mtopics.topicThumbnail', '!=', 0)->orderBy('mtopics.created_at', 'DESC')->take(4)->select('mtopics.id', 'mtopics.topicTitle', 'mtopics.topicSlug', 'mtopics.topicThumbnail', 'mtopics.created_at', 'mtopics.topicReplies', 'mtopics.topicViews')->get();
      }

      $topic->increment('topicViews');

      $previousTopic = Mtopic::where('mtopics.id', '<', $topic->id)->where('mtopics.topicStatus', '=', 'Published')->where('topicChannel', '=', $topic->topicChannel)->select('mtopics.id', 'mtopics.topicTitle', 'mtopics.topicSlug', 'mtopics.topicChannel', 'mtopics.topicThumbnail', 'mtopics.created_at', 'mtopics.topicType')->orderBy('mtopics.id','desc')->first();
      $nextTopic = Mtopic::where('mtopics.id', '>', $topic->id)->where('mtopics.topicStatus', '=', 'Published')->where('topicChannel', '=', $topic->topicChannel)->select('mtopics.id', 'mtopics.topicTitle', 'mtopics.topicSlug', 'mtopics.topicChannel', 'mtopics.topicThumbnail', 'mtopics.created_at', 'mtopics.topicType')->orderBy('mtopics.id','asc')->first();

      return Response::json(['topic' => $topic, 'user' => $user, 'relates' => $relates, 'previousTopic' => $previousTopic, 'nextTopic' => $nextTopic])->setCallback($request->input('callback'));
    }

    public function getReplies(Request $request, $slug)
    {
      $topic = Mtopic::where('mtopics.topicSlug', '=', $slug)->where('mtopics.topicStatus', '=', 'Published')->select('mtopics.id')->first();

      $replies = Mreply::where('mreplies.topicID', '=', $topic->id)->where('mreplies.replyParent', '=', 0)->where('mreplies.replyApproved', '=', 1)->join('users', 'mreplies.replyAuthor', '=', 'users.id')->orderBy('mreplies.created_at', 'ASC')->select('mreplies.id', 'mreplies.replyParent', 'mreplies.created_at', 'mreplies.replyBody', 'mreplies.childCount', 'mreplies.replyFeature', 'users.avatar', 'users.name', 'users.displayName')->get()->toArray();
      $childReplies = Mreply::where('mreplies.topicID', '=', $topic->id)->where('mreplies.replyParent', '!=', 0)->where('mreplies.replyApproved', '=', 1)->join('users', 'mreplies.replyAuthor', '=', 'users.id')->orderBy('mreplies.created_at', 'ASC')->select('mreplies.id', 'mreplies.replyParent', 'mreplies.created_at', 'mreplies.replyBody', 'mreplies.replyFeature', 'users.avatar', 'users.name', 'users.displayName')->get()->toArray();
      $featureReplies = Mreply::where('mreplies.topicID', '=', $topic->id)->where('mreplies.replyFeature', '=', 1)->where('mreplies.replyApproved', '=', 1)->join('users', 'mreplies.replyAuthor', '=', 'users.name')->orderBy('mreplies.created_at', 'ASC')->select('mreplies.id', 'mreplies.replyAuthor', 'mreplies.replyParent', 'mreplies.created_at', 'mreplies.replyBody', 'users.avatar')->get();

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

      return Response::json(['replies' => $replies])->setCallback($request->input('callback'));
    }

    public function voteTopic(Request $request, $id)
    {
      $topic = Mtopic::find($id);
      $user = Auth::user()->id;
      $check = DB::table('votes')->where('userID', '=', $user)->where('contentID', '=', $topic->id)->first();
      if(empty($check))
      {
        $topic->topicVotes = $topic->topicVotes + 1;
        $topic->save();
        DB::table('votes')->insert(array('userID' => $user, 'contentID' => $topic->id));

        $notifyAuthor = User::where('id', '=', $topic->topicAuthor)->first();
        $notificationCheck = Notification::where('userID', '=', $topic->topicAuthor)->where('notifierID', '=', $user)->where('contentID', '=', $topic->id)->where('notificationType', '=', 'Alert')->where('notificationSubType', '=', 'Vote')->first();
        if(empty($notificationCheck))
        {
          $notification = new Notification;
          $notification->userID = $notifyAuthor->id;
          $notification->notifierID = $user;
          $notification->contentID = $topic->id;
          $notification->notificationType = 'Alert';
          $notification->notificationSubType = 'Vote';
          $notification->notificationRead = 0;
          $notification->save();

        }

        return Response::json(1)->setCallback($request->input('callback'));
      }
      else {
        if($topic->topicVotes > 0)
        {
          $topic->topicVotes = $topic->topicVotes - 1;
          $topic->save();
        }
        DB::table('votes')->where('userID', '=', $user)->where('contentID', '=', $topic->id)->delete();
        return Response::json(0)->setCallback($request->input('callback'));
      }
    }

    public function storeReply(Request $request)
    {
      $rules = array(
        'topicID'		=> 	'required',
        'replyBody'			=>	'required'
      );
      $validator = Validator::make($request->json()->all(), $rules);

      if ($validator->fails()) {
          return Response::json(0)->setCallback($request->input('callback'));
      } else {

        $topicID = $request->json('topicID');
        $replyBody = $request->json('replyBody');
        $replyAuthor = Auth::user();
        $replyParent = $request->json('parentID');
        $childCount = 0;

        $topicCheck = Mtopic::find($topicID);
        if($topicCheck->allowReplies == 0)
        {
          return Response::json(7)->setCallback($request->input('callback'));
        }

        $pastReplies = Mreply::where('replyAuthor', '=', $replyAuthor->id)->select('id', 'created_at')->orderBy('id', 'DESC')->skip(5)->take(1)->first();
        $currentTime = date('Y-m-d H:i:s');

        if(!empty($pastReplies) && $replyAuthor->role != 1)
        {
          $datetime1 = new DateTime($pastReplies->created_at);
          $datetime2 = new DateTime($currentTime);
          $interval = $datetime1->diff($datetime2);

          if($interval->format('%a%H') < 1) {
            return Response::json(5)->setCallback($request->input('callback'));
          }
        }

        if(strlen($replyBody) > 500)
        {
          return Response::json(3)->setCallback($request->input('callback'));
        }
        else {

          $replyBody = Markdown::convertToHtml($replyBody);
          $replyBody = Purifier::clean($replyBody);

          $converter = new HtmlConverter();
          $replyBody = $converter->convert($replyBody);

          if(substr_count($replyBody, 'img') > 1 || substr_count($replyBody, 'href') > 1 || substr_count($replyBody, 'youtube.com') > 1)
          {
            return Response::json(4)->setCallback($request->input('callback'));
          }
          else {
            if(empty($replyParent))
            {
              $replyParent = 0;
            }
            elseif(!empty($replyParent))
            {
              $parentReply = Mreply::where('id', '=', $replyParent)->select('id', 'childCount', 'replyAuthor', 'replyParent')->first();
              if($parentReply->replyParent != 0)
              {
                return Response::json(6)->setCallback($request->input('callback'));
              }
            }

            $options = Option::find(1);
            if($options->replyModeration == 0)
            {
              $approved = 1;
            }
            else
            {
              $approved = 0;
            }

            $reply = new Mreply;
            $reply->topicID = $topicID;
            $reply->replyBody = $replyBody;
            $reply->replyAuthor = $replyAuthor->id;
            $reply->replyParent = $replyParent;
            $reply->replyApproved = $approved;
            $reply->replyFeature = 0;
            if($replyParent != 0)
            {
              $parentReply->childCount = $parentReply->childCount + 1;
              $parentReply->save();
            }
            $reply->save();

            $replyAuthor->increment('replies');
            $topic = Mtopic::where('id', '=', $topicID)->first();
            $topic->increment('topicReplies');

            $website = $options->website;
            $url = $options->baseurl;
            $domain = preg_replace( "#^[^:/.]*[:/]+#i", "", $url);
            $sender = "no-reply@".$domain;

            $notifyAuthor = User::where('id', '=', $topic->topicAuthor)->first();
            $notification = new Notification;
            $notification->userID = $notifyAuthor->id;
            $notification->notifierID = $replyAuthor->id;
            $notification->contentID = $reply->id;
            $notification->notificationType = 'Alert';
            $notification->notificationSubType = 'Reply';
            $notification->notificationRead = 0;
            $notification->save();
            //DB::table('notifications')->insert(array('userID' => $notifyAuthor->id, 'notifierID' => $replyAuthor->id, 'contentID' => $reply->id, 'notificationType' => 'Alert', 'notificationSubType' => 'Reply', 'notificationRead' => 0));
            if($notifyAuthor->emailReply == 1)
            {
              $sendReply = $notifyAuthor;
              Mail::send('emails.reply', ['topic' => $topic, 'reply' => $reply, 'sendReply' => $sendReply, 'replyAuthor' => $replyAuthor, 'website' => $website, 'url' => $url, 'sender' => $sender], function ($message) use ($reply, $sendReply, $replyAuthor, $website, $url, $sender){
                $message->from($sender, $website);
                $message->to($sendReply->email, $sendReply->displayName)->subject($replyAuthor->displayName.' has replied to you.');
              });
            }
            if($replyParent != 0)
            {
              $sendReply = User::where('id', '=', $parentReply->replyAuthor)->select('displayName', 'email', 'emailReply')->first();
              if($sendReply->emailReply == 1)
              {
                Mail::send('emails.reply', ['topic' => $topic, 'reply' => $reply, 'sendReply' => $sendReply, 'replyAuthor' => $replyAuthor, 'website' => $website, 'url' => $url, 'sender' => $sender], function ($message) use ($reply, $sendReply, $replyAuthor, $website, $url, $sender){
                  $message->from($sender, $website);
                  $message->to($sendReply->email, $sendReply->displayName)->subject($replyAuthor->displayName.' has replied to you.');
                });
              }
            }
            if($options->replyModeration == 0)
            {
              $replyData = Mreply::where('mreplies.id', '=', $reply->id)->where('mreplies.replyApproved', '=', 1)->join('users', 'mreplies.replyAuthor', '=', 'users.id')->select('mreplies.id', 'mreplies.replyParent', 'mreplies.created_at', 'mreplies.replyBody', 'mreplies.childCount', 'users.avatar', 'users.name', 'users.displayName')->first();
              $replyData['showChildren'] = 0;
              $replyData['childReplies'] = array();
              return Response::json($replyData)->setCallback($request->input('callback'));
            }
            else
            {
              return Response::json(2)->setCallback($request->input('callback'));
            }
          }
        }
      }
    }

    public function getUser(Request $request, $name)
    {
      $user = User::where('users.name', '=', $name)->where('users.ban', '=', 0)->join('roles', 'users.role', '=', 'roles.id')->select('users.id', 'users.name', 'users.displayName', 'users.avatar', 'roles.roleName')->first();

      if(!empty($user))
      {
        return Response::json($user)->setCallback($request->input('callback'));
      }
      else
      {
        return Response::json(1)->setCallback($request->input('callback'));
      }
    }

    public function updateProfile(Request $request)
    {
      $id = Auth::user()->id;
      $profile = User::find($id);

      $displayName = Purifier::clean($request->input('displayName'));
      $email = Purifier::clean($request->input('email'));
      $avatar = $request->file('avatar');
      $password = Purifier::clean($request->input('password'));
      $confirm = Purifier::clean($request->input('confirm'));
      $emailReply = Purifier::clean($request->input('emailReply'));
      $emailDigest = Purifier::clean($request->input('emailDigest'));

      if($displayName == NULL)
      {
        $displayName = $profile->name;
      }
      else {
        $profile->displayName = $displayName;
      }
      if($email != NULL)
      {
        $profile->email = $email;
      }
      if($emailReply != NULL)
      {
        $profile->emailReply = $emailReply;
      }
      if($emailDigest != NULL)
      {
        $profile->emailDigest = $emailDigest;
      }

      if($avatar != NULL)
      {

        if(File::size($avatar) > 2097152)
        {
          return Response::json(2)->setCallback($request->input('callback'));
        }
        else {

          $imageFile = 'storage/media/users/avatars';

          if (!is_dir($imageFile)) {
            mkdir($imageFile,0777,true);
          }

          $fileName = str_random(8);
          $avatar->move($imageFile, $fileName.'.png');
          $avatar = $imageFile.'/'.$fileName.'.png';

          if (extension_loaded('fileinfo')) {
            $img = Image::make($avatar);

            list($width, $height) = getimagesize($avatar);
            if($width > 200)
            {
              $img->resize(200, null, function ($constraint) {
                  $constraint->aspectRatio();
              });
              if($height > 200)
              {
                $img->crop(200, 200);
              }
            }
            $img->save($avatar);
          }

          $profile->avatar = $avatar;
        }
      }
      if($password != NULL)
      {
        if($password === $confirm)
        {
          $password = Hash::make($password);
          $profile->password = $password;
        } else {
          return Response::json(0)->setCallback($request->input('callback'));
        }
      }

      $profile->save();

      $userData = User::where('users.id', '=', $profile->id)->join('roles', 'users.role', '=', 'roles.id')->select('users.displayName', 'users.email', 'users.avatar')->first();
      return Response::json($userData)->setCallback($request->input('callback'));
    }

  public function deactivateUser()
  {
    $user = Auth::user();
    $user = User::find($user->id);

    $user->activated == 0;
    $user->save();

    return Response::json(1)->setCallback($request->input('callback'));
  }

  public function storeMessage(Request $request)
  {
    $rules = array(
      'askBody' => 'required'
    );
    $validator = Validator::make($request->json()->all(), $rules);

    if ($validator->fails()) {
      return Response::json(0)->setCallback($request->input('callback'));
    } else {

      $options = Option::find(1);

      if($options->allowAsk == 1)
      {
        $senderID = Auth::user()->id;
        $recipientID = $options->owner;
        $messageBody = Purifier::clean($request->json('askBody'));

        $past = Message::where('senderID', '=', $senderID)->select('id', 'created_at')->orderBy('id', 'DESC')->skip(5)->take(1)->first();
        $currentTime = date('Y-m-d H:i:s');

        if(!empty($pastReplies))
        {
          $datetime1 = new DateTime($past->created_at);
          $datetime2 = new DateTime($currentTime);
          $interval = $datetime1->diff($datetime2);

          if($interval->format('%a%H') < 1) {
            return Response::json(3)->setCallback($request->input('callback'));
          }
        }

        $message = new Message;

        $message->senderID = $senderID;
        $message->recipientID = $recipientID;
        $message->messageBody = $messageBody;
        $message->messageRead = 0;
        $message->messageArchived = 0;
        $message->save();

        $notification = new Notification;
        $notification->userID = $recipientID;
        $notification->notifierID = $senderID;
        $notification->contentID = $message->id;
        $notification->notificationType = 'Message';
        $notification->notificationRead = 0;
        $notification->save();

        return Response::json(1)->setCallback($request->input('callback'));
      } else {
        return Response::json(2)->setCallback($request->input('callback'));
      }
    }
  }

  public function search(Request $request)
  {
    $rules = array(
      'searchType' => 'required',
      'searchContent' => 'required'
    );
    $validator = Validator::make($request->json()->all(), $rules);

    if ($validator->fails()) {
      return Response::json(0)->setCallback($request->input('callback'));
    } else {

      $searchType = Purifier::clean($request->json('searchType'));
      $searchContent = Purifier::clean($request->json('searchContent'));

      if($searchType == 'Topic')
      {
        $result = Mtopic::where('mtopics.topicStatus', '=', 'Published')->where('mtopics.topicTitle', 'LIKE', '%'.$searchContent.'%')->join('mchannels', 'mtopics.topicChannel', '=', 'mchannels.id')->orderBy('mtopics.created_at', 'DESC')->select('mtopics.id', 'mtopics.topicTitle', 'mtopics.topicSlug', 'mtopics.topicThumbnail', 'mtopics.topicAudio', 'mtopics.topicVideo', 'mtopics.created_at', 'mtopics.topicReplies', 'mtopics.topicViews', 'mtopics.topicChannel', 'mchannels.channelTitle', 'mtopics.topicType')->paginate(10);
      }
      else if($searchType == 'Channel')
      {
        $result = Mchannel::where('mchannels.channelTitle', 'LIKE', '%'.$searchContent.'%')->select('mchannels.id', 'mchannels.channelTitle', 'mchannels.channelSlug', 'mchannels.channelImg')->paginate(10);
      }
      else if($searchType == 'User')
      {
        $result = User::where('users.ban', '!=', 1)->where('users.displayName', 'LIKE', '%'.$searchContent.'%')->select('users.id', 'users.avatar', 'users.name', 'users.displayName')->paginate(10);
      }

      return Response::json($result)->setCallback($request->input('callback'));
    }
  }
}
