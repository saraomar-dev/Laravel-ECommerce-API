<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function register(RegisterRequest $request)
    {
    $user= User::Create([
    'email'=>$request->email,
    'password'=>Hash::make($request->password),
    'name'=>$request->name,
    ]);
    return response()->json(['user'=>new UserResource($user),
                            'message'=>'registered successfully'], 201);
    }
    public function login(LoginRequest $request)
    {
        if(!Auth::attempt(['email' => $request->email, 'password' => $request->password])){
        return response()->json(['message'=>'email or password is invalid'], 401);
        }
        $user=User::where('email',$request->email)->FirstOrFail();
        $token=$user->createToken('auth_token')->plainTextToken;
        return response()->json(['message'=>'log in successfully','user'=>new UserResource($user),'token'=>$token], 201);
    }
    public function logout(Request $request)
    {
    $request->user()->currentAccessToken()->delete();
    return response()->json(['message'=>'log out succesfully'], 200);
    }
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
