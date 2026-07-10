<?php

namespace App\Console\Commands;

use App\Services\WordPressBlogImporter;
use App\Services\WordPressSqlLoader;
use Illuminate\Console\Command;

class ImportWordPressBlogsCommand extends Command
{
    protected $signature = 'blogs:import-wordpress
                            {file : Path to the WordPress .sql dump}
                            {--keep-db : Keep the temporary import database after completion}
                            {--force-update : Update blogs that already exist (matched by slug)}';

    protected $description = 'Import published WordPress posts from a SQL dump into the blogs table';

    private const IMPORT_DB = 'pixelssoft_wp_import';

    public function handle(): int
    {
        $file = $this->argument('file');
        if (!is_file($file)) {
            $file = base_path($file);
        }

        if (!is_file($file)) {
            $this->error("SQL file not found: {$this->argument('file')}");
            return self::FAILURE;
        }

        $config = config('database.connections.mysql');

        $this->info('Loading WordPress tables from SQL dump (this may take a few minutes)...');

        $loader = new WordPressSqlLoader(
            $config['host'],
            (int) ($config['port'] ?? 3306),
            $config['username'],
            $config['password'],
            self::IMPORT_DB,
        );

        try {
            $loader->load($file);
            $this->info('WordPress tables loaded into temporary database.');

            $importer = new WordPressBlogImporter(self::IMPORT_DB);
            $stats = $importer->import(skipExisting: !$this->option('force-update'));

            $this->table(
                ['Metric', 'Count'],
                collect($stats)->map(fn ($v, $k) => [str_replace('_', ' ', ucfirst($k)), $v])->values()->all()
            );

            if (!$this->option('keep-db')) {
                $loader->cleanup();
                $this->info('Temporary import database removed.');
            }

            $this->info('WordPress blog import completed.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }
}
