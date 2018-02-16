<?php

namespace SuperV\Platform\Console;

use Illuminate\Console\Command;

class SuperVInstallCommand extends Command
{
    protected $signature = 'superv:install';

    protected $description = 'Install SuperV Platform';

    public function handle()
    {
        $this->comment('Installing SuperV');
        $this->call('migrate', ['--scope' => 'platform']);

        $this->setEnv();

        $this->comment("SuperV installed..! \n");
    }

    protected function setEnv()
    {
        $file = base_path('.env');
        $contents = str_replace('SV_INSTALLED=false', 'SV_INSTALLED=true', file_get_contents($file));
        file_put_contents($file, $contents);
    }
}