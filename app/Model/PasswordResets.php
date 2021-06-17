<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PasswordResets extends Model
{
    use \App\Http\Traits\UsesUuid;

    protected $table = 'password_resets';

    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'code', 'attempts'];

    public $timestamps = true;

    protected $attributes = [
        'attempts' => 0,
    ];

    protected $guarded = [];
}
