<?php


namespace App\Models\Repositories;


use App\Models\InvoiceLine;
use Prettus\Repository\Eloquent\BaseRepository;

class InvoiceLineRepository extends BaseRepository
{

    public function model()
    {
        return InvoiceLine::class;
    }
}