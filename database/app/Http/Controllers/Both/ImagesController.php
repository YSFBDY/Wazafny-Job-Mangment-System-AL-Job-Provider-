<?php

namespace App\Http\Controllers\Both;
use App\Http\Controllers\Controller;

use App\Models\User;

use Illuminate\Http\Request;
use App\Http\Requests\SeekerProfile\UpdateCoverImgequest;
use App\Http\Requests\SeekerProfile\UpdateProfileImgequest;


use Illuminate\Support\Facades\Storage;
use App\Traits\HandleFiles;


class ImagesController extends Controller
{
    use HandleFiles;


    public function showprofileimg($user_id) {
        $user = User::select('user_id', 'profile_img')->where('user_id', $user_id)->first();
        if (!$user->profile_img) {
            return response()->json(['message' => 'Profile image not found']);
        }
        return response()->json([
            'user' => $user
        ]);
    }


    public function updateprofileimg(UpdateProfileImgequest $request) { 
        $user = User::where('user_id', $request->user_id)->first();
        $this->deleteOldFile($user->profile_img, 'profile_images');
        $newFileName = $this->handleFileUpload($request, 'profile_img', 'profile_images');
        $user->update(['profile_img' => $newFileName]);
        return response()->json(['message' => 'Profile image updated successfully']);
    }
    


    public function deleteProfileImage($user_id){
        $user = User::where('user_id', $user_id)->first();
        $this->deleteOldFile($user->profile_img, 'profile_images');
        $user->update(['profile_img' => null]);
        return response()->json(['message' => 'Profile image deleted successfully']);
    }



    


    public function showcoverimg($user_id) {
        $user = User::select('user_id', 'cover_img')->where('user_id', $user_id)->first();
        if (!$user->cover_img) {
            return response()->json([
                'message' => "Cover image not found"
            ]);
        }
        return response()->json([
            'user' => $user
        ]);
    }


    public function updatecoverimg(UpdateCoverImgequest $request) {
        $user = User::where('user_id', $request->user_id)->first();
        $this->deleteOldFile($user->cover_img, 'cover_images');
        $newFileName = $this->handleFileUpload($request, 'cover_img', 'cover_images');
        $user->update(['cover_img' => $newFileName]);
        return response()->json(['message' => 'Cover image updated successfully']);
        
    }


    public function deleteCoverImage($user_id){
        $user = User::where('user_id', $user_id)->first();
        $this->deleteOldFile($user->cover_img, 'cover_images');
        $user->update(['cover_img' => null]);
        return response()->json(['message' => 'Cover image deleted successfully']);
    }




    
}
