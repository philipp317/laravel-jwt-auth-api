<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\User;
use App\Http\Requests;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;

use Tymon\JWTAuth\JWTAuth;
use JWTAuthException;

class AuthController extends Controller
{
    private $user;
    private $jwtauth;

    public function __construct(User $user, JWTAuth $jwtauth)
    {
        $this->user = $user;
        $this->jwtauth = $jwtauth;
    }

    public function register(RegisterRequest $request)
    {
        if ($request->get('facebook') == "default") {
            $newUser = $this->user->create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'google' => $request->get('google'),
                'facebook' => $request->get('facebook')
            ]);            
        } else if ($request->get('google') == "default") {
            $user = User::where('facebook', $request->input('facebook'))->first();
            if(!$user){
                if($request->get("email")) {
                    $newUser = $this->user->create([
                        'name' => $request->get('name'),
                        'email' => $request->get('email'),
                        'google' => $request->get('google'),
                        'facebook' => $request->get('facebook')
                    ]); 
                } else {
                    $newUser = $this->user->create([
                        'name' => $request->get('name'),
                        'google' => $request->get('google'),
                        'facebook' => $request->get('facebook')
                    ]);
                }
            }
        } else if ($request->get('phone')) {
            $newUser = $this->user->create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'phone' => $request->get('phone'),
                'password' => bcrypt($request->get('password'))
            ]);
        }

        if (!$newUser) {
            return response()->json(['failed_to_create_new_user'], 500);
        }

        return response()->json([
            'token' => $this->jwtauth->fromUser($newUser),
            'uuid' => bcrypt($newUser->id)
        ]);
    }

    public function login(LoginRequest $request)
    {
        //get user credentials; email, password
        $credentials = [];
        
        if($request->input('email')) {
            $credentials = $request->only('email', 'password');
            $user = User::where('email', $request->input('email'))->first();
        } else if($request->input('phone')) {
            $credentials = $request->only('phone', 'password');
            $user = User::where('phone', $request->input('phone'))->first();
        }
        
        $token = null;

        try {
            $token = $this->jwtauth->attempt($credentials);
            if (!$token) {
                return response()->json(['Invalid email or password'], 422);
            }
        } catch (JWTAuthException $e) {
            return response()->json(['failed_to_create_token'], 500);
        }

        return response()->json([compact('token'), 
            'uuid' => bcrypt($user->id)]);
    }

    public function getUuid(Request $request) {
        if($request->input('email')) {
            $user = User::where('email', $request->input('email'))->first();
        } else if($request->input('fbid')) {
            $user = User::where('facebook', $request->input('fbid'))->first();
        }
        if($user) {
            $id = $user->id;
            return response()->json(['uuid' => bcrypt($id)]); 
        }  
    }
}
