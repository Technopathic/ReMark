<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Role;
use App\Option;
use \Input;
use \Response;
use \Mail;
use \Image;
use \Auth;
use \Redirect;
use \DB;
use \Purifier;

class AuthenticateController extends Controller
{

  public function __construct()
  {
    $this->middleware('jwt.auth', ['except' => ['checkEmail', 'doSignIn', 'doSignUp', 'refreshToken']]);
  }

 public function index()
 {

 }

 public function checkEmail(Request $request)
 {
   $rules = array(
     'email'	        => 	'required|email'
   );
   $validator = Validator::make(Purifier::clean($request->json()->all()), $rules);

   if ($validator->fails()) {
     return Response::json(0);
   } else {
     $options = Option::find(1);

     if($options->allowRegistration == 1)
     {
       $email = Purifier::clean($request->json('email'));

       $userCheck = User::where('email', '=', $email)->first();

       if(empty($userCheck))
       {
         //Run Sign Up
         return Response::json(1);
       }
       else {
         //Send Sign In Email
         $website = $options->website;
         $url = $options->baseurl;
         $domain = preg_replace( "#^[^:/.]*[:/]+#i", "", $url);
         $sender = "no-reply@".$domain;
         $username = $userCheck->displayName;
         $token = str_random(30);

         $userCheck->activation_token = $token;
         $userCheck->last_login = time();
         $userCheck->save();

         Mail::send('emails.auth', ['email' => $email, 'username'=> $username, 'token' => $token, 'website' => $website, 'url' => $url, 'sender' => $sender], function ($message) use ($email, $username, $token, $website, $url, $sender){
           $message->from($sender, $website);
           $message->to($email, $username)->subject($website. '- Sign In');
         });

         return Response::json(2);
       }
     } else {
       //Registration Not Allowed
       return Response::json(5);
     }
   }
 }

 public function doSignUp(Request $request)
 {
   $rules = array(
     'email'	        => 	'required|email'
   );
   $validator = Validator::make(Purifier::clean($request->json()->all()), $rules);

   if ($validator->fails()) {
     return Response::json(0);
   } else {

     $options = Option::find(1);

     if($options->allowRegistration == 1)
     {
       $email = Purifier::clean($request->json('email'));
       $username = explode("@", $email);
       $username = $username[0];

       $username = preg_replace('/[^0-9A-Z]/i', "" ,$username);
       $sub = substr($username, 0, 2);
       $fullName = $username;

       $userCheck = User::where('email', '=', $email)->orWhere('name', '=', $username)->select('email', 'name')->first();

       if(empty($userCheck))
       {
         $role = Role::find(2);

         $user = new User;
         $user->email = $email;
         $user->name = $username;
         $user->displayName = $fullName;
         $user->avatar = "https://invatar0.appspot.com/svg/".$sub.".jpg?s=100";
         $user->role = $role->id;
         $token = str_random(30);
         $user->activation_token = $token;
         $user->last_login = time();

         $user->save();
         $role->roleCount = $role->roleCount + 1;
         $role->save();

         //Send Sign In Email
         $website = $options->website;
         $url = $options->baseurl;
         $domain = preg_replace( "#^[^:/.]*[:/]+#i", "", $url);
         $sender = "no-reply@".$domain;
         $username = $user->displayName;

         Mail::send('emails.auth', ['email' => $email, 'username'=> $username, 'token' => $token, 'website' => $website, 'url' => $url, 'sender' => $sender], function ($message) use ($email, $username, $token, $website, $url, $sender){
           $message->from($sender, $website);
           $message->to($email, $username)->subject($website. '- Sign In');
         });

         //Success
         return Response::json(1);
       }
       else if($userCheck->email == $email)
       {
         //Email Already Registered
         return Response::json(2);
       }
       else if($userCheck->name == $username)
       {
         //Username already Registered
         return Response::json(3);
       }
     } else {
       //Registration Not Allowed
       return Response::json(5);
     }
   }
 }

 public function doSignIn(Request $request)
 {
   $token = $request->json('token');
   $user = User::where('activation_token', '=', $token)->first();

   if(!empty($user))
   {
     $current = time();
     $last_login = strtotime($user->last_login);
     $min = round(abs($current - $last_login) / 60,2);
     if($min < 15)
     {
        try {
          if (! $token = JWTAuth::fromUser($user)) {
            return response()->json(['error' => 'invalid_credentials'], 401);
          }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        if($user->ban == 1) {
          //User is banned
          return Response::json(0);
        }
        else {
          $user->activation_token = NULL;
          $user->save();

          return Response::json(compact('token'));
        }
     }
     else {
       return Response::json(2);
     }
   } else {
     //User not found & Token expired
     return Response::json(2);
   }
 }

  public function getAuthenticatedUser()
  {
      try {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
          return response()->json(['user_not_found'], 404);
        }
      } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
        return response()->json(['token_expired'], $e->getStatusCode());
      } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        return response()->json(['token_invalid'], $e->getStatusCode());
      } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
        return response()->json(['token_absent'], $e->getStatusCode());
      }
      return response()->json(compact('user'));
  }

  public function refreshToken() {
    $token = JWTAuth::getToken();
    if(!$token){
        throw new BadRequestHtttpException('Token not provided');
    }
    try{
        $token = JWTAuth::refresh($token);
    }catch(TokenInvalidException $e){
        throw new AccessDeniedHttpException('The token is invalid');
    }

    return Response::json($token);
  }

}
