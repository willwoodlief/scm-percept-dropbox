# scm-plugin-sample-theming

Demo for switching out a blade, changing colors on another page, and adds a footer link to all pages

Installed demo stuff in the sample theme plugin

Changes that happen when this is installed:

* the loading icon (via blade overload)
* the top menu icon (via image filter)
* the birthdays in the dashboard (via blade substitution
* adds two new routes and views
* shows one of these route as a new menu item - the other new page can be accessed then
* adds random quotes to the bottom of the dashboard (via bottom panel filter and action to listen to which route is used)
* colors the active projects on the dashboards (via added css stylesheet)
* adds a new db table that links with invoices
* adds a new model for this table
* when a new invoice is created, a new row for the new model is created
* adds a new column with some info from the new db table into the invoices page

## install instructions

add this as a folder in the plugins

`composer install`  `artisan migrate`


 
