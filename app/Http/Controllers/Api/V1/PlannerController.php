<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Planner\PlannerOrderRequest;
use App\Model\Habit;
use App\Model\HabitCompleted;
use App\Model\Planner;
use App\Model\Task;
use App\Model\TaskCompleted;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PlannerController extends Controller
{
    private $weekStartAt = Carbon::SUNDAY; // domingo

    public function index()
    {
        return [
            'inbox' => $this->inbox(),
            'today' => $this->today(),
            'tomorrow' => $this->tomorrow(),
            'this_week' => $this->this_week(),
            'next_week' => $this->next_week(),
            'this_month' => $this->this_month(),
            'next_month' => $this->next_month(),
        ];
    }

    /**
     * task: not completed, just parent tasks, schedule = true, date = today
     * habit: not completed = today, just parent habits, repeat = weekDay, exceptions
     * order: {1, 2, 3, null) or id by default
     */
    public function today()
    {
        try {
            $today = Carbon::today(Auth::user()->timezone);

            $tasks = Task::with(['subtasks'])
                ->where('parent_id', null)
                ->where('user_id', Auth::id())
                ->where('schedule', true)
                ->where('completed_at', null)
                ->whereDate('due_date', $today)
                //->doesntHave('completed')
                ->orderBy('order', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            return $tasks;
        } catch (\Throwable $th) {
            throw new \Exception('Ops! ocorreu um erro!');
        }
    }

    /**
     * task: not habit, just parent tasks, schedule = true, date = tomorrow
     * habit: not completed = today, just parent habits, repeat = weekDay, exceptions
     * order: {1, 2, 3, null) or id by default
     */
    public function tomorrow()
    {
        try {
            $tomorrow = Carbon::tomorrow(Auth::user()->timezone);

            $tasks = Task::with(['subtasks'])
                ->where('parent_id', null)
                ->where('user_id', Auth::id())
                ->where('schedule', true)
                ->where('completed_at', null)
                ->whereDate('due_date', $tomorrow)
                //->doesntHave('completed')
                ->orderBy('order', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            return $tasks;
        } catch (\Throwable $th) {
            throw new \Exception('Ops! ocorreu um erro!');
        }
    }

    /**
     * task: just parent, schedule = false, date between last sunday => next saturday
     * order: {1, 2, 3, null)
     */
    public function this_week()
    {
        try {
            $firstDayOfWeek = Carbon::today(
                Auth::user()->timezone
            )->startOfWeek($this->weekStartAt);
            $nextSaturday = Carbon::today(Auth::user()->timezone)
                ->startOfWeek($this->weekStartAt)
                ->addWeeks(1)
                ->subDay()
                ->endOfDay();

            $tasks = Task::with(['subtasks'])
                ->where('habit_id', null)
                ->where('parent_id', null)
                ->where('user_id', Auth::id())
                ->where('schedule', false)
                ->where('completed_at', null)
                ->whereBetween('due_date', [$firstDayOfWeek, $nextSaturday])
                //->doesntHave('completed')
                ->orderBy('due_date', 'asc')
                ->paginate(20);

            return $tasks;
        } catch (\Throwable $th) {
            throw new \Exception('Ops! ocorreu um erro!');
        }
    }

    /**
     * task: just parent, schedule = false, date between next sunday => next saturday of next week
     */
    public function next_week()
    {
        try {
            $nextSunday = Carbon::today(Auth::user()->timezone)
                ->startOfWeek($this->weekStartAt)
                ->addWeeks(1);

            $saturdayOfNextWeek = Carbon::today(Auth::user()->timezone)
                ->startOfWeek($this->weekStartAt)
                ->addWeeks(2)
                ->subDay()
                ->endOfDay();

            $tasks = Task::with(['subtasks'])
                ->where('habit_id', null)
                ->where('parent_id', null)
                ->where('user_id', Auth::id())
                ->where('schedule', false)
                ->where('completed_at', null)
                ->whereBetween('due_date', [$nextSunday, $saturdayOfNextWeek])
                //->doesntHave('completed')
                ->orderBy('due_date', 'asc')
                ->paginate(20);

            return $tasks;
        } catch (\Throwable $th) {
            throw new \Exception('Ops! ocorreu um erro!');
        }
    }

    /**
     * task: just parent, schedule = false, date between first day of month => last day of month
     */
    public function this_month()
    {
        try {
            $firstDayOfMonth = Carbon::today(
                Auth::user()->timezone
            )->firstOfMonth();
            $lastDayOfMonth = Carbon::today(Auth::user()->timezone)
                ->lastOfMonth()
                ->endOfDay();

            $tasks = Task::with(['subtasks'])
                ->where('habit_id', null)
                ->where('parent_id', null)
                ->where('user_id', Auth::id())
                ->where('schedule', false)
                ->where('completed_at', null)
                ->whereBetween('due_date', [$firstDayOfMonth, $lastDayOfMonth])
                //->doesntHave('completed')
                ->orderBy('due_date', 'asc')
                ->paginate(20);

            return $tasks;
        } catch (\Throwable $th) {
            throw new \Exception('Ops! ocorreu um erro!');
        }
    }

    /**
     * task: just parent, schedule = false, date between next sunday => next saturday of next week
     */
    public function next_month()
    {
        try {
            $firstDayOfNextMonth = Carbon::today(Auth::user()->timezone)
                ->addMonth()
                ->firstOfMonth();

            $lastDayOfNextMonth = Carbon::today(Auth::user()->timezone)
                ->addMonth()
                ->lastOfMonth()
                ->endOfDay();

            $tasks = Task::with(['subtasks'])
                ->where('habit_id', null)
                ->where('parent_id', null)
                ->where('user_id', Auth::id())
                ->where('schedule', false)
                ->where('completed_at', null)
                ->whereBetween('due_date', [
                    $firstDayOfNextMonth,
                    $lastDayOfNextMonth,
                ])
                //->doesntHave('completed')
                ->orderBy('due_date', 'asc')
                ->paginate(20);

            return $tasks;
        } catch (\Throwable $th) {
            throw new \Exception('Ops! ocorreu um erro!');
        }
    }

    /**
     * REGRAS:
     *   Atrasadas
     *    - schedule true com data anterior a hoje
     *    - schedule false com data anterior a último domingo
     *   Sem data
     *    - date = null
     */
    public function inbox()
    {
        try {
            $today = Carbon::today(Auth::user()->timezone);

            // overdue => último domingo (semana passada)
            $overdue = Carbon::today(Auth::user()->timezone)->startOfWeek(
                $this->weekStartAt
            );

            $tasks = Task::with(['subtasks'])
                ->where('habit_id', null)
                ->where('parent_id', null)
                ->where('user_id', Auth::id())
                ->where('completed_at', null)
                ->where(function (Builder $query) use ($today, $overdue) {
                    // schedule = true && date < hoje
                    $query->orWhere(function (Builder $query) use ($today) {
                        $query->where('schedule', true);
                        $query->where('due_date', '<', $today);
                    });

                    // schedule = false && date < domingo
                    $query->orWhere(function (Builder $query) use ($overdue) {
                        $query->where('schedule', false);
                        $query->where('due_date', '<', $overdue);
                    });

                    // date == null
                    $query->orWhere('due_date', null);
                })
                ->orderBy('due_date', 'desc')
                //->orderBy('id', 'desc')
                ->paginate(20);

            return $tasks;
        } catch (\Throwable $th) {
            throw new \Exception('Ops! ocorreu um erro!');
        }
    }

    /**
     * Alterar ordem dos itens
     * Recebe array com todos os ids das tarefas, int da ordem na lista e type (task/habit)
     * TODO: user permissions
     */
    public function order(Request $request)
    {
        try {
            // { id: int, order: int, type: string (task/habit), period_id: int }
            //dd($request);

            $items = [];

            $items = $request->input();

            if (count($items)) {
                foreach ($items as $item) {
                    $task = Task::findOrFail($item['id']);

                    if ($task->user_id !== Auth::id()) {
                        abort(403);
                    }

                    if (
                        $item['period'] == 'inbox' or
                        $item['period'] == 'unset'
                    ) {
                        $task->period = null;
                    } else {
                        $task->period = $item['period'];
                    }

                    $task->order = $item['order'];
                    $task->save();
                }
            }
        } catch (\Throwable $th) {
            throw new \Exception('Ops! ocorreu um erro!');
        }
    }

    public function completed()
    {
        try {
            $tasks = Task::with(['subtasks'])
                ->where('parent_id', null)
                ->where('user_id', Auth::id())
                ->where('schedule', true)
                ->where('completed_at', '!=', null)
                ->orderBy('completed_at', 'desc')
                ->orderBy('id', 'asc')
                ->paginate(20);

            return $tasks;
        } catch (\Throwable $th) {
            throw new \Exception('Ops! ocorreu um erro!');
        }
    }
}
