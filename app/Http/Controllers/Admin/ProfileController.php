<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminProfileUpdateRequest;
use App\Http\Requests\AdminUpdatePasswordRequest;
use App\Models\Admin;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use RealRashid\SweetAlert\Facades\Alert;
class ProfileController extends Controller
{
    use FileUploadTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::guard('admin')->user();
        return view('admin.profile.index',compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AdminProfileUpdateRequest $request, string $id)
    {
        $admin = Admin::findOrFail($id);

        // Handle avatar image
        $imagePath = $this->handleFileUpload($request, 'image', $request->old_image);

        // Handle cover banner image
        $coverPath = $this->handleFileUpload($request, 'cover_image', $request->old_cover_image);

        // Save updated writer profile data
        $admin->image = !empty($imagePath) ? $imagePath : $request->old_image;
        if (!empty($coverPath)) {
            $admin->cover_image = $coverPath;
        }
        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->designation = $request->designation;
        $admin->bio = $request->bio;
        $admin->website = $request->website;
        $admin->twitter = $request->twitter;
        $admin->linkedin = $request->linkedin;
        $admin->facebook = $request->facebook;
        $admin->instagram = $request->instagram;
        $admin->save();

        toast(__('admin.Updated Successfully'), 'success')->width('400');

        return redirect()->back();
    }

    /**
     * Update the specified resource for password.
     */

    public function passwordUpdate(AdminUpdatePasswordRequest $request,string $id){

        $admin = Admin::findOrFail($id);
        $admin ->password = bcrypt($request->password);
        $admin->save();
        toast(__('admin.Updated Successfully'),'success')->width('400');
        return redirect()->back();
    }
}
