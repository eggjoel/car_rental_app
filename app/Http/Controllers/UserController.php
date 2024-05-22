<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
 
class UserController extends Controller
{
    //Register the user
    public function register(Request $request)
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()],422);
        }

        // Create new user
        $user = User::create([
            'firstname' => $request->input('firstname'),
            'lastname' => $request->input('lastname'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
        ]);
        
        // Return success response with user details
        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }

     // Login the user
     public function login(Request $request)
     {
         // Validate incoming request data
         $validator = Validator::make($request->all(), [
             'email' => 'required|email',
             'password' => 'required|string',
         ]);
 
         // If validation fails, return error response
         if ($validator->fails()) {
             return response()->json(['error' => $validator->errors()], 422);
         }
 
         // Attempt to authenticate the user
         if (Auth::attempt(['email' => $request->input('email'), 'password' => $request->input('password')])) {
             // Authentication successful
             $user = Auth::user();
             // Generate a new API token using Sanctum
              /** @var \App\Models\MyUserModel $user **/
             $token = $user->createToken('authToken')->plainTextToken;
             // Return success response with user details and token
             return response()->json(['message' => 'User logged in successfully', 'user' => $user, 'token' => $token], 200);
         } else {
             // Authentication failed
             return response()->json(['error' => 'Unauthorized'], 401);
         }
     }
}
