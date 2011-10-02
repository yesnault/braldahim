<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Batchs_CreationRuines extends Bral_Batchs_Batch
{

    const TOTAL_RUINES = 1000;

    public function calculBatchImpl()
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationRuines - calculBatchImpl - enter -");

        Zend_Loader::loadClass("TypeLieu");
        Zend_Loader::loadClass("Zone");
        Zend_Loader::loadClass("Lieu");
        $retour = null;

        $retour .= $this->calculCreation();
        $retour .= $this->suppressionRuineSurEau();
        $retour .= $this->suppressionRuineSurRouteVisible();

        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationRuines - calculBatchImpl - exit -");
        return $retour;
    }

    private function suppressionRuineSurRouteVisible()
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationRuines - suppressionRuineSurRouteVisible - enter -");
        $retour = "";

        // Suppression des bosquets partout où il y a une route visible
        Zend_Loader::loadClass("Route");
        $routeTable = new Route();
        $routes = $routeTable->fetchAll();

        $where = "";
        foreach ($routes as $r) {
            $or = "";
            if ($where != "") {
                $or = " OR ";
            }

            $where .= $or . " (x_lieu = " . $r["x_route"] . " AND y_lieu = " . $r["y_route"] . " AND z_lieu = " . $r["z_route"] . " and id_fk_type_lieu=" . TypeLieu::ID_TYPE_RUINE . ") ";
        }

        if ($where != "") {
            $lieuTable = new Lieu();
            $lieuTable->delete($where);
        }
        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationRuines - suppressionRuineSurRouteVisible - exit -");
        return $retour;
    }

    private function suppressionRuineSurEau()
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationRuines - suppressionRuineSurEau - enter -");
        $retour = "";

        // Suppression des ruines partout où il y a une eau
        Zend_Loader::loadClass("Eau");
        $eauTable = new Eau();
        $eaux = $eauTable->fetchall();

        $lieuTable = new Lieu();
        $nb = 0;
        $where = "";
        foreach ($eaux as $r) {
            $or = "";
            if ($where != "") {
                $or = " OR ";
            }

            $where .= $or . " (x_lieu = " . $r["x_eau"] . " AND y_lieu = " . $r["y_eau"] . " AND z_lieu = " . $r["z_eau"] . " and id_fk_type_lieu=" . TypeLieu::ID_TYPE_RUINE . ") ";

            $nb++;
            if ($nb == 1000) {
                $lieuTable->delete($where);
                $nb = 0;
                $where = "";
            }
        }

        if ($where != "") {
            $lieuTable->delete($where);
        }
        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationRuines - suppressionRuineSurEau - exit -");
        return $retour;
    }

    private function calculCreation()
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationRuines - calculCreation - enter -");
        $retour = "";

        $zoneTable = new Zone();
        $zones = $zoneTable->fetchAll();
        $lieuTable = new Lieu();
        $tmp = "";

        $superficieZones = array();
        $superficieTotale = 0;

        foreach ($zones as $z) {
            $superficieZones[$z["id_zone"]] = ($z["x_max_zone"] - $z["x_min_zone"]) * ($z["y_max_zone"] - $z["y_min_zone"]);
            $superficieTotale = $superficieTotale + ($superficieZones[$z["id_zone"]]);
        }

        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationRuines - nbMaxMonde(" . self::TOTAL_RUINES . ")  suptotal(" . $superficieTotale . ")");
        foreach ($zones as $z) {
            $tmp = "";
            $nbCreation = ceil(self::TOTAL_RUINES * ($superficieZones[$z["id_zone"]] / $superficieTotale));
            $nbActuel = $lieuTable->countVue($z["x_min_zone"], $z["y_min_zone"], $z["x_max_zone"], $z["y_max_zone"], 0, TypeLieu::ID_TYPE_RUINE);

            $aCreer = $nbCreation - $nbActuel;
            if ($aCreer <= 0) {
                $tmp = " deja pleine";
            }
            if ($aCreer > 0) {
                $retour .= $this->insert($z, $aCreer, $lieuTable);
            } else {
                $retour .= "zone(" . $z["id_zone"] . ") pleine de ruine nbActuel(" . $nbActuel . ") max(" . $nbCreation . "). ";
            }
        }

        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationRuines - calculCreation - exit -");
        return $retour;
    }

    private function insert($zone, $aCreer, $lieuTable)
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationRuines - insert - enter - idzone(" . $zone['id_zone'] . ") nbACreer(" . $aCreer . ")");
        $retour = "idzone(" . $zone['id_zone'] . ") aCreer(" . $aCreer . "). ";

        for ($i = 1; $i <= $aCreer; $i++) {
            usleep(Bral_Util_De::get_de_specifique(50, 10000));
            $x = Bral_Util_De::get_de_specifique($zone["x_min_zone"], $zone["x_max_zone"]);
            usleep(Bral_Util_De::get_de_specifique(100, 10000));
            $y = Bral_Util_De::get_de_specifique($zone["y_min_zone"], $zone["y_max_zone"]);

            $this->insertDb($lieuTable, $x, $y, 0);
        }
        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationRuines - insert - exit -");
        return $retour;
    }

    private function insertDb($lieuTable, $x, $y, $z)
    {
        if ($lieuTable->countByCase($x, $y, $z) == 0) {
            $data = array(
                'nom_lieu' => "Ruine",
                'x_lieu' => $x,
                'y_lieu' => $y,
                'z_lieu' => $z,
                'description_lieu' => "",
                'etat_lieu' => 100,
                'id_fk_type_lieu' => TypeLieu::ID_TYPE_RUINE,
                'date_creation_lieu' => date("Y-m-d H:i:s"),
            );
            $lieuTable->insert($data);
        }
    }
}