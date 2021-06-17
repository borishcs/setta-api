<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class sendEmail extends Mailable
{
    use Queueable, SerializesModels;

    private $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        try {
            $this->subject($this->data['subject']);
            $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $this->to($this->data['address'], $this->data['to']);

            if ($this->data['cc']) {
                $this->cc($this->data['cc']);
            }

            if ($this->data['attach']) {
                $this->attach(storage_path($this->data['attach']));
            }

            return $this->view($this->data['template'], [
                'data' => $this->data['data'],
            ]);

            return true;
        } catch (\Throwable $th) {
            throw new \Exception($th);
        }
    }
}
