<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class UserConfig extends Model
{
    use SoftDeletes;
    use \App\Http\Traits\UsesUuid;

    protected $table = 'user_config';

    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'config_id', 'value'];

    protected $attributes = [
        'value' => null,
    ];

    public $timestamps = true;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (!$this->user_id) {
            $this->user_id = Auth::id();
        }
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function config()
    {
        return $this->hasOne('App\Model\Config', 'id', 'config_id');
    }

    /**
     * scopes
     */

    public function scopeUser(Builder $query)
    {
        return $query->where('user_id', Auth::id());
    }

    public function scopeConfig(Builder $query, $config_title)
    {
        return $query
            ->select('user_config.*')
            ->join('config', 'user_config.config_id', 'config.id')
            ->where('config.title', $config_title)
            ->where('config.deleted_at', null);
    }
}
