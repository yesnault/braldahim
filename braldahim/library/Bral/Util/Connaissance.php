<?php
/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Connaissance
{

    private function __construct()
    {
    }

    public static function calculConnaissanceMin($valeur, $nbDe3, $distance)
    {
        $retour = $valeur - (Bral_Util_De::getLanceDeSpecifique($nbDe3, 1, 3) + $distance - 1);
        if ($retour < 0) {
            $retour = 0;
        }
        return $retour;
    }

    public static function calculConnaissanceMax($valeur, $nbDe3, $distance)
    {
        return $valeur + Bral_Util_De::getLanceDeSpecifique($nbDe3, 1, 3) + $distance - 1;
    }

}