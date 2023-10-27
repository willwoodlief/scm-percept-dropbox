<?php

namespace Percept\Dropbox;

use App\Plugins\PluginRef;
use Exception;
use Illuminate\Support\Facades\Log;
use Percept\Dropbox\Models\DropboxProjectFile;
use Kunnu\Dropbox\Dropbox as DropboxClient;
use Illuminate\Support\Facades\DB;
use Kunnu\Dropbox\Models\AccessToken;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

//this is a facade anywhere in any code can use ScmPluginTest::logMe

/**
 * This is called from the class Percept\Dropbox\Facades\PerceptDropbox
 *
 * The methods here are called as the facade static methods, so PerceptDropbox::getPluginRef()
 *
 */
class PerceptDropbox
{
    /**
     * use a plugin ref to resolve the media path via the getResourceUrl() method
     * @var PluginRef
     */
    protected PluginRef $ref;

    protected $dropboxClient;

    protected $sortBy = [
        'date' => 'file_unix_timestamp',
        'name' => 'file_name',
        'type' => 'file_extension',
        'size' => 'file_size'
    ];

    protected $sortOrder = [
        'ascending' => 'asc',
        'descending' => 'desc'
    ];

    /**
     * This plugin only uses a single instance of this class, and that only uses a single instance of the PluginRef, here we create that
     */
    public function __construct(DropboxClient $dropboxClient)
    {
        $this->ref = new PluginRef(dirname(__FILE__,2));
        $this->dropboxClient = $dropboxClient;
        
    }


    /**
     * Public accessor to get the plugin ref, usage done in the blades mostly
     * @return PluginRef
     */
    public function getPluginRef() : PluginRef {
        return $this->ref;
    }

    public function getSortBy($key) : string {
        return $key && $this->sortBy[$key] ? $this->sortBy[$key] : 'file_unix_timestamp';
    }

    public function getSortOrder($key) : string {
        return $key && $this->sortOrder[$key] ? $this->sortOrder[$key] : 'desc';
    }

    public function uploadProjectDoc(int $project_id, $file) : void {
        try{
            $param = [
                'mode' => 'add',
                'autorename' => false
            ];
            $this->dropboxClient->upload($file, '/uploads/projects/'.$project_id.'/documents/'.$file->getClientOriginalName(), $param);
        }catch(Exception $e){
            Log::error($e->getMessage());
        }
    }

    public function deleteProjectDoc(DropboxProjectFile $file) : void {
        try{
            $this->dropboxClient->delete($file->getFullFilePath());
        }catch(Exception $e){
            Log::error($e->getMessage());
        }
    }

    public function getTemporaryLink(DropboxProjectFile $file) : string {
        $link = '';
        try{
            $link = $this->dropboxClient->getTemporaryLink($file->getFullFilePath())->getLink();
        }catch(Exception $e){
            Log::error($e->getMessage());
        }
        return $link;         
    }

    public function getProjectDocs(int $project_id) : array {
        $docs = [];
        try{
            
            $response = $this->dropboxClient->listFolder('/uploads/projects/'.$project_id.'/documents');
            do{
                foreach($response->getItems() as $file){
                    $docs[] = new DropboxProjectFile($file->getData());
                }
                if ($response->hasMoreItems()) {
                    $cursor = $response->getCursor();
                    $response = $this->dropboxClient->listFolderContinue($cursor);
                }
            } while ($response->hasMoreItems());
        } catch (Exception $e){
            Log::error($e->getMessage());
        }    

        return $docs;
    }  

    public function connect(){

        $callbackUrl = route('percept-dropbox-connect');
        $authHelper = $this->dropboxClient->getAuthHelper();

        $row = DB::table('percept_dropbox_access_token')->first();

        if($row){
            $expire_at = $row->expire_at;
            $accessToken = new AccessToken(json_decode($row->token_data, true));
            if($accessToken && $expire_at && $expire_at > time() + (5*60) ){
                $this->dropboxClient->setAccessToken($accessToken->getToken());
            } 
        } else {
            $accessToken = NULL;
        }
                
        if(!$accessToken && !request()->has('code') && !request()->has('state')){
            $params = [];
            $urlState = null ;

            $tokenAccessType = "offline";
            
            $authUrl = $authHelper->getAuthUrl($callbackUrl, $params, $urlState, $tokenAccessType);
            if(request()->has('disconnected')){
                echo "You are now discounnected<br>";
            }
            echo "<a href='" . $authUrl . "'>Connect with Dropbox</a>";
            exit;
        }

        if (!$accessToken && request()->has('code') && request()->has('state')) {    
            $code = request()->input('code');
            $state = request()->input('state');

            $authHelper->getPersistentDataStore()->set('state', filter_var($state, FILTER_SANITIZE_STRING));
            $accessToken = $authHelper->getAccessToken($code, $state, $callbackUrl);
            $this->dropboxClient->setAccessToken($accessToken->getToken());
            DB::table('percept_dropbox_access_token')->updateOrInsert([],[
                'token_data' => json_encode($accessToken->getData()),
                'token' => $accessToken->getToken(),
                'expire_at' => time() + $accessToken->getExpiryTime()
            ]);
            echo "You are now connected with Dropbox. You can now close this window.<br>";
            echo "<a class='btn btn-primary' href='" . route('percept-dropbox-disconnect') . "'>Disconnect Dropbox</a>";
        } else if($accessToken){
            echo "You are now connected with Dropbox. You can now close this window.<br>";
            echo "<a class='btn btn-primary' href='" . route('percept-dropbox-disconnect') . "'>Disconnect Dropbox</a>";
        }
        
    }

    public function disconnect(){
        DB::table('percept_dropbox_access_token')->delete();
        return redirect(route('percept-dropbox-connect',['disconnected'=> 1]));
    }

}
