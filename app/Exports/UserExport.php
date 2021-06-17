<?php

namespace App\Exports;

use App\User;
use Illuminate\Support\Carbon;

use Maatwebsite\Excel\Concerns\{WithHeadings, FromCollection, Exportable};
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UserExport implements FromCollection, WithHeadings
{
    use Exportable;

    private $headings = [
        'ID User',
        'Nome',
        'Email',
        'Data',
        'Telefone',
        'Profissão',
        'Interesse',
        'Faixa Etária',
    ];

    public function collection()
    {
        $users = DB::table('users')
            ->select(
                'id',
                'name',
                'email',
                'created_at',
                'phone',
                'profession',
                'interest',
                'age'
            )
            ->whereBetween('created_at', [
                Carbon::now()
                    ->subday()
                    ->startOfDay(),
                Carbon::now()->startOfDay(),
            ])
            ->get();

        return $users;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
