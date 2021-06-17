<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\TaskCompletedRequest;
use App\Http\Requests\Task\TaskStoreRequest;
use App\Http\Requests\Task\TaskUpdateRequest;
use App\Http\Requests\Task\BatchUpdateRequest;
use App\Model\Task;
use App\Model\TaskCompleted;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    private $weekStartAt = Carbon::SUNDAY; // domingo
    private $schedule = false;
    private $due_date;

    public function index()
    {
        $tasks = Task::where('parent_id', null)
            ->where('user_id', Auth::id())
            ->with(['subtasks'])
            ->orderBy('due_date', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(20);

        return response($tasks, 200);
    }

    public function due_date($when)
    {
        if ($when == 'inbox' or $when == null) {
            $this->schedule = false;

            return null;
        }

        if ($when == 'today') {
            $this->schedule = true;

            return $this->due_date = Carbon::today(Auth::user()->timezone);
        }

        if ($when == 'tomorrow') {
            $this->schedule = true;

            return $this->due_date = Carbon::tomorrow(Auth::user()->timezone);
        }

        if ($when == 'next_week') {
            $this->schedule = false;
            $nextSunday = Carbon::today(Auth::user()->timezone)
                ->startOfWeek($this->weekStartAt)
                ->addWeeks(1);

            return $this->due_date = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $nextSunday,
                Auth::user()->timezone
            );
        }

        if ($when == 'this_week') {
            $this->schedule = false;
            $firstDayOfWeek = Carbon::today(
                Auth::user()->timezone
            )->startOfWeek($this->weekStartAt);

            return $this->due_date = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $firstDayOfWeek,
                Auth::user()->timezone
            );
        }

        if ($when == 'next_month') {
            $this->schedule = false;
            $firstDayOfNextMonth = Carbon::today(Auth::user()->timezone)
                ->addMonth()
                ->firstOfMonth();

            return $this->due_date = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $firstDayOfNextMonth,
                Auth::user()->timezone
            );
        }
    }

    public function store(TaskStoreRequest $request)
    {
        if ($request->has('when')) {
            $this->due_date = TaskController::due_date($request->when);
        }

        try {
            $tsx = DB::transaction(function () use ($request) {
                $task = new Task();
                $task->period = null;
                $task->title = $request->title;

                // when parent -> tag is required
                if (!$request->has('parent_id') || !$request->parent_id) {
                    if (!$request->has('tag_id')) {
                        return $this->validate($request, [
                        ]);
                    }

                    $task->tag_id = $request->tag_id;
                }

                if ($request->has('period')) {
                    $task->period = $request->period;
                    if (
                        $request->period == 'inbox' or
                        $request->period == 'unset'
                    ) {
                        $task->period = null;
                    }
                }

                if ($request->has('parent_id') && $request->has('subtasks')) {
                    return response()->json(
                        [
                            'errors' => [
                                'parent_id' =>
                                    'Não é possível criar sub tarefa para outra sub tarefa.',
                            ],
                        ],
                        400
                    );
                }

                if ($request->has('parent_id')) {
                    $task->parent_id = $request->parent_id;
                    $task->tag_id = null;
                    $task->period = null;
                }

                if ($request->has('note')) {
                    $task->note = $request->note;
                }

                if ($this->due_date) {
                    $task->due_date = $this->due_date;
                }

                if ($this->schedule) {
                    $task->schedule = $this->schedule;
                }

                $task->timezone = Auth::user()->timezone;

                $task->save();

                if ($request->has('subtasks') && count($request->subtasks)) {
                    foreach ($request->subtasks as $subtask) {
                        $currentSubtask = new Task();
                        $currentSubtask->parent_id = $task->id;
                        $currentSubtask->title = $subtask['title'];
                        $currentSubtask->save();
                    }
                }

                return $task;
            }, 2);

            if ($tsx) {
                $findTask = Task::with('subtasks')
                    ->where('id', (string) $tsx->id)
                    ->first();

                return response($findTask, 201);
            }

            return response('Error', 500);
        } catch (\Throwable $th) {
            throw new \Exception('Ops! ocorreu um erro, tente novamente!');
        }
    }

    public function show($id)
    {
        $task = Task::findOrFail($id);

        if ($task->user_id !== Auth::id()) {
            abort(404);
        }

        $task = Task::where('id', $id)
            ->where('user_id', Auth::id())
            ->with(['subtasks'])
            ->first();

        return response($task, 200);
    }

    public function update(TaskStoreRequest $request, $id)
    {
        try {
            if ($request->has('when')) {
                $this->due_date = TaskController::due_date($request->when);
            }

            $task = Task::findOrFail($id);
            $task->period = null;
            $datetime_format = 'Y-m-d H:i:s';
            $timezone = '0';

            if ($task->user_id !== Auth::id()) {
                abort(403);
            }

            if ($this->due_date) {
                $task->due_date = $this->due_date;
            } else {
                $task->due_date = null;
                $task->schedule = false;
            }

            if ($request->has('title')) {
                $task->title = $request->title;
            }

            if ($request->has('note')) {
                $task->note = $request->note;
            }

            if ($request->has('parent_id')) {
                $task->parent_id = $request->parent_id;
            }

            if ($request->has('tag_id')) {
                $task->tag_id = $request->tag_id;
            }

            if ($request->has('period')) {
                $task->period = $request->period;
                if (
                    $request->period == 'inbox' or
                    $request->period == 'unset'
                ) {
                    $task->period = null;
                }
            }

            $task->schedule = $this->schedule;

            $task->timezone = Auth::user()->timezone;

            $task->save();

            return response($task, 200);
        } catch (\Throwable $th) {
            throw new \Exception('Ops! ocorreu um erro!');
        }
    }

    public function destroy($id)
    {
        try {
            $task = Task::findOrFail($id);

            if ($task->user_id !== Auth::id()) {
                abort(403);
            }

            $task->delete();

            return response($task, 200);
        } catch (\Throwable $th) {
            throw new \Exception('Ops! ocorreu um erro!');
        }
    }

    public function completed($id)
    {
        $task = Task::findOrFail($id);
        $now = Carbon::now(Auth::user()->timezone);

        $date = Carbon::createFromFormat('Y-m-d H:i:s', $now);

        if ($task->user_id !== Auth::id()) {
            return response()->json(
                [
                    'errors' => [
                        'user_id' => 'você nao tem permissão para esta ação.',
                    ],
                ],
                403
            );
        }

        $task->completed_at = $date;
        $task->schedule = true;
        $task->save();

        return response($task, 200);
    }

    public function deleteCompleted($id)
    {
        try {
            $task = Task::findOrFail($id);

            if ($task->user_id !== Auth::id()) {
                return response()->json(
                    [
                        'errors' => [
                            'user_id' =>
                                'você nao tem permissão para esta ação.',
                        ],
                    ],
                    400
                );
            }

            $task->completed_at = null;
            $task->save();

            return response($task, 200);
        } catch (\Throwable $th) {
            throw new \Exception('Ops! ocorreu um erro!');
        }
    }

    public function batch_update(BatchUpdateRequest $request)
    {
        $items = [];

        $items = $request->input();

        if (count($items)) {
            DB::beginTransaction();
            try {
                foreach ($items as $item) {
                    $task = Task::findOrFail($item['id']);
                    $task->period = null;
                    if ($task->user_id !== Auth::id()) {
                        abort(403);
                    }

                    /**
                     * @var when = [null, inbox, today, tomorrow, this_week, next_week, next_month]
                     */

                    $this->due_date = TaskController::due_date($item['when']);

                    if ($this->due_date or $item['when'] == 'inbox') {
                        $task->due_date = $this->due_date;
                    }

                    if (isset($item['title'])) {
                        $task->title = $item['title'];
                    }

                    if (isset($item['period'])) {
                        if (
                            $item['period'] == 'inbox' or
                            $item['period'] == 'unset'
                        ) {
                            $task->period = null;
                        } else {
                            $task->period = $item['period'];
                        }
                    }

                    $task->schedule = $this->schedule;

                    $task->timezone = Auth::user()->timezone;

                    DB::update(
                        'update tasks set due_date = ? , period = ?, schedule = ?, title = ? where id = ?',
                        [
                            $task->due_date,
                            $task->period,
                            $task->schedule,
                            $task->title,
                            $item['id'],
                        ]
                    );
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback(); //if rollback due to error occurs in query 3 then no data will be saved in table 1 and 2...Not Mandatory

                return response()->json(
                    [
                        'errors' => [
                            'id' => 'Não foi possivel concluir as alterações.',
                        ],
                    ],
                    400
                );
            }
        }
    }
}
