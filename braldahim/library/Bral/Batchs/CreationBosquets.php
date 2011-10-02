<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Batchs_CreationBosquets extends Bral_Batchs_Batch
{

    public function calculBatchImpl()
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - calculBatchImpl - enter -");

        Zend_Loader::loadClass('CreationBosquets');
        Zend_Loader::loadClass('Bosquet');
        Zend_Loader::loadClass('TypeBosquet');
        Zend_Loader::loadClass('Zone');

        $retour = null;

        $retour .= $this->calculCreation();
        $retour .= $this->suppressionBosquetSurRouteVisible();
        $retour .= $this->suppressionBosquetSurEau();

        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - calculBatchImpl - exit -");
        return $retour;
    }

    private function suppressionBosquetSurRouteVisible()
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - suppressionBosquetSurRouteVisible - enter -");
        $retour = "";

        // Suppression des bosquets partout où il y a une route visible
        Zend_Loader::loadClass("Route");
        $routeTable = new Route();
        $nbRoutes = $routeTable->countAllVisibleHorsBalise();

        $limit = 1000;
        $where = "";
        $bosquetTable = new Bosquet();

        for ($offset = 0; $offset <= $nbRoutes + $limit; $offset = $offset + $limit) {
            $routes = $routeTable->findAllVisibleHorsBalise($limit, $offset);
            $nb = 0;
            $where = "";
            foreach ($routes as $r) {
                $or = "";
                if ($where != "") {
                    $or = " OR ";
                }
                $where .= $or . " (x_bosquet = " . $r["x_route"] . " AND y_bosquet = " . $r["y_route"] . " AND z_bosquet = " . $r["z_route"] . ") ";

                $nb++;
                if ($nb == $limit) {
                    $bosquetTable->delete($where);
                    $nb = 0;
                    $where = "";
                }
            }

            if ($where != "") {
                $bosquetTable->delete($where);
            }
        }

        if ($where != "") {
            $bosquetTable->delete($where);
        }
        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - suppressionBosquetSurRouteVisible - exit -");
        return $retour;
    }

    private function suppressionBosquetSurEau()
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - suppressionBosquetSurEau - enter -");
        $retour = "";

        // Suppression des bosquets partout où il y a une eau
        Zend_Loader::loadClass("Eau");
        $eauTable = new Eau();

        $bosquetTable = new Bosquet();

        $nbEaux = $eauTable->countAll();
        $limit = 1000;

        $where = "";

        for ($offset = 0; $offset <= $nbEaux + $limit; $offset = $offset + $limit) {
            $eaux = $eauTable->fetchall(null, null, $limit, $offset);
            $nb = 0;
            $where = "";
            foreach ($eaux as $r) {
                $or = "";
                if ($where != "") {
                    $or = " OR ";
                }

                $where .= $or . " (x_bosquet = " . $r["x_eau"] . " AND y_bosquet = " . $r["y_eau"] . " AND z_bosquet = " . $r["z_eau"] . ") ";

                $nb++;
                if ($nb == $limit) {
                    $bosquetTable->delete($where);
                    $nb = 0;
                    $where = "";
                }
            }

            if ($where != "") {
                $bosquetTable->delete($where);
            }
        }

        if ($where != "") {
            $bosquetTable->delete($where);
        }

        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - suppressionBosquetSurEau - exit -");
        return $retour;
    }

    private function calculCreation()
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - calculCreation - enter -");
        $retour = "";

        $zoneTable = new Zone();

        $creationBosquetsTable = new CreationBosquets();
        $creationBosquets = $creationBosquetsTable->fetchAll(null, "id_fk_type_bosquet_creation_bosquets");
        $nbCreationBosquets = count($creationBosquets);
        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - nbCreationBosquets=" . $nbCreationBosquets);

        $typeBosquetTable = new TypeBosquet();
        $typeBosquets = $typeBosquetTable->fetchAll();
        $nbTypeBosquets = count($typeBosquets);
        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - nbTypeBosquets=" . $nbTypeBosquets);

        // selection des environnements / zones concernes
        $environnementIds = $this->getEnvironnementsConcernes($creationBosquets);
        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - nb environnement concernes=" . count($environnementIds));
        $zones = $zoneTable->findByIdEnvironnementList($environnementIds, false);
        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - nb zones concernees=" . count($zones));

        $bosquetTable = new Bosquet();
        $tmp = "";

        $superficieZones = array();
        $superficieTotale = array();

        foreach ($creationBosquets as $c) {
            // on recupere la supercifie totale de toutes les zones concernees par ce type
            foreach ($zones as $z) {
                if ($z["id_fk_environnement_zone"] == $c["id_fk_environnement_creation_bosquets"]) {
                    $superficieZones[$z["id_zone"]] = ($z["x_max_zone"] - $z["x_min_zone"]) * ($z["y_max_zone"] - $z["y_min_zone"]);
                    if (array_key_exists($c["id_fk_type_bosquet_creation_bosquets"], $superficieTotale)) {
                        $superficieTotale[$c["id_fk_type_bosquet_creation_bosquets"]] = $superficieTotale[$c["id_fk_type_bosquet_creation_bosquets"]] + ($superficieZones[$z["id_zone"]]);
                    } else {
                        $superficieTotale[$c["id_fk_type_bosquet_creation_bosquets"]] = $superficieZones[$z["id_zone"]];
                    }
                }
            }
        }

        foreach ($creationBosquets as $c) {
            $t = null;
            foreach ($typeBosquets as $type) {
                if ($c["id_fk_type_bosquet_creation_bosquets"] == $type["id_type_bosquet"]) {
                    $t = $type;
                    break;
                }
            }

            if ($t != null) {
                Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - traitement du bosquet " . $t["id_type_bosquet"] . " nbMaxMonde(" . $t["nb_creation_type_bosquet"] . ") environnement(" . $c["id_fk_environnement_creation_bosquets"] . ") suptotal(" . $superficieTotale[$c["id_fk_type_bosquet_creation_bosquets"]] . ")");
                foreach ($zones as $z) {
                    if ($z["id_fk_environnement_zone"] == $c["id_fk_environnement_creation_bosquets"]) {
                        $tmp = "";
                        $nbCreation = ceil($t["nb_creation_type_bosquet"] * ($superficieZones[$z["id_zone"]] / $superficieTotale[$c["id_fk_type_bosquet_creation_bosquets"]]));
                        $nbActuel = $bosquetTable->countVue($z["x_min_zone"], $z["y_min_zone"], $z["x_max_zone"], $z["y_max_zone"], 0, $t["id_type_bosquet"]);

                        $aCreer = $nbCreation - $nbActuel;
                        if ($aCreer <= 0) {
                            $tmp = " deja pleine";
                        }
                        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - zone(" . $z["id_zone"] . ") nbActuel:" . $nbActuel . " max:" . $nbCreation . $tmp . " supzone(" . $superficieZones[$z["id_zone"]] . ") suptotal(" . $superficieTotale[$c["id_fk_type_bosquet_creation_bosquets"]] . ")");
                        if ($aCreer > 0) {
                            $retour .= $this->insert($t["id_type_bosquet"], $z, $aCreer, $bosquetTable);
                        } else {
                            $retour .= "zone(" . $z["id_zone"] . ") pleine de bosquet(" . $t["id_type_bosquet"] . ") nbActuel(" . $nbActuel . ") max(" . $nbCreation . "). ";
                        }
                    }
                }
            }
        }

        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - calculCreation - exit -");

        return $retour;
    }

    private function getEnvironnementsConcernes($creationBosquets)
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - getEnvironnementsConcernes - enter -");
        $environnementIds = null;
        foreach ($creationBosquets as $n) {
            $environnementIds[$n["id_fk_environnement_creation_bosquets"]] = $n["id_fk_environnement_creation_bosquets"];
        }
        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - getEnvironnementsConcernes - exit -");
        return $environnementIds;
    }

    private function insert($idTypeBosquet, $zone, $aCreer, $bosquetTable)
    {
        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - insert - enter - idtype(" . $idTypeBosquet . ") idzone(" . $zone['id_zone'] . ") nbACreer(" . $aCreer . ")");
        $retour = "bosquet(" . $idTypeBosquet . ") idzone(" . $zone['id_zone'] . ") aCreer(" . $aCreer . "). ";

        for ($i = 1; $i <= $aCreer; $i++) {
            usleep(Bral_Util_De::get_de_specifique(50, 10000));
            $x = Bral_Util_De::get_de_specifique($zone["x_min_zone"], $zone["x_max_zone"]);
            usleep(Bral_Util_De::get_de_specifique(100, 10000));
            $y = Bral_Util_De::get_de_specifique($zone["y_min_zone"], $zone["y_max_zone"]);

            $nbCasesAutour = Bral_Util_De::get_de_specifique(2, 9);
            $numeroBosquet = null;

            for ($j = 0; $j <= $nbCasesAutour; $j++) {
                for ($k = 0; $k <= $nbCasesAutour; $k++) {
                    $i = $i + 1;
                    $idBosquet = $this->insertDb($bosquetTable, $idTypeBosquet, $x + $j, $y + $k, 0, Bral_Util_De::get_de_specifique(5, 15), $numeroBosquet);

                    if ($numeroBosquet == null && $idBosquet != null) {
                        $numeroBosquet = $idBosquet;
                        $where = 'id_bosquet = ' . $idBosquet;
                        $data['numero_bosquet'] = $numeroBosquet;
                        $bosquetTable->update($data, $where);
                    }

                }
            }
        }
        Bral_Util_Log::batchs()->trace("Bral_Batchs_CreationBosquets - insert - exit -");
        return $retour;
    }

    private function insertDb($bosquetTable, $idTypeBosquet, $x, $y, $z, $quantite, $numeroBosquet)
    {
        if ($bosquetTable->countByCase($x, $y, $z) == 0) {
            $data = array(
                'id_fk_type_bosquet_bosquet' => $idTypeBosquet,
                'x_bosquet' => $x,
                'y_bosquet' => $y,
                'z_bosquet' => $z,
                'quantite_restante_bosquet' => $quantite,
                'quantite_max_bosquet' => $quantite,
                'numero_bosquet' => $numeroBosquet,
            );
            return $bosquetTable->insert($data);
        }
    }
}