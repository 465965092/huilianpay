<?php

namespace Denghr\Huilianpay;

use Illuminate\Support\ServiceProvider;

class HuilianpayServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        if (!file_exists(config_path('huilianpay.php'))) {
            $this->publishes([
                (__DIR__) . '/config/huilianpay.php' => config_path('huilianpay.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            (__DIR__) . '/config/huilianpay.php', 'huilianpay'
        );
        //// 单例绑定服务
        $this->app->singleton('huilianpay', function ($app) {
            return new Huilianpay($app['config']);
         });
    }

    public function provides()
     {
        // 因为延迟加载 所以要定义 provides 函数 具体参考laravel 文档
         return ['huilianpay'];
     }
}
