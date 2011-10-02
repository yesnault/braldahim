<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Echoppes_Liste extends Bral_Echoppes_Echoppe
{

    function getNomInterne()
    {
        return "box_echoppe";
    }

    function render()
    {
        return $this->view->render("echoppes/liste.phtml");
    }

    function prepareCommun()
    {
        Zend_Loader::loadClass("Echoppe");
        Zend_Loader::loadClass("BraldunsMetiers");
        Zend_Loader::loadClass("Region");

        $this->idEchoppeCourante = null;

        $regionTable = new Region();
        $regions = $regionTable->fetchAll(null, 'nom_region');
        $regions = $regions->toArray();

        $regionCourante = null;
        foreach ($regions as $r) {
            if ($r["x_min_region"] <= $this->view->user->x_braldun &&
                    $r["x_max_region"] >= $this->view->user->x_braldun &&
                    $r["y_min_region"] <= $this->view->user->y_braldun &&
                    $r["y_max_region"] >= $this->view->user->y_braldun
            ) {
                $regionCourante = $r;
                break;
            }
        }

        $echoppesTable = new Echoppe();
        $echoppesRowset = $echoppesTable->findByIdBraldun($this->view->user->id_braldun);

        $tabEchoppes = null;
        foreach ($echoppesRowset as $e) {
            $tabEchoppes[] = array(
                "id_echoppe" => $e["id_echoppe"],
                "x_echoppe" => $e["x_echoppe"],
                "y_echoppe" => $e["y_echoppe"],
                "id_metier" => $e["id_metier"],
                "id_region" => $e["id_region"],
                "nom_region" => $e["nom_region"]
            );
            if ($this->view->user->x_braldun == $e["x_echoppe"] &&
                    $this->view->user->y_braldun == $e["y_echoppe"]
            ) {
                $this->idEchoppeCourante = $e["id_echoppe"];
            }
        }

        $braldunsMetiersTable = new BraldunsMetiers();
        $braldunsMetierRowset = $braldunsMetiersTable->findMetiersByBraldunId($this->view->user->id_braldun);
        $tabMetiers = null;
        $tabMetierCourant = null;
        $this->view->constructionPossible = false;

        foreach ($braldunsMetierRowset as $m) {
            if ($this->view->user->sexe_braldun == 'feminin') {
                $nom_metier = $m["nom_feminin_metier"];
            } else {
                $nom_metier = $m["nom_masculin_metier"];
            }

            $regionsMetier = null;
            $tabEchoppesMetier = null;
            foreach ($regions as $r) {
                $regionMetier = null;
                $regionMetier["nom_region"] = $r["nom_region"];
                $regionMetier["nom_systeme_region"] = $r["nom_systeme_region"];
                $regionMetier["id_region"] = $r["id_region"];
                $regionMetier["echoppe"] = null;
                if (count($tabEchoppes) > 0) {
                    foreach ($tabEchoppes as $e) {
                        if ($e["id_metier"] == $m["id_metier"] &&
                                $r["id_region"] == $e["id_region"]
                        ) {
                            $regionMetier["echoppe"] = $e;
                        }
                    }
                }
                $regionsMetier[] = $regionMetier;
            }

            $t = array("id_metier" => $m["id_metier"],
                "nom_metier" => $nom_metier,
                "nom_systeme_metier" => $m["nom_systeme_metier"],
                "est_actif" => $m["est_actif_hmetier"],
                "regions" => $regionsMetier,
            );

            if ($m["construction_echoppe_metier"] == "oui") {
                $tabMetiers[] = $t;
            }

            if ($m["est_actif_hmetier"] == "oui") {
                $tabMetierCourant = $t;
                if ($m["construction_echoppe_metier"] == "oui") {
                    $this->view->constructionPossible = true;
                } else {
                    $this->view->constructionPossible = false;
                }
            }
        }

        $this->view->tabRegions = $regions;
        $this->view->tabRegionCourante = $regionCourante;
        $this->view->tabMetierCourant = $tabMetierCourant;
        $this->view->tabMetiers = $tabMetiers;

        $this->view->echoppes = $tabEchoppes;
        $this->view->nEchoppes = count($tabEchoppes);

        $this->view->nom_interne = $this->getNomInterne();

        return $this->idEchoppeCourante; // utilise dans Bral_Box_Echoppes
    }

    public function getIdEchoppeCourante()
    {
        return false; // toujours null ici, neccessaire pour EchoppesController
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