<?php

namespace App\Services;

use App\Model\Habit;
use App\Classes\Schedule\DateHandling;
use App\Classes\Domain\Habits\PersistRecurringTask;

class RecurringTasksService extends PersistRecurringTask
{
    private $limitDateTask;
    private $currentDate;

    public function __construct(
        Habit $habit,
        DateHandling $dateHandling
    )
    {
        parent::__construct($habit, $dateHandling);
        $this->currentDate  = $this->dateHandling->currentDate();
    }

    public function lauchTask($habit, $dateTaskModel = null)
    {   
        $this->limitDateTask = $this->setLimitDateRecurrence($habit['final_date']);
        while ($this->currentDate < $this->limitDateTask) {
            if (in_array($this->currentDate->dayOfWeek, $habit['repeat']))
            {
                if($dateTaskModel && $this->currentDate->diffInDays($dateTaskModel) == 0 )
                {
                    $this->currentDate = $this->currentDate->add('1 day');
                    continue;
                }
                $this->prepareRecurringTasks($habit, $this->currentDate);
            }
            $this->currentDate = $this->currentDate->add('1 day');
        }
        $this->currentDate = $this->dateHandling->firstDayOfNextMonth();
    }
}
