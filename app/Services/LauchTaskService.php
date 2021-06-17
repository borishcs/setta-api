<?php

namespace App\Services;

use App\User;
use App\Model\Habit;
use App\Classes\Schedule\DateHandling;
use App\Classes\Domain\Habits\MonthPersistRecurringTask;

use Carbon\Carbon;

class LauchTaskService
{
    private $limitDateTask;
    private $firstDayNextMonth;
    private $currentDate;
    private $habit;
    private $dateHandling;
    private $monthRecurring;
    private $timezone;

    public function __construct()
    {
        ini_set('max_execution_time', 10000);
        ini_set('set_time_limit', -1);
    }

    private function instanceMonthPersistRecurringTask($timezone)
    {
        $this->dateHandling = new DateHandling($timezone);
        $this->firstDayNextMonth = $this->dateHandling->firstDayOfNextMonth();
        $this->currentDate = $this->dateHandling->firstDayOfNextMonth();
        $this->monthRecurring = new MonthPersistRecurringTask(
            $this->habit,
            $this->dateHandling
        );
    }

    /**
     * Search all Habits ativos.
     * @return Array [Habits] or Array[]
     */
    private function searchHabits()
    {
        return $this->habit
            ->whereBetween('habits.created_at', [
                '2020-12-11 00:00:00',
                '2020-12-20 23:59:59',
            ])
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();
        //->Where("habit_id", "50005c42-e603-41df-95a6-1e432016a8fe")
        // ->toArray();
    }

    private function searchTimezone($user_id)
    {
        return User::where('id', $user_id)->first();
    }

    public function receiveHabits()
    {
        $this->habit = new Habit();

        $habits = $this->searchHabits();

        //dd($habits);

        if (!empty($habits)) {
            array_walk($habits, function (&$habit, &$key) {
                $this->timezone = $this->searchTimezone($habit['user_id']);
                $this->instanceMonthPersistRecurringTask(
                    $this->timezone['timezone']
                );
                $this->monthRecurring->detachTasks(
                    $habit['id'],
                    $this->firstDayNextMonth
                );
                $this->lauchTask($habit);
            });
        }
    }

    public function lauchTask($habit)
    {
        if (
            $this->firstDayNextMonth < $habit['final_date'] ||
            $habit['final_date'] == null
        ) {
            $this->limitDateTask = $this->monthRecurring->setLimitDateRecurrence(
                $habit['final_date']
            );
            while ($this->currentDate < $this->limitDateTask) {
                if (in_array($this->currentDate->dayOfWeek, $habit['repeat'])) {
                    $this->monthRecurring->prepareRecurringTasks(
                        $habit,
                        $this->currentDate,
                        $this->timezone['timezone']
                    );
                }
                $this->currentDate = $this->currentDate->add('1 day');
            }
        }
        $this->currentDate = $this->dateHandling->firstDayOfNextMonth();
    }
}
