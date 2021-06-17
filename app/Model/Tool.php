<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Tool extends Model
{
    protected $table = 'tool';

    public $timestamps = true;

    protected $primaryKey = 'id';

    protected $fillable = ['title', 'icon', 'description'];
}
