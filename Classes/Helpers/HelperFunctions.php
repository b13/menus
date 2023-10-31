<?php

namespace B13\Menus\Helpers;

class HelperFunctions
{
    /*
     * Takes an $string and transforms it to be used as a HTML ID
     *
     */
    public static function getAnchorId($string)
    {
        //Lower case everything
        $string = strtolower($string);
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);
        return $string;
    }

}
