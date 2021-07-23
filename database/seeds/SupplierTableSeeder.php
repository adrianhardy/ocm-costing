<?php

use Illuminate\Database\Seeder;

class SupplierTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $suppliers = [
            'Aurora', 'Banaghans', 'Davies', 'Henbrandt', 'HTI', 'Kandy',
            'Keel', 'Toyland', 'OOTB', 'PMS', 'Peterkin', 'Premier', 'RMS', 'RSW', 'SES',
            'Singleton', 'Sunstar', 'Tomy'
        ];
        foreach ($suppliers as $name) {
            $supplier = new \App\Models\Supplier([
                'name' => $name
            ]);
            $supplier->save();
        }
    }
}
