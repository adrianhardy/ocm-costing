<?php

namespace App\Console\Commands;

use App\Models\Document;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class ExpungeOldFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:expunge {--pretend}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $saved = 0;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->cleanUpImports();

        $this->comment("TOTAL SAVED :  {$this->saved}");

    }

    protected function cleanUpImports()
    {
        $query = \App\Models\Import
            ::query()
            ->where(
                'created_at',
                '<',
                Carbon::now()->subMonths(24)->endOfDay()
            );

        /** @var \App\Models\Import[] $imports */
        $query->chunk(500, function($imports) {
            foreach ($imports as $import) {
                if (Storage::disk('imports')->exists($import->path)) {
                    $this->saved += Storage::disk('imports')->size($import->path);
                    $this->saved += Storage::disk('imports')->size($import->log_path);
                    $this->info("Deleting {$import->path}...");
                    $this->info("Deleting {$import->log_path}...");
                    Storage::disk('imports')->delete($import->path);
                    Storage::disk('imports')->delete($import->log_path);
                }
            }
        });
    }
}
