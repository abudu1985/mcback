<?php
/**
 * Created by PhpStorm.
 * User: Anna
 * Date: 13.12.2017
 * Time: 15:32
 */
namespace App\Http\Controllers;
use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;



class UserController extends Controller
{
   public function signUp(Request $request)
   {
       $this->validate($request,[
           'name' => 'required',
           'email' => 'required|email|unique:users',
           'password' => 'required'
       ]);
       $user = new User([
           'name' => $request->input('name'),
           'email' => $request->input('email'),
           'password' => bcrypt($request->input('password'))
       ]);
       $user->save();
       return response()->json([
          'message' => 'Successfully created user'
       ]);
   }

   public function signin(Request $request)
   {
       $this->validate($request,[
           'email' => 'required|email',
           'password' => 'required'
       ]);
       $credientals = $request->only('email', 'password');
       try {
           if(!$token = JWTAuth::attempt($credientals)) {
               return response()->json([
                   'error' => 'Invalid Credentials!'
               ], 401);
           }
       } catch(JWTException $e) {
           return response()->json([
               'error' => 'Could not create token!'
           ], 500);
       }
       $email = $request->input('email');
       $user = User::where('email', $email)->first();

       return response()->json([
           'token' => $token,
           'user_name' => $user->name
       ], 200);
   }

   public function findEmail($email)
   {
       $user = User::where('email', $email)->first();
       if($user){
           return response()->json('already exist', 200);
       } else {
           return response()->json([], 200);
       }
   }
}