<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;

class StatusController extends Controller
{
    public function status()
    {
        return response(['status' => true], 200);
    }
}
