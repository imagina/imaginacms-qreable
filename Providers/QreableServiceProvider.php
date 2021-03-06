<?php

namespace Modules\Qreable\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Traits\CanPublishConfiguration;
use Modules\Core\Events\BuildingSidebar;
use Modules\Core\Events\LoadingBackendTranslations;
use Modules\Qreable\Events\Handlers\RegisterQreableSidebar;

class QreableServiceProvider extends ServiceProvider
{
    use CanPublishConfiguration;
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerBindings();
        $this->app['events']->listen(BuildingSidebar::class, RegisterQreableSidebar::class);

        $this->app['events']->listen(LoadingBackendTranslations::class, function (LoadingBackendTranslations $event) {
            $event->load('locations', array_dot(trans('qreable::locations')));
            // append translations

        });
    }

    public function boot()
    {
        $this->publishConfig('qreable', 'permissions');

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }

    private function registerBindings()
    {
        $this->app->bind(
            'Modules\Qreable\Repositories\QrRepository',
            function () {
                $repository = new \Modules\Qreable\Repositories\Eloquent\EloquentQrRepository(new \Modules\Qreable\Entities\Qr());

                if (! config('app.cache')) {
                    return $repository;
                }

                return new \Modules\Qreable\Repositories\Cache\CacheQrDecorator($repository);
            }
        );
// add bindings

    }
}
