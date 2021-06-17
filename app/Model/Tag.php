<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use SoftDeletes;
    use \App\Http\Traits\UsesUuid;

    protected $table = 'tags';

    protected $primaryKey = 'id';

    protected $fillable = ['title'];

    public $timestamps = true;
}
