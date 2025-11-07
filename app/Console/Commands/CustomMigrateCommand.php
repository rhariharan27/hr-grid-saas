<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CustomMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'opencorehr:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate the OpenCore HR database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
      $this->call('migrate', [
        '--path' => 'database/migrations',
      ]);
    }
}
