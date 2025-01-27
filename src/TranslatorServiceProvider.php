<?php

namespace Aerni\Translator;

use Aerni\Translator\Contracts\TranslationService;
use Aerni\Translator\Services\GoogleAdvancedTranslationService;
use Aerni\Translator\Services\GoogleBasicTranslationService;
use Google\Cloud\Translate\V2\TranslateClient;
use Google\Cloud\Translate\V3\TranslationServiceClient;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class TranslatorServiceProvider extends AddonServiceProvider {
    protected $modifiers = [
        Modifiers\Translate::class,
    ];

    protected $tags = [
        Tags\Translate::class,
    ];

    protected $fieldtypes = [
        Fieldtypes\Translator::class,
    ];

    protected $routes = [
        'cp' => __DIR__ . '/../routes/cp.php',
    ];

    protected $scripts = [
        __DIR__ . '/../resources/dist/js/translator.js',
    ];

    public function boot(): void {
        parent::boot();

        Statamic::booted( function() {
            $this->registerTranslationService();
        } );
    }

    protected function registerTranslationService(): void {
        $translationService = config( 'translator.translation_service' );

        if( $translationService === 'google_basic' ) {
            $this->registerGoogleBasic();
        }

        if( $translationService === 'google_advanced' ) {
            $this->registerGoogleAdvanced();
        }
    }

    protected function registerGoogleBasic(): void {
        $this->app->singleton( TranslationService::class, GoogleBasicTranslationService::class );

        $this->app->singleton( TranslateClient::class, function() {
            return new TranslateClient( [
                'key' => config( 'translator.google_translation_api_key' ),
            ] );
        } );
    }

    protected function registerGoogleAdvanced(): void {
        $this->app->singleton( TranslationService::class, function() {
            return new GoogleAdvancedTranslationService(
                $this->app->make( TranslationServiceClient::class ),
                config( 'translator.google_cloud_project' )
            );
        } );

        $this->app->singleton( TranslationServiceClient::class, function() {
            return new TranslationServiceClient( [
                'credentials' => config( 'translator.google_application_credentials' ),
            ] );
        } );
    }
}
