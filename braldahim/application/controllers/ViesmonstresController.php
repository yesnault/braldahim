<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class ViesmonstresController extends Zend_Controller_Action {

    function init() {
        $this->initView();
        Bral_Util_Securite::controlBatchsOrAdmin($this->_request);
        
        $this->view->config = Zend_Registry::get('config');

        Zend_Loader::loadClass('GroupeMonstre');
        Zend_Loader::loadClass('Monstre');
        Zend_Loader::loadClass("Bral_Monstres_VieGroupesNuee");
        Zend_Loader::loadClass("Bral_Util_ConvertDate");
    }

    function vieAction() {
        $vieGroupe = new Bral_Monstres_VieGroupesNuee();
        $vieGroupe->vieGroupesAction();
        $this->render();
    }
}