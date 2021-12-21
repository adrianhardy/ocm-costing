<?php

namespace App\Console\Commands;

use App\Domain\Excel\ParseProductSheet;
use App\Domain\Excel\PrepareProductsForImport;
use App\Models\Factory;
use App\Models\Import;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteLine;
use App\Models\Supplier;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Spatie\MediaLibrary\Models\Media;

class ImportProductsCommand extends Command
{
    use Traits\LogsImportsTrait;
    use Traits\CalculatesTotalImportRowsTrait;
    use Traits\MapsImportRowsTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:products {import} {--skip-images}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Products';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        set_time_limit(0);

        /** @var Import $import */
        $import = Import::query()->findOrFail($this->argument('import'));
        $supplier = Supplier::query()->findOrFail($import->config['supplier_id']);
        $factory = Factory::query()->findOrFail($import->config['factory_id']);
        $this->setupLog($import);

        $this->log->info("STARTING IMPORT ...");

        if (!Storage::disk('imports')->exists($import->path)) {
            // SET STATUS ERROR
            return;
        }
        $this->calculateTotalRows($import);

        $parse = new ParseProductSheet();
        $parse->read($import->getSplFileObject());

        $prep = new PrepareProductsForImport($parse->rows, $parse->images);
        $products = $prep->prepare(
            $import->mapping,
            $import->skip_header_rows
        );

        $this->log->warning("Found {$products->count()} potential products in sheet");

        // Process the data in two phases
        DB::beginTransaction();

        foreach ($products as $data) {
            $validator = $this->createProductValidator($data);
            $import->current_line = $data['_source']['row'];

            if ($validator->fails()) {
                $messages = $validator->getMessageBag()->first();
                $this->log->warning("Product failed validation on line $import->current_line : $messages");
                $import->total_errors++;
                $import->save();
                continue;
            }

            /** @var Product $product */
            $product = Product::query()->where('article_number', $data['article_number'])->first();
            if (!$product) {
                $product = new Product();
                $product->supplier()->associate($supplier);
            }
            $product->fill($data->only([
                'name',
                'product_description',
                'item_number',
                'article_number',
                'barcode',
                'packing_instructions',
                'case_inner_qty',
                'unit_price',
                'case_weight_kg',
                'unit_weight_kg',
                'unit_height_m',
                'unit_width_m'
            ])->toArray());

            $product->fill([
                'units_per_case' => $data->get('case_qty'),
                'height_m'      => $data->get('case_height_m'),
                'width_m'       => $data->get('case_width_m'),
                'length_m'      => $data->get('case_depth_m'),
                'unit_length_m' => $data->get('unit_depth_m')
            ]);

            // workaround not nullable constraint
            if (!$product->item_number) {
                $product->item_number = '';
            }

            $product->save();
            if (!$product->factories->pluck('id')->contains($factory->id)) {
                $product->factories()->save($factory);
            }

            if ($data->get('images') && $data->get('images')->isNotEmpty()) {
                $product->clearMediaCollection('default');
                if (count($data->get('images')) > 1) {
                    xdebug_break();
                }
                foreach ($data->get('images') as $i => $blob) {
                    $product->addMediaFromBase64($blob)
                        ->usingFileName("product-{$product->id}-{$i}.png")
                        ->toMediaCollection('default');
                }
            }

            if ($data->get('case_volume_cu_f') && !$data->get('case_volume_cu_m')) {
                $data['case_volume_cu_m'] = $data->get('case_volume_cu_f') / settings('cubic_metres_to_feet');
            }

            $import->save();
        }

        DB::commit();

    }

    /**
     * @param $product
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function createProductValidator($data)
    {
        $data = Collection::wrap($data);
        $validator = Validator::make([
            'item_number'           => $data->get('item_number'),
            'article_number'        => $data->get('article_number'),
            'name'                  => $data->get('name'),
            'unit_price'            => $data->get('unit_price'),
            'product_details'       => $data->get('product_details'),
            'packing_instructions'  => $data->get('packing_instructions'),
            'barcode'               => $data->get('barcode')
        ],[
            'item_number'           => 'min:0|max:200',
            'article_number'        => 'required|min:2|max:200',
            'name'                  => 'required|min:2|max:200',
            'product_details'       => 'sometimes|min:0|max:512',
            'packing_instructions'  => 'sometimes|min:0|max:512',
            'unit_price'            => 'required|numeric|min:0',
            'barcode'               => 'sometimes|min:0|max:32'
        ]);

        return $validator;
    }

}
