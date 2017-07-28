<?php namespace SuperV\Platform;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use SuperV\Platform\Domains\Droplet\Module\Jobs\DetectActiveModuleJob;

class PlatformEventProvider extends EventServiceProvider
{
    protected $listen = [
        'superv.app.loaded' => [
            DetectActiveModuleJob::class
        ],
    ];

    public function boot()
    {
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $key => $listener) {
                app('events')->listen($event, $listener);
            }
        }

        foreach ($this->subscribe as $subscriber) {
            app('events')->subscribe($subscriber);
        }
    }
}