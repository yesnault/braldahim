<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Evenements extends Bral_Box_Box
{

    function getTitreOnglet()
    {
        return "&Eacute;v&eacute;nements";
    }

    function getNomInterne()
    {
        return "box_evenements";
    }

    function getChargementInBoxes()
    {
        return false;
    }

    function setDisplay($display)
    {
        $this->view->display = $display;
    }

    function render()
    {
        if ($this->view->affichageInterne) {
            Zend_Loader::loadClass('Evenement');
            Zend_Loader::loadClass('TypeEvenement');
            Zend_Loader::loadClass('Bral_Util_ConvertDate');

            $this->preparePage();
            $this->prepareRender();
            if ($this->_request->get("box") == "box_evenements") {
                $this->prepareDetails();
            }
        }
        $this->view->nom_interne = $this->getNomInterne();
        return $this->view->render("interface/evenements.phtml");
    }

    private function prepareRender()
    {
        $suivantOk = false;
        $precedentOk = false;
        $tabEvenements = null;
        $tabTypeEvenements = null;
        $evenementTable = new Evenement();
        $evenements = $evenementTable->findByIdBraldun($this->view->user->id_braldun, $this->_page, $this->_nbMax, $this->_filtre);
        unset($evenementTable);

        foreach ($evenements as $p) {
            $tabEvenements[] = array(
                "id_evenement" => $p["id_evenement"],
                "type" => $p["nom_type_evenement"],
                "date" => Bral_Util_ConvertDate::get_datetime_mysql_datetime('\l\e d/m/y \&\a\g\r\a\v\e; H:i:s', $p["date_evenement"]),
                "details" => $p["details_evenement"],
                "details_bot" => $p["details_bot_evenement"],
            );
        }
        unset($evenements);

        $typeEvenementTable = new TypeEvenement();
        $typeEvenements = $typeEvenementTable->fetchall();
        unset($typeEvenementTable);

        $tabTypeEvenements[] = array(
            "id_type_evenement" => -1,
            "nom" => "(Tous)"
        );
        foreach ($typeEvenements as $t) {
            $tabTypeEvenements[] = array(
                "id_type_evenement" => $t->id_type_evenement,
                "nom" => $t->nom_type_evenement
            );
        }
        unset($typeEvenements);

        if ($this->_page == 1) {
            $precedentOk = false;
        } else {
            $precedentOk = true;
        }

        if (count($tabEvenements) == 0 || count($tabEvenements) < $this->_nbMax) {
            $suivantOk = false;
        } else {
            $suivantOk = true;
        }

        $this->view->precedentOk = $precedentOk;
        $this->view->suivantOk = $suivantOk;
        $this->view->evenements = $tabEvenements;
        $this->view->typeEvenements = $tabTypeEvenements;
        $this->view->nbEvenements = count($this->view->evenements);

        $this->view->page = $this->_page;
        $this->view->filtre = $this->_filtre;

        unset($precedentOk);
        unset($suivantOk);
        unset($tabEvenements);
        unset($tabTypeEvenements);
    }

    private function prepareDetails()
    {
        $this->view->evenement = null;
        $idEvenement = -1;
        if ($this->_request->get("valeur_5") != null) {
            $idEvenement = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_5"));
        } else {
            return;
        }

        $trouve = false;
        foreach ($this->view->evenements as $t) {
            if ($t["id_evenement"] == $idEvenement) {
                $this->view->evenement = $t;
                $trouve = true;
            }
        }

        if ($trouve == false) {
            throw new Zend_Exception(get_class($this) . " Evenement invalide:" . $idEvenement);
        }
    }

    private function preparePage()
    {
        $this->_page = 1;
        if (($this->_request->get("box") == "box_evenements") && ($this->_request->get("valeur_1") == "f")) {
            $this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_2"));
        } else if (($this->_request->get("box") == "box_evenements") && ($this->_request->get("valeur_1") == "p")) {
            $this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) - 1;
            $this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
        } else if (($this->_request->get("box") == "box_evenements") && ($this->_request->get("valeur_1") == "s")) {
            $this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3")) + 1;
            $this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
        } else if (($this->_request->get("box") == "box_evenements") && ($this->_request->get("valeur_1") == "d")) {
            $this->_page = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_3"));
            $this->_filtre = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_4"));
        } else {
            $this->_page = 1;
            $this->_filtre = -1;
        }

        if ($this->_page < 1) {
            $this->_page = 1;
        }
        $this->_nbMax = $this->view->config->game->evenements->nb_affiche;
    }
}
