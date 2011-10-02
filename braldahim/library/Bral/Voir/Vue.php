<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Voir_Vue
{

    function __construct($request, $view)
    {
        $this->_request = $request;
        $this->view = $view;
    }

    function setDisplay($display)
    {
        $this->view->display = $display;
    }

    function render()
    {
        echo $this->view->render("voir/vue.phtml");
    }
}
