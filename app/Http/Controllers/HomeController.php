<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index() {
        $user = User::query()->first();
        $comments = $user->comments()
            ->orderBy('created_at', 'DESC')
            ->paginate(5);
        return response()->view('home', [
            'comments' => $comments
        ]);
    }
}
