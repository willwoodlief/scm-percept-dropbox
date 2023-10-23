<?php

namespace Percept\Dropbox\Facades;

use App\Plugins\PluginRef;
use Illuminate\Support\Facades\Facade;

/**
 * Plugins do not need any facades, but given this is laravel, this these are convenient to lump some logic together that is needed by the plugin
 *
 * This class is found by the laravel framework  via the composer.json extra field "aliases"
 *
 * All this class does is hook up the Percept\Dropbox\PerceptDropbox class to the framework
 *
 * @uses \Percept\Dropbox\PerceptDropbox::getPluginRef()
 * @uses \Percept\Dropbox\PerceptDropbox::getBladeRoot()
 * @method static PluginRef getPluginRef()
 * @method static string getBladeRoot()
 */
class PerceptDropbox extends Facade
{
    /**
     * This laravel function does the hooking up of our class with the name of this Facade
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Percept\Dropbox\PerceptDropbox::class;
    }
}
