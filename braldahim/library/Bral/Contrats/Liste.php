<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Contrats_Liste extends Bral_Contrats_Contrats
{

    function getNomInterne()
    {
        return "box_quete_interne";
    }

    function render()
    {
        return $this->view->render("contrats/liste.phtml");
    }

    function getTitreAction()
    {
    }

    public function calculNbPa()
    {
    }

    function prepareCommun()
    {
        Zend_Loader::loadClass("Contrat");
        Zend_Loader::loadClass("Bral_Util_Lien");

        $contratTable = new Contrat();
        $contrats = $contratTable->findByIdBraldun($this->view->user->id_braldun);

        $idContratEnCours = null;

        $tabContrats = null;
        if ($contrats != null && count($contrats) > 0) {
            foreach ($contrats as $f) {
                $cible = Bral_Util_Lien::remplaceBaliseParNomEtJs("[b" . $f["id_braldun"] . "]", true);
                $contrat = array(
                    'id_contrat' => $f["id_contrat"],
                    'cible' => $cible,
                    'date_creation_contrat' => $f["date_creation_contrat"],
                    'date_fin_contrat' => $f["date_fin_contrat"],
                    'type_contrat' => $f["type_contrat"],
                    'etat_contrat' => $f["etat_contrat"],
                );

                if ($f["date_fin_contrat"] == null && $f["etat_contrat"] == 'en cours') {
                    $idContratEnCours = $f["id_contrat"];
                }

                $tabContrats[] = $contrat;
            }
        }

        $this->view->contrats = $tabContrats;

        $this->view->htmlContrat = "";

        if ($idContratEnCours != null) {
            Zend_Loader::loadClass("Bral_Contrat_Factory");
            $voir = Bral_Contrat_Factory::getVoir($this->request, $this->view, $idContratEnCours);
            $this->view->htmlContrat = $voir->render();
        }

        $this->view->idContratEnCours = $idContratEnCours;
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