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
