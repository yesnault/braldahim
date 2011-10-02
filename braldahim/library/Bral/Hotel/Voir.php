<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Hotel_Voir extends Bral_Hotel_Hotel
{

    function getNomInterne()
    {
        return $this->box_lieu;
    }

    public function getTitreAction()
    {
        return null;
    }

    function render()
    {
        if ($this->box_lieu == "box_hotel_resultats") {
            return $this->view->render("hotel/voir/resultats.phtml");
        } else {
            return $this->view->render("hotel/voir.phtml");
        }
    }

    function prepareCommun()
    {
        Zend_Loader::loadClass("Bral_Util_Lot");
        $this->view->lots = Bral_Util_Lot::getLotsByHotel();
        $this->box_lieu = "box_lieu";
    }

    function prepareFormulaire()
    {
    }

    function prepareResultat()
    {
    }

    function getListBoxRefresh()
    {
    }
}