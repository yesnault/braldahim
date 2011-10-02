<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Vie
{

    function __construct()
    {
    }

    public static function calculRegenerationBraldun(&$braldun, &$jetRegeneration)
    {
        $jetRegeneration = 0;

        if ($braldun->pv_restant_braldun < $braldun->pv_max_braldun + $braldun->pv_max_bm_braldun) {
            $jetRegeneration = Bral_Util_De::getLanceDe10($braldun->regeneration_braldun);
            $jetRegeneration = $jetRegeneration + $braldun->regeneration_bm_braldun;

            if ($jetRegeneration < 0) { // pas de regénération négative (même si le malus est important)
                $jetRegeneration = 0;
            }

            if ($braldun->pv_restant_braldun + $jetRegeneration > $braldun->pv_max_braldun + $braldun->pv_max_bm_braldun) {
                $jetRegeneration = $braldun->pv_max_braldun + $braldun->pv_max_bm_braldun - $braldun->pv_restant_braldun;
            }

            $braldun->pv_restant_braldun = $braldun->pv_restant_braldun + $jetRegeneration;
        }
    }

    public static function calculRegenerationMonstre(&$monstre)
    {
        $jetRegeneration = 0;

        if ($monstre["pv_restant_monstre"] < $monstre["pv_max_monstre"]) {

            $jetRegeneration = Bral_Util_De::getLanceDe10($monstre["regeneration_monstre"]);
            $jetRegeneration = $jetRegeneration - $monstre["regeneration_malus_monstre"];

            if ($jetRegeneration < 0) { // pas de regénération négative (même si le malus est important)
                $jetRegeneration = 0;
            }

            if ($monstre["pv_restant_monstre"] + $jetRegeneration > $monstre["pv_max_monstre"]) {
                $jetRegeneration = $monstre["pv_max_monstre"] - $monstre["pv_restant_monstre"];
            }

            $monstre["pv_restant_monstre"] = $monstre["pv_restant_monstre"] + $jetRegeneration;
        }

        return $jetRegeneration;
    }

}