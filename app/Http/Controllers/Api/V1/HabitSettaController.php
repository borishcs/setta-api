<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Model\HabitSetta;

class HabitSettaController extends Controller
{
    public function index()
    {
        try {
            $habitsetta = HabitSetta::orderBy('id', 'asc')->get();

            return response($habitsetta, 200);
        } catch (\Throwable $th) {
            throw new Exception('Ops! ocorreu um erro!');
        }
    }
}
