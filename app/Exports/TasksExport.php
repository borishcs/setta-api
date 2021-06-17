<?php

namespace App\Exports;

use App\User;
use Illuminate\Support\Carbon;

use Maatwebsite\Excel\Concerns\{WithHeadings, FromCollection, Exportable};
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TasksExport implements FromCollection, WithHeadings
{
    use Exportable;

    private $headings = [
        'ID Task',
        'ID User',
        'ID Tag',
        'Titulo',
        'Data',
        'Periodo',
        'Recorrente',
        'Data de Criação User',
    ];

    public function collection()
    {
        $tasks = DB::table('tasks')
            ->join('users', 'users.id', '=', 'tasks.user_id')
            ->select(
                'tasks.id',
                'tasks.user_id',
                'tasks.tag_id',
                'tasks.title',
                'tasks.due_date',
                'tasks.period',
                'tasks.habit_id',
                'users.created_at'
            )
            ->whereBetween('tasks.created_at', [
                Carbon::now()
                    ->subday()
                    ->startOfDay(),
                Carbon::now()->startOfDay(),
            ])
            ->get();

        return $tasks;
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
