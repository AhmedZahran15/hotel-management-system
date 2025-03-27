<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClientsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Client::select('id', 'name', 'country', 'gender', 'user_id', 'approved_by', 'created_at', 'updated_at')
            ->get();
    }
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Country',
            'Gender',
            'User ID',
            'Approved By',
            'Created At',
            'Updated At',
        ];
    }
    public function map($client): array
    {
        return [
            $client->id,
            $client->name,
            $client->country,
            $client->gender,
            $client->user_id,
            $client->approved_by,
            $client->created_at->format('Y-m-d H:i:s'),
            $client->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
