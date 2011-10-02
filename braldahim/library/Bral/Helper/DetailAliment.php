<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Helper_DetailAliment
{

    public static function afficherPrix($e)
    {
        Zend_Loader::loadClass("Bral_Helper_DetailPrix");
        return Bral_Helper_DetailPrix::afficherPrix($e, "_echoppe_aliment");
    }
}
