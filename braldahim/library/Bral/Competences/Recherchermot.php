<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Recherchermot extends Bral_Competences_Competence
{

    function prepareCommun()
    {
        Zend_Loader::loadClass('LabanRune');
        Zend_Loader::loadClass('Bral_Helper_Communaute');

        // on verifie que le Braldûn possede au moins une rune

        $this->view->rechercherMotOk = false;

        Zend_Loader::loadClass("MotRunique");
        $motRuniqueTable = new MotRunique();
        $motsRuniques = $motRuniqueTable->fetchAll(null, "suffixe_mot_runique ASC");

        $tabMotsRuniques = null;
        $longueurMax = floor($this->view->user->sagesse_base_braldun / 2);

        $motCourant = null;
        $id_mot_courant = (int)$this->request->get("id_mot");

        foreach ($motsRuniques as $mot) {
            $longueur = $this->calculLongeurMot($mot);
            if ($this->calculLongeurMot($mot) <= $longueurMax) {
                $selected = "";
                if ($id_mot_courant == $mot["id_mot_runique"]) {
                    $selected = "selected";
                }

                $tabMotsRuniques[$mot["id_mot_runique"]] = array(
                    "id_mot_runique" => $mot["id_mot_runique"],
                    "suffixe_mot_runique" => $mot["suffixe_mot_runique"],
                    "id_fk_type_rune_1_mot_runique" => $mot["id_fk_type_rune_1_mot_runique"],
                    "id_fk_type_rune_2_mot_runique" => $mot["id_fk_type_rune_2_mot_runique"],
                    "id_fk_type_rune_3_mot_runique" => $mot["id_fk_type_rune_3_mot_runique"],
                    "id_fk_type_rune_4_mot_runique" => $mot["id_fk_type_rune_4_mot_runique"],
                    "id_fk_type_rune_5_mot_runique" => $mot["id_fk_type_rune_5_mot_runique"],
                    "id_fk_type_rune_6_mot_runique" => $mot["id_fk_type_rune_6_mot_runique"],
                    "nb_runes" => $longueur,
                    "selected" => $selected,
                );

                if ($id_mot_courant == $mot["id_mot_runique"]) {
                    $motCourant = $tabMotsRuniques[$mot["id_mot_runique"]];
                }
            }
        }

        $tabRunes = null;
        $niveauAtelier = null;

        Zend_Loader::loadClass("Lieu");
        Zend_Loader::loadClass("TypeLieu");
        Zend_Loader::loadClass("Bral_Util_Communaute");

        $lieuxTable = new Lieu();
        $lieuRowset = null;
        if ($this->view->user->id_fk_communaute_braldun != null) {
            $lieuRowset = $lieuxTable->findByIdCommunaute($this->view->user->id_fk_communaute_braldun, $this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, false, TypeLieu::ID_TYPE_ATELIER, Bral_Util_Communaute::NIVEAU_ATELIER_RECHERCHE);
        }
        if ($lieuRowset != null && count($lieuRowset) == 1) {

            $niveauAtelier = $lieuRowset[0]["niveau_lieu"];

            Zend_Loader::loadClass("Coffre");
            Zend_Loader::loadClass("CoffreRune");

            $coffreTable = new Coffre();
            $coffre = $coffreTable->findByIdCommunaute($this->view->user->id_fk_communaute_braldun);
            if ($coffre == null || count($coffre) != 1) {
                throw new Zend_Exception("Erreur Coffre Communaute : " . $this->view->user->id_fk_communaute_braldun);
            } else {
                $coffre = $coffre[0];
            }

            $coffreRuneTable = new CoffreRune();
            $coffreRunes = $coffreRuneTable->findByIdCoffre($coffre["id_coffre"], "oui");

            foreach ($coffreRunes as $l) {
                $tabRunes[$l["id_rune_coffre_rune"]] = array(
                    "id_fk_type_rune" => $l["id_fk_type_rune"],
                    "nom_type_rune" => $l["nom_type_rune"],
                    "image_type_rune" => $l["image_type_rune"],
                    "effet_type_rune" => $l["effet_type_rune"],
                    "id_rune_coffre_rune" => $l["id_rune_coffre_rune"],
                );
                $this->view->rechercherMotOk = true;
            }
        } else { // Rune dans le laban uniquement

            Zend_Loader::loadClass("LabanRune");
            $labanRuneTable = new LabanRune();
            $labanRunes = $labanRuneTable->findByIdBraldun($this->view->user->id_braldun, "oui");

            foreach ($labanRunes as $l) {
                $tabRunes[$l["id_rune_laban_rune"]] = array(
                    "id_fk_type_rune" => $l["id_fk_type_rune"],
                    "nom_type_rune" => $l["nom_type_rune"],
                    "image_type_rune" => $l["image_type_rune"],
                    "effet_type_rune" => $l["effet_type_rune"],
                    "id_rune_laban_rune" => $l["id_rune_laban_rune"],
                );
                $this->view->rechercherMotOk = true;
            }
        }


        $this->view->nbRune = count($tabRunes);
        $this->view->runes = $tabRunes;

        $this->view->motsRuniques = $tabMotsRuniques;
        $this->view->motCourant = $motCourant;
        $this->view->niveauAtelier = $niveauAtelier;
    }

    private function calculLongeurMot($mot)
    {
        $longueur = 100;
        if ($mot["id_fk_type_rune_1_mot_runique"] != null &&
                $mot["id_fk_type_rune_2_mot_runique"] != null &&
                $mot["id_fk_type_rune_3_mot_runique"] != null &&
                $mot["id_fk_type_rune_4_mot_runique"] != null &&
                $mot["id_fk_type_rune_5_mot_runique"] != null &&
                $mot["id_fk_type_rune_6_mot_runique"] != null
        ) {
            $longueur = 6;
        } else if ($mot["id_fk_type_rune_5_mot_runique"] != null) {
            $longueur = 5;
        } else if ($mot["id_fk_type_rune_4_mot_runique"] != null) {
            $longueur = 4;
        } else if ($mot["id_fk_type_rune_3_mot_runique"] != null) {
            $longueur = 3;
        } else if ($mot["id_fk_type_rune_2_mot_runique"] != null) {
            $longueur = 2;
        } else {
            $longueur = 1;
        }
        return $longueur;
    }

    function prepareFormulaire()
    {
        if ($this->view->assezDePa == false) {
            return;
        }
    }

    function prepareResultat()
    {
        if ($this->view->assezDePa == false) {
            throw new Zend_Exception(get_class($this) . " Pas assez de PA : " . $this->view->user->pa_braldun);
        }

        if ($this->view->rechercherMotOk == false) {
            throw new Zend_Exception(get_class($this) . " Identifier Rune : pas de rune");
        }

        $idMot = $this->request->get("valeur_1");
        $nbRunes = $this->request->get("valeur_2");
        $runes = $this->request->get("valeur_3");

        if ((int)$idMot . "" != $this->request->get("valeur_1") . "") {
            throw new Zend_Exception(get_class($this) . " Mot invalide=" . $idMot);
        } else {
            $idMot = (int)$idMot;
        }

        if (!array_key_exists($idMot, $this->view->motsRuniques)) {
            throw new Zend_Exception(get_class($this) . " idMot interdit A=" . $idMot);
        }

        if ($this->view->motCourant["id_mot_runique"] != $idMot) {
            throw new Zend_Exception(get_class($this) . " mot courant invalide:a:" . $this->view->motCourant["id_mot_runique"] . "m:" . $idMot);
        }

        $motRecherche = $this->view->motsRuniques[$idMot];

        if ((int)$nbRunes . "" != $this->request->get("valeur_2") . "") {
            throw new Zend_Exception(get_class($this) . " Nb Rune invalide=" . $nbRunes);
        } else {
            $nbRunes = (int)$nbRunes;
        }

        if ($runes == "" || $runes == null) {
            throw new Zend_Exception(get_class($this) . " Runes invalides=" . $runes);
        }

        $runes = substr($runes, 0, strlen($runes) - 1); // suppression de la virgule
        $tabRunesJs = explode(",", $runes);
        $tabRunes = null;

        // on regarde si les runes sont présentes dans le laban
        $tmp = $this->view->runes;
        $nb = 0;
        foreach ($tabRunesJs as $u) {
            $trouve = false;
            foreach ($tmp as $k => $r) {
                if ((int)$u == $k) {
                    $nb++;
                    $tabRunes[$nb] = $r;
                    $trouve = true;
                    break;
                }
            }
            if ($trouve == false) {
                throw new Zend_Exception(get_class($this) . " Rune invalide =" . $u);
            }
        }

        if ($nb != $nbRunes) {
            throw new Zend_Exception(get_class($this) . " Nombre de runes invalides A n1=" . $nb . " n2=" . $nbRunes);
        }

        if ($nb != $motRecherche["nb_runes"]) {
            throw new Zend_Exception(get_class($this) . " Nombre de runes invalides B n1=" . $nb . " n2=" . $motRecherche["nb_runes"]);
        }

        // calcul des jets
        $this->calculJets();

        if ($this->view->okJet1 === true) {
            $this->calculRecherchermot($motRecherche, $tabRunes);
        }

        $this->calculPx();
        $this->calculBalanceFaim();
        $this->majBraldun();
    }

    private function calculRecherchermot($motRecherche, $tabRunes)
    {

        $ordre = 0;
        $nbPositionOk = 0;
        $nbOkMalPlace = 0;
        foreach ($tabRunes as $k => $v) {
            $ordre++;
            if ($motRecherche["id_fk_type_rune_" . $ordre . "_mot_runique"] == null) {
                break;
            }

            if ($motRecherche["id_fk_type_rune_" . $ordre . "_mot_runique"] == $v["id_fk_type_rune"]) {
                $nbPositionOk++;
            } else {
                for ($i = 1; $i <= 6; $i++) {
                    if ($motRecherche["id_fk_type_rune_" . $i . "_mot_runique"] != null && $motRecherche["id_fk_type_rune_" . $i . "_mot_runique"] == $v["id_fk_type_rune"]) {
                        $nbOkMalPlace++;
                    }
                }
            }
        }

        $this->view->nbPositionOk = $nbPositionOk;
        $this->view->nbOkMalPlace = $nbOkMalPlace;

        $this->view->tabRunes = $tabRunes;
        $this->view->nbRunes = count($this->view->tabRunes);
    }

    function getListBoxRefresh()
    {
        return $this->constructListBoxRefresh(array("box_competences"));
    }
}
