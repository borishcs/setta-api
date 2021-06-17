<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Model\Habit;

class Task extends Model
{
    use SoftDeletes;
    use \App\Http\Traits\UsesUuid;

    protected $table = 'tasks';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'parent_id',
        'period',
        'tag_id',
        'habit_id',
        'title',
        'note',
        'due_date',
        'schedule',
        'order',
        'completed_at',
        'timezone',
    ];

    public $timestamps = true;

    protected $attributes = [
        'tag_id' => null,
        'period' => null,
        'parent_id' => null,
        'note' => null,
        'order' => null,
        'completed_at' => null,
        'due_date' => null,
        'schedule' => false,
    ];

    protected $appends = ['type', 'when', 'repeat', 'final_date'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (!$this->user_id) {
            $this->user_id = Auth::id();
        }
    }

    public function getTypeAttribute()
    {
        if ($this->parent_id) {
            return 'subtask';
        }

        return 'task';
    }

    public function getWhenAttribute()
    {
        if ($this->due_date) {
            $weekStartAt = Carbon::SUNDAY; // domingo

            $due_date = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $this->due_date,
                Auth::user()->timezone
            );
            $due_date = Carbon::parse($due_date)->format('Y-m-d');

            $today = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                Carbon::today(Auth::user()->timezone),
                Auth::user()->timezone
            );
            $today = Carbon::parse($today)->format('Y-m-d');

            $tomorrow = Carbon::createFromFormat(
                'Y-m-d H:i:s',
                Carbon::tomorrow(Auth::user()->timezone),
                Auth::user()->timezone
            );
            $tomorrow = Carbon::parse($tomorrow)->format('Y-m-d');

            $firstDayOfWeek = Carbon::today(
                Auth::user()->timezone
            )->startOfWeek($weekStartAt);
            $firstDayOfWeek = Carbon::parse($firstDayOfWeek)->format('Y-m-d');

            $nextSunday = Carbon::today(Auth::user()->timezone)
                ->startOfWeek($weekStartAt)
                ->addWeeks(1)
                ->format('Y-m-d');
            $nextSunday = Carbon::parse($nextSunday)->format('Y-m-d');

            $firstDayOfNextMonth = Carbon::today(Auth::user()->timezone)
                ->addMonth()
                ->firstOfMonth()
                ->format('Y-m-d');
            $firstDayOfNextMonth = Carbon::parse($firstDayOfNextMonth)->format(
                'Y-m-d'
            );

            if ($due_date == $today) {
                return 'today';
            }

            if ($due_date == $tomorrow) {
                return 'tomorrow';
            }

            if ($due_date == $firstDayOfWeek) {
                return 'this_week';
            }

            if ($due_date == $nextSunday) {
                return 'next_week';
            }

            if ($due_date == $firstDayOfNextMonth) {
                return 'next_month';
            }
        }
        return null;
    }

    public function getRepeatAttribute()
    {
        if ($this->habit_id) {
            $habit = Habit::where('id', $this->habit_id)->first();
            return $habit->repeat;
        }

        return null;
    }

    public function getFinaldateAttribute()
    {
        if ($this->habit_id) {
            $habit = Habit::where('id', $this->habit_id)->first();
            return $habit->final_date;
        }

        return null;
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function parent()
    {
        return $this->belongsTo('App\Model\Task');
    }

    public function tag()
    {
        return $this->belongsTo('App\Model\Tag', 'tag_id', 'id');
    }

    public function subtasks()
    {
        return $this->hasMany('App\Model\Task', 'parent_id', 'id');
    }

    public function completed(Builder $query)
    {
        return $query->where('completed_at', null);
    }
}
