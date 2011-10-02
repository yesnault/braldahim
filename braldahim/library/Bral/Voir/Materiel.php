<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Voir_Materiel
{

    function __construct($request, $view)
    {
        Zend_Loader::loadClass("Materiel");
        Zend_Loader::loadClass("HistoriqueMateriel");
        Zend_Loader::loadClass("TypeHistoriqueMateriel");
        Zend_Loader::loadClass("Bral_Util_Materiel");
        Zend_Loader::loadClass("Bral_Helper_DetailMateriel");

        $this->_request = $request;
        $this->view = $view;
    }

    function getNomInterne()
    {
        return "box_voir_materiel_inner";
    }

    function setDisplay($display)
    {
        $this->view->display = $display;
    }

    function render()
    {
        $this->view->materiel = null;
        $this->view->connu = false;

        $val = $this->_request->get("materiel");
        if ($val != "" && ((int)$val . "" == $val . "")) {
            return $this->renderData();
        } else {
            $this->view->flux = $this->view->render("voir/materiel/profil.phtml");
            ;
            return $this->view->render("voir/materiel.phtml");
        }
    }

    private function renderData()
    {
        $materielTable = new Materiel();
        $idMateriel = Bral_Util_Controle::getValeurIntVerif($this->_request->get("materiel"));
        $materielRowset = $materielTable->findByIdMaterielWithDetails($idMateriel);
        if (count($materielRowset) == 1) {
            $this->view->materiel = $this->prepareMateriel($materielRowset[0]);
            $this->view->connu = true;
        }

        if ($this->_request->get("menu") == "historique" && $this->view->connu != null) {
            return $this->renderHistorique();
        } else {
            if ($this->_request->get("direct") == "historique") {
                $flux = $this->renderHistorique();
            } else {
                $flux = $this->view->render("voir/materiel/profil.phtml");
            }
            $this->view->flux = $flux;
            return $this->view->render("voir/materiel.phtml");
        }
    }

    private function prepareMateriel($p)
    {
        $materiel = array(
            "id_materiel" => $p["id_materiel"],
            "id_type_materiel" => $p["id_type_materiel"],
            "nom" => $p["nom_type_materiel"],
            'capacite' => $p["capacite_type_materiel"],
            'durabilite' => $p["durabilite_type_materiel"],
            'usure' => $p["usure_type_materiel"],
            'poids' => $p["poids_type_materiel"],
        );
        return $materiel;
    }

    function renderHistorique()
    {
        Zend_Loader::loadClass("Bral_Util_Materiel");

        if ($this->view->user != null && $this->view->user->id_braldun != null) {
            $this->view->possede = Bral_Util_Materiel::possedeMateriel($this->view->user->id_braldun, $this->view->materiel["id_materiel"]);
        } else {
            $this->view->possede = false;
        }

        $this->preparePage();

        $suivantOk = false;
        $precedentOk = false;
        $tabHistorique = null;
        $tabTypeHistorique = null;
        $historiqueMaterielTable = new HistoriqueMateriel();
        $historiqueMateriels = $historiqueMaterielTable->findByIdMateriel($this->view->materiel["id_materiel"], $this->_page, $this->_nbMax, $this->_filtre);

        foreach ($historiqueMateriels as $p) {
            $tabHistorique[] = array(
                "type" => $p["nom_type_historique_materiel"],
                "date" => Bral_Util_ConvertDate::get_datetime_mysql_datetime('\l\e d/m/y \&\a\g\r\a\v\e; H:i:s', $p["date_historique_materiel"]),
                "details" => $p["details_historique_materiel"],
            );
        }

        $typeHistoriqueMaterielTable = new TypeHistoriqueMateriel();
        $typeHistoriqueMateriel = $typeHistoriqueMaterielTable->fetchall(null, array("nom_type_historique_materiel"));

        $tabTypeHistorique[] = array(
            "id_type_historique" => -1,
            "nom" => "(Tous)");

        foreach ($typeHistoriqueMateriel as $t) {
            $tabTypeHistorique[] = array(
                "id_type_historique" => $t->id_type_historique_materiel,
                "nom" => $t->nom_type_historique_materiel
            );
        }

        if ($this->_page == 1) {
            $precedentOk = false;
        } else {
            $precedentOk = true;
        }

        if (count($tabHistorique) == 0 || count($tabHistorique) < $this->_nbMax) {
            $suivantOk = false;
        } else {
            $suivantOk = true;
        }

        $this->view->precedentOk = $precedentOk;
        $this->view->suivantOk = $suivantOk;
        $this->view->historique = $tabHistorique;
        $this->view->typeHistorique = $tabTypeHistorique;
        $this->view->nbHistorique = count($this->view->historique);

        $this->view->nom_interne = $this->getNomInterne();
        $this->view->page = $this->_page;
        $this->view->filtre = $this->_filtre;
        return $this->view->render("voir/materiel/historique.phtml");
    }

    private function preparePage()
    {
        $this->_page = 1;
        if (($this->_request->get("caction") == "ask_voir_materiel") && ($this->_request->get("valeur_1") == "f")) {
            $this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_2"));
        } else if (($this->_request->get("caction") == "ask_voir_materiel") && ($this->_request->get("valeur_1") == "p")) { // si le joueur a clique sur une icone
            $this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) - 1;
            $this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
        } else if (($this->_request->get("caction") == "ask_voir_materiel") && ($this->_request->get("valeur_1") == "s")) {
            $this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) + 1;
            $this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
        } else {
            $this->_page = 1;
            $this->_filtre = -1;
        }

        if ($this->_page < 1) {
            $this->_page = 1;
        }
        $this->_nbMax = $this->view->config->game->historique->materiel->nb_affiche;
    }
}
