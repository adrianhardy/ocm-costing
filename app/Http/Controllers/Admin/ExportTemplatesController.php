<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\SplFileInfo;

class ExportTemplatesController extends Controller
{
    public function index()
    {
        $templates = Storage::disk('export_templates')->allFiles('sales-orders');
        return response()->view('admin.export-templates.index', [
            'templates' => collect($templates)->map(function($file) {
                return new \SplFileInfo(Storage::disk('export_templates')->path($file));
            })
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'template' => 'required|file'
        ]);
        $request->file('template')->storeAs('sales-orders',
            $request->file('template')->getClientOriginalName(),
            [ 'disk' => 'export_templates']);
        return back()->with('alert-success', 'New export template added');
    }

    /**
     * This comes in as a get request
     * @param Request $request
     */
    public function destroy(Request $request)
    {
        Storage::disk('export_templates')->delete('sales-orders/' . $request->query('template'));
        return back()->with('alert-success', 'Deleted template');
    }
}
