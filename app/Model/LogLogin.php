<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class LogLogin extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $table = 'accesslog';

    public $timestamps = true;

    protected $fillable = ['user_id', 'timezone'];

}
