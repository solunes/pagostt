<?php

namespace Solunes\Pagostt;

use Illuminate\Support\ServiceProvider;

class PagosttServiceProvider extends ServiceProvider {

    protected $defer = false;

    public function boot() {
        /* Publicar Elementos */
        $this->publishes([
            __DIR__ . '/config' => config_path()
        ], 'config');
        $this->publishes([
            __DIR__.'/assets' => public_path('assets/pagostt'),
        ], 'assets');

        /* Cargar Traducciones */
        $this->loadTranslationsFrom(__DIR__.'/lang', 'pagostt');

        /* Cargar Vistas */
        $this->loadViewsFrom(__DIR__ . '/views', 'pagostt');
    }


    public function register() {
        /* Registrar ServiceProvider Internos */
        //$this->app->register('Rossjcooper\LaravelHubSpot\HubSpotServiceProvider');

        /* Registrar Alias */
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        //$loader->alias('HubSpot', 'Rossjcooper\LaravelHubSpot\Facades\HubSpot');

        $loader->alias('Pagostt', '\Solunes\Pagostt\App\Helpers\Pagostt');

        /* Comandos de Consola */
        $this->commands([
            \Solunes\Pagostt\App\Console\TestEncryption::class,
        ]);

        $this->mergeConfigFrom(
            __DIR__ . '/config/pagostt.php', 'pagostt'
        );
    }
    
}
