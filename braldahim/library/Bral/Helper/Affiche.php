<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Helper_Affiche
{

    public static function copie($texte)
    {
        return strip_tags(preg_replace('/<br \/> /', "\n\r", $texte));
    }

}
