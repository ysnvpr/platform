<?php

namespace Tests\Platform;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use SuperV\Platform\Domains\Droplet\DropletModel;
use SuperV\Platform\Domains\Droplet\Installer;
use SuperV\Platform\Domains\Routing\RouteRegistrar;
use SuperV\Platform\Facades\PlatformFacade;
use SuperV\Platform\PlatformServiceProvider;

class TestCase extends OrchestraTestCase
{
    /**
     * Temporary directory to be created and
     * afterwards deleted in storage folder
     *
     * @var string
     */
    protected $tmpDirectory;

    protected $packageProviders = [];

    protected $appConfig = [];

    protected function getPackageProviders($app)
    {
        return array_flatten(array_merge($this->packageProviders, [PlatformServiceProvider::class]));
    }

    protected function getPackageAliases($app)
    {
        return ['Platform' => PlatformFacade::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app->setBasePath(realpath(__DIR__.'/../../'));

        if (! empty($this->appConfig)) {
            $app['config']->set($this->appConfig);
        }
    }

    protected function setUp()
    {
        parent::setUp();

        $this->withFactories(__DIR__.'/../database/factories');

        if ($this->tmpDirectory) {
            $this->tmpDirectory = __DIR__.'/../tmp/'.$this->tmpDirectory;
            if (! file_exists($this->tmpDirectory)) {
                app('files')->makeDirectory($this->tmpDirectory, 0755, true);
            }
        }

        if (method_exists($this, 'refreshDatabase')) {
            $this->artisan('superv:install');
            config(['superv.installed' => true]);

            (new PlatformServiceProvider($this->app))->boot();
        }
    }

    /**
     * @param string $slug
     * @param string $path
     * @return \SuperV\Platform\Domains\Droplet\Droplet
     * @throws \SuperV\Platform\Exceptions\PathNotFoundException
     */
    protected function setUpDroplet($slug = 'droplets.sample', $path = 'tests/Platform/__fixtures__/sample-droplet')
    {
        ComposerLoader::load(base_path($path));
        $this->app->make(Installer::class)
                  ->setSlug($slug)
                  ->setPath($path)
                  ->install();

        $entry = DropletModel::bySlug($slug);

        return $entry->resolveDroplet();
    }

    /**
     * @param       $port
     * @param       $hostname
     * @param null  $theme
     * @param array $roles
     * @param null  $model
     * @return void
     */
    protected function setUpPort($port, $hostname, $theme = null, $roles = [], $model = null)
    {
        $ports = [
            $port => [
                'hostname' => $hostname,
                'theme'    => $theme,
                'roles'    => $roles,
                'model'    => $model,
            ],
        ];
        config(['superv.ports' => $ports]);
    }

    protected function route($uri, $action, $port)
    {
        app(RouteRegistrar::class)->setPort($port)->registerRoute($uri, $action);
    }

    protected function tearDown()
    {
        if ($this->tmpDirectory) {
            app('files')->deleteDirectory(__DIR__.'/../tmp');
        }

        parent::tearDown();
    }

    protected function setUpPorts()
    {
        return config([
            'superv.ports' => [
                'web' => [
                    'hostname' => 'superv.io',
                    'theme'    => 'themes.starter',
                ],
                'acp' => [
                    'hostname' => 'superv.io',
                    'prefix'   => 'acp',
                ],
                'api' => [
                    'hostname' => 'api.superv.io',
                ],
            ],
        ]);
    }

    protected function assertProviderRegistered($provider)
    {
        $this->assertContains($provider, array_keys($this->app->getLoadedProviders()));
    }

    protected function assertProviderNotRegistered($provider)
    {
        $this->assertNotContains($provider, array_keys($this->app->getLoadedProviders()));
    }
}