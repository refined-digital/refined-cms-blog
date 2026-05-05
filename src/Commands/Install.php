<?php

namespace RefinedDigital\Blog\Commands;

use Illuminate\Console\Command;
use Validator;
use Artisan;
use DB;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refinedCMS:install-blog';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs the blog files';

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
        $this->migrate();
        $this->seed();
        $this->publish();
        $this->copyDefaults();
        $this->installContentBlock();
        $this->info('Blog has been successfully installed');
    }


    protected function migrate()
    {
        $this->output->writeln('<info>Migrating the database</info>');
        Artisan::call('migrate', [
            '--path' => 'vendor/refineddigital/cms-blog/src/Database/Migrations',
            '--force' => 1,
        ]);
    }

    protected function seed()
    {
        $this->output->writeln('<info>Seeding the database</info>');
        Artisan::call('db:seed', [
            '--class' => '\\RefinedDigital\\Blog\\Database\\Seeds\\DatabaseSeeder',
            '--force' => 1
        ]);
    }

    protected function publish()
    {
        Artisan::call('vendor:publish', [
            '--tag' => 'blog',
        ]);

        // grab the blog details template id
        $template = \DB::table('templates')
                        ->whereName('Blog Details')
                        ->first();

        if (isset($template->id)) {
            // override the template id of the blog details
            $configFile = config_path('blog.php');
            $file = file_get_contents($configFile);
            $search = [ "'__ID__'" ];
            $replace = [ $template->id ];
            file_put_contents($configFile, str_replace($search, $replace, $file));
        }
    }

    private function copyDefaults()
    {
        $this->output->writeln('<info>Copying Templates</info>');
        $this->copy('views/templates');
        $this->copy('css/components');
    }

    private function copy($assetDir)
    {

        $dir = __DIR__.'/defaults/'.$assetDir.'/';
        $templates = scandir($dir);
        array_shift($templates);array_shift($templates);

        if (!is_dir(resource_path($assetDir))) {
            mkdir(resource_path($assetDir));
        }
        if (sizeof($templates)) {
            try {
                foreach ($templates as $template) {
                $this->output->writeln(' <info>Copying '.$template.'</info>');
                    $contents = file_get_contents($dir.$template);
                    file_put_contents(resource_path($assetDir.'/'.$template), $contents);
                }
            } catch(\Exception $e) {
                $this->output->writeln('<error>Failed to copy all assets</error>');
            }
        }
    }

    private function installContentBlock()
    {
        $from = __DIR__.'/defaults/app/RefinedCMS/Blocks/BlogListing';
        $to = app_path('RefinedCMS/Content/Blocks');

        exec('cp -r '.$from.' '.$to);

        $appFile = app_path('RefinedCMS/content/Providers/ContentServiceProvider.php');

        // get the contents of the file
        $appData = file_get_contents($appFile);

        $search = [
            "// register the content fields",
            "use Illuminate\Support\ServiceProvider;"
        ];
        $replace = [
            '// register the content fields'."\n\t\t".'$agg->register(BlogListing::class);',
            'use App\\RefinedCMS\\Content\\Blocks\\BlogListing\\BlogListing;'."\nuse Illuminate\Support\ServiceProvider;"
        ];

        $appData = str_replace($search, $replace, $appData);

        file_put_contents($appFile, $appData);
    }

}
