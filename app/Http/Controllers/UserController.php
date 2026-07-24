<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Container\Attributes\Auth as AttributesAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    { $users=User::paginate(5);

    return  UserResource::collection($users);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RegisterRequest $request)
    {
    $user= User::Create([
    'email'=>$request->email,
    'password'=>Hash::make($request->password),
    'name'=>$request->name,
    'role'=>$request->role,

    ]);
    return response()->json(['user'=>new UserResource($user),
                            'message'=>'created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json(['user'=>new UserResource($user)], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        if($user->role==='admin'&&auth()->id()!==$user->id){
            return response()->json([
                            'message'=>'you can not edit another admin'], 403);
        }
        $user->update($request->validated());
        return response()->json(['user'=>new UserResource($user),'message'=>'updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if($user->role==='admin'&&auth()->id()!==$user->id){
            return response()->json([
                            'message'=>'you can not delete another admin'], 403);
        }
        if(auth()->id()===$user->id){
        return response()->json([
                            'message'=>'you can not delete your self'], 403);
    }
    if ($user->image) {
    Storage::disk('public')->delete($user->image);
}
    $user->tokens()->delete();
    $user->delete();
     return response()->json([
                            'message'=>'user deleted successfully'], 200);
        }
    }

