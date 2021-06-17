<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Model\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Formatter\JsonFormatter;

class MomentController extends Controller
{
    private $weekStartAt = Carbon::SUNDAY; // domingo

    public function index(Request $request)
    {
        $timeNow = Carbon::now();

        //hoje
        $today = Carbon::today();
        //se for 1 dia do mês
        $firstDayOfMonth = Carbon::today()->firstOfMonth();

        //Define periodo da manhã
        $startMorning = Carbon::createFromTimeString('07:00:00'); //'07:00:00'
        $endMorning = Carbon::createFromTimeString('23:59:59'); //'11:59:59'

        if ($timeNow->between($startMorning, $endMorning)) {
            //se for 1 dia do mês
            if (Carbon::today()->eq($firstDayOfMonth)) {
                return $this->firstDayMonth();
            }
            //se for DOMINGO
            elseif ($today->dayOfWeek == Carbon::SUNDAY) {
                return $this->thisWeek();
            } else {
                return $this->morning();
            }
        }
    }

    public function morning()
    {
        $today = Carbon::today();

        try {
            $tasks = Task::with(['subtasks'])
                ->where('habit_id', null)
                ->where('parent_id', null)
                ->where('user_id', Auth::id())
                ->where('completed_at', null)
                ->where(function (Builder $query) use ($today) {
                    // schedule = true && date < hoje
                    $query->orWhere(function (Builder $query) use ($today) {
                        $query->where('schedule', true);
                        $query->where('due_date', '<', $today);
                    });

                    $query->orWhere('schedule', false);
                })
                ->orderBy('due_date', 'desc')
                ->orderBy('id', 'desc')
                ->paginate(20);

            // Set the format
            $output = "%channel%.%level_name%: %message% %context% %extra%";
            $formatter = new LineFormatter($output);
            // Setup the logger
            $logger = new Logger('my_logger');
            $syslogHandler = new SyslogUdpHandler(
                env('PAPERTRAIL_URL'),
                env('PAPERTRAIL_PORT')
            );
            $syslogHandler->setFormatter($formatter);
            $logger->pushHandler($syslogHandler);

            $beautifiedJsonObjectString = json_encode($tasks);

            // Use the new logger
            $logger->info("/api/v1/moment", [
                'object' => $beautifiedJsonObjectString,
                Auth::id(),
            ]);
        } catch (\Throwable $th) {
            throw new Exception('Ops! ocorreu um erro!');
        }

        return response()->json([
            'type' => 'morning',
            'tasks' => $tasks,
        ]);
    }

    public function firstDayMonth()
    {
        try {
            $today = Carbon::today();

            $tasks = Task::with(['subtasks'])
                ->where('habit_id', null)
                ->where('parent_id', null)
                ->where('user_id', Auth::id())
                ->where('completed_at', null)
                ->where(function (Builder $query) use ($today) {
                    // schedule = true && date < hoje
                    $query->orWhere(function (Builder $query) use ($today) {
                        $query->where('schedule', true);
                        $query->where('due_date', '<', $today);
                    });

                    $query->orWhere('schedule', false);
                })
                ->orderBy('due_date', 'desc')
                ->orderBy('id', 'desc')
                ->paginate(20);

            return response()->json([
                'type' => 'month',
                'tasks' => $tasks,
            ]);
        } catch (\Throwable $th) {
            throw new Exception('Ops! ocorreu um erro!');
        }
    }

    public function thisWeek()
    {
        try {
            $today = Carbon::today();

            $tasks = Task::with(['subtasks'])
                ->where('habit_id', null)
                ->where('parent_id', null)
                ->where('user_id', Auth::id())
                ->where('completed_at', null)
                ->where(function (Builder $query) use ($today) {
                    // schedule = true && date < hoje
                    $query->orWhere(function (Builder $query) use ($today) {
                        $query->where('schedule', true);
                        $query->where('due_date', '<', $today);
                    });

                    $query->orWhere('schedule', false);
                })
                ->orderBy('due_date', 'desc')
                ->orderBy('id', 'desc')
                ->paginate(20);

            return response()->json([
                'type' => 'week',
                'tasks' => $tasks,
            ]);
        } catch (\Throwable $th) {
            throw new Exception('Ops! ocorreu um erro!');
        }
    }

    public function todayLost()
    {
        $today = Carbon::today();

        $tasks = Task::with('subtasks')
            ->where('habit_id', null)
            ->where('parent_id', null)
            ->where('user_id', Auth::id())
            ->where('schedule', true)
            ->whereDate('due_date', $today)
            ->where('completed_at', null)
            ->orderBy('due_date', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(20);

        return response()->json([
            'type' => 'lost_today',
            'tasks' => $tasks,
        ]);
    }

    public function taskLost()
    {
        $today = Carbon::today();

        $tasks = Task::with('subtasks')
            ->where('habit_id', null)
            ->where('parent_id', null)
            ->where('user_id', Auth::id())
            ->whereDate('due_date', '<', $today)
            ->where('completed_at', null)
            ->orderBy('due_date', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(20);

        return response()->json([
            'type' => 'lost_all',
            'tasks' => $tasks,
        ]);
    }
}
