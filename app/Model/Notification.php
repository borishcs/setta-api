<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use SoftDeletes;

    protected $table = 'notification';

    public $timestamps = true;

    protected $fillable = ['title', 'subtitle', 'name'];

    protected $guarded = [];
}
