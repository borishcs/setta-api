<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Timer extends Model
{
    use SoftDeletes;

    protected $table = 'timer';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'task_id',
        'habit_id',
        'tag_id',
        'estimated_time',
        'estimated_used_time',
        'rest_time',
        'rest_used_time',
        'started_at',
        'finished_at',
    ];

    public $timestamps = true;

    protected $attributes = [
        'task_id' => null,
        'habit_id' => null,
        'tag_id' => null,
        'started_at' => null,
        'finished_at' => null,
    ];

    protected $appends = ['from', 'total_used_focus', 'total_used_rest'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (!$this->user_id) {
            $this->user_id = Auth::id();
        }
    }

    /**
     * total: estimated_used_time + each add with type 1
     */
    public function getTotalUsedFocusAttribute()
    {
        $estimated_time_in_seconds =
            strtotime($this->estimated_used_time) - strtotime('TODAY');

        $total_adds_in_seconds = 0;
        if (count($this->adds)) {
            foreach ($this->adds as $add) {
                if ($add->type === 1) {
                    // only type 1
                    $total_adds_in_seconds +=
                        strtotime($add->add) - strtotime('TODAY');
                }
            }
        }

        $total_in_seconds = $estimated_time_in_seconds + $total_adds_in_seconds;
        $total_formatted = gmdate("H:i:s", $total_in_seconds);

        return $total_formatted;
    }

    /**
     * total: rest_used_time + each add with type 2
     */
    public function getTotalUsedRestAttribute()
    {
        $rest_time_in_seconds =
            strtotime($this->rest_used_time) - strtotime('TODAY');

        $total_rest_adds_in_seconds = 0;
        if (count($this->adds)) {
            foreach ($this->adds as $add) {
                if ($add->type === 2) {
                    // only type 2
                    $total_rest_adds_in_seconds +=
                        strtotime($add->add) - strtotime('TODAY');
                }
            }
        }

        $total_in_seconds = $rest_time_in_seconds + $total_rest_adds_in_seconds;
        $rest_formatted = gmdate("H:i:s", $total_in_seconds);

        return $rest_formatted;
    }

    public function getFromAttribute()
    {
        if ($this->task_id) {
            return 'task';
        }

        if ($this->habit_id) {
            return 'habit';
        }

        if ($this->tag_id) {
            return 'tag';
        }

        return null;
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function task()
    {
        return $this->belongsTo('App\Model\Task');
    }

    public function habit()
    {
        return $this->belongsTo('App\Model\Habit');
    }

    public function tag()
    {
        return $this->belongsTo('App\Model\Tag');
    }

    public function adds()
    {
        return $this->hasMany('App\Model\TimerAdd');
    }

    /**
     * scopes
     */

    public function scopeUser(Builder $query)
    {
        return $query->where('user_id', Auth::id());
    }
}
