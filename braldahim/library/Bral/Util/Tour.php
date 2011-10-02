<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Tour
{


    /**
     * Retourne la duree en H:m:s du tour de base d'un braldûn.
     * Si le braldûn est dans un match de soule, le tour de base est divisé par deux.
     *
     * @static
     * @param $braldun Braldun
     * @param $config Config
     * @return string duree sous le format H:m:s
     */
    public static function getDureeBaseProchainTour($braldun, $config)
    {

        $minutesProchain = Bral_Util_ConvertDate::getMinuteFromHeure($config->game->tour->duree_base);
        $minutesProchain = $minutesProchain - (10 * $braldun->sagesse_base_braldun);

        if ($braldun->est_soule_braldun == "oui") {
            $minutesProchain = intval($minutesProchain / 2);
        }

        return Bral_Util_ConvertDate::getHeureFromMinute($minutesProchain);
    }

    public static function getTabMinutesProchainTour($braldun)
    {
        $retour = null;
        $retour["minutesBase"] = Bral_Util_ConvertDate::getMinuteFromHeure($braldun->duree_prochain_tour_braldun);
        $retour["minutesBlessures"] = 0;
        $retour["minutesBM"] = $braldun->duree_bm_tour_braldun;
        $config = Zend_Registry::get('config');

        $totalPvSansBm = intval($config->game->pv_base + $braldun->vigueur_base_braldun * $config->game->pv_max_coef);

        if (($braldun->pv_max_braldun + $braldun->pv_max_bm_braldun) >= $braldun->pv_restant_braldun) {
            $retour["minutesBlessures"] = floor($retour["minutesBase"] / (4 * $totalPvSansBm)) * ($totalPvSansBm - $braldun->pv_restant_braldun);
            $retour["heureMinuteTotal"] = Bral_Util_ConvertDate::getHeureFromMinute($retour["minutesBase"] + $retour["minutesBlessures"] + $retour["minutesBM"]);
        } else {
            $retour["heureMinuteTotal"] = Bral_Util_ConvertDate::getHeureFromMinute($retour["minutesBase"] + $retour["minutesBM"]);
        }
        return $retour;
    }

    public static function updateTourTabac($braldun)
    {
        Zend_Loader::loadClass("BraldunsCompetences");

        $braldunsCompetencesTables = new BraldunsCompetences();
        $braldunCompetences = $braldunsCompetencesTables->findByIdBraldun($braldun->id_braldun);

        foreach ($braldunCompetences as $c) {
            if ($c["nb_tour_restant_bonus_tabac_hcomp"] > 0) {

                $nb = $c["nb_tour_restant_bonus_tabac_hcomp"] - 1;
                if ($nb < 0) {
                    $nb = 0;
                }
                $data = array('nb_tour_restant_bonus_tabac_hcomp' => $nb);
                $where = "id_fk_competence_hcomp = " . $c["id_fk_competence_hcomp"] . " AND id_fk_braldun_hcomp=" . $braldun->id_braldun;
                $braldunsCompetencesTables->update($data, $where);
            } else if ($c["nb_tour_restant_malus_tabac_hcomp"] > 0) {
                $nb = $c["nb_tour_restant_malus_tabac_hcomp"] - 1;
                if ($nb < 0) {
                    $nb = 0;
                }
                $data = array('nb_tour_restant_malus_tabac_hcomp' => $nb);
                $where = "id_fk_competence_hcomp = " . $c["id_fk_competence_hcomp"] . " AND id_fk_braldun_hcomp=" . $braldun->id_braldun;
                $braldunsCompetencesTables->update($data, $where);
            }
        }
    }
}