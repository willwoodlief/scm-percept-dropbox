# Percept Dropbox Plugin

Please see issues list. 

when submitting code changes, please make a pull request

see https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/creating-a-pull-request-from-a-fork

## install instructions
1. Add this as a folder in the `/plugins` directory of your project.  

2. Run `composer update kunalvarma05/dropbox-php-sdk` command.   

3. Create an APP on Dropbox from here https://www.dropbox.com/developers/apps   

4. You have to add `https://{your-domain.com}/percept-dropbox/connect` as a Redirect URI in your Dropbox APP under OAuth 2 Redirect URIs.  

5. In your laravel environment file, please add following key  

* `PERCEPT_DROPBOX_CLIENT_ID={replace_this_with_your_dropbox_App_key}`

* `PERCEPT_DROPBOX_CLIENT_SECRET={replace_this_with_your_dropbox_App_secret}` 

For sorting file list you can add following  

* `PERCEPT_DROPBOX_FILES_SORT_BY="size"` Here you can use any one possible value from this [name, date, type, size]  

* `PERCEPT_DROPBOX_FILES_SORT_ORDER="descending"` Here you can use any one possible value from this [ascending, descending]  

8. Once you have added above settings in your project env file. Please visit `https://{your-domain.com}/percept-dropbox/connect` and from here click on the link `Connect with Dropbox`.  

9. You will be redirected to oAuth login url of Dropbox. From here you have to click on `Continue` and then have to click on `Allow` button.  

10. Once you allow access, you will get connected page saying "You are now connected with Dropbox. You can now close this window."  


At this point you have got dropbox authentication token saved on DB table percept_dropbox_access_token. This token will be used for making Dropbox API call.  


This plugin will ensure authentication token will get refreshed every 3 hours using schedule task that eventually run a command `scm-percept-dropbox:refresh_token`.    


You have now setup and linked your Dropbox storage. Try uploading a project file now.

## Steps to create Dropbox APP
1. Visit the Dropbox App Console.
2. Click the Create app button.
3. Under Choose an API section, select Scoped Access.
4. Under Choose the type of access you need, select Full Dropbox.
5. Enter a name for your custom app.
6. If you have a personal and a business account which are linked, you will be asked to select which account you want to own the app. Once you make a selection, you will be asked to sign in to that account.
7. Click the Create app button. You will be redirected to the console for your app. Note the presence of your App key and App secret on this page (not pictured). You will need to enter these into Gravity Forms to connect this custom app once you have followed the remaining steps.
8. Add the OAuth Redirect URI `https://{your-domain.com}/percept-dropbox/connect` to your Dropbox app settings under the OAuth2 Redirect URIs section. Change `{your-domain.com}` with your website domain name.
9. Once you have entered the information above, scroll up and click on the Permissions tab. On the permissions tab, you will need to select the files.content.write, files.content.read, sharing.write, sharing.read, file_requests.write, file_requests.read, permissions in order to allow the add-on features to work as expected.
10. Click the Submit button at the bottom of the page for the new Permissions to take effect.

