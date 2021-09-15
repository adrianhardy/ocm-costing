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

        foreach ($mapped as $i => $row) {
            if ($row->get('article_number') && $row->get('unit_price')) {
                $nextRow = $mapped[$i+1];
                $candidates = $this->images->filter(function($image) use ($row, $nextRow) {
                    return $image['coords']['row'] >= $row['_source']['row']
                        && $image['coords']['row'] < $nextRow['_source']['row'];
                });

                if ($candidates->count() > 0) {
                    $row['image'] = base64_encode($candidates->first()['blob']);
                }
            }
        }

        return $mapped->filter(function($row) {
            return $row->get('article_number', false) && $row->get('unit_price', false);
        })->values();
    }
}
