<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BlackListController extends Controller
{
    public function check_blacklist($version)
    {
        try {
            $blacklist = [
                '0.2.0',
                '0.2.1',
                '0.2.2',
                '0.2.3',
                '0.2.4',
                '0.2.5',
                '0.2.6',
                '0.3.0',
                '0.3.1',
                '0.3.2',
                '0.3.3',
                '0.3.4',
                '0.3.5',
                '0.3.6',
                '0.4.0',
                '0.4.1',
                '0.4.2',
                '0.4.3',
                '0.4.4',
                '0.4.5',
                '0.4.6',
                '0.4.7',
                '0.4.16',
                '0.4.17',
                '0.4.18',
                '0.4.19',
                '0.4.20',
                '0.4.21',
            ];

            if (in_array($version, $blacklist)) {
                return true;
            }

            return false;
        } catch (\Throwable $th) {
            throw new Exception('Ops! ocorreu um erro!');
        }
    }
}
