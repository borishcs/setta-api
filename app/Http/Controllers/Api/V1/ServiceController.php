<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Model\Habit;
use App\Services\LauchTaskService;
use App\Classes\Schedule\DateHandling;

class ServiceController extends Controller
{  
    private $lauchTasksService;
    public function __construct()
    {
        $this->lauchTasksService = new LauchTaskService ();
    }

    public function monthly()
    {
        $this->lauchTasksService->receiveHabits();
        return response('Lauch Monthly Tasks', 200);
    }
}