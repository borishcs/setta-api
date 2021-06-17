<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\EmailController;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UserExport;
use App\Exports\TasksExport;
use App\Exports\SubscribersExport;

class CronJobsController extends Controller
{
    public function handleExecuteCron()
    {
        $this->sendUsers();
        $this->sendTasks();
        $this->sendSubscribers();
    }

    public function sendUsers()
    {
        Excel::store(new UserExport(), 'users.xlsx', 'exports');

        try {
            $sendEmail = EmailController::send(
                'larissa.vaz@setta.co',
                ['ramiro@setta.co', 'vitor.clemon@setta.co', 'rafael@setta.co'],
                'Setta',
                'Relatório de Usuarios',
                'app/exports/users.xlsx',
                'Setta Bot',
                'emails.template'
            );
        } catch (JWTException $exception) {
            return response()->json(
                [
                    'message' => 'erro ao enviar e-mail.',
                ],
                500
            );
        }

        return true;
    }

    public function sendTasks()
    {
        Excel::store(new TasksExport(), 'tasks.xlsx', 'exports');

        try {
            $sendEmail = EmailController::send(
                'ramiro@setta.co',
                ['vitor.clemon@setta.co', 'rafael@setta.co'],
                'Setta',
                'Relatório de Tarefas',
                'app/exports/tasks.xlsx',
                'Setta Bot',
                'emails.template'
            );
        } catch (JWTException $exception) {
            return response()->json(
                [
                    'message' => 'erro ao enviar e-mail.',
                ],
                500
            );
        }

        return true;
    }

    public function sendSubscribers()
    {
        Excel::store(new SubscribersExport(), 'Subscribers.xlsx', 'exports');

        try {
            $sendEmail = EmailController::send(
                'ramiro@setta.co',
                [
                    'larissa.vaz@setta.co',
                    'vitor.clemon@setta.co',
                    'rafael@setta.co',
                ],
                'Setta',
                'Relatório de Subscribers',
                'app/exports/Subscribers.xlsx',
                'Setta Bot',
                'emails.template'
            );
        } catch (JWTException $exception) {
            return response()->json(
                [
                    'message' => 'erro ao enviar e-mail.',
                ],
                500
            );
        }

        return true;
    }
}
