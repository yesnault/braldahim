<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Communaute_Gerermembres extends Bral_Communaute_Communaute
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

    function prepareCommun()
    {
        Zend_Loader::loadClass("Communaute");
        Zend_Loader::loadClass("RangCommunaute");

        $this->view->message = null;
        $this->updateRangBraldun();
        $this->updateExclureBraldun();
    }

    function prepareFormulaire()
    {
    }

    function prepareResultat()
    {
    }

    function getListBoxRefresh()
    {
        return array("box_communaute_evenements");
    }

    function getNomInterne()
    {
        return "box_communaute_gestion_interne";
    }

    function anotherXmlEntry()
    {
        if ($this->view->message != null) {
            $xml_entry = new Bral_Xml_Entry();
            $xml_entry->set_type("action");
            $xml_entry->set_valeur("effect.disappear");
            $xml_entry->set_data("communaute_gerer_membre_update");
            return $xml_entry;
        } else {
            return null;
        }
    }


    public function getTablesHtmlTri()
    {
        $tab[] = "table-gerermembres";
        return $tab;
    }

    private function prepareRender()
    {
        $communaute = null;

        $communauteTable = new Communaute();
        $communauteRowset = $communauteTable->findById($this->view->user->id_fk_communaute_braldun);
        if (count($communauteRowset) == 1) {
            $communaute = $communauteRowset[0];
        }

        if ($communaute == null) {
            throw new Zend_Exception(get_class($this) . " Communaute Invalide");
        }

        $braldunTable = new Braldun();
        $braldunRowset = $braldunTable->findByIdCommunaute($communaute["id_communaute"]);

        $nbMembresTotal = count($braldunRowset);
        $tabMembres = null;

        foreach ($braldunRowset as $m) {
            $tabMembres[] = array(
                "id_braldun" => $m["id_braldun"],
                "nom_braldun" => $m["nom_braldun"],
                "prenom_braldun" => $m["prenom_braldun"],
                "sexe_braldun" => $m["sexe_braldun"],
                "niveau_braldun" => $m["niveau_braldun"],
                "date_entree" => $m["date_entree_communaute_braldun"],
                "id_rang_communaute" => $m["id_rang_communaute"],
                "nom_rang_communaute" => $m["nom_rang_communaute"],
                "ordre_rang_communaute" => $m["ordre_rang_communaute"],
            );
        }

        $rangCommunauteTable = new RangCommunaute();
        $rangsCommunauteRowset = $rangCommunauteTable->findByIdCommunaute($communaute["id_communaute"]);
        $tabRangs = null;

        foreach ($rangsCommunauteRowset as $r) {
            if ($r["ordre_rang_communaute"] > Bral_Util_Communaute::ID_RANG_GESTIONNAIRE) {
                $tabRangs[] = array(
                    "id_rang" => $r["id_rang_communaute"],
                    "nom" => $r["nom_rang_communaute"],
                    "ordre_rang_communaute" => $r["ordre_rang_communaute"],
                );
            }
        }

        $this->view->tabRangs = $tabRangs;
        $this->view->tabMembres = $tabMembres;
        $this->view->nbMembresTotal = $nbMembresTotal;
        $this->view->nom_interne = $this->getNomInterne();
    }

    public function render()
    {
        $this->prepareRender();
        return $this->view->render("interface/communaute/gerer/membres.phtml");
    }

    private function updateRangBraldun()
    {
        if ($this->_request->get("valeur_1") == "r") {
            $this->prepareRender();

            $idBraldun = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_7"));
            $idRangBraldun = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_8"));

            $braldunTrouve = false;
            foreach ($this->view->tabMembres as $m) {
                if ($m["id_braldun"] == $idBraldun && $m["ordre_rang_communaute"] != 1) { // le gestionnaire ne peut pas etre modifie
                    $braldunTrouve = true;
                    $ordreAncienRang = $m["ordre_rang_communaute"];
                    $sexe = $m["sexe_braldun"];
                    break;
                }
            }
            if ($braldunTrouve == false) {
                throw new Zend_Exception(get_class($this) . " Braldûn invalide : val=" . $idBraldun);
            }

            $rangTrouve = false;
            foreach ($this->view->tabRangs as $r) {
                if ($r["id_rang"] == $idRangBraldun) {
                    $rangTrouve = true;
                    $nouveauRang = $r;
                    break;
                }
            }
            if ($rangTrouve == false) {
                throw new Zend_Exception(get_class($this) . " rang invalide : val=" . $idRangBraldun);
            }

            $braldunTable = new Braldun();
            $data = array('id_fk_rang_communaute_braldun' => $idRangBraldun);
            $where = 'id_braldun = ' . $idBraldun;
            $braldunTable->update($data, $where);

            $this->view->message = "Modification du Braldûn " . $idBraldun . " effectuée";

            $message = "[Ceci est un message automatique de communauté]" . PHP_EOL;
            $message .= " Votre rang de communauté a été modifié !" . PHP_EOL;
            $message .= " Nouveau rang : " . $nouveauRang['nom'] . ' n°' . $nouveauRang['ordre_rang_communaute'];
            Bral_Util_Messagerie::envoiMessageAutomatique($this->view->user->id_braldun, $idBraldun, $message, $this->view);

            Zend_Loader::loadClass("TypeEvenementCommunaute");
            Zend_Loader::loadClass("Bral_Util_EvenementCommunaute");

            $details = "[b" . $idBraldun . "]";
            $detailsBot = "";

            if ($sexe == "feminin") {
                $pronom = "Elle";
                $e = "e";
            } else {
                $pronom = "Il";
                $e = "";
            }

            if ($ordreAncienRang == Bral_Util_Communaute::ID_RANG_NOUVEAU) {
                $type = TypeEvenementCommunaute::ID_TYPE_ACCEPTATION_MEMBRE;
                $detailsBot .= "[b" . $idBraldun . "] est accepté" . $e . " dans la communauté." . PHP_EOL . PHP_EOL;
            } else {
                $type = TypeEvenementCommunaute::ID_TYPE_RANG_MEMBRE;
                $detailsBot .= "[b" . $idBraldun . "] a changé de rang." . PHP_EOL . PHP_EOL;
            }

            $detailsBot .= $pronom . " occupe le rang  n°" . $nouveauRang['ordre_rang_communaute'] . ' : ' . $nouveauRang['nom'] . "." . PHP_EOL . PHP_EOL;
            $detailsBot .= "Action réalisée par [b" . $this->view->user->id_braldun . "]";

            Bral_Util_EvenementCommunaute::ajoutEvenements($this->view->user->id_fk_communaute_braldun, $type, $details, $detailsBot, $this->view);

        }
    }

    private function updateExclureBraldun()
    {
        if ($this->_request->get("valeur_1") == "e") {
            $this->prepareRender();

            $idBraldun = Bral_Util_Controle::getValeurIntVerif($this->_request->get("valeur_9"));

            $braldunTrouve = false;
            foreach ($this->view->tabMembres as $m) {
                if ($m["id_braldun"] == $idBraldun && $m["ordre_rang_communaute"] != 1) { // le gestionnaire ne peut pas etre exclu
                    $braldunTrouve = true;
                    $sexe = $m["sexe_braldun"];
                    break;
                }
            }
            if ($braldunTrouve == false) {
                throw new Zend_Exception(get_class($this) . " Braldûn invalide : val=" . $idBraldun);
            }

            $braldunTable = new Braldun();
            $data = array(
                'id_fk_communaute_braldun' => null,
                'id_fk_rang_communaute_braldun' => null,
                'date_entree_communaute_braldun' => null,
            );
            $where = 'id_braldun = ' . $idBraldun;
            $braldunTable->update($data, $where);

            $this->view->message = "Exclusion du Braldûn " . $idBraldun . " effectuée";

            Zend_Loader::loadClass("TypeEvenementCommunaute");
            Zend_Loader::loadClass("Bral_Util_EvenementCommunaute");

            $details = "Exclusion : [b" . $idBraldun . "]";
            $detailsBot = "[b" . $idBraldun . "] a été exclu";

            if ($sexe == "feminin") {
                $detailsBot .= "e";
            }

            $detailsBot .= " de la communauté." . PHP_EOL . PHP_EOL;

            $detailsBot .= "Action réalisée par [b" . $this->view->user->id_braldun . "]";
            Bral_Util_EvenementCommunaute::ajoutEvenements($this->view->user->id_fk_communaute_braldun, TypeEvenementCommunaute::ID_TYPE_DEPART_MEMBRE, $details, $detailsBot, $this->view);
        }
    }
}
