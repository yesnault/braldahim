<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
abstract class Bral_Filatures_Filatures
{

    function __construct($nomSystemeAction, $request, $view, $action)
    {
        $this->view = $view;
        $this->request = $request;
        $this->action = $action;
        $this->nom_systeme = $nomSystemeAction;

        $this->prepareCommun();

        switch ($this->action) {
            case "ask" :
                $this->prepareFormulaire();
                break;
            case "do":
                $this->prepareResultat();
                break;
            default:
                throw new Zend_Exception(get_class($this) . "::action invalide :" . $this->action);
        }
    }

    abstract function prepareCommun();

    abstract function prepareFormulaire();

    abstract function prepareResultat();

    abstract function getListBoxRefresh();

    abstract function getNomInterne();

    abstract function getTitreAction();

    abstract function calculNbPa();

    public function getIdEchoppeCourante()
    {
        return false;
    }

    public function getIdChampCourant()
    {
        return false;
    }

    public function getTablesHtmlTri()
    {
        return false;
    }

    abstract function render();

}