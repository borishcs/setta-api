<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Config extends Model
{
    use SoftDeletes;
    use \App\Http\Traits\UsesUuid;

    protected $table = 'config';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = ['module', 'title', 'default', 'description'];

    protected $attributes = [
        'default' => null,
        'description' => null,
    ];

    /**
     * scopes
     */

    public function scopeTitle(Builder $query, $config_title)
    {
        $find = $query
            ->where('title', $config_title)
            ->where('deleted_at', null)
            ->first();

        if (!$find) {
            return '';
        }

        return $find;
    }
}
