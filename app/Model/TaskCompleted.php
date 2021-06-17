<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class TaskCompleted extends Model
{
    use SoftDeletes;
    use \App\Http\Traits\UsesUuid;

    protected $table = 'tasks_completed';

    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'task_id', 'completed_at', 'descount'];

    public $timestamps = true;

    protected $attributes = [
        'completed_at' => null,
        'descount' => null,
    ];

    protected $appends = ['type'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (!$this->user_id) {
            $this->user_id = Auth::id();
        }
    }

    public function getTypeAttribute()
    {
        return 'task';
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function task()
    {
        return $this->belongsTo('App\Model\Task');
    }
}
