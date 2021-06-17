<?php

namespace App\Http\Controllers;

use App;

use App\Mail\sendEmail;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    /**
     * @param $email = destination email
     * @param $name = username
     * @param string $title = title of email
     * @param array $data = extra infos
     * @param $template = blade template
     * @return bool
     */
    public static function send(
        $email,
        $cc,
        $name,
        $title = 'Setta',
        $attach,
        $data = [],
        $template
    ) {
        try {
            $dataEmail = [];
            $dataEmail = [
                'address' => $email,
                'to' => $name,
                'cc'=> $cc,
                'subject' => $title,
                'attach' => $attach,
                'template' => $template,
                'data' => $data,
            ];

            Mail::send(new sendEmail($dataEmail));
            return true;
        } catch (\Throwable $th) {
            throw new Exception('Ops! erro ao enviar o email de registro!');
        }
    }
}
