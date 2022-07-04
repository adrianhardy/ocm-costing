<?php

namespace App\Http\Controllers\Api\Pricing;

use App\Http\Controllers\Controller;
use App\Models\AmazonGuidance;
use App\Transformers\AmazonGuidanceTransformer;
use Illuminate\Http\Request;

class AmazonGuidanceController extends Controller
{
    public function index(){
        $guidance = AmazonGuidance::query()->where('id', 1)->first();

        return response()->json(fractal($guidance, new AmazonGuidanceTransformer()));
    }
}
