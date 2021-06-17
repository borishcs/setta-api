<?php
namespace App\Classes\Schedule;

use Carbon\Carbon;

class DateHandling
{
    private $carbon;
    private $timezone;

    public function __construct($timezone)
    {
        $this->timezone = $timezone;
        $this->carbon = Carbon::today($this->timezone);
    }

    /**
     * Returns the current data
     * @return date
     */
    public function currentDate()
    {
        return Carbon::today($this->timezone);
    }

    /**
     * Returns the firts day of the next month.
     * @return date
     */
    public function firstDayOfNextMonth()
    {
        return Carbon::today($this->timezone);
        //->addMonth()
        //->firstOfMonth();
    }

    /**
     * Returns the last day of the month.
     * @return date
     */
    public function lastOfMonth()
    {
        return Carbon::today($this->timezone)->lastOfMonth();
    }

    /**
     * Returns the last day of the next month.
     * @return date
     */
    public function lastDayOfNextMonth()
    {
        return Carbon::today($this->timezone)->addDays(7);
        // ->lastOfMonth();
    }

    /**
     * Returns the current day of the week.
     * @return date
     */
    public function dayOfWeek()
    {
        return $this->carbon->dayOfWeek;
    }

    /**
     * Set the day of the week.
     * @param  int  $dayWeekend
     * @return string
     */
    public function setDayWeekend($dayWeekend)
    {
        switch ($dayWeekend) {
            case 0:
                return 'SUNDAY';
                break;
            case 1:
                return 'MONDAY';
                break;
            case 2:
                return 'TUESDAY';
                break;
            case 3:
                return 'WEDNESDAY';
                break;
            case 4:
                return 'THURSDAY';
                break;
            case 5:
                return 'FRIDAY';
                break;
            default:
                return 'SATURDAY';
                break;
        }
    }

    /**
     * Returns the day of the week as a number 0 for Sunday and 6 Saturday.
     * @param  int  $id
     * @return date
     */
    public function setDateWeekend($dayWeekend)
    {
        return $this->carbon->next($this->setDayWeekend($dayWeekend));
    }
}
