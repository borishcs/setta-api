<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Paywall extends Model
{
    protected $table = 'paywall';

    public $timestamps = true;

    protected $fillable = ['id', 'token', 'hour'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
