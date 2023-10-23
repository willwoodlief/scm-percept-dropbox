<?php
namespace Percept\Dropbox;


use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\Dropbox as DropboxClient;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Kunnu\Dropbox\Models\AccessToken;

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
        //$package->publishableProviderName = 'PerceptDropboxServiceProvider';
        $package
            ->name('percept-dropbox')
            //->hasViews(static::VIEW_BLADE_ROOT)
            //->publishesServiceProvider('PerceptDropboxServiceProvider')
            //->hasViews(static::VIEW_BLADE_ROOT)
            ->hasRoute('web')
            //->hasAssets()
            ->hasMigration('create_percept_dropbox_access_token')
            /*
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->startWith(function(InstallCommand $command) {
                        $command->info('start command');
                    })
                    //->publishConfigFile()
                    //->publishAssets()
                    ->publishMigrations()
                    //->copyAndRegisterServiceProviderInApp()
                    ->endWith(function(InstallCommand $command) {
                        $command->info('End command execution. Have a great day!');
                    });
                //$command->info($package->publishableProviderName."-".$package->shortName());
                //$command->comment($package->publishableProviderName."-".$package->shortName());
                
            })
            */
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
        
        $row = DB::table('percept_dropbox_access_token')->first();
        if($row){
            $expire_at = $row->expire_at;
            $accessToken = new AccessToken(json_decode($row->token_data, true));
            if(request()->input('disconnected') != '1'){
                $dropboxClient = $this->app->make(DropboxClient::class);
                if($accessToken && (!$expire_at || $expire_at <= time() + (5*60))){
                    $authHelper = $dropboxClient->getAuthHelper();
                    $accessToken = $authHelper->getRefreshedAccessToken($accessToken);

                    DB::table('percept_dropbox_access_token')->updateOrInsert([],[
                        'token_data' => json_encode($accessToken->getData()),
                        'token' => $accessToken->getToken(),
                        'expire_at' => time() + $accessToken->getExpiryTime()
                    ]);
                    //Log::debug("existing token expired, using refreshed token",['new accessToken'=>$accessToken->getToken(), 'expire' => time() + $accessToken->getExpiryTime()]);
                    $dropboxClient->setAccessToken($accessToken->getToken());
                } else if($accessToken){
                    //Log::debug("existing token alive, using existing token",['accessToken'=>$accessToken->getToken(), 'expire' => $expire_at]);
                    $dropboxClient->setAccessToken($accessToken->getToken());
                }
            } else {
                //Log::debug("Dropbox is disconnected");
            }
        }
        return $this;
    }
    public function packageRegistered() : void
    {
        $this->app->singleton(DropboxClient::class, function () {

            $dropboxApp = new DropboxApp(env('DROPBOX_APP_KEY'), env('DROPBOX_APP_SECRET'));
            $dropboxClient = new DropboxClient( $dropboxApp );
            
            return $dropboxClient;
        });
    }

}
