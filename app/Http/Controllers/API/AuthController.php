<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\SendCode;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    //"Implement basic authentication "
    public function register(Request $request)
    {
        //validate the request
        $validator = validator()->make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|string|min:8',
        ]);
        //if the validation fails
        if ($validator->fails()) {
            return response()->json([0, $validator->errors()->first(), $validator->errors()->first()]);
        }
        //here Generate random 6-digits verification code  for users
        $verificationCode = rand(100000, 999999);
        //after user pass validation successfully now sending data to database
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'verification_code' => $verificationCode
        ]);
        //and saving user data to database
        $user->save();
        // here Token is generated for user
        $token = $user->createToken(name: 'AuthToken')->plainTextToken;
        //send verification code to user email
        Mail::to($user->email)->send(new SendCode($user));
        //return response with token
        return response()->json(['message' => 'User registered. Check email for verification code.', [
            'token' => $token,
        ]]);
    }
    // Only verified accounts can login to the system
    public function verfiy_code(Request $request)
    {
        //validate the request
        $validator = validator()->make($request->all(), [
            'email' => 'required|email',
            'verification_code' => 'required',
        ]);
        //if the validation fails
        if ($validator->fails()) {
            return response()->json([0, $validator->errors()->first(), $validator->errors()->first()]);
        }
        //here get user by email
        $user = User::where('email', $request->email)->first();
        //if user is not found
        if (!$user) {
            return response()->json([0, 'User not found', 'User not found']);
        }
        //if user is already verified
        if ($user->verification_code == $request->verification_code) {
            //update user status to verified
            $user->update([
                'is_verified' => true,
                'verification_code' => null,
            ]);
            //return response with message
            return response()->json(['message' => 'User verified successfully.']);
        } else {
            //return response with message error for verification code
            return response()->json([0, 'Verification code is incorrect.', $validator->errors()->first()]);
        }
    }
    //login function
    public function login(Request $request)
    {
        //validate the request
        $validator = validator()->make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);
        //if the validation fails
        if ($validator->fails()) {
            return response()->json([0, $validator->errors()->first(), $validator->errors()->first()]);
        }
        //here get user by email
        $user = User::where('email', $request->email)->first();
        //if user is not found
        if(!$user){
            return response()->json(['message'=>'email not found']);
        }
        //if user is not verified
        if (!$user->is_verified) {
            return response()->json(['error' => 'Account not verified'], 403);
        }
        //if user is found
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        //create token
        $token = $user->createToken('AuthToken')->plainTextToken;
        //return response with token
        return response()->json(['message' => 'User logged in successfully.', [
            'token' => $token,
        ]]);
    }
    //system stats
    public function stats()
    {
        return response()->json([
            'total_users' => User::count(), //retrive how many users
            'total_posts' => Post::count(), //retrive how many posts
            'users_without_posts' => User::doesntHave('posts')->count() //retrive how many users without posts
        ]);
    }
}
