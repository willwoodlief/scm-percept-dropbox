<?php

namespace Percept\Dropbox\Models;

use App\Helpers\Projects\ProjectFile;
use Percept\Dropbox\Facades\PerceptDropbox;

/**
 * This shows how a ProjectFile can be extended to allow for remote content, or to handle files differently than the core logic
 *
 * This class is used in the demonstration of the @see ModelActions::ALL_PROJECT_FILE_EVENTS
 *
 *
 * When calling the parent constructor, Derived classes that are handling resources not on the file system,
 * should pass in null for the first two params : but pass in the file extension for the third
 *
 * Then then derived class can fill in the file_name, which is for display purposes, and should not include the extension (so cat and not cat.gif),
 * the file size (in mb), the unix timestamp for the file date, a human readable date/time string
 *
 * If the derived class is storing the file outside of the website's regular upload file directory, then it should also overload the function of getPublicFilePath()
 *
 * A derived class should also overload the deleteProjectFile()
 *
 * When its time for the file to be deleted, either by user action or when a project is deleted, then the class's deleteProjectFile will be called
 */
class DropboxProjectFile extends ProjectFile {
    protected ?string $url = null;


    public function __construct(mixed $file) {
        /*
        $this->url = $url;
        $path = parse_url($url, PHP_URL_PATH);
        $paths = explode('/',$path);
        $file = $paths[count($paths) - 1];
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        if (empty($file_name)) {
            $file_name = pathinfo($file, PATHINFO_FILENAME);
        }
        */
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_name = pathinfo($file['name'], PATHINFO_FILENAME);
        parent::__construct(null,null,$extension);

        $this->file_name = $file_name;
        $this->file_size = round($file['size'] / 1048576, 2);//13.58;

        $dateTime = new \DateTime($file['client_modified'], new \DateTimeZone('UTC')); // we can use server_modified too.

        $this->file_unix_timestamp = $dateTime->getTimestamp();
        $this->file_date = $dateTime->format('F j, Y');
        $this->downloadable_from_this_server = $file['is_downloadable']; 
        $this->full_file_path = $file['path_display'];
    }

    public function getPublicFilePath(): ?string
    {
        //here we may need dropbox download URL
        return $this->url ? $this->url : PerceptDropbox::getTemporaryLink($this);
    }

    public function deleteProjectFile() : void {
        //nothing being done here, we are linking to a url we cannot control in this demo
        PerceptDropbox::deleteProjectDoc($this);
    }




}
