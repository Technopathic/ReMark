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
use App\Subscription;
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
    $this->middleware('jwt.auth', ['except' => ['authenticate', 'getAuth', 'doSignUp', 'confirmToken', 'storeSubscription', 'confirmSubscription', 'refreshToken']]);
  }

 public function index()
 {

 }

  public function doSignUp(Request $request)
  {
    $rules = array(
      'email'	        => 	'required|email',
      'username'			=>	'required',
      'password'			=>	'required'
    );
    $validator = Validator::make(Purifier::clean($request->json()->all()), $rules);

    if ($validator->fails()) {
      return Response::json(0)->setCallback($request->input('callback'));
    } else {

      $options = Option::find(1);

      if($options->allowRegistration == 1)
      {
        $email = Purifier::clean($request->json('email'));
        $username = Purifier::clean($request->json('username'));
        $fullName = Purifier::clean($request->json('fullName'));
        $password = Purifier::clean($request->json('password'));

        $username = preg_replace('/[^0-9A-Z]/i', "" ,$username);
        $sub = substr($username, 0, 2);

        if(empty($fullName))
        {
          $fullName = $username;
        }

        $userCheck = User::where('email', '=', $email)->orWhere('name', '=', $username)->select('email', 'name')->first();

        if(empty($userCheck))
        {

          $role = Role::find(2);

          $user = new User;
          $user->email = $email;
          $user->name = $username;
          $user->password = Hash::make($password);
          $user->displayName = $fullName;
          $user->avatar = "https://invatar0.appspot.com/svg/".$sub.".jpg?s=100";
          $user->role = $role->id;
          $token = str_random(30);
          $user->activation_token = $token;

          $options = Option::first();
          $website = $options->website;
          $url = $options->baseurl;
          $domain = preg_replace( "#^[^:/.]*[:/]+#i", "", $url);
          $sender = "no-reply@".$domain;

          Mail::send('emails.confirmation', ['email' => $email, 'username'=> $username, 'token' => $token, 'website' => $website, 'url' => $url, 'sender' => $sender], function ($message) use ($email, $username, $token, $website, $url, $sender){
            $message->from($sender, $website);
            $message->to($email, $username)->subject($website. '- Verify Your Email');
          });

          $user->save();
          $role->roleCount = $role->roleCount + 1;
          $role->save();

          if($options->requireActivation == 0)
          {
            //Success
            return Response::json(1)->setCallback($request->input('callback'));
          }
          else
          {
            //Require Activation
            return Response::json(6)->setCallback($request->input('callback'));
          }

        } else {
          if($userCheck->email === $email)
          {
            //Email Already Registered
            return Response::json(2)->setCallback($request->input('callback'));
          }
          elseif($userCheck->name === $username)
          {
            //Username already Registered
            return Response::json(3)->setCallback($request->input('callback'));
          }
        }
      } else {
        //Registration Not Allowed
        return Response::json(5)->setCallback($request->input('callback'));
      }
    }
  }

  public function confirmToken(Request $request)
  {
    $token = Purifier::clean($request->json('token'));
    $user = User::where('activation_token','=',$token)->first();

    if(!empty($user))
    {
      if($user->activated == 0)
      {
        $user->activated = 1;
        $user->save();
        //Success
        return Response::json(1)->setCallback($request->input('callback'));
      }
      else {
        //User Activated already
        return Response::json(0)->setCallback($request->input('callback'));
      }
    } else {
      //User not found
      return Response::json(2)->setCallback($request->input('callback'));
    }
  }

  public function authenticate(Request $request)
  {
      $email = $request->json('email');
      $password = $request->json('password');
      $hash = Hash::make($password);
      $options = Option::find(1);
      $userCheck = User::where('email', '=', $email)->first();
      if(!empty($userCheck))
      {
        $cred = array("email", "password");
        $credentials = compact("email", "password", $cred);
        try {
          if (! $token = JWTAuth::attempt($credentials)) {
              return response()->json(['error' => 'invalid_credentials'], 401);
          }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        if($userCheck->ban == 1) {
          //User is banned
          return Response::json(0)->setCallback($request->input('callback'));
        }
        else {
          if($options->requireActivation == 1 && $userCheck->activated == 0)
          {
            //Require Activation
            return Response::json(3)->setCallback($request->input('callback'));
          }
          else {
            return Response::json(compact('token'))->setCallback($request->input('callback'));
          }
        }
      } else {
        //User not found
        return Response::json(2)->setCallback($request->input('callback'));
      }
  }

  public function getAuthenticatedUser(Request $request)
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
      return response()->json(compact('user'))->setCallback($request->input('callback'));
  }

  public function refreshToken(Request $request) {
    $token = JWTAuth::getToken();
    if(!$token){
        throw new BadRequestHtttpException('Token not provided');
    }
    try{
        $token = JWTAuth::refresh($token);
    }catch(TokenInvalidException $e){
        throw new AccessDeniedHttpException('The token is invalid');
    }

    return Response::json($token)->setCallback($request->input('callback'));
  }

  public function resetPassword(Request $request)
  {
    $rules = array(
      'resetId'		=> 	'required'
    );
    $validator = Validator::make($request->json()->all(), $rules);

    if ($validator->fails()) {
        return Response::json(0)->setCallback($request->input('callback'));
    } else {

      $options = Option::find(1);

      $resetId = $request->json('resetId');

      if(filter_var($resetId, FILTER_VALIDATE_EMAIL)) {
        $user = User::where('email', '=', $resetId)->select('email', 'name')->first();
        if(!empty($user))
        {
          $token = Hash::make($user->email);
          $website = $options->website;
          DB::table('password_resets')->insert(array('email' => $user->email, 'token' => $token));
          Mail::send('emails.resetPassword', ['user' => $user, 'token' => $token, 'website' => $website], function ($m) use ($user, $token, $website) {
              $m->to($user->email)->subject($website.' - Password Reset');
          });

          return Response::json(1)->setCallback($request->input('callback'));
        }
        else {
          return Response::json(2)->setCallback($request->input('callback'));
        }
      }
      else {
        $user = User::where('name', '=', $resetId)->select('email', 'name')->first();
        if(!empty($user))
        {
          $token = Hash::make($user->email);
          $website = $options->website;
          DB::table('password_resets')->insert(array('email' => $user->email, 'token' => $token));
          Mail::send('emails.resetPassword', ['user' => $user, 'token' => $token, 'website' => $website], function ($m) use ($user, $token, $website) {
              $m->to($user->email)->subject($website.' - Password Reset');
          });

          return Response::json(1)->setCallback($request->input('callback'));
        }
        else {
          return Response::json(3)->setCallback($request->input('callback'));
        }
      }
    }
  }

  public function confirmReset(Request $request, $token)
  {
    $rules = array(
      'newPassword'		=> 	'required',
      'confirmPassword' => 'required'
    );
    $validator = Validator::make($request->json()->all(), $rules);
    if ($validator->fails()) {
        return Response::json(0)->setCallback($request->input('callback'));
    } else {

      $reset = DB::table('password_resets')->where('token', '=', $token)->first();
      if(empty($reset))
      {
        //Token not found
        return Response::json(0)->setCallback($request->input('callback'));
      }
      else {
        $date1 = new DateTime($reset->created_at);
        $date2 = new DateTime();

        $diff = $date2->diff($date1);

        $hours = $diff->h;
        $hours = $hours + ($diff->days*24);

        if($hours > 24)
        {
          //This reset form has expired.
          $reset->delete();
          return Response::json(2)->setCallback($request->input('callback'));
        }
        else {
          $newPassword = $request->json('newPassword');
          $confirmPassword = $request->json('confirmPassword');

          if($newPassword != $confirmPassword)
          {
            //Passwords do not match.
            return Response::json(3)->setCallback($request->input('callback'));
          }
          else {
            $user = User::where('email', '=', $reset->email)->first();

            $user->password = Hash::make($newPassword);
            $user->save();

            return Response::json(1)->setCallback($request->input('callback'));
          }
        }
      }
    }
  }

  public function storeSubscription(Request $request)
  {

    $rules = array(
      'contact'		=> 	'required|email'
    );
    $validator = Validator::make($request->json()->all(), $rules);

    if ($validator->fails()) {
        return Response::json(0)->setCallback($request->input('callback'));
    } else {

      $subscriptionContact = $request->json('contact');

      $options = Option::find(1);

      if($options->allowSubscription == 1)
      {
        $subCheck = Subscription::where('subscriptionContact', '=', $subscriptionContact)->first();
        if(empty($subCheck))
        {
          if(filter_var($subscriptionContact, FILTER_VALIDATE_EMAIL)) {
            $subscriptionType = "Email";
            $websiteName = $options->website;
            $subscriptionID = Hash::make($subscriptionContact);

            Mail::send('emails.confirmSubscription', ['subscriptionContact' => $subscriptionContact, 'websiteName' => $websiteName, 'subscriptionID' => $subscriptionID], function ($m) use ($subscriptionContact, $websiteName, $subscriptionID) {
                $m->to($subscriptionContact)->subject($websiteName.' - Confirm Follow');
            });
          }
          else {
            if($options->textEnabled == true)
            {
              $subscriptionType = "Text";
            }
            else {
              //Admin has not allowed Texting
              return Response::json(2)->setCallback($request->input('callback'));
            }
          }

          $subscription = new Subscription;
          $subscription->subscriptionContact = $subscriptionContact;
          $subscription->subscriptionType = $subscriptionType;
          $subscription->subscriptionFrequency = "Weekly";
          $subscription->subscriptionID = $subscriptionID;
          $subscription->subscriptionActive = false;
          $subscription->save();

          return Response::json(1)->setCallback($request->input('callback'));
        }
        else {
          //You have already signed up
          return Response::json(3)->setCallback($request->input('callback'));
        }
      }
      else {
        //Admin does not allow Subscribing
        return Response::json(4)->setCallback($request->input('callback'));
      }
    }
  }

  public function confirmSubscription(Request $request)
  {
    $token = $request->json('token');
    $subscription = Subscription::where('subscriptionID', '=', $token)->first();

    if(!empty($subscription))
    {
      if($subscription->subscriptionActive == 0)
      {
        $subscription->subscriptionActive = 1;
        $subscription->save();
        //Activate
        return Response::json(1)->setCallback($request->input('callback'));
      }
      else {
        //Already Active
        return Response::json(0)->setCallback($request->input('callback'));
      }
    } else {
      //Token not found
      return Response::json(2)->setCallback($request->input('callback'));
    }
  }

  public function unSubscribe(Request $request, $token)
  {
    $subscription = Subscription::where('subscriptionID', '=', $token)->first();

    if(!empty($subscription))
    {
      if($subscription->subscriptionActive == true)
      {
        $subscription->subscriptionActive = false;
        $subscription->save();
        //Unsubscribed
        return Response::json(1)->setCallback($request->input('callback'));
      }
      else {
        //Not subscribed
        return Response::json(0)->setCallback($request->input('callback'));
      }
    } else {
      //Token not found
      return Response::json(2)->setCallback($request->input('callback'));
    }
  }
}
