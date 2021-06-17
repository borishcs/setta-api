<?php

use Illuminate\Support\Facades\Route;

Route::get('/cron', 'Api\PushNotificationController@handleExecuteCron');

// Status Route
Route::get('/', 'Api\StatusController@status');

// Auth
Route::post('login', 'Api\AuthController@login');
Route::post('register', 'Api\AuthController@register');
Route::post('verify-token', 'Api\AuthController@verify_token');

// Reset Password
Route::post('password/forgot', 'Api\PasswordResetController@forgot');
Route::post('password/reset', 'Api\PasswordResetController@reset');
Route::post('password/reset/{code}', 'Api\PasswordResetController@verify');

// Home
Route::get('home/categories', 'Api\HomeController@categories');
Route::apiResource('home', 'Api\HomeController');

// Webhook Stripe
Route::post('paywall/integrate', 'Api\PaywallController@integrate')->middleware(
    'logs'
);

//Check Blacklist
Route::get('check-blacklist/{id}', 'Api\BlackListController@check_blacklist');

//TOKENPUSH
Route::post('notification-token', 'Api\TokenPushController@store');

Route::middleware(['auth.jwt'])->group(function () {
    // Auth
    Route::get('profile', 'Api\AuthController@profile');
    Route::get('logout', 'Api\AuthController@logout');

    //TOKENPUSH
    Route::post('notification-token/attach', 'Api\TokenPushController@attach');
    Route::post(
        'notification-token/dettach',
        'Api\TokenPushController@dettach'
    );
    Route::post(
        'notification-token/attempts',
        'Api\TokenPushController@attempts'
    );

    // Tags
    Route::get('tags', 'Api\TagController@index');

    // Periods
    Route::get('periods', 'Api\PeriodController@index');

    // Tool
    Route::get('tool', 'Api\ToolController@index');

    //habit setta
    Route::get('habit_setta', 'Api\HabitSettaController@index');

    // Moments
    Route::prefix('moment')->group(function () {
        Route::get('morning', 'Api\MomentController@morning');
        Route::get('firstDayMonth', 'Api\MomentController@firstDayMonth');
        Route::get('thisWeek', 'Api\MomentController@thisWeek');
        Route::get('todayLost', 'Api\MomentController@todayLost');
        Route::get('taskLost', 'Api\MomentController@taskLost');
    });

    // statisct
    /*
    Route::prefix('statistic')->group(function () {
        Route::get('', 'Api\StatisticController@index');
        Route::get('tag', 'Api\StatisticController@statistic_tag');
        Route::get('days_use_app', 'Api\StatisticController@days_use_app');
        Route::get('total_task_app', 'Api\StatisticController@total_task_app');
        Route::get(
            'total_days_app_cache',
            'Api\StatisticController@total_days_app_cache'
        );
    });
    */

    // User Config
    /*
    Route::prefix('user/config')->group(function () {
        // Tutorial
        Route::get('tutorial', 'Api\TutorialController@index');
        Route::post('tutorial', 'Api\TutorialController@finished');

        // Notificação
        Route::post(
            'notification/enable',
            'Api\NotificationController@enable_notification'
        );
        Route::post(
            'notification/disable',
            'Api\NotificationController@disable_notification'
        );
    });
    */

    // Config
    //Route::apiResource('config', 'Api\ConfigController');

    // User Social
    //Route::apiResource('user_social', 'Api\UserSocialController');

    // Tasks
    Route::prefix('tasks')->group(function () {
        Route::get('', 'Api\TaskController@index');
        Route::post('', 'Api\TaskController@store')->middleware('paywall');
        Route::get('{id}', 'Api\TaskController@show');
        Route::put('{id}', 'Api\TaskController@update')->middleware(
            'paywall',
            'logs'
        );
        Route::delete('{id}', 'Api\TaskController@destroy');
        Route::post('{id}/completed', 'Api\TaskController@completed');
        Route::delete('{id}/completed', 'Api\TaskController@deleteCompleted');
        Route::get('{id}/statistic', 'Api\TaskController@statistic');
    });

    // Habit
    Route::prefix('habit')->group(function () {
        Route::get('', 'Api\HabitController@index');
        Route::post('', 'Api\HabitController@store')->middleware('paywall');
        Route::get('{id}', 'Api\HabitController@show');
        Route::put('{id}', 'Api\HabitController@update')->middleware(
            'paywall',
            'logs'
        );
        Route::delete('{id}', 'Api\HabitController@destroy');
        Route::post('{id}/completed', 'Api\HabitController@completed');
        Route::post('{id}/exception', 'Api\HabitController@exception');
        Route::delete('{id}/completed', 'Api\HabitController@deleteCompleted');
        Route::post('storeSubhabit', 'Api\HabitController@storeSubhabit');
        Route::get('{id}/statistic', 'Api\HabitController@statistic');
    });

    // Planner
    Route::prefix('planner')->group(function () {
        Route::get('', 'Api\PlannerController@index'); // all tabs
        Route::get('today', 'Api\PlannerController@today');
        Route::get('tomorrow', 'Api\PlannerController@tomorrow');
        Route::get('this_week', 'Api\PlannerController@this_week');
        Route::get('next_week', 'Api\PlannerController@next_week');
        Route::get('this_month', 'Api\PlannerController@this_month');
        Route::get('next_month', 'Api\PlannerController@next_month');
        Route::get('inbox', 'Api\PlannerController@inbox');
        Route::get('completed', 'Api\PlannerController@completed');
        Route::post('order', 'Api\PlannerController@order');
    });

    // Notification
    Route::apiResource('notification', 'Api\NotificationController');

    // Notification Inbox
    Route::apiResource('notification_inbox', 'Api\NotificationInboxController');

    // Timer
    /*
    Route::prefix('timer')
        ->middleware('paywall')
        ->group(function () {
            Route::get('', 'Api\TimerController@index');
            Route::post('', 'Api\TimerController@timer');
            Route::delete('{timer_id}', 'Api\TimerController@destroy');
            Route::post('{timer_id}/add', 'Api\TimerController@add');
            Route::delete(
                '{timer_id}/add/{add_id}',
                'Api\TimerController@destroy_add'
            );
        });
    */

    // Paywall
    Route::prefix('paywall')->group(function () {
        Route::get('status', 'Api\PaywallController@status');
        Route::get('products', 'Api\PaywallController@products');
        Route::get('plans', 'Api\PaywallController@plans');
        Route::get('coupons', 'Api\PaywallController@coupons');
        Route::get('coupons/{code}', 'Api\PaywallController@coupon_verify');
        Route::get('customer', 'Api\PaywallController@customer');
        Route::post('subscribe', 'Api\PaywallController@subscribe')->middleware(
            'logs'
        );
    });
});
