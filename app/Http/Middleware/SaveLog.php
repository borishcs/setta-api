<?php

namespace App\Http\Middleware;

use App\Model\Log;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaveLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        DB::connection()->enableQueryLog();

        return $next($request);
    }

    public function terminate()
    {
        $queries = DB::getQueryLog();

        // after
        foreach ($queries as $sql) {
            if (explode(" ", $sql['query'])[0] != 'select') {
                $log = new Log();
                $log->type = explode(" ", $sql['query'])[0];
                $log->query =
                    $sql['query'] .
                    ' [ ' .
                    implode(' , ', $sql['bindings']) .
                    ' ]';
                $log->time = $sql['time'];
                $log->user_id = Auth::id();
                $log->save();
            }
        }
    }
}
