<?php

namespace App\Models;

use App\Models\Base\Import as BaseImport;
use Illuminate\Support\Facades\Storage;
use Spatie\ModelStatus\HasStatuses;

class Import extends BaseImport
{

    use HasStatuses;

    const STATUS_PENDING = 'pending';

	protected $fillable = [
		'aborted_at',
		'completed_at',
		'creator_id',
		'skip_header_rows',
		'path',
		'type',
		'total_rows',
		'total_errors',
		'current_line',
		'mapping',
        'config',
		'log_path'
	];

    /**
     * @return \SplFileObject
     */
    public function getSplFileObject()
    {
        $path = Storage::disk('imports')->path($this->path);
        return new \SplFileObject($path, 'r');
    }


}
