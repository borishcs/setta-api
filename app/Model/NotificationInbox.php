<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\Notification;

class NotificationInbox extends Model
{
    use SoftDeletes;

    protected $table = 'notification_inbox';

    public $timestamps = true;

    protected $fillable = [
        'notification_id',
        'title',
        'subtitle',
        'user_id',
        'status',
        'visible',
    ];

    protected $guarded = [];
}
