<?php


namespace robertogallea\LaravelMetrics;


use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Collection;
use robertogallea\LaravelMetrics\Http\Middleware\Mark;
use robertogallea\LaravelMetrics\Http\Middleware\MeasureTime;
use robertogallea\LaravelMetrics\Listeners\EventListener;
use robertogallea\LaravelMetrics\Models\Interfaces\PerformsMetrics;
use robertogallea\LaravelMetrics\Models\MetricRegistry;

class MetricsServiceProvider extends ServiceProvider
{
    /**
     * Registers the ServiceProvider
     */
    public function register()
    {
        $this->app->singleton(
            MetricRegistry::class,
            function ($app) {
                return new MetricRegistry();
            }
        );

        $this->mergeConfigFrom(
            $this->packagePath('config/metrics.php'),
            'metrics'
        );

        $this->registerCollectionMacros();
    }

    /**
     * Boots the ServiceProvider
     */
    public function boot()
    {
        $this->registerEventListener();

        $this->registerMiddlewares();

        $this->loadMigrations();

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    private function registerCollectionMacros()
    {
        Collection::macro('variance', function ($column = null) {
            $collection = $this;

            $mean = $collection->avg();
            $count = $collection->count();

            return $collection->transform(function ($value) use ($mean) {
                    return (($value - $mean) * ($value - $mean));
                })->sum() / $count;
        });

        Collection::macro('stDev', function($column = null) {
            return sqrt($this->variance($column));
        });

        Collection::macro('cumulative', function () {
            $cumulative = 0;

            return $this->transform(function ($item, $key) use (&$cumulative) {
                $value = $item + $cumulative;
                $cumulative += $value;

                return $value;
            });
        });

        Collection::macro('kolmSmirn', function ($collection) {
            return $this->transform(function ($item, $key) use ($collection) {
                return abs($item - ($collection->get($key)));
            })->max();
        });
    }

    private function loadMigrations()
    {
        if (! class_exists('CreateMetricsTable')) {
            $this->publishes([
                __DIR__.'/../stubs/create_metrics_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_metrics_table.php'),
            ], 'migrations');
        }
    }

    private function packagePath($path)
    {
        return __DIR__ . "/../$path";
    }

    private function registerEventListener()
    {
        Event::listen(PerformsMetrics::class, EventListener::class);
    }

    private function bootForConsole()
    {
        $this->publishes([
            $this->packagePath('config/metrics.php') => config_path('metrics.php')
        ], 'config');
    }

    private function registerMiddlewares()
    {
        $this->app['router']->aliasMiddleware('measure-time', MeasureTime::class);
        $this->app['router']->aliasMiddleware('mark', Mark::class);
    }
}