<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InstructionsController extends Controller
{
    public function index(Request $request) {
        return response()->json(['instructions' => setting('instructions')]);
    }

    public function store(Request $request) {
        setting()->set(
            'instructions', $request->input('instructions')
        );
        setting()->save();

        return response()->json();
    }
}
