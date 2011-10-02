<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Box
{

    private function __construct()
    {
    }

    public static function calculBoxToRefresh0PA(&$tab)
    {

        if (!in_array("box_vue", $tab)) {
            $tab[] = "box_vue";
        }
        if (!in_array("box_competences", $tab)) {
            $tab[] = "box_competences";
        }
        if (!in_array("box_charrette", $tab)) {
            $tab[] = "box_charrette";
        }
        if (!in_array("box_soule", $tab)) {
            $tab[] = "box_soule";
        }
    }

    public static function calculBoxToRefresh1PA(&$tab)
    {

        if (!in_array("box_soule", $tab)) {
            $tab[] = "box_soule";
        }
        if (!in_array("box_vue", $tab)) {
            $tab[] = "box_vue";
        }
    }

}