<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Lieux_Hopital extends Bral_Lieux_Lieu
{

    private $_utilisationPossible = false;
    private $_coutCastars = null;

    function prepareCommun()
    {
        Zend_Loader::loadClass("Lieu");

        $this->_coutCastars = $this->calculCoutCastars();
        $this->_utilisationPossible = (($this->view->user->castars_braldun - $this->_coutCastars) >= 0);
        $this->view->recupPossible = false;

        if ($this->view->user->pv_restant_braldun < $this->view->user->pv_max_braldun + $this->view->user->pv_max_bm_braldun) {
            $this->view->recupPossible = true;
        }

    }

    function prepareFormulaire()
    {
        $this->view->utilisationPossible = $this->_utilisationPossible;
        $this->view->coutCastars = $this->_coutCastars;
    }

    function prepareResultat()
    {

        // verification qu'il y a assez de castars
        if ($this->_utilisationPossible == false) {
            throw new Zend_Exception(get_class($this) . " Achat impossible : castars:" . $this->view->user->castars_braldun . " cout:" . $this->_coutCastars);
        }

        if ($this->view->recupPossible == false) {
            throw new Zend_Exception("Bral_Lieux_Hopital :: Nombre PV full");
        }

        $this->view->jetRegeneration = 0;
        Zend_Loader::loadClass("Bral_Util_Vie");
        Bral_Util_Vie::calculRegenerationBraldun(&$this->view->user, &$this->view->jetRegeneration);

        $this->view->user->castars_braldun = $this->view->user->castars_braldun - $this->_coutCastars;

        $this->majBraldun();
        $this->view->coutCastars = $this->_coutCastars;
    }

    function getListBoxRefresh()
    {
        return $this->constructListBoxRefresh(array("box_laban"));
    }

    private function calculCoutCastars()
    {
        return 10;
    }
}