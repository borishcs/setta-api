<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TokenPush extends Model
{
    use SoftDeletes;
    use \App\Http\Traits\UsesUuid;

    protected $table = 'token_push';

    protected $primaryKey = 'id';

    protected $fillable = ['token_push', 'device_id', 'attempts', 'user_id'];

    protected $attributes = [
        'attempts' => null,
        'user_id' => null,
        'attempts' => 0,
    ];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function scopeTokenPush(Builder $query)
    {
        return $query->where('user_id', Auth::id());
    }
}
