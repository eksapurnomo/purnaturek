<?php

namespace App\Console\Commands;

use Database\Seeders\PurNatureKCatalogSeeder;
use Illuminate\Console\Command;

class SeedPurNatureKCatalogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purnaturek:seed-catalog';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed initial two-product catalog (Kratom Powder & Kratom Extract) with approved landing page assets and Model B wholesale pricing';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Seeding PurNatureK initial wholesale catalog...');

        $seeder = app(PurNatureKCatalogSeeder::class);
        $seeder->run();

        $this->info('PurNatureK wholesale catalog successfully provisioned and indexed.');

        return self::SUCCESS;
    }
}
