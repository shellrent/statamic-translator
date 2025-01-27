<?php

namespace Aerni\Translator\Tests;

use Aerni\Translator\TranslatorServiceProvider;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Extend\Manifest;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Statamic;

abstract class TestCase extends OrchestraTestCase {
    /**
     * Load package service provider
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders( $app ): array {
        return [
            TranslatorServiceProvider::class,
            StatamicServiceProvider::class,
        ];
    }

    /**
     * Load package aliases
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageAliases( $app ): array {
        return [
            'Statamic' => Statamic::class,
        ];
    }

    /**
     * Load Environment
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp( $app ): void {
        parent::getEnvironmentSetUp( $app );

        // Make sure the .env file is loaded
        $app->useEnvironmentPath( __DIR__ . '/..' );
        $app->bootstrapWith( [ LoadEnvironmentVariables::class ] );

        $app->make( Manifest::class )->manifest = [
            'aerni/translator' => [
                'id' => 'aerni/translator',
                'namespace' => 'Aerni\\Translator\\',
            ],
        ];
    }

    /**
     * Resolve the application configuration and set the Statamic configuration
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function resolveApplicationConfiguration( $app ): void {
        parent::resolveApplicationConfiguration( $app );

        $configs = [
            'assets', 'cp', 'forms', 'routes', 'sites',
            'stache', 'static_caching', 'system', 'users',
        ];

        foreach( $configs as $config ) {
            $app['config']->set( "statamic.$config", require( __DIR__ . "/../vendor/statamic/cms/config/{$config}.php" ) );
        }

        $app['config']->set( 'statamic.editions.pro', true );

        $app['config']->set( 'translator', require( __DIR__ . '/../config/translator.php' ) );
    }
}
