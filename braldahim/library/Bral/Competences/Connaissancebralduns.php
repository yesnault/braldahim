<?php
/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Connaissancebralduns extends Bral_Competences_Competence
{

    function prepareCommun()
    {
        Zend_Loader::loadClass("Bral_Util_Commun");

        /*
           * Si le Braldûn n'a pas de PA, on ne fait aucun traitement
           */
        $this->calculNbPa();
        if ($this->view->assezDePa == false) {
            return;
        }

        $vue_nb_cases = Bral_Util_Commun::getVueBase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun) + $this->view->user->vue_bm_braldun;
        $this->view->distance = $vue_nb_cases;

        if ($this->view->distance < 0) {
            $this->view->distance = 0;
        }

        $x_min = $this->view->user->x_braldun - $this->view->distance;
        $x_max = $this->view->user->x_braldun + $this->view->distance;
        $y_min = $this->view->user->y_braldun - $this->view->distance;
        $y_max = $this->view->user->y_braldun + $this->view->distance;

        // recuperation des monstres qui sont presents sur la vue
        $tabBralduns = null;
        $braldunTable = new Braldun();
        $bralduns = $braldunTable->selectVue($x_min, $y_min, $x_max, $y_max, $this->view->user->z_braldun, $this->view->user->id_braldun, false);

        foreach ($bralduns as $h) {
            $tab = array(
                'id_braldun' => $h["id_braldun"],
                'nom_braldun' => $h["nom_braldun"],
                'prenom_braldun' => $h["prenom_braldun"],
                'x_braldun' => $h["x_braldun"],
                'y_braldun' => $h["y_braldun"],
                'dist_braldun' => max(abs($h["x_braldun"] - $this->view->user->x_braldun), abs($h["y_braldun"] - $this->view->user->y_braldun))
            );
            $tabBralduns[] = $tab;
        }

        $this->view->tabBralduns = $tabBralduns;
        $this->view->nBralduns = count($tabBralduns);

    }

    function prepareFormulaire()
    {
        if ($this->view->assezDePa == false) {
            return;
        }
        if ($this->view->nBralduns > 0) {
            foreach ($this->view->tabBralduns as $key => $row) {
                $dist[$key] = $row['dist_braldun'];
            }
            array_multisort($dist, SORT_ASC, $this->view->tabBralduns);
        }
    }

    function prepareResultat()
    {

        // Verification des Pa
        if ($this->view->assezDePa == false) {
            throw new Zend_Exception(get_class($this) . " Pas assez de PA : " . $this->view->user->pa_braldun);
        }

        if (((int)$this->request->get("valeur_1") . "" != $this->request->get("valeur_1") . "")) {
            throw new Zend_Exception(get_class($this) . " Braldûn invalide : " . $this->request->get("valeur_1"));
        } else {
            $idBraldun = (int)$this->request->get("valeur_1");
        }

        $cdmBraldun = false;
        if (isset($this->view->tabBralduns) && count($this->view->tabBralduns) > 0) {
            foreach ($this->view->tabBralduns as $m) {
                if ($m["id_braldun"] == $idBraldun) {
                    $cdmBraldun = true;
                    $dist = $m["dist_braldun"];
                    $this->view->distance = $dist;
                    break;
                }
            }
        }
        if ($cdmBraldun === false) {
            throw new Zend_Exception(get_class($this) . " Braldûn invalide (" . $idBraldun . ")");
        }

        $this->calculJets();
        if ($this->view->okJet1 === true) {
            $this->calculCDM($idBraldun, $dist);
        }
        $this->calculPx();
        $this->calculBalanceFaim();
        $this->majBraldun();
    }

    private function calculCDM($idBraldun, $dist_braldun)
    {
        $braldunTable = new Braldun();
        $braldunRowset = $braldunTable->findById($idBraldun);
        $braldun = $braldunRowset->toArray();
        $tabCDM["id_braldun"] = $braldun["id_braldun"];
        $tabCDM["prenom_braldun"] = $braldun["prenom_braldun"];
        $tabCDM["nom_braldun"] = $braldun["nom_braldun"];
        $tabCDM["niveau_braldun"] = $braldun["niveau_braldun"];

        $tabCDM["max_vue_braldun"] = Bral_Util_Commun::getVueBase($braldun["x_braldun"], $braldun["y_braldun"], $braldun["z_braldun"]) + $braldun["vue_bm_braldun"];

        $tabCDM["max_deg_braldun"] = ($braldun["force_base_braldun"] + $this->view->config->game->base_force) * 6 + $braldun["force_bm_braldun"] + $braldun["force_bbdf_braldun"] + $braldun["bm_degat_braldun"];

        $tabCDM["max_att_braldun"] = ($braldun["agilite_base_braldun"] + $this->view->config->game->base_agilite) * 6 + $braldun["agilite_bm_braldun"] + $braldun["agilite_bbdf_braldun"] + $braldun["bm_attaque_braldun"];

        $tabCDM["max_def_braldun"] = ($braldun["agilite_base_braldun"] + $this->view->config->game->base_agilite) * 6 + $braldun["agilite_bm_braldun"] + $braldun["agilite_bbdf_braldun"] + $braldun["bm_defense_braldun"];

        $tabCDM["max_sag_braldun"] = ($braldun["sagesse_base_braldun"] + $this->view->config->game->base_sagesse) * 6 + $braldun["sagesse_bm_braldun"] + $braldun["sagesse_bbdf_braldun"];

        $tabCDM["max_vig_braldun"] = ($braldun["vigueur_base_braldun"] + $this->view->config->game->base_vigueur) * 6 + $braldun["vigueur_bm_braldun"] + $braldun["vigueur_bbdf_braldun"];

        $tabCDM["max_reg_braldun"] = $braldun["regeneration_braldun"] * 10 + $braldun["regeneration_bm_braldun"];

        $armureTotale = $braldun["armure_naturelle_braldun"] + $braldun["armure_equipement_braldun"] + $braldun["armure_bm_braldun"];
        if ($armureTotale < 0) {
            $armureTotale = 0;
        }
        $tabCDM["min_arm_braldun"] = floor($armureTotale - $armureTotale * (Bral_Util_De::get_1D10()) / 100);
        $tabCDM["max_arm_braldun"] = ceil($armureTotale + $armureTotale * (Bral_Util_De::get_1D10()) / 100);
        if ($tabCDM["max_arm_braldun"] == 0) {
            $tabCDM["max_arm_braldun"] = 1;
        }

        $tabCDM["min_pvmax_braldun"] = floor($braldun["pv_max_braldun"] - $braldun["pv_max_braldun"] * (Bral_Util_De::get_1D10()) / 100);
        $tabCDM["max_pvmax_braldun"] = ceil($braldun["pv_max_braldun"] + $braldun["pv_max_braldun"] * (Bral_Util_De::get_1D10()) / 100);

        $tabCDM["min_pvact_braldun"] = floor($braldun["pv_restant_braldun"] - $braldun["pv_restant_braldun"] * (Bral_Util_De::get_1D10()) / 100);
        $tabCDM["max_pvact_braldun"] = ceil($braldun["pv_restant_braldun"] + $braldun["pv_restant_braldun"] * (Bral_Util_De::get_1D10()) / 100);
        if ($tabCDM["max_pvact_braldun"] > $tabCDM["max_pvmax_braldun"]) {
            $tabCDM["max_pvact_braldun"] = $tabCDM["max_pvmax_braldun"];
        }
        if ($tabCDM["min_pvact_braldun"] > $tabCDM["min_pvmax_braldun"]) {
            $tabCDM["min_pvact_braldun"] = $tabCDM["min_pvmax_braldun"];
        }

        $duree_base_tour_minute = Bral_Util_ConvertDate::getMinuteFromHeure($braldun["duree_courant_tour_braldun"]);
        $tabCDM["min_dla_braldun"] = Bral_Util_ConvertDate::getHeureFromMinute($duree_base_tour_minute - floor($duree_base_tour_minute * (Bral_Util_De::get_1D10()) / 100));
        $tabCDM["max_dla_braldun"] = Bral_Util_ConvertDate::getHeureFromMinute($duree_base_tour_minute + ceil($duree_base_tour_minute * (Bral_Util_De::get_1D10()) / 100));

        $this->view->tabCDM = $tabCDM;

        $id_type = $this->view->config->game->evenements->type->competence;
        $details = "[b" . $this->view->user->id_braldun . "] a réussi l'utilisation d'une compétence sur [b" . $braldun["id_braldun"] . "]";
        $messageCible = $this->view->user->prenom_braldun . ' ' . $this->view->user->nom_braldun . ' a utilisé sa compétence connaissance des braldûns sur vous ! ' . PHP_EOL;
        $this->setDetailsEvenement($details, $id_type);
        $this->setDetailsEvenementCible($braldun["id_braldun"], "braldun", $braldun["niveau_braldun"], $messageCible);

    }

    function getListBoxRefresh()
    {
        return $this->constructListBoxRefresh(array("box_competences", "box_laban"));
    }
}
