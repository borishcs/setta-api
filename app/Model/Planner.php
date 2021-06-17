<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Planner extends Model
{
    use SoftDeletes;

    protected $table = 'planner';

    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'task_id', 'habit_id', 'order'];

    public $timestamps = true;

    protected $attributes = [
        'task_id' => null,
        'habit_id' => null,
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (!$this->user_id) {
            $this->user_id = Auth::id();
        }
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
}
