<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HabitSetta extends Model
{
    use SoftDeletes;
    use \App\Http\Traits\UsesUuid;

    protected $table = 'habits_setta';

    protected $primaryKey = 'id';

    protected $fillable = ['tag_id', 'title', 'note', 'image'];

    public $timestamps = true;

    protected $attributes = [
        'note' => null,
        'image' => null,
    ];
}
