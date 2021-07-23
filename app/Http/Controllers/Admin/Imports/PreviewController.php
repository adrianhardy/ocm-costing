<?php

namespace App\Http\Controllers\Admin\Imports;

use App\Domain\Excel\ParseProductSheet;
use App\Domain\Excel\PrepareProductsForImport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PreviewController extends Controller
{

    /**
     * Parse through the XLSX file, storing the first N rows' data
     * along with ALL the images in the sheet. Because this is the
     * last time we're parsing the XLSX, we're going to store
     * everything we need in the cache, keyed by '$code'.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate($request, [
            'xlsx' => 'required|file|max:50000|mimes:xlsx,xls',
            'code' => 'required'
        ]);

        $parse = new ParseProductSheet();
        $parse->read($request->file('xlsx')->getFileInfo());
        $csv = new \SplTempFileObject();
        foreach ($parse->rows as $i => $row) {
            $csv->fputcsv($row->pluck('value')->toArray());
            if ($i > 200) {
                break;
            }
        }

        $bytes = $csv->ftell();
        $csv->rewind();
        Cache::put('import-preview-' . $request->query('code'), [
            'cells' => $parse->rows,
            'images' => $parse->images->filter(),
        ], Carbon::now()->addHour());
        return response($csv->fread($bytes), 200, [
            'x-quote-reference' => $parse->quoteNumber
        ]);
    }

    /**
     * Grab the uploaded spreadsheet out of the cache and re-map the columns
     * for the preview. Also, we're going to sift through all images and pluck
     * out the ones we think are matches ...
     *
     * @param Request $request
     * @param $import
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $preview)
    {
        $cache = Cache::get('import-preview-' . $preview);
        $images = collect($cache['images']);
        $cells = collect($cache['cells']);
        $prep = new PrepareProductsForImport($cells, $images);
        $mapped = $prep->prepare($request->input('mapping', []), $request->input('skip_rows'));
        return response()->json([
            'items' => $mapped
        ]);
    }
}
