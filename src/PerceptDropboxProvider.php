<?php
namespace Percept\Dropbox;


use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\Dropbox as DropboxClient;
use Illuminate\Console\Scheduling\Schedule;
use Percept\Dropbox\Commands\RefreshToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * This plugin uses the https://github.com/spatie/laravel-package-tools library to make the boilerplate laravel for a new service. It is optional, but makes it simpler. But regardless, the plugin needs to extend the Illuminate\Support\ServiceProvider class
 *
 * This class is found by the framework in the composer.json extra.providers field
 *
 * In this example class, I only need to override two methods configurePackage(), and packageBooted().
 *
 * In the configurePackage() I use the laravel-package-tools to register new blade views, new routes,commands, facades, assets, migrations.
 * The routes will load in the controllers used for the routes
 *
 * In a laravel package that uses its own views, these views are always prefixed. See the constant I made called VIEW_BLADE_ROOT and how its used in the other files
 *
 * Note that we can organize the resources however we need in resources/dist and the blades however we see fit at resources/views
 */
class PerceptDropboxProvider extends PackageServiceProvider
{
    /**
     * This holds the prefix for all the blades defined in the service package here. It can have any unique value not shared by other plugins
     */
    //const VIEW_BLADE_ROOT = 'PerceptThemeDocs';

    /**
     * This is inherited from the base class, and allows me to register the name,views, route, assets, migrations, commands
     *
     * This function is called each time the laravel code runs
     *
     * @param Package $package
     * @return void
     */
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('percept-dropbox')
            ->hasConfigFile()
            ->hasRoute('web')
            ->hasMigration('create_percept_dropbox_access_token')
            ->hasCommand(RefreshToken::class)
            ->runsMigrations()
        ;
    }

    /**
     * I encapsulate all the plugin logic in this class, which inherits from the plugin class
     *
     * @var PluginLogic
     */
    protected PluginLogic $plugin_logic;

    /**
     * This method overrides the base class empty method, that is called when the package is fully ready for use. Its called each time the laravel code runs
     *
     * Here we just create the new PluginLogic
     *
     * @return $this
     */
    public function packageBooted()
    {
        $this->plugin_logic = new PluginLogic();
        $this->plugin_logic->initialize();

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('scm-percept-dropbox:refresh_token')
                ->everyThreeHours()
                ->appendOutputTo(storage_path('logs/scm-percept-dropbox.log'));
        });
        if (Schema::hasTable('percept_dropbox_access_token')) {            
            $row = DB::table('percept_dropbox_access_token')->first();
            if($row){
                $dropboxClient = app(DropboxClient::class);                
                $dropboxClient->setAccessToken($row->token);
            }
        } 
        return $this;
    }
    public function packageRegistered() : void
    {
        $this->app->singleton(DropboxClient::class, function () {

            $dropboxApp = new DropboxApp(config('percept-dropbox.client_id'), config('percept-dropbox.client_secret'));
            $dropboxClient = new DropboxClient( $dropboxApp );
            return $dropboxClient;
        });
    }

}
