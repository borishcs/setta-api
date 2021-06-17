<?php

namespace App\Exports;

use App\User;
use Illuminate\Support\Carbon;

use Maatwebsite\Excel\Concerns\{WithHeadings, FromCollection, Exportable};
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SubscribersExport implements FromCollection, WithHeadings
{
    use Exportable;

    private $headings = [
        'ID User',
        'Nome',
        'Email',
        'Telefone',
        'Plano',
        'Plataforma',
    ];

    public function collection()
    {
        $users = DB::table('users')
            ->select(
                'id',
                'name',
                'email',
                'phone',
                'payment_plan',
                'subscription_platform'
            )
            ->where('paid', true)
            ->get();

        return $users;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
