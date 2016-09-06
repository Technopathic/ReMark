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

  public function getAPIInstall(Request $request)
  {
    if(DB::connection()->getDatabaseName())
    {
      return Response::json(1)->setCallback($request->input('callback'));
    } else {
      return Response::json(0)->setCallback($request->input('callback'));
    }
  }

  public function storeAPIInstall(Request $request)
  {
    $rules = array(
      'databaseUser'	        => 	'required',
      'databaseName'			    =>	'required',
      'siteName'              =>  'required',
      'adminName'             =>  'required',
      'adminEmail'            =>  'required',
      'adminPassword'         =>  'required',
      'passwordConfirm'       =>  'required'
    );
    $validator = Validator::make($request->json()->all(), $rules);

    if ($validator->fails()) {
      return Response::json(0)->setCallback($request->input('callback'));
    } else {
        $databaseUser = $request->json('databaseUser');
        $databasePassword = $request->json('databasePassword');
        $databaseName = $request->json('databaseName');

        $siteName = $request->json('siteName');

        $adminName = $request->json('adminName');
        $adminEmail = $request->json('adminEmail');
        $adminPassword = $request->json('adminPassword');
        $passwordConfirm = $request->json('passwordConfirm');

        $connection = mysqli_connect("127.0.0.1", $databaseUser,$databasePassword,$databaseName);

        if(!$connection)
        {
          //Could not connect
          return Response::json(2)->setCallback($request->input('callback'));
        } else {
          $checkName = preg_match('/[^A-Z]/i', $adminName);
          if($checkName === 0){
            $checkPassword = preg_match('/\s/', $adminPassword);
            if($checkPassword === 0)
            {
              $checkEmail = filter_var($adminEmail, FILTER_VALIDATE_EMAIL);
              if($checkEmail === $adminEmail)
              {
                if($adminPassword != $passwordConfirm)
                {
                  //Passwords do not match
                  return Response::json(3)->setCallback($request->input('callback'));
                } else {

                  $env = base_path()."/.env";
                  $content = file_get_contents($env);
                  $content .= "\n";
                  $content .= "APP_URL=".$request->root()."\n";
                  $content .= "DB_DATABASE=".$databaseName."\n";
                  $content .= "DB_USERNAME=".$databaseUser."\n";
                  $content .= "DB_PASSWORD=".$databasePassword."\n";
                  file_put_contents($env, $content);

                  $adminPassword = Hash::make($adminPassword);

                  //Success
                  return Response::json(['adminName' => $adminName, 'adminPassword' => $adminPassword, 'adminEmail' => $adminEmail, 'siteName' => $siteName])->setCallback($request->input('callback'));
                  //return Redirect::to('installDB/'.$adminName.'/'.$adminPassword.'/'.$adminEmail.'/'.$siteName);
                }
              } else {
                //Email is not valid
                return Response::json(4)->setCallback($request->input('callback'));
              }
            } else {
              //Password cannot contain spaces
              return Response::json(5)->setCallback($request->input('callback'));
            }
          } else {
            //Username cannot contain spaces or special characters
            return Response::json(6)->setCallback($request->input('callback'));
          }
        }
      }
    }

    public function installAPIDB(Request $request)
    {
      $rules = array(
        'adminName'          =>   'required',
        'adminPassword'	     =>   'required',
        'adminEmail'			   =>   'required',
        'siteName'           =>   'required'
      );
      $validator = Validator::make($request->json()->all(), $rules);

      if ($validator->fails()) {
        return Response::json(0)->setCallback($request->input('callback'));
      } else {

        $adminName = $request->json('adminName');
        $adminPassword = $request->json('adminPassword');
        $adminEmail = $request->json('adminEmail');
        $siteName = $request->json('siteName');

        Artisan::call('cache:clear');
        Artisan::call('key:generate');
        Artisan::call('jwt:generate');
        Artisan::call('migrate', ['--env' => 'production', '--force' => true]);

        $sub = substr($adminName, 0, 2);
        $avatar = "https://invatar0.appspot.com/svg/".$sub.".jpg?s=100";
        DB::table('options')->insert(array('id' => 1, 'owner' => 1, 'website' => $siteName, 'baseurl' => $request->root()));
        DB::table('users')->insert(array('id' => 1, 'name' => $adminName, 'email' => $adminEmail, 'password' => $adminPassword, 'avatar' => $avatar, 'displayName' => $adminName, 'role' => 1, 'activated' => 1));

        Artisan::call('db:seed', ['--env' => 'production', '--force' => true]);

        $routeFile = (__DIR__.'/../routes.php');
        $routes = file_get_contents($routeFile);
        $storeRoute = str_replace("Route::post('storeAPIInstall', 'InstallationController@storeAPIInstall');", "", $routes);
        file_put_contents($routeFile, $storeRoute);
        $routes = file_get_contents($routeFile);
        $DBRoute = str_replace("Route::post('installAPIDB', 'InstallationController@installAPIDB');", "", $routes);
        file_put_contents($routeFile, $DBRoute);

        return Response::json(1)->setCallback($request->input('callback'));
      }
    }
}
