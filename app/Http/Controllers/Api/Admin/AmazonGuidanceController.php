<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AmazonGuidance;
use Illuminate\Http\Request;

class AmazonGuidanceController extends Controller
{
    public function store(Request $request){
        $guidance = AmazonGuidance::query()->where('id', 1)->first();

        $this->validate($request, [
            'collection' => 'required',
            'file' => 'required|image|mimes:jpg,jpeg,png|max:10000'
        ]);

        switch($request->input('collection')) {
            case 'sl':
                $guidance->addMediaFromRequest('file')
                    ->usingName($request->input('collection'))
                    ->usingFileName('sl.' . $request->file('file')->getClientOriginalExtension())
                    ->toMediaCollection('sl');
                break;
            case 'local_fba':
                $guidance->addMediaFromRequest('file')
                    ->usingName($request->input('collection'))
                    ->usingFileName('local_fba.' . $request->file('file')->getClientOriginalExtension())
                    ->toMediaCollection('local_fba');
                break;
            case 'enf_fba':
                $guidance->addMediaFromRequest('file')
                    ->usingName($request->input('collection'))
                    ->usingFileName('enf_fba.' . $request->file('file')->getClientOriginalExtension())
                    ->toMediaCollection('enf_fba');
                break;
        }
    }
}
