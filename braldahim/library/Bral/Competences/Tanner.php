<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Competences_Tanner extends Bral_Competences_Competence
{

    function prepareCommun()
    {
        Zend_Loader::loadClass("Echoppe");

        // On regarde si le Braldûn est dans une de ses echopppes
        $echoppeTable = new Echoppe();
        $echoppes = $echoppeTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);

        $this->view->tannerEchoppeOk = false;
        $this->view->tannerPeauOk = false;

        if ($echoppes == null || count($echoppes) == 0) {
            $this->view->tannerEchoppeOk = false;
            return;
        }

        $this->view->nbPeauMax = $this->view->user->force_base_braldun;
        if ($this->view->nbPeauMax < 1) {
            $this->view->nbPeauMax = 1;
        }

        $this->view->nbArrierePeau = 0;

        $idEchoppe = -1;
        foreach ($echoppes as $e) {
            if ($e["id_fk_braldun_echoppe"] == $this->view->user->id_braldun &&
                    $e["nom_systeme_metier"] == "tanneur" &&
                    $e["x_echoppe"] == $this->view->user->x_braldun &&
                    $e["y_echoppe"] == $this->view->user->y_braldun &&
                    $e["z_echoppe"] == $this->view->user->z_braldun
            ) {
                $this->view->tannerEchoppeOk = true;
                $idEchoppe = $e["id_echoppe"];

                $echoppeCourante = array(
                    'id_echoppe' => $e["id_echoppe"],
                    'x_echoppe' => $e["x_echoppe"],
                    'y_echoppe' => $e["y_echoppe"],
                    'id_metier' => $e["id_metier"],
                    'quantite_peau_arriere_echoppe' => $e["quantite_peau_arriere_echoppe"],
                );
                if ($e["quantite_peau_arriere_echoppe"] >= $this->view->nbPeau) {
                    $this->view->tannerPeauOk = true;
                    $this->view->nbArrierePeau = $this->view->nbArrierePeau + $e["quantite_peau_arriere_echoppe"];
                }
                break;
            }
        }

        if ($this->view->tannerEchoppeOk == false) {
            return;
        }

        if ($this->view->nbPeauMax > $this->view->nbArrierePeau) {
            $this->view->nbPeauMax = $this->view->nbArrierePeau;
        }

        if ($this->view->nbPeauMax < 1) {
            $this->view->tannerPeauOk = false;
        }

        $this->idEchoppe = $idEchoppe;
    }

    function prepareFormulaire()
    {
        if ($this->view->assezDePa == false) {
            return;
        }
    }

    function prepareResultat()
    {
        // Verification des Pa
        if ($this->view->assezDePa == false) {
            throw new Zend_Exception(get_class($this) . " Pas assez de PA : " . $this->view->user->pa_braldun);
        }

        if ($this->view->tannerEchoppeOk == false || $this->view->tannerPeauOk == false) {
            throw new Zend_Exception(get_class($this) . " tanner interdit ");
        }

        if ((int)$this->request->get("valeur_1") . "" != $this->request->get("valeur_1") . "") {
            throw new Zend_Exception(get_class($this) . " Nombre invalide");
        } else {
            $nombre = (int)$this->request->get("valeur_1");
        }

        if ($nombre < 0 || $nombre > $this->view->nbPeauMax) {
            throw new Zend_Exception(get_class($this) . " Nombre invalide b");
        }

        // calcul des jets
        $this->calculJets();

        if ($this->view->okJet1 === true) {
            $this->calculTanner($nombre);
        }

        $this->calculPx();
        $this->calculBalanceFaim();
        $this->majBraldun();
    }

    private function calculTanner($nb)
    {

        $quantiteCuir = 0;
        $quantiteFourrure = 0;

        for ($j = 1; $j <= $nb; $j++) {
            $tirage = Bral_Util_De::getLanceDe6($this->view->config->game->base_force + $this->view->user->force_base_braldun);
            $tirage = $tirage + $this->view->user->force_bm_braldun + $this->view->user->force_bbdf_braldun;

            $tirage2 = Bral_Util_De::getLanceDe6($this->view->config->game->base_force + $this->view->user->force_base_braldun);

            if ($tirage > $tirage2) {
                $tirage = Bral_Util_De::get_1d2();
                if ($tirage == 1) {
                    $quantiteCuir = $quantiteCuir + 1;
                } else {
                    $quantiteFourrure = $quantiteFourrure + 1;
                }
            }
        }


        $echoppeTable = new Echoppe();
        $data = array(
            'id_echoppe' => $this->idEchoppe,
            'quantite_peau_arriere_echoppe' => -$nb,
            'quantite_cuir_arriere_echoppe' => $quantiteCuir,
            'quantite_fourrure_arriere_echoppe' => $quantiteFourrure,
        );
        $echoppeTable->insertOrUpdate($data);

        $this->view->quantitePeauUtilisee = $nb;
        $this->view->quantiteCuir = $quantiteCuir;
        $this->view->quantiteFourrure = $quantiteFourrure;
    }

    public function getIdEchoppeCourante()
    {
        if (isset($this->idEchoppe)) {
            return $this->idEchoppe;
        } else {
            return false;
        }
    }

    function getListBoxRefresh()
    {
        return $this->constructListBoxRefresh(array("box_competences", "box_echoppes"));
    }
}
