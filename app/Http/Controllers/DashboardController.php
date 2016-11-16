<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Role;
use App\Feed;
use App\Mtopic;
use App\Mreply;
use App\Mchannel;
use App\Option;
use App\Notification;
use App\App;
use \Input;
use \Response;
use \Image;
use \Auth;
use \Session;
use \Redirect;
use \DB;
use \ZipArchive;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use \File;
use \Artisan;

class DashboardController extends Controller
{
  public function __construct()
  {
    $this->middleware('jwt.auth');
  }

  public function Dashboard(Request $request)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $topics = Mtopic::select('id', 'topicSlug', 'topicTitle', 'topicThumbnail', 'topicReplies', 'topicViews')->orderBy('created_at', 'DESC')->take(5)->get();
      $replies = Mreply::join('users', 'mreplies.replyAuthor', '=', 'users.id')->join('mtopics', 'mreplies.topicID', '=', 'mtopics.id')->orderBy('mreplies.created_at', 'DESC')->select('mreplies.id', 'mreplies.topicID', 'mreplies.replyParent', 'mreplies.replyAuthor', 'mreplies.replyBody', 'mtopics.topicSlug', 'mreplies.created_at', 'users.avatar', 'users.displayName')->take(5)->get();
      $users = User::where('activated', '=', 1)->where('role', '!=', 'Administrator')->select('id', 'name', 'avatar', 'replies', 'displayName')->take(5)->get();

