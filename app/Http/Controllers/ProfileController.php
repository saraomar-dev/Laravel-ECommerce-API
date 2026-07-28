<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateMyProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function editProfile(UpdateMyProfileRequest $validated_data)
    {
        $user=auth()->user();
        $user->update($validated_data->validated());
        return response()->json(['user'=>new UserResource($user),'message'=>'updated successfully'], 200);

    }
    public function addPhone(Request $request)
    {
    $user=auth()->user();
    $num=$request->validate(['phone'=>'required|string|min:11']);
    $user->update($num);
    return response()->json(['user'=>new UserResource($user),'message'=>'phone number was added successfully'], 200);
    }
    public function addAdress(Request $request)
    {
    $user=auth()->user();
    $address=$request->validate(['address'=>'required|string|max:50']);
    $user->update($address);
    return response()->json(['user'=>new UserResource($user),'message'=>'address was added successfully'], 200);
    }
    public function addImage(Request $request)
    {
        $request->validate(['image'=>'required|mimes:png,jpg,jpeg,gif|max:2048']);
        if($request->hasFile('image')){
            $user=auth()->user();
            if ($user->image) {
            Storage::disk('public')->delete($user->image);
            }
        $path=$request->file('image')->store('users','public');
        $user->update(['image'=>$path]);
        return response()->json(['profile'=>new UserResource($user),'message'=>'image added successfully'], 201);
        }
    }
    public function showProfile()
    {
        $user=auth()->user();

        return response()->json(['your profile :'=> new UserResource($user)], 200);
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
    public function show(User $profile)
    {
        return response()->json(['profile'=>new UserResource($profile)], 200);
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
