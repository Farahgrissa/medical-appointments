<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Afficher le profil
    public function show()
    {
        $user = Auth::user();

        return Inertia::render('Profile/PatientProfile', [ 
            'user' => $user
        ]);
    }

    // Afficher le formulaire d'édition
    public function edit()
    {
        $user = Auth::user();

        return Inertia::render('Profile/Edit_UpdateProfile', [ 
            'user' => $user
        ]);
    }

    // Mettre à jour le profil
 public function update(Request $request)
    {/** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6',
        ]);

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->gender = $request->gender;
        $user->date_of_birth = $request->date_of_birth;
        $user->address = $request->address;

        if (!empty($request->password)) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    // Afficher la page de suppression
    public function delete()
    {
        $user = Auth::user();

        return Inertia::render('Profile/DeleteProfile', [ 
            'user' => $user
        ]);
    }

    // Supprimer le compte
    public function destroy()
    {/** @var \App\Models\User $user */
        $user = Auth::user();

        Auth::logout();
        $user->delete();

        return redirect('/welcome')->with('success', 'Your account has been deleted.');
    }
}
