<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{
    public function logout()
    {
        auth()->logout();
        return redirect('/')->with('success', 'You are now logged out.');
    }

    public function showCorrectHomepage()
    {
        if (auth()->check()) {
            return view('homepage-feed');
        } else {
            return view('homepage');
        }
    }

    public function login(Request $request)
    {
        $incomingFields = $request->validate([
            'loginusername' => 'required',
            'loginpassword' => 'required'
        ]);

        if (auth()->attempt(['username' => $incomingFields['loginusername'], 'password' => $incomingFields['loginpassword']])) {
            $request->session()->regenerate();
            return redirect('/')->with('success', 'You have successfully logged in.');
        } else {
            return redirect('/')->with('failure', 'Invalid login.');
        }
    }

    public function register(Request $request)
    {
        $incomingFields = $request->validate([
            'username' => ['required', 'min:3', 'max:20', Rule::unique('users', 'username')],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'min:8', 'confirmed']
        ]);

        $incomingFields['password'] = bcrypt($incomingFields['password']);

        $user = User::create($incomingFields);
        auth()->login($user);
        return redirect('/')->with('success', 'Thank you for creating an account.');
    }

    public function viewProfile(User $user)
    {
        return view('profile-posts', ['username' => $user->username, 'avatar' => $user->avatar, 'posts' => $user->posts()->latest()->get(), 'postCount' => $user->posts()->count()]);
    }

    public function showAvatarForm(User $user)
    {
        return view('avatar-form');
    }

    public function storeAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2000'],
        ]);

        $img = Image::make($request->file('avatar'))->fit(120)->encode('jpg');
        $user  = auth()->user();
        $filename = $user->id . '-' . uniqid() . '.jpg';

        Storage::put('public/avatars/' . $filename, $img);
        $oldAvatar = $user->avatar;
        $user->avatar = $filename;
        $user->save();
        if($oldAvatar != '/fallback-avatar.jpg'){
            Storage::delete(str_replace("/storage/", "public/", $oldAvatar));
        }
        // $request->file('avatar')->store('public/avatars');
        return back()->with('success', 'Congratulations! Your avatar has been updated.');
    }
}
