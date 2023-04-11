<?php


namespace App\Models\Repositories;


use App\Models\Invoice;
use Prettus\Repository\Eloquent\BaseRepository;

class InvoiceRepository extends BaseRepository
{

    public function model()
    {
        return Invoice::class;
    }
}