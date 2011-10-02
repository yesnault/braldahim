<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
Zend_Loader::loadClass("Bral_Lieux_Hopital");

class Bral_Lieux_Infirmerie extends Bral_Lieux_Hopital
{

    function prepareCommun()
    {
        parent::prepareCommun();

        Zend_Loader::loadClass("Bral_Util_Communaute");
        Zend_Loader::loadClass("TypeLieu");

        if (Bral_Util_Communaute::getNiveauDuLieu($this->view->idCommunauteLieu, TypeLieu::ID_TYPE_INFIRMERIE) < Bral_Util_Communaute::NIVEAU_INFIRMERIE_SOIGNER) {
            throw new Zend_Exception("Erreur Infirmerie, niveau invalide");
        }
    }
}