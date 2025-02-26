<?php
   
    namespace Itpathsolutions\Phpinfo;
    use Illuminate\Support\ServiceProvider;
    class PhpInfoServiceProvider extends ServiceProvider {
        public function boot(): void
        {
            $this->loadRoutesFrom(__DIR__.'/routes/web.php');
            $this->loadViewsFrom(__DIR__.'/resources/views', 'phpinfo');
            $this->mergeConfigFrom(
                __DIR__.'/Config/phpinfo.php', 'phpinfo'
            );
    
            // Publish configuration file to the application's config directory
            $this->publishes([
                __DIR__.'/Config/phpinfo.php' => config_path('phpinfo.php'),
            ]);
        }
        public function register()
        {
      }
   }
?>