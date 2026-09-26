<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('Users/Index', ['users' => User::orderBy('name')->paginate(25)]);
    }

    public function store(Request $r)
    {
        $data = $r->validate(['name' => 'required|string|max:255', 'email' => 'required|email|unique:users', 'role' => 'required|in:admin,monitor', 'password' => 'required|string|min:12|confirmed']);
        $data['password'] = Hash::make($data['password']);
        $data['email_verified_at'] = now();
        User::create($data);

        return back()->with('success', 'User created.');
    }

    public function update(Request $r, User $user)
    {
        $data = $r->validate(['role' => 'required|in:admin,monitor']);
        abort_if($user->is($r->user()) && $data['role'] !== 'admin', 422, 'You cannot remove your own admin role.');
        $user->update($data);

        return back()->with('success', 'Role updated.');
    }
}
