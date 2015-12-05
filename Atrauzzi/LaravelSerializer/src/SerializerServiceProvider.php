<?php

namespace Atrauzzi\LaravelSerializer;

use Illuminate\Support\ServiceProvider as Base;
//
use Illuminate\Foundation\Application;
use JMS\Serializer\Builder\CallbackDriverFactory;
use JMS\Serializer\SerializerBuilder;
use Doctrine\Common\Annotations\Reader;

class SerializerServiceProvider extends Base
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/config.php' => config_path('serializer.php')
        ]);
        $this->mergeConfigFrom(
            __DIR__ . '/config/config.php', 'serializer'
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->singleton('JMS\Serializer\Builder\DriverFactoryInterface', function (Application $app) {
            return new CallbackDriverFactory(
            // Note: Because we're using mappings from the L5 configuration system, there's no
            // real use for $metadataDirs and $reader.
                function (array $metadataDirs, Reader $reader) use ($app) {
                    return $app->make('Atrauzzi\LaravelSerializer\MetadataDriver');
                }
            );
        });

        $this->app->singleton('JMS\Serializer\Serializer', function (Application $app) {

            /** @var \Illuminate\Config\Repository $config */
            $config = $app->make('Illuminate\Config\Repository');

            return SerializerBuilder
                ::create()
                ->setCacheDir(storage_path('cache/serializer'))
                ->setDebug($config->get('app.debug'))
                ->setMetadataDriverFactory($app->make('JMS\Serializer\Builder\DriverFactoryInterface'))
                ->build();

        });

    }

}
