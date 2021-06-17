<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $table = 'logs';

    public $timestamps = true;

    protected $fillable = ['type', 'query', 'time', 'user_id'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
