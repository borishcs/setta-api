<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimerAdd extends Model
{
    use SoftDeletes;

    protected $table = 'timer_add';

    protected $primaryKey = 'id';

    protected $fillable = ['timer_id', 'add', 'type'];

    public $timestamps = true;

    protected $attributes = [
        'add' => '00:05:00',
    ];

    public function timer()
    {
        return $this->belongsTo('App\Model\Timer');
    }
}
