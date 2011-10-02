<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Communaute_Rangs extends Bral_Communaute_Communaute
{

    function getTitreOnglet()
    {
    }

    function setDisplay($display)
    {
        $this->view->display = $display;
    }

    function getTitre()
    {
        return null;
    }

    function getListBoxRefresh()
    {
        return array("box_communaute_evenements");
    }

    function prepareCommun()
    {
        Zend_Loader::loadClass("Communaute");
        Zend_Loader::loadClass("RangCommunaute");
        $this->preparePage();
        $this->updateRang();
    }

    function prepareFormulaire()
    {
    }

    function prepareResultat()
    {
    }

    function getNomInterne()
    {
        return "box_communaute_gestion_interne";
    }

    function preparePage()
    {
        Zend_Loader::loadClass('Bral_Util_Communaute');

        $communauteTable = new Communaute();
        $communauteRowset = $communauteTable->findById($this->view->user->id_fk_communaute_braldun);
        if (count($communauteRowset) == 1) {
            $communaute = $communauteRowset[0];
        }

        if ($this->view->user->rangCommunaute > Bral_Util_Communaute::ID_RANG_ADJOINT) {
            throw new Zend_Exception(get_class($this) . " Vos n'etes pas gestionnaire ou adjoint de la communauté");
        }
        if ($communaute == null) {
            throw new Zend_Exception(get_class($this) . " Communaute Invalide");
        }
        $this->communaute = $communaute;
    }

    function render()
    {
        $rangCommunauteTable = new RangCommunaute();
        $rangsCommunauteRowset = $rangCommunauteTable->findByIdCommunaute($this->communaute["id_communaute"]);
        $tabRangs = null;

        foreach ($rangsCommunauteRowset as $r) {
            $tabRangs[] = array(
                "id_rang" => $r["id_rang_communaute"],
                "nom" => $r["nom_rang_communaute"],
                "description" => $r["description_rang_communaute"],
                "ordre_rang_communaute" => $r["ordre_rang_communaute"],
            );
        }

        $this->view->tabRangs = $tabRangs;
        $this->view->nom_interne = $this->getNomInterne();
        return $this->view->render("interface/communaute/gerer/rangs.phtml");
    }

    private function updateRang()
    {
        if (($this->_request->get("caction") == "ask_communaute_rangs") && ($this->_request->get("valeur_1") != "") && ($this->_request->get("valeur_2") != "")) {
            $champ = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_1"));
            $idRang = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_2"));

            Zend_Loader::loadClass('Zend_Filter');
            Zend_Loader::loadClass('Zend_Filter_StripTags');
            Zend_Loader::loadClass('Zend_Filter_StringTrim');
            $filter = new Zend_Filter();
            $filter->addFilter(new Zend_Filter_StringTrim())->addFilter(new Zend_Filter_StripTags());
            $valeur = stripslashes($filter->filter($this->_request->get('valeur_3')));
        } else {
            return;
        }

        if ($champ == 1) {
            if (mb_strlen($valeur) > 40) {
                throw new Zend_Exception(get_class($this) . " Valeur invalide : valeur=" . $valeur);
            } else {
                $champSql = "nom_rang_communaute";
                $valeurSql = $valeur;
                $titre = "Titre";
            }
        } elseif ($champ == 2) {
            if (mb_strlen($valeur) > 200) {
                throw new Zend_Exception(get_class($this) . " Valeur invalide : valeur=" . $valeur);
            } else {
                $champSql = "description_rang_communaute";
                $valeurSql = $valeur;
                $titre = "Description";
            }
        } else {
            throw new Zend_Exception(get_class($this) . " Champ invalide : champ=" . $champ);
        }

        $rangCommunauteTable = new RangCommunaute();
        $rang = $rangCommunauteTable->findByIdRang($idRang);
        $rang = $rang[0];

        $titreAvant = $rang["nom_rang_communaute"];
        $descriptionAvant = $rang["description_rang_communaute"];
        $ordre = $rang["ordre_rang_communaute"];

        $data = array($champSql => $valeurSql);
        $where = " id_rang_communaute=" . intval($idRang);
        $where .= " AND id_fk_communaute_rang_communaute=" . $this->communaute["id_communaute"];
        $rangCommunauteTable->update($data, $where);

        $rang = $rangCommunauteTable->findByIdRang($idRang);
        $rang = $rang[0];
        $titreApres = $rang["nom_rang_communaute"];
        $descriptionApres = $rang["description_rang_communaute"];

        Zend_Loader::loadClass("TypeEvenementCommunaute");
        Zend_Loader::loadClass("Bral_Util_EvenementCommunaute");

        $details = "Rang n°" . $ordre . " (" . $titre . ")";
        $detailsBot = "Rang n°" . $ordre . PHP_EOL;

        if ($titre == "Description") {
            $detailsBot .= "Modification de la description du rang." . PHP_EOL;
        } else {
            $detailsBot .= "Modification du titre du rang." . PHP_EOL;
        }

        $detailsBot .= "Anciennes valeurs : " . PHP_EOL . "Titre:" . $titreAvant . PHP_EOL;
        $detailsBot .= "Description:" . $descriptionAvant . PHP_EOL . PHP_EOL;

        $detailsBot .= "Nouvelles valeurs : " . PHP_EOL . "Titre:" . $titreApres . PHP_EOL;
        $detailsBot .= "Description:" . $descriptionApres . PHP_EOL . PHP_EOL;

        $detailsBot .= PHP_EOL . "Action réalisée par [b" . $this->view->user->id_braldun . "]";
        Bral_Util_EvenementCommunaute::ajoutEvenements($this->view->user->id_fk_communaute_braldun, TypeEvenementCommunaute::ID_TYPE_RANG_LIBELLE, $details, $detailsBot, $this->view);

    }
}
