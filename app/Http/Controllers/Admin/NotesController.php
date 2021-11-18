<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class NotesController extends Controller
{
    public function store(Request $request)
    {
        /** @var User $user */
        $user = \App\Models\User::first();
        $user->comment($request->input('comment'));

        return back()->with('alert-success', 'Comment added');
    }
}
