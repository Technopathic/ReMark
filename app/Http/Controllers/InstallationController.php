<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use \DB;
use \Response;
use \Input;
use \Artisan;
use \Hash;

class InstallationController extends Controller
{

  public function __construct()
  {

  }

  public function getAPIInstall()
  {
    if(DB::connection()->getDatabaseName())
    {
      return Response::json(1);
    } else {
      return Response::json(0);
    }
  }

  public function storeAPIInstall(Request $request)
  {
    $rules = array(
      'databaseUser'	        => 	'required',
      'databaseName'			    =>	'required',
      'siteName'              =>  'required',
      'adminEmail'            =>  'required'
    );
    $validator = Validator::make($request->json()->all(), $rules);

    if ($validator->fails()) {
      return Response::json(0);
    } else {
        $databaseUser = $request->json('databaseUser');
        $databasePassword = $request->json('databasePassword');
        $databaseName = $request->json('databaseName');

        $siteName = $request->json('siteName');

        $adminEmail = $request->json('adminEmail');

        $emailHost = $request->json('emailHost');
        $emailPort = $request->json('emailPort');
        $emailUsername = $request->json('emailName');
        $emailPassword = $request->json('emailPass');

        $connection = mysqli_connect("127.0.0.1", $databaseUser,$databasePassword,$databaseName);

        if(!$connection)
        {
          //Could not connect
          return Response::json(2);
        } else {
          $checkEmail = filter_var($adminEmail, FILTER_VALIDATE_EMAIL);
          if($checkEmail === $adminEmail)
          {

            $env = base_path()."/.env";
            $content = file_get_contents($env);
            $content .= "\n";
            $content .= "APP_URL=".$request->root()."\n";
            $content .= "DB_DATABASE=".$databaseName."\n";
            $content .= "DB_USERNAME=".$databaseUser."\n";
            $content .= "DB_PASSWORD=".$databasePassword."\n";

            if(empty($emailHost) || empty($emailPort) || empty($emailUsername) || empty($emailPassword))
            {
              $content .="MAIL_DRIVER=mail"."\n";
              $content .="MAIL_HOST=null"."\n";
              $content .="MAIL_PORT=null"."\n";
              $content .="MAIL_USERNAME=null"."\n";
              $content .="MAIL_PASSWORD=null"."\n";
              $content .="MAIL_ENCRYPTION=null"."\n";
            }
            else {
              $content .="MAIL_DRIVER=smtp"."\n";
              $content .="MAIL_HOST=null".$emailHost."\n";
              $content .="MAIL_PORT=null".$emailPort."\n";
              $content .="MAIL_USERNAME=null".$emailUsername."\n";
              $content .="MAIL_PASSWORD=null".$emailPassword."\n";
              $content .="MAIL_ENCRYPTION=null"."\n";
            }
            file_put_contents($env, $content);

            //Success
            return Response::json(['adminName' => $adminName, 'adminEmail' => $adminEmail, 'siteName' => $siteName]);

          } else {
            //Email is not valid
            return Response::json(4);
          }
        }
      }
    }

    public function installAPIDB(Request $request)
    {
      $rules = array(
        'adminName'          =>   'required',
        'adminEmail'			   =>   'required',
        'siteName'           =>   'required'
      );
      $validator = Validator::make($request->json()->all(), $rules);

      if ($validator->fails()) {
        return Response::json(0);
      } else {

        $adminName = $request->json('adminName');
        $adminEmail = $request->json('adminEmail');
        $siteName = $request->json('siteName');

        $adminName = explode("@", $adminEmail);
        $adminName = $adminName[0];

        Artisan::call('cache:clear');
        Artisan::call('key:generate');
        Artisan::call('jwt:generate');
        Artisan::call('migrate', ['--env' => 'production', '--force' => true]);

        $sub = substr($adminName, 0, 2);
        $avatar = "https://invatar0.appspot.com/svg/".$sub.".jpg?s=100";
        DB::table('options')->insert(array('id' => 1, 'owner' => 1, 'website' => $siteName, 'baseurl' => $request->root()));
        DB::table('users')->insert(array('id' => 1, 'name' => $adminName, 'email' => $adminEmail, 'avatar' => $avatar, 'displayName' => $adminName, 'role' => 1, 'activated' => 1));

        Artisan::call('db:seed', ['--env' => 'production', '--force' => true]);

        $routeFile = (__DIR__.'/../routes.php');
        $routes = file_get_contents($routeFile);
        $storeRoute = str_replace("Route::post('storeAPIInstall', 'InstallationController@storeAPIInstall');", "", $routes);
        file_put_contents($routeFile, $storeRoute);
        $routes = file_get_contents($routeFile);
        $DBRoute = str_replace("Route::post('installAPIDB', 'InstallationController@installAPIDB');", "", $routes);
        file_put_contents($routeFile, $DBRoute);

        return Response::json(1);
      }
    }
}
