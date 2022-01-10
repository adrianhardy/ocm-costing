<?php


namespace App\Domain\Excel;


use Illuminate\Support\Collection;

class PrepareProductsForImport
{
    /**
     * @var Collection
     */
    private $rows;
    /**
     * @var Collection
     */
    private $images;

    /**
     * @param Collection $rows
     * @param Collection $images
     */
    public function __construct(Collection $rows, Collection $images)
    {
        $this->rows = $rows;
        $this->images = $images;
    }

    /**
     * @param $mapping
     * @param int $skipRows
     * @return Collection
     */
    public function prepare($mapping, $skipRows = 1)
    {
        $mapped = collect();
        $mapping = Collection::wrap($mapping);
        foreach ($this->rows as $i => $row) {
            if ($skipRows > 0 && $i < $skipRows) {
                continue;
            }
            $mappedRow = collect();
            foreach ($mapping as $column => $name) {

                if (!$name) {
                    // skip empty mapping columns, like "[ None ]"
                    continue;
                }

                if (isset($row[$column])) {
                    $mappedRow[$name] = $row[$column]['value'];
                    $mappedRow['_source'] = $row[$column]['coords'];
                }
            }
            $mapped->push($mappedRow);
        }

        $mapped = $mapped->map(function($sourceRow, $i) use ($mapped) {
            $row = $sourceRow;
            if ($sourceRow->get('article_number') && $sourceRow->get('unit_price')) {
                // scan down for more images
                $nextRow = $mapped->get($i+1);

                if (trim($sourceRow->get('product_description')) && $nextRow && $nextRow->get('product_description')) {
                    $row['product_description'] .= "\n";
                }

                if (trim($sourceRow->get('packing_instructions')) && $nextRow && $nextRow->get('packing_instructions')) {
                    $row['packing_instructions'] .= "\n";
                }

                // continuation of descriptions?
                for ($j = 1; $j < 10; $j++) {
                    $nextRow = $mapped->get($i+$j);
                    if ($nextRow) {
                        if ($nextRow->get('article_number') && $nextRow->get('unit_price')) {
                            break;
                        }
                        if (isset($nextRow['product_description'])) {
                            $row['product_description'] .= trim($nextRow['product_description']) . "\n";
                        }
                        if (isset($nextRow['packing_instructions'])) {
                            $row['packing_instructions'] .= trim($nextRow['packing_instructions']) . "\n";
                        }
                    }
                }
            }

            if ($row->get('product_description')) {
                $row['product_description'] = trim($row['product_description']);
            }

            if ($row->get('packing_instructions')) {
                $row['packing_instructions'] = trim($row['packing_instructions']);
            }
            return $row;
        });

        $productsOnly = $mapped->filter(function($row) {
            return $row->get('article_number', false) && $row->get('unit_price', false);
        })->values();


        // after deciding on all the products, we run through them once more to
        // find out where they start and end. We use this to determine which images
        // are eligible to sit against the current product... if there's no next row
        // then we tack all remaining images against the last product.
        $productsOnly = $productsOnly->map(function($row, $i) use ($productsOnly) {

            $nextRow = isset($productsOnly[$i + 1])?$productsOnly[$i + 1]:null;

            $candidates = $this->images->filter(function($image) use ($row, $nextRow) {

                if (!$nextRow) {
                    return $image['coords']['row'] >= $row['_source']['row'];
                }

                return $image['coords']['row'] >= $row['_source']['row']
                    && $image['coords']['row'] < $nextRow['_source']['row'];
            });

            // previously, this just used to map a single image:
            // $row['image'] = $candidates->first() ...
            if ($candidates->count() > 0) {
                $row['images'] = $candidates->map(function($image) {
                    return base64_encode($image['blob']);
                });
            }

            return $row;
        });

        return $productsOnly;
    }
}
