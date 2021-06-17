<?php

namespace App;

use App\Model\UserConfig;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, SoftDeletes;
    use \App\Http\Traits\UsesUuid;

    protected $stripe;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'phone',
        'timezone',
        'premium_expire_at',
        'grace_period_start_at',
        'grace_period_days',
        'paid',
        'subscription_id',
        'subscription_platform',
        'profession',
        'interest',
        'age',
        'terms_of_use',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'stripe_id',
        'stripe_subscription_end',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $dates = ['created_at', 'updated_at'];

    protected $guarded = [];

    protected $attributes = [
        'stripe_id' => null,
        'stripe_subscription_end' => null,
        'premium_expire_at' => null,
        'paid' => false,
        'subscription_id' => null,
        'subscription_platform' => null,
    ];

    protected $appends = ['tutorial_config', 'notification_config', 'premium'];

    public function socials()
    {
        return $this->hasMany('App\Model\UserSocial', 'user_id', 'id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower(trim($value));
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    public function getTutorialConfigAttribute()
    {
        $config_tutorial = UserConfig::with(['config'])
            ->user()
            ->config('tutorial_finished')
            ->count();

        if (!$config_tutorial) {
            return false;
        }

        return $config_tutorial;
    }

    public function getNotificationConfigAttribute()
    {
        $config_notification = UserConfig::with(['config'])
            ->user()
            ->config('active_notifications')
            ->first();

        if (!$config_notification) {
            return false;
        }

        return (bool) $config_notification->value;
    }

    public function getPremiumAttribute()
    {
        $now = now();

        //payment status
        if ($this->paid == true) {
            return true;
        }

        if (!$this->grace_period_start_at) {
            return true;
        }

        //grace expire status
        $grace_period_expire = Carbon::parse($this->grace_period_start_at)
            ->addDays($this->grace_period_days);

        if ($now->isBefore($grace_period_expire)) {
            return true;
        }

        return false;
    }
}
