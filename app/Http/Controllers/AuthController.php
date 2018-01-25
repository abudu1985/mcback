<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Category;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;
use DB, Hash, Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Mail\Message;


class AuthController extends Controller
{
    /**
     * API Register
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ];

        $input = $request->only(
            'name',
            'email',
            'password',
            'password_confirmation'
        );

        $validator = Validator::make($input, $rules);

        if($validator->fails()) {
            $error = $validator->messages()->toJson();
            return response()->json(['name'=> 'Not confirmed password']);  // 'success'=> false,
        }

        $name = $request->name;
        $email = $request->email;
        $password = $request->password;
        $user = User::create(['name' => $name, 'email' => $email, 'password' => Hash::make($password)]);
        $category = Category::create(['name' => 'Дом', 'capacity' => 100, 'user_id' => $user->id]);

        $verification_code = str_random(30); //Generate verification code
        DB::table('user_verifications')->insert(['user_id'=>$user->id,'token'=>$verification_code]);

        $subject = "Please verify your email address.";
        Mail::send('email.verify', ['name' => $name, 'verification_code' => $verification_code],
            function($mail) use ($email, $name, $subject){
                $mail->from(getenv('FROM_EMAIL_ADDRESS'), "ig1985@ukr.net");
                $mail->to($email, $name);
                $mail->subject($subject);
            });
        return response()->json($verification_code);
    }


    /**
     * API Verify User
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyUser($verification_code)
    {
        $check = DB::table('user_verifications')->where('token',$verification_code)->first();

        if(!is_null($check)){
            $user = User::find($check->user_id);

            if($user->is_verified == 1){
                return response()->json([
                    'success'=> true,
                    'message'=> 'Account already verified..'
                ]);
            }

            $user->update(['is_verified' => 1]);
            DB::table('user_verifications')->where('token',$verification_code)->delete();
        }
        return redirect()->away('http://localhost:4200/login?nowCanLogin=' .str_replace('"', "", $verification_code),
            201, ['X-Requested-With' => 'XMLHttpRequest']);

    }


    /**
     * API Login, on success return JWT Auth token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];

        $input = $request->only('email', 'password');

        $validator = Validator::make($input, $rules);

        if($validator->fails()) {
            $error = $validator->messages()->toJson();
            return response()->json(['success'=> false, 'error'=> $error]);
        }

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
            'is_verified' => 1
        ];

        try {
            // attempt to verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['success' => false, 'error' => 'Invalid Credentials. Please make sure you entered the right information and you have verified your email address.'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['success' => false, 'error' => 'could_not_create_token'], 500);
        }

        // all good so return the token
        return response()->json(['success' => true, 'data'=> [ 'token' => $token ]]);
    }


    /**
     * Log out
     * Invalidate the token, so user cannot use it anymore
     * They have to relogin to get a new token
     *
     * @param Request $request
     */
    public function logout(Request $request) {
        $this->validate($request, ['token' => 'required']);

        try {
            JWTAuth::invalidate($request->input('token'));
            return response()->json(['success' => true]);
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['success' => false, 'error' => 'Failed to logout, please try again.'], 500);
        }
    }


    /**
     * API Recover Password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recover(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            $error_message = "Your email address was not found.";
            return response()->json(['success' => false, 'error' => ['email'=> $error_message]], 401);
        }

        $verification_code = str_random(30); //Generate verification code
        $user->reset_token_code = $verification_code;
        $user->save();

        $subject = "Please verify your email address.";
        $email = $request->email;
        $name = $user->name;
        Mail::send('email.reset', ['name' => $user->name, 'verification_code' => $verification_code],
            function($mail) use ($email, $name, $subject){
                $mail->from(getenv('FROM_EMAIL_ADDRESS'), "ig1985@ukr.net");
                $mail->to($email, $name);
                $mail->subject($subject);
            });
        return response()->json($verification_code);
    }

    /**
     * @param $verification_code
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function reset($verification_code)
    {
        $user = User::where('reset_token_code', $verification_code)->first();

        if (!$user) {
            $error_message = "Your email address was not found.";
            return response()->json(['success' => false, 'error' => ['email'=> $error_message]], 401);
        } else {
            return redirect()->away('http://localhost:4200/login?reset_pass=' .str_replace('"', "", $verification_code),
                201, ['X-Requested-With' => 'XMLHttpRequest']);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetpas(Request $request)
    {
        $user = User::where('reset_token_code', $request->reset_token_code)->first();
        if($user) {
            $user->password = bcrypt($request->password);
            $user->reset_token_code = '';
            if ($user->save()) {
                return response()->json(['password' => true], 200);
            }
        } else {
            return response()->json(['password' => false], 200);
        }
    }
}
