<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Charrette_Partage extends Bral_Charrette_Charrette
{

    function getNomInterne()
    {
        return "box_action";
    }

    function getTitreAction()
    {
        return "Charrette : Gestion des partages";
    }

    function prepareCommun()
    {
        Zend_Loader::loadClass("Charrette");
        Zend_Loader::loadClass("CharrettePartage");

        $tabCharrettes = null;
        $this->view->possedeCharrette = false;
        $this->view->attraperCharrettePossible = false;

        $charretteTable = new Charrette();

        $charretteRowset = $charretteTable->findByIdBraldun($this->view->user->id_braldun);
        if (count($charretteRowset) == 1) {
            $charrette = $charretteRowset[0];
            $this->view->possedeCharrette = true;
        } else {
            return;
        }
        $this->view->charrette = $charrette;

        $charrettePartageTable = new CharrettePartage();
        $partages = $charrettePartageTable->findByIdCharrette($charrette["id_charrette"]);
        $listBralduns = null;
        if (count($partages) > 0) {
            foreach ($partages as $b) {
                $listBralduns .= $b["id_fk_braldun_charrette_partage"] . ",";
            }
        }

        $tabBralduns["aff_js_destinataires"] = "";
        $tabBralduns["destinataires"] = "";
        if ($listBralduns != null) {
            $tabBralduns = Bral_Util_Messagerie::constructTabBraldun($listBralduns, "valeur_3_partage");
        }
        $this->view->tabBralduns = $tabBralduns;
    }

    function prepareFormulaire()
    {
    }

    function prepareResultat()
    {
        if ($this->view->possedeCharrette == false) {
            throw new Zend_Exception(get_class($this) . " charrette invalide ");
        }

        if ($this->request->get("valeur_1") != "oui" && $this->request->get("valeur_1") != "non") {
            throw new Zend_Exception(get_class($this) . " Charrette valeur_1 invalide : " . $this->request->get("valeur_1"));
        } else {
            $partageCommunaute = $this->request->get("valeur_1");
        }

        if ($this->request->get("valeur_2") != "oui" && $this->request->get("valeur_2") != "non") {
            throw new Zend_Exception(get_class($this) . " Charrette valeur_2 invalide : " . $this->request->get("valeur_2"));
        } else {
            $partageBralduns = $this->request->get("valeur_2");
        }

        $this->calculPartage($partageCommunaute, $partageBralduns);
        $this->calculBalanceFaim();
        $this->setEstEvenementAuto(false);
        $this->setEstAvecPa(false);
    }

    private function calculPartage($partageCommunaute, $partageBralduns)
    {
        $charretteTable = new Charrette();

        $data = array(
            'est_partage_communaute_charrette' => $partageCommunaute,
            'est_partage_bralduns_charrette' => $partageBralduns,
        );
        $where = "id_charrette = " . $this->view->charrette["id_charrette"];
        $charretteTable->update($data, $where);

        $charrettePartageTable = new CharrettePartage();

        $where = "id_fk_charrette_partage = " . $this->view->charrette["id_charrette"];
        $charrettePartageTable->delete($where);

        $bralduns = $this->recupereBraldunFromValeur3();

        if (count($bralduns) > 0) {
            foreach ($bralduns as $b) {
                $data = array(
                    'id_fk_charrette_partage' => $this->view->charrette["id_charrette"],
                    'id_fk_braldun_charrette_partage' => $b["id_braldun"],
                );
                $charrettePartageTable->insert($data);
            }
        }

    }

    private function recupereBraldunFromValeur3()
    {
        Zend_Loader::loadClass('Zend_Filter_StripTags');
        $filter = new Zend_Filter_StripTags();
        $braldunsList = $filter->filter(trim($this->request->get('valeur_3')));

        $idBraldunsTab = preg_split("/,/", $braldunsList);

        $braldunTable = new Braldun();
        $bralduns = $braldunTable->findByIdList($idBraldunsTab);

        return $bralduns;
    }

    function getListBoxRefresh()
    {
        $tab = array("box_charrette");
        return $this->constructListBoxRefresh($tab);
    }
}