      return Response::json(['replies' => $replies, 'topics' => $topics, 'users' => $users])->setCallback($request->input('callback'));
    }
    else {
      return Response::json(403)->setCallback($request->input('callback'));
    }
  }

  public function getNotifications(Request $request)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $alerts = Notification::where('userID', '=', $user->id)->where('notificationType', '=', 'Alert')->orderBy('updated_at', 'DESC')->where('notificationRead', '=', 0)->get();
      $messages = Notification::where('userID', '=', $user->id)->where('notificationType', '=', 'Message')->orderBy('updated_at', 'DESC')->where('notificationRead', '=', 0)->get();
      $globals = "";

      return Response::json(['alerts' => $alerts, 'messages' => $messages, 'globals' => $globals])->setCallback($request->input('callback'));
    }
    else {
      return Response::json(403)->setCallback($request->input('callback'));
    }
  }

  public function showNotifications(Request $request, $type)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      if($type == "Alert") {
        $replies = Notification::where('notifications.userID', '=', $user->id)->where('notifications.notificationType', '=', 'Alert')->where('notifications.notificationSubType', '=', 'Reply')->join('mreplies', 'notifications.contentID', '=', 'mreplies.id')->join('mtopics', 'mreplies.topicID', '=', 'mtopics.id')->select('notifications.id', 'notifications.contentID', 'notifications.notificationType', 'notifications.notificationSubType', 'notifications.notificationRead', 'notifications.updated_at', 'mreplies.replyBody', 'mreplies.replyAuthor', 'mreplies.topicID', 'mreplies.replyFlagged', 'mreplies.replyApproved', 'mreplies.replyFeature', 'mtopics.topicSlug', 'mtopics.topicTitle')->orderBy('notifications.updated_at')->paginate(10);
        $votes = Notification::where('notifications.userID', '=', $user->id)->where('notifications.notificationType', '=', 'Alert')->where('notifications.notificationSubType', '=', 'Vote')->join('votes', 'notifications.contentID', '=', 'votes.id')->where('votes.voteType', '=', 'Topic')->join('users', 'votes.userID', '=', 'users.id')->join('mtopics', 'votes.contentID', '=', 'mtopics.id')->select('notifications.id', 'notifications.notificationType', 'notifications.notificationSubType', 'notifications.notificationRead', 'notifications.updated_at', 'votes.contentID', 'votes.userID', 'votes.voteDirection', 'votes.voteType', 'users.name', 'mtopics.topicSlug')->orderBy('notifications.updated_at')->paginate(10);

        return Response::json(['replies' => $replies, 'votes' => $votes])->setCallback($request->input('callback'));
      }
      else if($type == "Message") {
        $messages = Notification::where('notifications.notificationType', '=', 'Message')->join('messages', 'notifications.contentID', '=', 'messages.id')->join('users', 'messages.senderID', '=', 'users.id')->select('notifications.id', 'notifications.contentID', 'notifications.notificationType', 'notifications.notificationRead', 'notifications.created_at', 'messages.senderID', 'messages.recipientID', 'messages.messageTitle', 'messages.messageBody', 'messages.messageArchived', 'users.name')->orderBy('created_at', 'DESC')->paginate(10);

        return Response::json(['messages' => $messages])->setCallback($request->input('callback'));
      }
      else if($type == "Global") {

      }
    }
    else {
      return Response::json(403)->setCallback($request->input('callback'));
    }
  }

  public function openNotification(Request $request, $id)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $notification = Notification::find($id);

      if($notification->notificationRead == 0)
      {
        $notification->notificationRead = 1;
        $notification->save();
      }
    }
    else {
      return Response::json(403)->setCallback($request->input('callback'));
    }
  }

  public function deleteNotification(Request $request, $id)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $notification = Notification::find($id);
      $notification->delete();
      return Response::json(1)->setCallback($request->input('callback'));
    }
    else {
      return Response::json(403)->setCallback($request->input('callback'));
    }
  }

  public function getCatalogue(Request $request)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $masterDir = base_path().'/storage/feeds/master';
      $master = file_get_contents('https://github.com/Technopathic/ReMark-Feeds/archive/master.zip');
      $masterFile = fopen($masterDir.'/master.zip', 'w+');
      fwrite($masterFile, $master);
      fclose($masterFile);
      $zip = new ZipArchive;
      if($zip->open($masterDir.'/master.zip') === true) {
        $zip->extractTo($masterDir);
        $zip->close();
      }
      unlink($masterDir.'/master.zip');

      $masterData = file_get_contents($masterDir.'/ReMark-Feeds-master/master.json');
      $masterData = json_decode($masterData, true);

      $version = $masterData['Version'];
      $option = Option::find(1);
      $option->feedVer = $version;
      $option->save();
      return Response::json($masterData['contents'])->setCallback($request->input('callback'));
    }
    else {
      return Response::json(403)->setCallback($request->input('callback'));
    }
  }

  public function runFeed(Request $request)
  {
    $auth = Auth::user();

    if($auth->role == 1)
    {
      $rules = array(
        'feed' => 'required',
        'input' => 'required'
      );
      $validator = Validator::make($request->json()->all(), $rules);

      if ($validator->fails()) {
          return Response::json(0)->setCallback($request->input('callback'));
      } else {

        $link = $request->json('feed');
        $input = $request->json('input');
        $custom = $request->json('custom');

        $feedDir = base_path().'/storage/feeds/installed';
        $feed = file_get_contents($link);
        $feedFile = fopen($feedDir.'/temp.zip', 'w+');
        fwrite($feedFile, $feed);
        fclose($feedFile);
        $zip = new ZipArchive;
        if($zip->open($feedDir.'/temp.zip') === true) {
          $zip->extractTo($feedDir);
          $dir = trim($zip->getNameIndex(0), '/');
          $zip->close();
        }

        unlink($feedDir.'/temp.zip');

        $feedData = file_get_contents($feedDir.'/'.$dir.'/feed.json');
        $feedData = json_decode($feedData, true);

        $check = Feed::where('feedName', '=', $feedData['info']['title'])->first();
        if(!empty($check) && $input == false)
        {
          return Response::json(2)->setCallback($request->input('callback'));
        }
        else {
          $data = new Feed;

          if($input == true && !empty($custom)) {
            $data->feedUrl = $custom;
          } else {
            $data->feedURL = $feedData['info']['source'];
          }
          $data->feedName = $feedData['info']['title'];
          $data->feedTags = $feedData['info']['tags'];
          $data->feedDesc = $feedData['info']['description'];
          $data->feedImg = $feedData['info']['icon'];
          if(!empty($feedData['info']['type']))
          {
            $data->feedType = $feedData['info']['type'];
          }
          if(!empty($feedData['info']['api']))
          {
            $data->feedAPI = $feedData['info']['api'];
          }
          $data->feedLoc = $feedDir.'/'.$dir;
          $data->save();

          $feedData = Feed::where('id', '=', $data->id)->select('id', 'feedUrl', 'feedName', 'feedTags', 'feedImg', 'feedLoc', 'created_at')->first();
          return Response::json($feedData)->setCallback($request->input('callback'));
        }
      }
    } else {
      return Response::json(403)->setCallback($request->input('callback'));
    }
  }

  public function getFeeds(Request $request)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $feeds = Feed::select('id', 'feedUrl', 'feedName', 'feedTags', 'feedImg', 'feedLoc', 'created_at')->get();

      return Response::json($feeds)->setCallback($request->input('callback'));
    }
    else {
      return Response::json(403)->setCallback($request->input('callback'));
    }
  }

  public function selectFeed(Request $request, $id)
  {
    $user = Auth::user();

    if($user->role == 1)
    {
      $feed = Feed::find($id);

      $data = file_get_contents($feed->feedLoc.'/feed.json');
      $data = json_decode($data, true);

      $result = array();

      if($feed->feedType == 'normal')
      {
        $client = new Client();
        $guzzle = $client->getClient();
        $guzzle = new \GuzzleHttp\Client(['verify' => base_path().'/resources/assets/cacert.pem']);
        $client->setClient($guzzle);
        $crawler = $client->request('GET', $feed->feedUrl);

        $items = $crawler->filter($data['feed']['container'])->each(function ($node) {
            return $node->html();
        });

        function cleanString($text) {
            $utf8 = array(
                '/[áàâãªä]/u'   =>   '',
                '/[ÁÀÂÃÄ]/u'    =>   '',
                '/[ÍÌÎÏ]/u'     =>   '',
                '/[íìîï]/u'     =>   '',
                '/[éèêë]/u'     =>   '',
                '/[ÉÈÊË]/u'     =>   '',
                '/[óòôõºö]/u'   =>   '',
                '/[ÓÒÔÕÖ]/u'    =>   '',
                '/[úùûü]/u'     =>   '',
                '/[ÚÙÛÜ]/u'     =>   '',
                '/ç/'           =>   '',
                '/Ç/'           =>   '',
                '/ñ/'           =>   '',
                '/Ñ/'           =>   '',
                '/–/'           =>   '-',
                '/[’‘‹›‚]/u'    =>   "'",
                '/[“”«»„]/u'    =>   '"',
                '/ /'           =>   ' ',
            );
            return preg_replace(array_keys($utf8), array_values($utf8), $text);
        }

        foreach($items as $key => $value)
        {
          $crawler = new Crawler($value);
          $title = $crawler->filter($data['feed']['title'])->each(function ($node){
              return cleanString($node->text());
          });
          if(!empty($title[0]))
          {
            $result[$key]['title'] = $title[0];
          }
          else {
            $result[$key]['title'] = "";
          }

          $author = $crawler->filter($data['feed']['author'])->each(function ($node) {
              return $node->text();
          });
          if(!empty($author[0]))
          {
            $result[$key]['author'] = $author[0];
          }
          else {
            $result[$key]['author'] = "";
          }

          $date = $crawler->filter($data['feed']['date'])->each(function ($node) {
              return $node->text();
          });
          if(!empty($date[0]))
          {
            $result[$key]['date'] = $date[0];
          }
          else {
            $result[$key]['date'] = "";
          }

          $content = $crawler->filter($data['feed']['content'])->each(function ($node){
              return cleanString($node->text());
          });
          if(!empty($content[0]))
          {
            $result[$key]['content'] = $content[0];
          }
          else {
            $result[$key]['content'] = "";
          }

          $media = $crawler->filter($data['feed']['media'])->each(function ($node) use ($data){
            if($node->attr($data['feed']['mediaSrc']) != NULL)
            {
              return $node->attr($data['feed']['mediaSrc']);
            }
            else {
              return $node->attr('src');
            }
          });
          if(!empty($media[0]))
          {
            $result[$key]['media'] = $media[0];
          }
          else {
            $result[$key]['media'] = "";
          }


          $link = $crawler->filter($data['feed']['link'])->each(function ($node) {
              return $node->attr('href');
          });
          if(!empty($link[0]))
          {
            $result[$key]['link'] = $link[0];
          }
          else {
            $result[$key]['link'] = "";
          }
        }

        $options = $data['options'];

        return Response::json(['feed' => $feed, 'result' => $result, 'options' => $options])->setCallback($request->input('callback'));
      }
      else if($feed->feedType == 'api')
      {

        $client = file_get_contents($feed->feedAPI);
        $client = json_decode($client);

        $title = explode(", ", $data['feed']['title']);
        $author = explode(", ", $data['feed']['author']);
        $date = explode(", ", $data['feed']['date']);
        $content = explode(", ", $data['feed']['content']);
        $media = explode(", ", $data['feed']['media']);
        $link = explode(", ", $data['feed']['link']);

        foreach($client->$data['feed']['container'] as $key => $value)
        {
          $result[$key]['title'] = $value;
          for($i = 0; $i < count($title); $i++)
          {
            $result[$key]['title'] = $result[$key]['title']->$title[$i];
          }

          $result[$key]['author'] = $value;
          for($i = 0; $i < count($author); $i++)
          {
            $result[$key]['author'] = $result[$key]['author']->$author[$i];
          }

          $result[$key]['date'] = $value;
          for($i = 0; $i < count($date); $i++)
          {
            $result[$key]['date'] = $result[$key]['date']->$date[$i];
          }

          $result[$key]['content'] = $value;
          for($i = 0; $i < count($content); $i++)
          {
            $result[$key]['content'] = $result[$key]['content']->$content[$i];
          }

          $result[$key]['media'] = $value;
          for($i = 0; $i < count($media); $i++)
          {
            $result[$key]['media'] = $result[$key]['media']->$media[$i];
          }

          $result[$key]['link'] = $value;
          for($i = 0; $i < count($link); $i++)
          {
            $result[$key]['link'] = $result[$key]['link']->$link[$i];
          }
        }

        $options = $data['options'];

        return Response::json(['feed' => $feed, 'result' => $result, 'options' => $options])->setCallback($request->input('callback'));
      }
    }
    else {
      return Response::json(403)->setCallback($request->input('callback'));
    }
  }

  public function getBookmarks(Request $request)
  {
    $user = Auth::user();

    if($user->role == 1)
    {
      $bookmarks = DB::table('bookmarks')->paginate(12);

      return Response::json($bookmarks)->setCallback($request->input('callback'));
    }
    else {
      return Response::json(403)->setCallback($request->input('callback'));
    }
  }

  public function bookmarkFeed(Request $request)
  {
    $user = Auth::user();

    if($user->role == 1)
    {
      $feedID = $request->json('feedID');
      $bookmarkDomain = $request->json('bookmarkDomain');
      $bookmarkTitle = $request->json('bookmarkTitle');
      $bookmarkImg = $request->json('bookmarkImg');
      $bookmarkAuthor = $request->json('bookmarkAuthor');
      $bookmarkSource = $request->json('bookmarkSource');

      $feed = Feed::find($feedID);
      $data = file_get_contents($feed->feedLoc.'/feed.json');
      $data = json_decode($data, true);

      if(!empty($data['options']['prependLinks']))
      {
        $bookmarkSource = $data['options']['prependLinks'].$bookmarkSource;
      }

      if(!empty($data['options']['prependMedia']))
      {
        $bookmarkImg = $data['options']['prependMedia'].$bookmarkImg;
      }

      $bookmark = DB::table('bookmarks')->where('bookmarkSource', '=', $bookmarkSource)->first();
      if(empty($bookmark))
      {
        DB::table('bookmarks')->insert(array('feedID' => $feedID, 'bookmarkDomain' => $bookmarkDomain, 'bookmarkTitle' => $bookmarkTitle, 'bookmarkImg' => $bookmarkImg, 'bookmarkAuthor' => $bookmarkAuthor, 'bookmarkSource' => $bookmarkSource));
        return Response::json(1)->setCallback($request->input('callback'));
      }
      else {
        DB::table('bookmarks')->where('bookmarkSource', '=', $bookmarkSource)->delete();
        return Response::json(0)->setCallback($request->input('callback'));
      }
    } else {
      return Response::json(403)->setCallback($request->input('callback'));
    }
  }

  public function unBookmarkFeed(Request $request)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $id = $request->json('id');
      $bookmark = DB::table('bookmarks')->where('id', '=', $id)->first();

      if(empty($bookmark))
      {
        return Response::json(0)->setCallback($request->input('callback'));
      }
      else {
        DB::table('bookmarks')->where('id', '=', $id)->delete();
        return Response::json(1)->setCallback($request->input('callback'));
      }
    } else {
      return Response::json(403)->setCallback($request->input('callback'));
    }
  }

  public function deleteFeed(Request $request)
  {
    $id = $request->json('id');
    $user = Auth::user();
    if($user->role == 1)
    {
      $feed = Feed::find($id);
      File::delete($feed->feedLoc);
      $feed->delete();
      return Response::json(1)->setCallback($request->input('callback'));
    } else {
      return Response::json(403)->setCallback($request->input('callback'));
    }
  }

  public function getOptions(Request $request)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $options = Option::find(1);
      $apps = DB::table('apps')->get();

      return Response::json(['options' => $options, 'apps' => $apps])->setCallback($request->input('callback'));
    } else {
      return Response::json(403)->setCallback($request->input('callback'));
    }

  }

  public function saveOptions(Request $request)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $options = Option::find(1);

      if($request->json('website') == NULL)
      {
        return Response::json(0)->setCallback($request->input('callback'));
      }
      else
      {
        $options->website = $request->json('website');
        $options->siteLogo = $request->json('siteLogo');
        $options->homePage = $request->json('homePage');
        $options->allowRegistration = $request->json('allowRegistration');
        $options->allowSubscription = $request->json('allowSubscription');
        $options->requireActivation = $request->json('requireActivation');
        $options->replyModeration = $request->json('replyModeration');
        $options->aboutWebsite = $request->json('aboutWebsite');
        $options->homeBanner = $request->json('homeBanner');
        $options->allowAsk = $request->json('allowAsk');

        $options->save();

        return Response::json(1)->setCallback($request->input('callback'));
      }
    } else {
      return Response::json(403)->setCallback($request->input('callback'));
    }
  }

  public function storeApp(Request $request)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $rules = array(
        'appData' => 'required'
      );
      $validator = Validator::make($request->all(), $rules);

      if ($validator->fails()) {
        return Response::json(0)->setCallback($request->input('callback'));
      } else {

        $appFile = $request->file('appData');
        $appMime = $appFile->getMimeType();

        if($appMime !=  'application/zip')
        {
          return Response::json(2)->setCallback($request->input('callback'));
        }
        else {

          $appName = pathinfo($appFile->getClientOriginalName(), PATHINFO_FILENAME);
          $appSlug = preg_replace("/ /","-",$appName);

          if (App::where('appName', '=', $appSlug)->exists()) {
             $appSlug = $appSlug.'_'.mt_rand(1, 9999);
          }

          $appDir = base_path().'/storage/apps/'.$appSlug;
          $zip = new ZipArchive;
          if($zip->open($appFile) === true) {
            $zip->extractTo($appDir);
            $zip->close();
          }

          if(file_exists($appDir.'/app.json'))
          {
            $appData = file_get_contents($appDir.'/app.json');
            $appData = json_decode($appData, true);
            $appName = $appData['appName'];
            $appAuthor = $appData['appAuthor'];
            $appVersion = $appData['appVersion'];
            $appDesc = $appData['appDesc'];
            $appPreview = $appDir.'/preview.png';
            $appFramework = $appData['appFramework'];
            $appDocs = $appData['appDocs'];

            $app = new App;

            $app->appName = $appName;
            $app->appSlug = $appSlug;
            $app->appAuthor = $appAuthor;
            $app->appVersion = $appVersion;
            $app->appDesc = $appDesc;
            $app->appPreview = $appPreview;
            $app->appFramework = $appFramework;
            $app->appActive = 0;
            $app->appDocs = $appDocs;
            $app->save();

            return Response::json(1)->setCallback($request->input('callback'));
          }
          else {
            File::deleteDirectory($appDir);
            return Response::json(3)->setCallback($request->input('callback'));
          }
        }
      }
    } else {
      return Response::json(403)->setCallback($request->input('callback'));
    }
  }

  public function activateApp(Request $request)
  {
    $user = Auth::user();

    if($user->role == 1)
    {

      $backupApp = base_path().'/storage/apps/backup';

      if (!is_dir($backupApp)) {
        mkdir($backupApp,0777,true);
      }

      $oldViews = public_path().'/views';
      $oldCss = public_path().'/css';
      $oldJs = public_path().'/js';
      $oldLib = public_path().'/lib';
      $oldImg = public_path().'/img';
      $oldFont = public_path().'/fonts';
      $oldIndex = public_path().'/index.html';

      $oldControllers = base_path().'/app/Http/Controllers';
      $oldModels = base_path().'/app/Models';
      $oldMigrations = base_path().'/database/migrations';
      $oldRoutes = base_path().'/app/Http/routes.php';

      File::copyDirectory($oldViews, $backupApp.'/views');
      File::copyDirectory($oldCss, $backupApp.'/css');
      File::copyDirectory($oldJs, $backupApp.'/js');
      File::copyDirectory($oldLib, $backupApp.'/lib');
      File::copyDirectory($oldImg, $backupApp.'/img');
      File::copyDirectory($oldFont, $backupApp.'/fonts');
      File::copy($oldIndex, $backupApp.'/index.html');

      File::copyDirectory($oldControllers, $backupApp.'/Controllers');
      File::copyDirectory($oldModels, $backupApp.'/Models');
      File::copyDirectory($oldMigrations, $backupApp.'/Migrations');
      File::copy($oldRoutes, $backupApp.'/routes.php');

      $current = App::where('appActive', '=', 1)->first();
      if(!empty($current)) {
        $currentDir = base_path().'/storage/apps/'.$current->appSlug;

        if(File::exists($currentDir.'/js'))
        {
          $currentJs = scandir($currentDir.'/js');
          foreach($currentJs as $key => $value)
          {
            File::delete($oldJs.'/'.$value);
          }
        }

        if(File::exists($currentDir.'/lib'))
        {
          $currentLib = scandir($currentDir.'/lib');
          foreach($currentLib as $key => $value)
          {
            File::delete($oldLib.'/'.$value);
          }
        }

        if(File::exists($currentDir.'/css'))
        {
          $currentCss = scandir($currentDir.'/css');
          foreach($currentCss as $key => $value)
          {
            File::delete($oldCss.'/'.$value);
          }
        }

        if(File::exists($currentDir.'/fonts'))
        {
          $currentFont = scandir($currentDir.'/fonts');
          foreach($currentFont as $key => $value)
          {
            File::delete($oldFont.'/'.$value);
          }
        }

        if(File::exists($currentDir.'/views'))
        {
          $currentViews = scandir($currentDir.'/views');
          foreach($currentViews as $key => $value)
          {
            File::delete($oldViews.'/'.$value);
          }
        }

        if(File::exists($currentDir.'/img'))
        {
          $currentImg = scandir($currentDir.'/img');
          foreach($currentImg as $key => $value)
          {
            File::delete($oldImg.'/'.$value);
          }
        }

        if(File::exists($currentDir.'/Controllers'))
        {
          $currentControllers = scandir($currentDir.'/Controllers');
          foreach($currentControllers as $key => $value)
          {
            File::delete($oldControllers.'/'.$value);
          }
        }

        if(File::exists($currentDir.'/Models'))
        {
          $currentModels = scandir($currentDir.'/Models');
          foreach($currentModels as $key => $value)
          {
            File::delete($oldModels.'/'.$value);
          }
        }

        if(File::exists($currentDir.'/Migrations'))
        {
          $currentMigrations = scandir($currentDir.'/Migrations');
          foreach($currentMigrations as $key => $value)
          {
            File::delete($oldMigrations.'/'.$value);
          }
        }

        if(File::exists($currentDir.'/routes.php'))
        {
          File::delete($oldRoutes);
          File::copy($backupApp.'/'.'routes.php', $oldRoutes);
        }

        if(File::exists($currentDir.'/index.html'))
        {
          File::delete($oldIndex);
          File::copy($backupApp.'/'.'index.html', $oldIndex);
        }
      }

      $id = $request->json('id');
      $app = App::find($id);
      $appDir = base_path().'/storage/apps/'.$app->appSlug;

      if (file_exists($appDir.'/js')) {
        File::copyDirectory($appDir.'/js', $oldJs);
      }

      if (file_exists($appDir.'/lib')) {
        File::copyDirectory($appDir.'/lib', $oldLib);
      }

      if (file_exists($appDir.'/css')) {
        File::copyDirectory($appDir.'/css', $oldCss);
      }

      if (file_exists($appDir.'/fonts')) {
        File::copyDirectory($appDir.'/fonts', $oldFont);
      }

      if (file_exists($appDir.'/views')) {
        File::copyDirectory($appDir.'/views', $oldViews);
      }

      if (file_exists($appDir.'/img')) {
        File::copyDirectory($appDir.'/img', $oldImg);
      }

      if (file_exists($appDir.'/Controllers')) {
        File::copyDirectory($appDir.'/Controllers', $oldControllers);
      }

      if (file_exists($appDir.'/Models')) {
        File::copyDirectory($appDir.'/Models', $oldModels);
      }

      if (file_exists($appDir.'/Migrations')) {
        File::copyDirectory($appDir.'/Migrations', $oldMigrations);
        //Artisan::call('migrate', ['--env' => 'production', '--force' => true]);
      }

      if (file_exists($appDir.'/routes.php')) {
        $routes = base_path().'/app/Http/routes.php';
        $currentRoutes = file_get_contents($routes);
        $newRoutes = file_get_contents($appDir.'/routes.php');
        $currentRoutes .= $newRoutes;
        file_put_contents($routes, $currentRoutes);
      }

      if(file_exists($appDir.'index.html')){
        File::copy($appDir.'/index.html', $oldIndex);
      }

      if(!empty($current))
      {
        $current->appActive = 0;
        $current->save();
      }

      $app->appActive = 1;
      $app->save();

      return Response::json(1)->setCallback($request->input('callback'));

    } else {
      return Response::json(403)->setCallback($request->input('callback'));
    }
  }

  public function deleteApp(Request $request)
  {
    $user = Auth::user();
    if($user->role == 1)
    {
      $id = $request->json('id');
      $app = App::find($id);

      if($app->id == 1)
      {
        //You cannot uninstall this app.
        return Response::json(0)->setCallback($request->input('callback'));
      }
      elseif($app->activated == 1)
      {
        //Please install another app first.
        return Response::json(2)->setCallback($request->input('callback'));
      }
      else {
        $appDir = base_path().'/storage/apps/'.$app->appSlug;
        File::deleteDirectory($appDir);
        $app->delete();
      }
      //Success
      return Response::json(1)->setCallback($request->input('callback'));
    } else {
      return Response::json(403)->setCallback($request->input('callback'));
    }
  }


  /*public function weeklyDigest()
  {
    $emails = Subscription::where('subscriptionType', '=', 'Email')->where('subscriptionActive', '=', 1)->get();
    $options = Option::find(1);
    $topics = Mtopic::where('mtopics.topicStatus', '=', 'Published')->orderBy('mtopics.created_at', 'DESC')->select('mtopics.id', 'mtopics.topicTitle', 'mtopics.topicSlug', 'mtopics.topicBody')->take(3)->get();

    if(!$topics->isEmpty())
    {
      foreach($emails as $key => $value)
      {
        Mail::send('emails.subscription', ['value' => $value, 'options' => $options, 'topics' => $topics], function ($message) use ($value, $options, $topics){
          $message->to($value->subscriptionContact)->subject($options->website. '- Weekly Digest');
        });
      }

    }
  }*/
}
