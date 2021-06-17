<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Habit\HabitStoreRequest;
use App\Http\Requests\Habit\HabitUpdateRequest;
use App\Model\Habit;
use App\Model\HabitSetta;
use App\Model\Task;
use App\Services\RecurringTasksService;
use App\Classes\Schedule\DateHandling;
use Carbon\Carbon;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HabitController extends Controller
{
    private $statusCode;
    private $dateHandling;
    private $currentDate;
    private $recurringTasksService;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $habits = Habit::where('user_id', Auth::id())
            ->orderBy('id', 'asc')
            ->paginate(20);

        return response($habits, 200);
    }

    /**
     * Manages route requests.
     * @param HabitStoreRequest $request
     * @return void or a Exception
     */
    public function store(HabitStoreRequest $request)
    {
        $this->instanceDateHandling();
        try {
            if ($request->has('task_id') and $request->has('habit_setta_id')) {
                return response([
                    'message' =>
                        'Houve um problema na requisição e não podemos processar a informação, tente novamente.',
                    'errors' => [
                        'duplicated_keys' =>
                            'Somente uma dos parametros pode ser enviado, task_id ou habit_setta_id',
                    ],
                ]);
            }

            if ($request->has('final_date')) {
                if (!$this->validateFieldFinalDate($request->final_date)) {
                    return response()->json(
                        [
                            'errors' => [
                                'final_date' =>
                                    'Informe uma data de Limite válida',
                            ],
                        ],
                        404
                    );
                }
            }

            if ($request->has('task_id')) {
                return $this->storeFromTask($request);
            } elseif ($request->has('habit_setta_id')) {
                return $this->storeFromHabitSetta($request);
            } elseif (
                !$request->has('task_id') and !$request->has('habit_setta_id')
            ) {
                return $this->storeFromBuilder($request);
            }
            return response()->json(
                [
                    'message' =>
                        'Ops! Houve um problema com a requisição, tente novamnete',
                ],
                500
            );
        } catch (\Throwable $th) {
            throw new \Exception('Ops! ocorreu um erro!');
        }
    }

    /**
     * Manages the inclusion request through a task.
     * @param Request $request
     * @return Habit or a Exception
     */
    protected function storeFromTask($request)
    {
        try {
            $this->validateRequest($request);
            $this->validTaskForHabit($request->task_id);

            if ($this->statusCode == 404) {
                return response()->json(
                    ['message' => 'Ops! Task não encontrada'],
                    404
                );
            }

            if ($this->statusCode == 409) {
                return response()->json(
                    ['message' => 'Ops! Habito Já Cadastrado'],
                    409
                );
            }

            $tsx = $this->persistHabit($request);
            if (!$tsx) {
                return response()->json(
                    [
                        'message' =>
                            'Ops! Houve um problema com a requisição, tente novamnete',
                    ],
                    500
                );
            }

            $this->relateTask($request->task_id, $tsx->id);
            $this->instanceRecurringTasksService();

            $task = $this->verifyTask($request->task_id);
            $timestamp = $task['due_date'];
            $timezoneTask = $task['timezone'];
            $dateTask = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $timestamp,
                $timezoneTask
            );
            $this->recurringTasksService->lauchTask($tsx, $dateTask);

            return response($tsx, 201);
        } catch (\Throwable $th) {
            throw new \Exception('Ops! ocorreu um erro!');
        }
    }

    /**
     * Manages the inclusion request through a habit setta.
     * @param Request $request
     * @return Habit or a Exception
     */
    protected function storeFromHabitSetta($request)
    {
        try {
            $this->validateRequest($request);

            $this->validHabitSettaForHabit($request->habit_setta_id);

            if ($this->statusCode == 404) {
                return response()->json(
                    ['message' => 'Ops! Hábito não encontrado'],
                    404
                );
            }

            if ($this->statusCode == 409) {
                return response()->json(
                    ['message' => 'Ops! Habito Já Cadastrado'],
                    409
                );
            }

            $tsx = $this->persistHabit($request);
            if (!$tsx) {
                return response()->json(
                    [
                        'message' =>
                            'Ops! Houve um problema com a requisição, tente novamnete',
                    ],
                    500
                );
            }
            $this->instanceRecurringTasksService();
            $this->recurringTasksService->lauchTask($tsx);

            return response($tsx, 201);
        } catch (\Throwable $th) {
            throw new \Exception('Ops! ocorreu um erro!');
        }
    }

    /**
     * Manages the inclusion request through a form.
     * @param Request $request
     * @return Habit or a Exception
     */
    protected function storeFromBuilder($request)
    {
        try {
            $this->validateRequest($request);

            $tsx = $this->persistHabit($request);
            if (!$tsx) {
                return response()->json(
                    [
                        'message' =>
                            'Ops! Houve um problema com a requisição, tente novamnete',
                    ],
                    500
                );
            }

            $this->instanceRecurringTasksService();
            $this->recurringTasksService->lauchTask($tsx);

            return response($tsx, 201);
        } catch (\Throwable $th) {
            throw new \Exception('Ops! ocorreu um erro!');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param HabitUpdateRequest $request
     * @param int $id
     * @return Habit
     */
    public function update(HabitUpdateRequest $request, $id)
    {
        $this->instanceDateHandling();
        $retroactive = false;
        if ($request->has('retroactive')) {
            $retroactive = $request->retroactive;
            if ($request->retroactive == null) {
                $retroactive = false;
            }
        }
        try {
            $habit = $this->verifyUpdateDeleteHabit($id);

            if (!$habit) {
                return response()->json(
                    [
                        'message' =>
                            'Hábito não encontrado ou Nao pertence ao Usuario',
                    ],
                    404
                );
            }

            if ($request->has('final_date')) {
                if (!$this->validateFieldFinalDate($request->final_date)) {
                    return response()->json(
                        ['message' => 'Informe uma data de Limite válida'],
                        404
                    );
                }
            }

            $bodyRequest = $request->all();

            array_walk($bodyRequest, function (&$attribute, &$key) use (
                &$habit
            ) {
                if ($key != 'retroactive') {
                    $habit->$key = $attribute;
                }
            });

            $habit->save();
            if (!$retroactive) {
                $tasks = $this->updateTasks($habit, $request->due_date);
                if ($tasks) {
                    return response($habit, 200);
                }
                return response()->json(
                    ['message' => 'Ops!! Ocorreu um erro tente novamente.'],
                    500
                );
            }

            $this->detachTasks($habit->id, $this->currentDate, false);
            $this->attachTasks($habit);

            return response($habit, 200);
        } catch (\Throwable $th) {
            throw new \Exception($th);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show($id)
    {
        try {
            $habit = Habit::where('id', $id)
                ->where('user_id', Auth::id())
                ->get();

            if ($habit->isEmpty()) {
                return response()->json(
                    [
                        'message' =>
                            'Hábito não encontrado ou Nao pertence ao Usuario',
                    ],
                    404
                );
            }

            return response($habit, 200);
        } catch (\Throwable $th) {
            throw new \Exception('Ops! ocorreu um erro!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     */
    public function destroy(HabitUpdateRequest $request, $id)
    {
        $this->instanceDateHandling();
        $retroactive = false;
        if ($request->has('retroactive')) {
            $retroactive = $request->retroactive;
            if ($request->retroactive == null) {
                $retroactive = false;
            }
        }

        $habit = $this->verifyUpdateDeleteHabit($id);
        if (!$habit) {
            return response()->json(
                [
                    'message' =>
                        'Hábito não encontrado ou Nao pertence ao Usuario',
                ],
                401
            );
        }

        if ($request->has('task_id')) {
            $task = $this->verifyTask($request->task_id);

            if (!$task) {
                return response()->json(
                    ['message' => 'Ops! Tarefa informada não encontrada'],
                    404
                );
            }
            $tasks = $this->detachTasks(
                $habit->id,
                $task->due_date,
                $retroactive
            );
            if ($tasks) {
                $this->setFinalHabit($habit->id, $task->due_date);

                return response()->json(
                    ['message' => 'Hábito inativado com sucesso'],
                    200
                );
            }
            return response()->json(
                ['message' => 'Ops! ocorreu um erro!'],
                500
            );
        }

        $tasks = $this->detachTasks(
            $habit->id,
            $this->currentDate,
            $retroactive
        );

        if ($tasks) {
            $isForceDelete = $this->getCompletedAtTask($habit->id) == false;
            $isInactive = $this->getCompletedAtTask($habit->id);

            if ($isForceDelete) {
                $habit->forceDelete();
            }

            if (!$isForceDelete) {
                $this->setFinalHabit($habit->id, $isInactive);
            }

            return response()->json(
                ['message' => 'Hábito inativado com sucesso'],
                200
            );
        }
        return response()->json(['message' => 'Ops! ocorreu um erro!'], 500);
    }

    /**
     * Inactivate habit without complete task
     * @param int $id
     * @return date completed_at
     */
    private function getCompletedAtTask($id)
    {
        try {
            $completedAt = Task::where('habit_id', $id)
                ->where('completed_at', '!=', null)
                ->orderBy('completed_at', 'desc')
                ->first();
            if (!$completedAt) {
                return false;
            }
            return $completedAt['completed_at'];
        } catch (\Throwable $th) {
            throw new \Exception('Ops! ocorreu um erro!');
        }
    }

    /**
     * Set Inactivate habit with task completed
     * @param int $habit_id
     * @param int $date
     * @return Habit
     */
    private function setFinalHabit($habit_id, $date)
    {
        try {
            return Habit::where('id', $habit_id)->update([
                'final_date' => $date,
            ]);
        } catch (\Throwable $th) {
            throw new \Exception('Ops! ocorreu um erro!');
        }
    }

    /**
     * Instance DateHamndling.
     * @return DateHandling and currentDate
     */
    private function instanceDateHandling()
    {
        $this->dateHandling = new DateHandling(Auth::user()->timezone);
        $this->currentDate = $this->dateHandling->currentDate();
    }

    /**
     * Instance RecurringTasksService.
     * @return RecurringTasksService $recurringTasksService
     */
    private function instanceRecurringTasksService()
    {
        $this->recurringTasksService = new RecurringTasksService(
            new Habit(),
            new DateHandling(Auth::user()->timezone)
        );
    }

    /**
     * Validates mandatory fields.
     * @param Request $request.
     * @return true or Exception
     */
    private function validateRequest($request)
    {
        return $this->validate($request, [
            'period' => 'required|string',
            'title' => 'required|string',
            'repeat' => 'required|array',
        ]);
    }

    /**
     * Detach tasks linked to habit.
     * @param int $habit_id
     * @return null or Exception
     */
    private function detachTasks($habit_id, $date, $retroactive = false)
    {
        try {
            $tsx = DB::transaction(function () use (
                $habit_id,
                $date,
                $retroactive
            ) {
                if (!$retroactive) {
                    return Task::where('habit_id', $habit_id)
                        ->where('completed_at', null)
                        ->where('due_date', '>=', $date)
                        ->forceDelete();
                }
                return Task::where('habit_id', $habit_id)
                    ->where('completed_at', null)
                    ->forceDelete();
            },
            2);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * Update tasks linked to habit.
     * @param int $habit_id
     * @return null or Exception
     */
    private function updateTasks($habit, $date)
    {
        try {
            Task::where('habit_id', $habit->id)
                ->where('completed_at', null)
                ->where('due_date', '>', $date)
                ->update([
                    'tag_id' => $habit->tag_id,
                    'period' => $habit->period,
                    'title' => $habit->title,
                    'note' => $habit->note,
                ]);
            $this->detachTasks($habit->id, $habit->final_date);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * Attach tasks linked to habit.
     * @param int $habit_id
     * @return null or Exception
     */
    private function attachTasks($habit)
    {
        try {
            $this->instanceRecurringTasksService();
            return $this->recurringTasksService->lauchTask($habit);
        } catch (\Throwable $th) {
            throw new \Exception('Ops! ocorreu um erro!');
        }
    }

    /**
     * Checks if a task exists.
     * @param Integer $id.
     * @return Task
     */
    private function verifyTask($id)
    {
        return Task::find($id);
    }

    /**
     * Checks if a habit exists.
     * @param Integer $id.
     * @return Habit
     */
    private function verifyHabitSetta($id)
    {
        return HabitSetta::find($id);
    }

    /**
     * Check if the habit belongs to the logged in user.
     * @param Integer $habit_id.
     * @return Habit
     */
    private function verifyHabit($habit_id)
    {
        return Habit::where('user_id', Auth::id())
            ->where('habit_setta_id', $habit_id)
            ->first();
    }

    /**
     * Check if the habit belongs to the logged in user.
     * @param Integer $habit_id.
     * @return Habit
     */
    private function verifyUpdateDeleteHabit($id)
    {
        return Habit::where('user_id', Auth::id())
            ->where('id', $id)
            ->first();
    }

    /**
     * Validate field final_date.
     * @param Date $finalDate.
     * @return Boolean
     */
    private function validateFieldFinalDate($finalDate)
    {
        return $this->currentDate < $finalDate ? true : false;
    }

    /**
     * Validates whether the habit can be created from a task.
     * @param Integer $task_id.
     * @return true or statusCode of Error
     */
    private function validTaskForHabit($task_id)
    {
        $taskValid = $this->verifyTask($task_id);
        if (!$taskValid) {
            return $this->statusCode = 404;
        }

        if ($taskValid->habit_id) {
            return $this->statusCode = 409;
        }
        return true;
    }

    /**
     * Validates whether the habit can be created from a habit setta.
     * @param Integer $habit_id.
     * @return true or statusCode of Error
     */
    private function validHabitSettaForHabit($habit_id)
    {
        $habitValid = $this->verifyHabitSetta($habit_id);
        if (!$habitValid) {
            return $this->statusCode = 404;
        }

        $habitValid = $this->verifyHabit($habit_id);
        if ($habitValid) {
            return $this->statusCode = 409;
        }
        return true;
    }

    /**
     * Set the relationship between a task and the habit.
     * @param Integer $task_id.
     * @param Integer $thabit_id.
     * @return void
     */
    private function relateTask($task_id, $habit_id)
    {
        $task = $this->verifyTask($task_id);
        $task->habit_id = $habit_id;
        $task->save();
    }

    /**
     * Set attributes for inserting of Task.
     * @param Request $request.
     * @return Habit or a Exception
     */
    private function persistHabit($request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $habit = new Habit();
                $habit->habit_setta_id = $request->has('habit_setta_id')
                    ? $request->habit_setta_id
                    : null;
                $habit->tag_id = $request->tag_id;
                $habit->period = $request->period;
                $habit->title = $request->title;
                $habit->note = $request->note;
                $habit->repeat = $request->repeat;
                $habit->final_date = $request->final_date;
                $habit->last_completed = null;
                $habit->streak = 0;
                $habit->max_streak = 0;
                $habit->save();
                return $habit;
            }, 2);
        } catch (\Throwable $th) {
            throw new \Exception('Ops! ocorreu um erro!');
        }
    }
}
