<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class CreatePermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to generate permissions for use throughout the system';

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
        $permissions = [
            'uk',
            'china',
            'pricing',
            'pricing_approve',
            'products'
        ];

        foreach($permissions as $perm) {
            $permission = Permission::findOrCreate($perm);
        }

        return;
    }
}
