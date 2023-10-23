<?php
namespace Percept\Dropbox;

use App\Helpers\Utilities;
use App\Models\Invoice;
use App\Plugins\Plugin;
use Illuminate\Support\Facades\Route;
use TorMorten\Eventy\Facades\Eventy;
use Percept\Dropbox\Facades\PerceptDropbox;
use App\Helpers\Projects\ProjectFile;
use Percept\Dropbox\Models\DropboxProjectFile;

/**
 * This is where the plugin registers its listeners and hooks up the code to deal with notices sent to those listeners
 *
 * It has some simple logic to demonstrate how to listen to one page being run (the dashboard), and has an initialize() function that is run on plugin startup
 */
class PluginLogic extends Plugin {

    /**
     * An action listener sets this bool value based if the laravel framework is currently serving the dashboard page
     * @var bool
     */
    protected bool $onDashboard = false;

    /**
     * Sets the dashboard bool to false
     */
    public function __construct()
    {
        $this->onDashboard = false;
    }

    /**
     *
     * @return void
     */
    public function initialize()
    {   
        Eventy::addAction(Plugin::ACTION_ROUTE_STARTED,function (\Illuminate\Http\Request $request) {
            
            $route_name = Route::getCurrentRoute()->getName();
            if ($route_name === 'project.file.create') {
                $file = $request->file('file');    
                $request->files->set('file', [NULL]);
                $project_id = Route::input('project_id');

                PerceptDropbox::uploadProjectDoc($project_id, $file);               
                
                return false;
            } 
        });

        /*
        Eventy::addFilter(Plugin::FILTER_SET_VIEW, function( string $bade_name) {
            if ($bade_name === 'projects.project_parts.project_documents') {
                return PerceptDropbox::getBladeRoot().'::override/projectdocs';
            }
            return $bade_name;
        }, 20, 1);
        */
        
        Eventy::addAction(Plugin::ACTION_START_DISCOVERY_PROJECT_FILES, function( \App\Models\Project $project):void {
            //Log::debug("Project starting file discovery",['project'=>$project->toArray()]);
            //dd($project);
        }, 20, 2);

        Eventy::addFilter(Plugin::FILTER_DISCOVER_PROJECT_FILE, function( ProjectFile $project_file): ?ProjectFile {
            //Log::debug("Project file found",['project_file'=>$project_file->toArray()]);
            // return null; // if do not want this file displayed
            return $project_file;
        }, 20, 2);

        /**
         * @returns ProjectFile[]
        */
        Eventy::addFilter(Plugin::FILTER_APPEND_PROJECT_FILES, function( array $extra_project_files ) : array {
            //$extra_project_files[] = new DropboxProjectFile('https://upload.wikimedia.org/wikipedia/commons/6/6d/CatD9T.jpg','totally-not-on-this-file-system');
            $project_id = Route::input('project_id');
            $extra_project_files = PerceptDropbox::getProjectDocs($project_id);   
            return $extra_project_files;
        }, 20, 2);

        Eventy::addFilter(Plugin::FILTER_SORT_PROJECT_FILES, function( array $all_project_files ): array {
            //do some sorting on the array $all_project_files            
            usort($all_project_files, function(ProjectFile $a,ProjectFile $b)  {
                return $a <=> $b;
            });
            return $all_project_files;
        }, 20, 2);


        Eventy::addAction(Plugin::ACTION_BEFORE_DELETING_PROJECT_FILE, function( ProjectFile $project_file):void {
            //dd("file for project about to be deleted",['project_file'=>$project_file->toArray()]);
            //Log::debug("file for project about to be deleted",['project_file'=>$project_file->toArray()]);
            /*
            if ($project_file instanceof DropboxProjectFile) {
                dd('this is instance of dropbox');
            } else {
                dd('this is not instance of dropbox');
            }
            */
        }, 20, 2);

    }
}
