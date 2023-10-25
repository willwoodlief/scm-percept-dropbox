# Percept Dropbox Plugin

Please see issues list. 

when submitting code changes, please make a pull request

see https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/creating-a-pull-request-from-a-fork

## install instructions
1. Add this as a folder in the /plugins directory of your project.  

2. Run "composer dumpautoload" command.   

3. Run "php artisan percept-dropbox:install" command. This will publish config file (config/percept-dropbox.php).  

4. Run "php artisan migrate --path=plugins/scm-percept-dropbox/database/migrations" command. This will create database table percept_dropbox_access_token  

5. Create an APP on Dropbox from here https://www.dropbox.com/developers/apps   

6. You have to add https://{your-domain.com}/percept-dropbox/connect as a Redirect URI in your Dropbox APP under OAuth 2 Redirect URIs.  

7. In your laravel environment file, please add following key  

PERCEPT_DROPBOX_CLIENT_ID={replace_this_with_your_dropbox_App_key}  

PERCEPT_DROPBOX_CLIENT_SECRET={replace_this_with_your_dropbox_App_secret}  

For sorting file list you can add following  

PERCEPT_DROPBOX_FILES_SORT_BY="size" Here you can use any one possible value from this [name, date, type, size]  

PERCEPT_DROPBOX_FILES_SORT_ORDER="descending" Here you can use any one possible value from this [ascending, descending]  

8. Once you have added above settings in your project env file. Please visit https://{your-domain.com}/percept-dropbox/connect and from here click on the link "Connect with Dropbox".  

9. You will be redirected to oAuth login url of Dropbox. From here you have to click on Continue and then have to click on Allow button.  

10. Once you allow access, you will get connected page saying "You are now connected with Dropbox. You can now close this window."  


At this point you have got dropbox authentication token saved on DB table percept_dropbox_access_token. This token will be used for making Dropbox API call.  


This plugin will ensure authentication token will get refreshed every 3 hours using schedule task that eventually run a command scm-percept-dropbox:refresh_token.    


You have now setup and linked your Dropbox storage. Try uploading a project file now.
