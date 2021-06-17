<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Model\Tag;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::all();

        return $tags;
    }
}
