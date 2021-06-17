<?php

use Illuminate\Support\Facades\Route;

Route::get('/cron', 'PushNotificationController@handleExecuteCron');

// Service
Route::prefix('services')->group(function () {
    Route::get('monthly', 'ServiceController@monthly');
});

// Status Route
Route::get('/', 'StatusController@status');

// Auth
Route::post('login', 'AuthController@login');
Route::post('register', 'AuthController@register');
Route::post('verify-token', 'AuthController@verify_token');

//Status Change
Route::post('payments/status-change', 'PaymentController@status_change');

// Reset Password
Route::post('password/forgot', 'PasswordResetController@forgot');
Route::post('password/reset', 'PasswordResetController@reset');
Route::post('password/reset/{code}', 'PasswordResetController@verify');

// Home
Route::get('home/categories', 'HomeController@categories');
Route::apiResource('home', 'HomeController');

// Webhook Stripe
Route::post('paywall/integrate', 'PaywallController@integrate')->middleware(
    'logs'
);

//Check Blacklist
Route::get('check-blacklist/{id}', 'BlackListController@check_blacklist');

//TOKENPUSH
Route::post('notification-token', 'TokenPushController@store');

//WEBHOOK
Route::post('webhook-google', 'WebhookController@storeGoogle');
Route::post('webhook-apple', 'WebhookController@storeApple');
Route::post('webhook-manual', 'WebhookController@storeManual');

Route::middleware(['auth.jwt'])->group(function () {
    // Auth
    Route::get('logout', 'AuthController@logout');

    //TOKENPUSH
    Route::post('notification-token/attach', 'TokenPushController@attach');
    Route::post('notification-token/dettach', 'TokenPushController@dettach');

    // Tags
    Route::get('tags', 'TagController@index');

    // Profile User
    Route::prefix('profile')->group(function () {
        Route::get('', 'UserController@index');
        Route::put('', 'UserController@update')->middleware('logs');
    });

    Route::middleware(['paywall'])->group(function () {
        Route::post(
            'notification-token/attempts',
            'TokenPushController@attempts'
        );

        //habit setta
        Route::get('habits-setta', 'HabitSettaController@index');

        // Moments
        Route::prefix('moment')->group(function () {
            Route::get('', 'MomentController@index');
            Route::get('morning', 'MomentController@morning');
            Route::get('firstDayMonth', 'MomentController@firstDayMonth');
            Route::get('thisWeek', 'MomentController@thisWeek');
            Route::get('todayLost', 'MomentController@todayLost');
            Route::get('taskLost', 'MomentController@taskLost');
        });

        // Tasks
        Route::prefix('tasks')->group(function () {
            Route::get('', 'TaskController@index');
            Route::post('', 'TaskController@store')->middleware('paywall');
            Route::get('{id}', 'TaskController@show')->middleware('paywall');
            Route::put('{id}', 'TaskController@update')->middleware('logs');
            Route::delete('{id}', 'TaskController@destroy');
            Route::post('{id}/completed', 'TaskController@completed');
            Route::delete('{id}/completed', 'TaskController@deleteCompleted');
            Route::get('{id}/statistic', 'TaskController@statistic');

            Route::post('batch_update', 'TaskController@batch_update');
        });

        // Habit
        Route::prefix('habits')->group(function () {
            Route::get('', 'HabitController@index');
            Route::post('', 'HabitController@store');
            Route::get('{id}', 'HabitController@show');
            Route::put('{id}', 'HabitController@update')->middleware('logs');
            Route::delete('{id}', 'HabitController@destroy');
        });

        // Planner
        Route::prefix('planner')->group(function () {
            Route::get('', 'PlannerController@index'); // all tabs
            Route::get('today', 'PlannerController@today');
            Route::get('tomorrow', 'PlannerController@tomorrow');
            Route::get('this_week', 'PlannerController@this_week');
            Route::get('next_week', 'PlannerController@next_week');
            Route::get('this_month', 'PlannerController@this_month');
            Route::get('next_month', 'PlannerController@next_month');
            Route::get('inbox', 'PlannerController@inbox');
            Route::get('completed', 'PlannerController@completed');
            Route::post('order', 'PlannerController@order');
        });

        // Notification
        Route::apiResource('notification', 'NotificationController');

        // Notification Inbox
        Route::apiResource('notification_inbox', 'NotificationInboxController');

        // Paywall
        Route::prefix('paywall')->group(function () {
            Route::get('status', 'PaywallController@status');
            Route::get('products', 'PaywallController@products');
            Route::get('plans', 'PaywallController@plans');
            Route::get('coupons', 'PaywallController@coupons');
            Route::get('coupons/{code}', 'PaywallController@coupon_verify');
            Route::get('customer', 'PaywallController@customer');
            Route::post('subscribe', 'PaywallController@subscribe')->middleware(
                'logs'
            );
        });
    });
});
