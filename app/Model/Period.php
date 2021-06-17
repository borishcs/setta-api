<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Period extends Model
{
    use SoftDeletes;

    protected $table = 'periods';

    protected $primaryKey = 'id';

    protected $fillable = [
        'title', 'icon', 'start', 'end'
    ];

    public $timestamps = true;
}
