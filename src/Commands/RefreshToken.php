<?php
namespace Percept\Dropbox\Commands;

use Illuminate\Console\Command;
use Kunnu\Dropbox\Dropbox as DropboxClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Kunnu\Dropbox\Models\AccessToken;
use Percept\Dropbox\Facades\PerceptDropbox;

class RefreshToken extends Command
{
    protected $signature = "scm-percept-dropbox:refresh_token {--info : More information}";

    protected $description = 'Refresh the Dropbox authentication token';

    public function handle()
    {
        if($this->option('info')) {
            $this->line($this->description);
            $this->newLine(1);
        } else {
            $dropboxClient = app(DropboxClient::class);
            $authHelper = $dropboxClient->getAuthHelper();

            $row = DB::table('percept_dropbox_access_token')->first();
            if($row){
                $accessToken = new AccessToken(json_decode($row->token_data, true));
                $accessToken = $authHelper->getRefreshedAccessToken($accessToken);
                DB::table('percept_dropbox_access_token')->updateOrInsert([],[
                    'token_data' => json_encode($accessToken->getData()),
                    'token' => $accessToken->getToken(),
                    'expire_at' => time() + $accessToken->getExpiryTime()
                ]);
        
                $dropboxClient->setAccessToken($accessToken->getToken());
                $this->line('Using Refreshed token');
                $this->newLine(1);
            } else {
                $this->line('Token not exists in DB table');
                $this->newLine(1);
            }
        }

        
        /*
        if($row){
            $expire_at = $row->expire_at;
            
            if(request()->input('disconnected') != '1'){
                $dropboxClient = $this->app->make(DropboxClient::class);
                if($accessToken && (!$expire_at || $expire_at <= time() + (5*60))){
                    

                   
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
        */
    }

}