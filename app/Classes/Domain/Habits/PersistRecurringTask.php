<?php

namespace App\Classes\Domain\Habits;

use App\Model\Habit;
use App\Model\Task;
use Illuminate\Support\Facades\Auth;
use App\Classes\Schedule\DateHandling;
use Illuminate\Support\Facades\DB;

abstract class PersistRecurringTask
{
    public function __construct(Habit $habit, DateHandling $dateHandling)
    {
        $this->habit        = $habit;
        $this->dateHandling = $dateHandling;
    }

    /**
     * Detach tasks linked to habit.
     * @param int $habit_id
     * @return boolean 
    */
    protected function detachTasks($habit_id, $date, $retroactive = false)
    {
        try {
            $tsx = DB::transaction(function () use ($habit_id, $date, $retroactive) {
                if(!$retroactive)
                {
                    return Task::where('habit_id', $habit_id)
                        ->where('completed_at', null)
                        ->where('due_date', '>=', $date)
                        ->forceDelete();
                }
                return Task::where('habit_id', $habit_id)
                    ->where('completed_at', null)
                    ->forceDelete();
            }, 2);
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * Search all Habits ativos.
     * @return Array [Habits] or Array[]
    */
    protected function searchHabits()
    {
        return $this->habit->all()->toArray();
    }
    
    /**
     * Set attributes for inserting a recurring teak.
     * @param Habit $habit.
     * @param Date $due_date.
     * @return Task or Exception
    */
    protected function prepareRecurringTasks($habit, $due_date)
    {
        $attributes["title"]    = $habit['title'];
        $attributes["tag_id"]   = $habit['tag_id'];
        $attributes["period"]   = $habit['period'];
        $attributes["habit_id"] = $habit['id'];
        $attributes["due_date"] = $due_date;
        $attributes["note"]     = $habit['note'];
        $attributes["timezone"] = Auth::user()->timezone;
        return $this->persistTask($attributes);
    }

    /**
     * Set attributes for inserting a recurring teak.
     * @param Array $attributes.
     * @return Task or a Exception
    */
    protected function persistTask($attributes)
    {
        try {
            $tsx = DB::transaction(function () use ($attributes) {
                $task = new Task();
                $task->title    = $attributes['title'];
                $task->habit_id = $attributes['habit_id'];
                $task->tag_id   = $attributes['tag_id'];
                $task->period   = $attributes['period'];
                $task->note     = $attributes['note'];
                $task->due_date = $attributes['due_date'];
                $task->timezone = $attributes["timezone"];
                $task->schedule = true;

                $task->save();

                return $task;
            }, 2);

            if ($tsx) {
                return true;
            }

            return false;
        } catch (\Exception $err) {
            return false;
        }
    }

    /**
     * Set limit date for recurrence.
     * @param Date $finalDate.
     * @return Date
    */
    protected function setLimitDateRecurrence($finalDate)
    {
        if($finalDate == null)
            return $this->dateHandling->lastDayOfNextMonth();

        if ($this->compareDates (
            $this->dateHandling->lastDayOfNextMonth(),
            $finalDate
        )) 
        {
            return $this->dateHandling->lastDayOfNextMonth();
        }
        return $finalDate;
    }

    /**
     * Compare two dates.
     * @param Date $date1.
     * @param Date $date2.
     * @return Boolean
    */
    protected function compareDates($date1, $date2)
    {        
        return ($date1 < $date2)
        ? true
        : false;
    }
}