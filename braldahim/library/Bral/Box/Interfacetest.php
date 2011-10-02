<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Interfacetest extends Bral_Box_Box
{

    function getTitreOnglet()
    {
        return "Vue [Test]";
    }

    function getNomInterne()
    {
        return "box_interfacetest";
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
        $this->view->nom_interne = $this->getNomInterne();
        return $this->view->render("interface/interfacetest.phtml");
    }

}
