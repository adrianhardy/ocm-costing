<?php

namespace App\Http\Controllers\Admin\Imports;

use App\Http\Controllers\Controller;
use App\Models\Import;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TemplateController extends Controller
{
    public function index(Request $request, $template)
    {
        return response()->download(storage_path("app/private/templates/$template"));
    }
}
