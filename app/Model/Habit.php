<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Habit extends Model
{
    use SoftDeletes;
    use \App\Http\Traits\UsesUuid;

    protected $table = 'habits';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'habit_setta_id',
        'tag_id',
        'period',
        'title',
        'note',
        'repeat',
        'final_date',
        'last_completed',
        'streak',
        'max_streak',
    ];

    public $timestamps = true;

    protected $attributes = [
        'habit_setta_id' => null,
        'note' => null,
        'final_date' => null,
        'last_completed' => null,
        'streak' => null,
        'max_streak' => null,
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (!$this->user_id) {
            $this->user_id = Auth::id();
        }
    }

    protected $casts = [
        'repeat' => 'array',
    ];

    public function getTypeAttribute()
    {
        return 'habit';
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function tag()
    {
        return $this->belongsTo('App\Model\Tag', 'tag_id', 'id');
    }

    public function habitSetta()
    {
        return $this->belongsTo('App\Model\HabitSetta', 'habit_setta_id', 'id');
    }
}
