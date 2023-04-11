<?php

namespace App\Http\Controllers\Uk\Invoices;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoicesController extends Controller
{
    public function index()
    {
        return view('uk.invoices.index');
    }

    public function edit(Invoice $invoice)
    {
        return view('uk.invoices.edit', compact('invoice'));
    }
}
