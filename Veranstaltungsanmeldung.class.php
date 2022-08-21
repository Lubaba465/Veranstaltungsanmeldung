<?php

class Veranstaltungsanmeldung extends  StudIPPlugin implements SystemPlugin
{




    public function __construct()
    {
        parent::__construct();
        StudipAutoloader::addAutoloadPath(__DIR__ . '/models');
        StudipAutoloader::addAutoloadPath(__DIR__ . '/classes');

        if ($GLOBALS['perm']->have_perm("dozent")) {

             $nav = new Navigation(
                _("Veranstaltungsanmeldung"),
                PluginEngine::getURL($this, array(), "my_courses")
            );

            Navigation::addItem("/browse/veranstaltungsanmeldung", $nav);
        }

        if ($GLOBALS['perm']->have_perm("admin")) {

            $nav = new Navigation(
                _("Veranstaltungsanmeldung"),
                PluginEngine::getURL($this, array(), "my_courses")
            );



            Navigation::addItem("/browse/veranstaltungsanmeldung", $nav);
        }
    }

    public function perform($unconsumed_path)
    {

        parent::perform($unconsumed_path);
    }


}

