<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Charrette_Sabot extends Bral_Charrette_Charrette
{

    function getNomInterne()
    {
        return "box_action";
    }

    function getTitreAction()
    {
        return "Charrette : Configuration du sabot";
    }

    function prepareCommun()
    {
        Zend_Loader::loadClass("Charrette");
        Zend_Loader::loadClass("Bral_Util_Charrette");

        $tabCharrettes = null;
        $this->view->possedeCharrette = false;
        $this->view->possedeSabot = false;

        $charretteTable = new Charrette();

        $charretteRowset = $charretteTable->findByIdBraldun($this->view->user->id_braldun);
        if (count($charretteRowset) == 1) {
            $charrette = $charretteRowset[0];
            $this->view->possedeCharrette = true;

            if (Bral_Util_Charrette::possedeSabot($this->view->charrette["id_charrette"])) {
                $this->view->possedeSabot = true;
            }

        } else {
            return;
        }
        $this->view->charrette = $charrette;

        $tabChiffres = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
        $this->view->chiffres = $tabChiffres;

    }

    function prepareFormulaire()
    {
    }

    function prepareResultat()
    {
        if ($this->view->possedeCharrette == false) {
            throw new Zend_Exception(get_class($this) . " charrette invalide ");
        }

        if ($this->view->possedeSabot == false) {
            throw new Zend_Exception(get_class($this) . " sabot invalide ");
        }

        Zend_Loader::loadClass("Bral_Util_Controle");

        $chiffre_1 = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_1"));
        $chiffre_2 = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_2"));
        $chiffre_3 = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_3"));
        $chiffre_4 = Bral_Util_Controle::getValeurIntVerif($this->request->get("valeur_4"));

        $this->calculSabot($chiffre_1, $chiffre_2, $chiffre_3, $chiffre_4);
        $this->calculBalanceFaim();
        $this->setEstEvenementAuto(false);
        $this->setEstAvecPa(false);
    }

    private function calculSabot($chiffre_1, $chiffre_2, $chiffre_3, $chiffre_4)
    {
        $charretteTable = new Charrette();

        $data = array(
            'sabot_1_charrette' => $chiffre_1,
            'sabot_2_charrette' => $chiffre_2,
            'sabot_3_charrette' => $chiffre_3,
            'sabot_4_charrette' => $chiffre_4,
        );
        $where = "id_charrette = " . $this->view->charrette["id_charrette"];
        $charretteTable->update($data, $where);


    }

    function getListBoxRefresh()
    {
        return $this->constructListBoxRefresh();
    }
}
