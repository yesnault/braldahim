<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Metier
{

    const METIER_MINEUR_ID = 1;
    const METIER_CHASSEUR_ID = 2;
    const METIER_BUCHERON_ID = 3;
    const METIER_HERBORISTE_ID = 4;
    const METIER_FORGERON_ID = 5;
    const METIER_APOTHICAIRE_ID = 6;
    const METIER_MENUISIER_ID = 7;
    const METIER_CUISINIER_ID = 8;
    const METIER_TANNEUR_ID = 9;
    const METIER_GUERRIER_ID = 10;
    const METIER_TERRASSIER_ID = 11;

    function __construct()
    {
    }

    public static function prepareMetier($idBraldun, $sexeBraldun)
    {
        Zend_Loader::loadClass("BraldunsMetiers");
        $braldunsMetiersTable = new BraldunsMetiers();
        $braldunsMetierRowset = $braldunsMetiersTable->findMetiersByBraldunId($idBraldun);
        unset($braldunsMetiersTable);
        $tabMetiers = null;
        $tabMetierCourant = null;
        $possedeMetier = false;

        foreach ($braldunsMetierRowset as $m) {
            $possedeMetier = true;

            if ($sexeBraldun == 'feminin') {
                $nom_metier = $m["nom_feminin_metier"];
            } else {
                $nom_metier = $m["nom_masculin_metier"];
            }

            $t = array("id_metier" => $m["id_metier"],
                "nom" => $nom_metier,
                "nom_systeme" => $m["nom_systeme_metier"],
                "est_actif" => $m["est_actif_hmetier"],
                "date_apprentissage" => Bral_Util_ConvertDate::get_date_mysql_datetime("d/m/Y", $m["date_apprentissage_hmetier"]),
                "description" => $m["description_metier"],
            );

            if ($m["est_actif_hmetier"] == "non") {
                $tabMetiers[] = $t;
            }

            if ($m["est_actif_hmetier"] == "oui") {
                $tabMetierCourant = $t;
            }
        }
        unset($braldunsMetierRowset);

        $retour["tabMetierCourant"] = $tabMetierCourant;
        $retour["tabMetiers"] = $tabMetiers;
        $retour["possedeMetier"] = $possedeMetier;
        return $retour;
    }

    public static function getIdMetierCourant($braldun)
    {
        Zend_Loader::loadClass("BraldunsMetiers");
        $braldunsMetiersTable = new BraldunsMetiers();
        $braldunsMetierRowset = $braldunsMetiersTable->findMetierCourantByBraldunId($braldun->id_braldun);

        if (count($braldunsMetiersTable) > 1) {
            throw new Zend_Exception("Bral_Util_Metier::getIdMetierCourant metier courant invalide:" . $braldun->id_braldun);
        }

        if (count($braldunsMetiersTable) == 1) {
            $idMetier = $braldunsMetierRowset[0]["id_metier"];
        } else {
            $idMetier = null;
        }
        return $idMetier;
    }
}