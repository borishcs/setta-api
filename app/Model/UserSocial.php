<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserSocial extends Model
{
    use \App\Http\Traits\UsesUuid;
    protected $table = 'user_social';

    public $timestamps = true;

    protected $fillable = ['user_id', 'type'];

    protected $attributes = [
        'type' => 'setta',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
