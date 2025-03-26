<?php

namespace App\Http\Controllers;

use App\Exports\ClientsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ClientExportController extends Controller
{
    public function export(Request $request)
    {
        
        return Excel::download(new ClientsExport, 'clients.xlsx');
    }
}
