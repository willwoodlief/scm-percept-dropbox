<?php
use Illuminate\Support\Facades\Route;
use Percept\Dropbox\Facades\PerceptDropbox;
Route::middleware(['web','auth'])->group(function () {
    Route::get('percept-dropbox', function(){
        //todo, show logged in user info if needed.
    });
    Route::get('percept-dropbox/connect', function(){
        return PerceptDropbox::connect();
    })->name('percept-dropbox-connect');
    
    Route::get('percept-dropbox/disconnect', function(){
        return PerceptDropbox::disconnect();
    })->name('percept-dropbox-disconnect');
});

