<?php


namespace App\Console\Commands\Traits;


use App\Models\Import;
use Illuminate\Support\Facades\Storage;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

trait LogsImportsTrait
{
    /**
     * @var Logger
     */
    protected $log;

    protected function setupLog(Import $import)
    {
        $path = Storage::disk('imports')->path($import->log_path);
        $stream = new StreamHandler(fopen($path, 'a'));
        $stream->setFormatter(new JsonFormatter());
        $log = new Logger('import', [$stream]);
        if ($this->output->isVeryVerbose()) {
            $log->pushHandler(new StreamHandler(fopen('php://stdout', 'w')));
        }
        $this->log = $log;
    }
}
