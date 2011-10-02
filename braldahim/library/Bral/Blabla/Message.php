<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Blabla_Message extends Bral_Blabla_Blabla
{

    function getNomInterne()
    {
        return "box_action";
    }

    function getTitreAction()
    {
        return "Nouveau Message";
    }

    function prepareCommun()
    {
    }


    function prepareFormulaire()
    {
    }

    function prepareResultat()
    {

        Zend_Loader::loadClass("Bral_Box_Blabla");
        if ($this->view->user->nb_tour_blabla_braldun >= Bral_Box_Blabla::NB_TOUR_MESSAGE_MAX) {
            throw new Zend_Exception("Erreur NB Message Blabla : Vous avez deja posté le maximum de messages dans votre tour");
        }

        Zend_Loader::loadClass("Zend_Filter_StripTags");
        $filter = new Zend_Filter_StripTags();
        $message = stripslashes(Bral_Util_BBParser::bbcodeStripPlus($this->request->get('valeur_1')));

        if ($message != "") {
            $this->calculMessage($message);

            $this->view->user->nb_blabla_braldun = $this->view->user->nb_blabla_braldun + 1;
            $this->view->user->nb_tour_blabla_braldun = $this->view->user->nb_tour_blabla_braldun + 1;
            $this->majBraldun();
        } else {
            throw new Zend_Exception("message invalide : " . $this->request->get("valeur_1"));
        }
        $this->setEstEvenementAuto(false);
    }

    public function calculNbPa()
    {
        $this->view->nb_pa = 0;
        $this->view->assezDePa = true;
    }

    private function calculMessage($message)
    {
        Zend_Loader::loadClass("Blabla");
        $blablaTable = new Blabla();

        $data = array(
            'id_fk_braldun_blabla' => $this->view->user->id_braldun,
            'x_blabla' => $this->view->user->x_braldun,
            'y_blabla' => $this->view->user->y_braldun,
            'z_blabla' => $this->view->user->z_braldun,
            'date_blabla' => date("Y-m-d H:i:s"),
            'message_blabla' => $message,
        );
        $blablaTable->insert($data);
    }

    function getListBoxRefresh()
    {
        $tab = array("box_blabla");
        return $this->constructListBoxRefresh($tab);
    }
}