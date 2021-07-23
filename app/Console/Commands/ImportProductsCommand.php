<?php

namespace App\Console\Commands;

use App\Domain\Excel\ParseProductSheet;
use App\Domain\Excel\PrepareProductsForImport;
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
        $products = $prep->prepare($import->mapping, $import->skip_header_rows);

        $this->log->warning("Found {$products->count()} potential products in sheet");

        // Process the data in two phases
        DB::beginTransaction();

        $quote = Quote::query()->where('reference', $import->config['quote_reference'])->first();
        if (!$quote) {
            $quote = new Quote([
                'reference' => Str::random(16)
            ]);
            if ($import->config['quote_reference']) {
                $quote->reference = $import->config['quote_reference'];
            }
            $quote->save();
        }

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
                'article_number',
                'item_number'
            ])->toArray());
            $product->save();
            if ($data->get('image')) {
                $product->clearMediaCollection('quote');
                $product->addMediaFromBase64(
                    $data->get('image')
                )
                ->usingFileName('product-' . $product->id . '.png')
                ->toMediaCollection('quote');
            }

            if ($data->get('case_volume_cu_f') && !$data->get('case_volume_cu_m')) {
                $data['case_volume_cu_m'] = 0.0283168 * $data->get('case_volume_cu_f');
            }

            $line = new QuoteLine([
                'barcode' => $data->get('barcode'),
                'packing_instructions' => $data->get('packing_instructions'),
                'case_qty' => $data->get('case_qty'),
                'case_inner_qty' => $data->get('case_inner_qty'),
                'total_unit_qty' => $data->get('total_unit_qty'),
                'unit_price' => $data->get('unit_price'),
                'case_volume_cu_m' => $data->get('case_volume_cu_m'),
                'case_height_m' => $data->get('case_height_m'),
                'case_width_m' => $data->get('case_width_m'),
                'case_depth_m' => $data->get('case_depth_m'),
            ]);
            $line->product()->associate($product);
            $quote->lines()->save($line);
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
            'packing_instructions'  => $data->get('packing_instructions'),
            'barcode'               => $data->get('barcode')
        ],[
            'item_number'           => 'required|min:2|max:200',
            'article_number'        => 'required|min:2|max:200',
            'name'                  => 'required|min:2|max:200',
            'packing_instructions'  => 'sometimes|min:0|max:512',
            'unit_price'            => 'required|numeric|min:0',
            'barcode'               => 'sometimes|min:0|max:32'
        ]);

        return $validator;
    }

}
