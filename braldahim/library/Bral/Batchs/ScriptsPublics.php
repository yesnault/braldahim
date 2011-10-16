<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Batchs_ScriptsPublics extends Bral_Batchs_Batch
{

	public function calculBatchImpl()
	{
		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - calculBatchImpl - enter -");
		$retour = null;

		$retour = $this->genereFichierBralduns();
		$retour .= $this->genereFichierCommunautes();
		$retour .= $this->genereFichierCommunautesRangs();
		$retour .= $this->genereFichierCompetences();
		$retour .= $this->genereFichierMetiers();
		$retour .= $this->genereFichierVilles();
		$retour .= $this->genereFichierLieuxVille();
		$retour .= $this->genereFichierRegions();
		$retour .= $this->genereFichierTitres();
		$retour .= $this->genereFichierDistinctions();
		$retour .= $this->genereFichierPlantes();
		$retour .= $this->genereFichierEnvironnements();
		$retour .= $this->genereFichierZones();
		$retour .= $this->genereFichierLots();

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - calculBatchImpl - exit -");
		return $retour;
	}

	private function genereFichierBralduns()
	{
		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierBralduns - enter -");
		$retour = "";
		Zend_Loader::loadClass("Bral_Util_Fichier");

		$braldunTable = new Braldun();
		$bralduns = $braldunTable->findAllJoueursAvecPnj();

		$contenu = "id_braldun;prenom_braldun;nom_braldun;niveau_braldun;sexe_braldun;";
		$contenu .= "nb_ko_braldun;nb_braldun_ko_braldun;nb_plaque_braldun;nb_braldun_plaquage_braldun;";
		$contenu .= "nb_monstre_kill_braldun;id_fk_mere_braldun;id_fk_pere_braldun;id_fk_communaute_braldun;";
		$contenu .= "id_fk_rang_communaute_braldun;url_blason_braldun;url_avatar_braldun;est_pnj_braldun;";
		$contenu .= "points_gredin_braldun;points_redresseur_braldun;points_distinctions_braldun";

		$contenu .= PHP_EOL;

		if (count($bralduns) > 0) {
			foreach ($bralduns as $h) {
				$contenu .= $h["id_braldun"] . ';';
				$contenu .= $h["prenom_braldun"] . ';';
				$contenu .= $h["nom_braldun"] . ';';
				$contenu .= $h["niveau_braldun"] . ';';
				$contenu .= $h["sexe_braldun"] . ';';
				$contenu .= $h["nb_ko_braldun"] . ';';
				$contenu .= $h["nb_braldun_ko_braldun"] . ';';
				$contenu .= $h["nb_plaque_braldun"] . ';';
				$contenu .= $h["nb_braldun_plaquage_braldun"] . ';';
				$contenu .= $h["nb_monstre_kill_braldun"] . ';';
				$contenu .= $h["id_fk_mere_braldun"] . ';';
				$contenu .= $h["id_fk_pere_braldun"] . ';';
				$contenu .= $h["id_fk_communaute_braldun"] . ';';
				$contenu .= $h["id_fk_rang_communaute_braldun"] . ';';
				$contenu .= $h["url_blason_braldun"] . ';';
				$contenu .= $h["url_avatar_braldun"] . ';';
				$contenu .= $h["est_pnj_braldun"] . ';';
				$contenu .= $h["points_gredin_braldun"] . ';';
				$contenu .= $h["points_redresseur_braldun"] . ';';
				$contenu .= $h["points_distinctions_braldun"];

				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_bralduns, $contenu);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierBralduns - exit -");
		return $retour;
	}

	private function genereFichierCommunautes()
	{
		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierCommunautes - enter -");
		$retour = "";
		Zend_Loader::loadClass("Bral_Util_Fichier");

		Zend_Loader::loadClass("Communaute");
		$communauteTable = new Communaute();
		$communautes = $communauteTable->findAll();

		$contenu = "id_communaute;nom_communaute;id_fk_braldun_gestionnaire_communaute;site_web_communaute";
		$contenu .= PHP_EOL;

		if (count($communautes) > 0) {
			foreach ($communautes as $c) {
				$contenu .= $c["id_communaute"] . ';';
				$contenu .= $c["nom_communaute"] . ';';
				$contenu .= $c["id_fk_braldun_gestionnaire_communaute"] . ';';
				$contenu .= $c["site_web_communaute"];
				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_communautes, $contenu);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierCommunautes - exit -");
		return $retour;
	}

	private function genereFichierCommunautesRangs()
	{
		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierCommunautesRangs - enter -");
		$retour = "";
		Zend_Loader::loadClass("Bral_Util_Fichier");

		Zend_Loader::loadClass("RangCommunaute");
		$rangCommunauteTable = new RangCommunaute();
		$rangs = $rangCommunauteTable->findAll();

		$contenu = "id_communaute;id_rang_communaute;ordre_rang_communaute;nom_rang_communaute";
		$contenu .= PHP_EOL;

		if (count($rangs) > 0) {
			foreach ($rangs as $r) {
				$contenu .= $r["id_fk_communaute_rang_communaute"] . ';';
				$contenu .= $r["id_rang_communaute"] . ';';
				$contenu .= $r["ordre_rang_communaute"] . ';';
				$contenu .= $r["nom_rang_communaute"];
				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_rangs_communautes, $contenu);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierCommunautesRangs - exit -");
		return $retour;
	}

	private function genereFichierCompetences()
	{
		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierCommunautesRangs - enter -");
		$retour = "";
		Zend_Loader::loadClass("Bral_Util_Fichier");

		Zend_Loader::loadClass("Competence");
		$competenceTable = new Competence();
		$competences = $competenceTable->findAll();

		$contenu = "id_competence;nom_systeme_competence;nom_competence;niveau_requis_competence;type_competence;id_fk_metier_competence";
		$contenu .= PHP_EOL;

		if (count($competences) > 0) {
			foreach ($competences as $c) {
				$contenu .= $c["id_competence"] . ';';
				$contenu .= $c["nom_systeme_competence"] . ';';
				$contenu .= $c["nom_competence"] . ';';
				if ($c["id_fk_metier_competence"] != null) {
					$contenu .= $c["niveau_min_metier"] . ';';
				} else {
					$contenu .= $c["niveau_requis_competence"] . ';';
				}
				$contenu .= $c["type_competence"] . ';';
				$contenu .= $c["id_fk_metier_competence"];
				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_competences, $contenu);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierCompetences - exit -");
		return $retour;
	}

	private function genereFichierMetiers()
	{
		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierMetiers - enter -");
		$retour = "";
		Zend_Loader::loadClass("Bral_Util_Fichier");

		Zend_Loader::loadClass("Metier");
		$metierTable = new Metier();
		$metiers = $metierTable->findAll();

		$contenu = "id_metier;nom_masculin_metier;nom_feminin_metier;nom_systeme_metier;";
		$contenu .= "construction_echoppe_metier;niveau_min_metier";
		$contenu .= PHP_EOL;

		if (count($metiers) > 0) {
			foreach ($metiers as $c) {
				$contenu .= $c["id_metier"] . ';';
				$contenu .= $c["nom_masculin_metier"] . ';';
				$contenu .= $c["nom_feminin_metier"] . ';';
				$contenu .= $c["nom_systeme_metier"] . ';';
				$contenu .= $c["construction_echoppe_metier"] . ';';
				$contenu .= $c["niveau_min_metier"];
				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_metiers, $contenu);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierMetiers - exit -");
		return $retour;
	}

	private function genereFichierVilles()
	{
		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierVilles - enter -");
		$retour = "";
		Zend_Loader::loadClass("Bral_Util_Fichier");

		Zend_Loader::loadClass("Ville");
		$villeTable = new Ville();
		$villes = $villeTable->findAllWithRegion();

		$contenu = "id_ville;nom_ville;est_capitale_ville;";
		$contenu .= "x_min_ville;y_min_ville;x_max_ville;y_max_ville;id_region;nom_region";
		$contenu .= PHP_EOL;

		if (count($villes) > 0) {
			foreach ($villes as $v) {
				$contenu .= $v["id_ville"] . ';';
				$contenu .= $v["nom_ville"] . ';';
				$contenu .= $v["est_capitale_ville"] . ';';
				$contenu .= $v["x_min_ville"] . ';';
				$contenu .= $v["y_min_ville"] . ';';
				$contenu .= $v["x_max_ville"] . ';';
				$contenu .= $v["y_max_ville"] . ';';
				$contenu .= $v["id_region"] . ';';
				$contenu .= $v["nom_region"];
				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_villes, $contenu);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierVilles - exit -");
		return $retour;
	}

	private function genereFichierLieuxVille()
	{
		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierLieuxVille - enter -");
		$retour = "";
		Zend_Loader::loadClass("Bral_Util_Fichier");

		Zend_Loader::loadClass("Lieu");
		$lieuTable = new Lieu();
		$lieux = $lieuTable->findAllLieuAvecVille();

		$contenu = "id_lieu;nom_lieu;nom_systeme_type_lieu;nom_type_lieu;id_type_lieu;";
		$contenu .= "x_lieu;y_lieu;id_ville;id_region";
		$contenu .= PHP_EOL;

		if (count($lieux) > 0) {
			foreach ($lieux as $v) {
				$contenu .= $v["id_lieu"] . ';';
				$contenu .= $v["nom_lieu"] . ';';
				$contenu .= $v["nom_systeme_type_lieu"] . ';';
				$contenu .= $v["nom_type_lieu"] . ';';
				$contenu .= $v["id_type_lieu"] . ';';
				$contenu .= $v["x_lieu"] . ';';
				$contenu .= $v["y_lieu"] . ';';
				$contenu .= $v["id_ville"] . ';';
				$contenu .= $v["id_region"] . ';';
				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_lieux_villes, $contenu);

		Bral_Util_Log::batchs()->trace("genereFichierBatiments - genereFichierLieuxVille - exit -");
		return $retour;
	}

	private function genereFichierRegions()
	{
		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierRegions - enter -");
		$retour = "";
		Zend_Loader::loadClass("Bral_Util_Fichier");

		Zend_Loader::loadClass("Region");
		$regionTable = new Region();
		$regions = $regionTable->findAll();

		$contenu = "id_region;nom_region;";
		$contenu .= "x_min_region;x_max_region;y_min_region;y_max_region;est_pvp_region";
		$contenu .= PHP_EOL;

		if (count($regions) > 0) {
			foreach ($regions as $v) {
				$contenu .= $v["id_region"] . ';';
				$contenu .= $v["nom_region"] . ';';
				$contenu .= $v["x_min_region"] . ';';
				$contenu .= $v["x_max_region"] . ';';
				$contenu .= $v["y_min_region"] . ';';
				$contenu .= $v["y_max_region"] . ';';
				$contenu .= $v["est_pvp_region"];
				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_regions, $contenu);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierRegions - exit -");
		return $retour;
	}

	private function genereFichierTitres()
	{
		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierTitres - enter -");
		$retour = "";
		Zend_Loader::loadClass("Bral_Util_Fichier");

		Zend_Loader::loadClass("BraldunsTitres");
		$titreTable = new BraldunsTitres();
		$titres = $titreTable->selectAll();

		$contenu = "id_braldun;date_acquis_htitre;id_type_titre;texte_titre;";
		$contenu .= PHP_EOL;

		if (count($titres) > 0) {
			foreach ($titres as $e) {
				$contenu .= $e["id_fk_braldun_htitre"] . ';';
				$contenu .= $e["date_acquis_htitre"] . ';';
				$contenu .= $e["id_type_titre"] . ';';
				if ($e["sexe_braldun"] == "masculin") {
					$contenu .= $e["nom_masculin_type_titre"] . ';';
				} else {
					$contenu .= $e["nom_feminin_type_titre"] . ';';
				}

				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_titres, $contenu);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierTitres - exit -");
		return $retour;
	}

	private function genereFichierDistinctions()
	{
		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierDistinctions - enter -");
		$retour = "";
		Zend_Loader::loadClass("Bral_Util_Fichier");

		Zend_Loader::loadClass("BraldunsDistinction");
		$distinctionTable = new BraldunsDistinction();
		$distinctions = $distinctionTable->selectAll();

		$contenu = "id_braldun;date_hdistinction;id_type_categorie;nom_type_categorie;";
		$contenu = "ordre_type_categorie;ordre_type_categorie;id_type_distinction;";
		$contenu = "date_hdistinction;texte_hdistinction;url_hdistinction;points_type_distinction;";
		$contenu .= PHP_EOL;

		if (count($distinctions) > 0) {
			foreach ($distinctions as $e) {
				$contenu .= $e["id_braldun"] . ';';
				$contenu .= $e["date_hdistinction"] . ';';
				$contenu .= $e["id_type_categorie"] . ';';
				$contenu .= $e["nom_type_categorie"] . ';';
				$contenu .= $e["ordre_type_categorie"] . ';';
				$contenu .= $e["id_type_distinction"] . ';';
				$contenu .= $e["date_hdistinction"] . ';';
				$contenu .= $e["texte_hdistinction"] . ';';
				$contenu .= $e["url_hdistinction"] . ';';
				$contenu .= $e["points_type_distinction"] . ';';

				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_distinctions, $contenu);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierDistinctions - exit -");
		return $retour;
	}

	private function genereFichierPlantes()
	{
		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierPlantes - enter -");
		$retour = "";
		Zend_Loader::loadClass("Bral_Util_Fichier");

		Zend_Loader::loadClass("TypePlante");
		Zend_Loader::loadClass("TypePartieplante");

		$typePlantesTable = new TypePlante();
		$typePlantesRowset = $typePlantesTable->findAll();

		$typePartiePlantesTable = new TypePartieplante();
		$typePartiePlantesRowset = $typePartiePlantesTable->fetchall();
		$typePartiePlantesRowset = $typePartiePlantesRowset->toArray();

		$tabPartiePlantesPreparees = null;
		$tabPartiePlantesBruts = null;

		$contenu = "id_type_plante;nom_type_plante;categorie_type_plante;";
		$contenu .= "prefix_type_plante;id_type_partieplante1;nom_type_partieplante1;";
		$contenu .= "id_type_partieplante2;nom_type_partieplante2;";
		$contenu .= "id_type_partieplante3;nom_type_partieplante3;";
		$contenu .= "id_type_partieplante4;nom_type_partieplante4";
		$contenu .= PHP_EOL;

		foreach ($typePlantesRowset as $t) {
			$contenu .= $t["id_type_plante"] . ";";
			$contenu .= $t["nom_type_plante"] . ";";
			$contenu .= $t["categorie_type_plante"] . ";";
			$contenu .= $t["prefix_type_plante"];
			$n = 1;
			foreach ($typePartiePlantesRowset as $p) {
				for ($i = 1; $i <= 4; $i++) {
					if ($t["id_fk_partieplante" . $i . "_type_plante"] == $p["id_type_partieplante"]) {
						$n++;
						$contenu .= ';' . $p["id_type_partieplante"] . ';';
						$contenu .= $p["nom_type_partieplante"];
					}
				}
			}
			for ($i = $n; $i <= 4; $i++) {
				$contenu .= ";;";
			}
			$contenu .= PHP_EOL;
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_plantes, $contenu);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierPlantes - exit -");
		return $retour;
	}

	private function genereFichierEnvironnements()
	{
		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierEnvironnements - enter -");
		$retour = "";
		Zend_Loader::loadClass("Bral_Util_Fichier");

		Zend_Loader::loadClass("Environnement");
		$environnementTable = new Environnement();
		$environnements = $environnementTable->fetchAll();

		$contenu = "nom_environnement;nom_systeme_environnement";
		$contenu .= PHP_EOL;

		if (count($environnements) > 0) {
			foreach ($environnements as $v) {
				$contenu .= $v["nom_environnement"] . ';';
				$contenu .= $v["nom_systeme_environnement"];
				$contenu .= PHP_EOL;
			}
		}

		$contenu .= "Eau peu profonde;peuprofonde";
		$contenu .= PHP_EOL;
		$contenu .= "Eau profonde;profonde";
		$contenu .= PHP_EOL;
		$contenu .= "Lac;lac";
		$contenu .= PHP_EOL;
		$contenu .= "Mer;mer";
		$contenu .= PHP_EOL;

		Zend_Loader::loadClass("TypeBosquet");
		$typeBosquetTable = new TypeBosquet();
		$typesBosquet = $typeBosquetTable->fetchAll();

		if (count($typesBosquet) > 0) {
			foreach ($typesBosquet as $v) {
				$contenu .= $v["nom_type_bosquet"] . ';';
				$contenu .= $v["nom_systeme_type_bosquet"];
				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_environnements, $contenu);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierEnvironnements - exit -");
		return $retour;
	}

	private function genereFichierZones()
	{
		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierZones - enter -");
		$retour = "";
		Zend_Loader::loadClass("Bral_Util_Fichier");

		Zend_Loader::loadClass("Zone");
		$zoneTable = new Zone();
		$zones = $zoneTable->fetchAllAvecEnvironnement("id_fk_donjon_zone is null and z_zone=0");

		$contenu = "id_zone;id_fk_environnement_zone;nom_systeme_environnement;x_min_zone;x_max_zone;y_min_zone;y_max_zone";
		$contenu .= PHP_EOL;

		if (count($zones) > 0) {
			foreach ($zones as $v) {
				$contenu .= $v["id_zone"] . ';';
				$contenu .= $v["id_fk_environnement_zone"] . ';';
				$contenu .= $v["nom_systeme_environnement"] . ';';
				$contenu .= $v["x_min_zone"] . ';';
				$contenu .= $v["x_max_zone"] . ';';
				$contenu .= $v["y_min_zone"] . ';';
				$contenu .= $v["y_max_zone"];
				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_zones, $contenu);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierZones - exit -");
		return $retour;
	}

	private function genereFichierLots()
	{
		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierLots - enter -");
		$retour = "";
		Zend_Loader::loadClass("Bral_Util_Fichier");
		Zend_Loader::loadClass("Bral_Util_Lot");

		$lots = Bral_Util_Lot::getLotsForCsv();

		$excludes = array(
			'equipements',
			'materiels',
			'aliments',
			'elements',
			'potions',
			'ingredients',
			'runes_non_identifiees',
			'runes_identifiees',
			'munitions',
			'graines',
			'minerais_bruts',
			'minerais_lingots',
			'partiesplantes_brutes',
			'partiesplantes_preparees',
			'partiesplantes_brutes_csv',
			'partiesplantes_preparees_csv',
			//'commentaire_lot',
		);

		$initFichiers = array();

		foreach ($excludes as $champ) {
			Bral_Util_Fichier::ecrire($this->config->fichier->liste_lots . '_' . str_replace('_csv', '', $champ) . '.csv', '');
		}

		$contenu = '';

		if (count($lots) == 0) {
			$contenu .= 'AUCUN_LOT' . PHP_EOL;
		} else {

			$init = false;

			foreach ($lots as $lot) {
				if (!$init) { // entête du fichier lots.csv
					foreach ($lot as $champ => $valeur) {
						if (!in_array($champ, $excludes)) {
							$contenu .= $champ . ';';
						}
					}
					$contenu .= PHP_EOL;
					$init = true;
				}

				foreach ($lot as $champ => $valeur) {
					if (!in_array($champ, $excludes)) {
						$contenu .= preg_replace('/\n/', '', nl2br($valeur)) . ';';
					} else {
						if ($champ == "partiesplantes_brutes" || $champ == "partiesplantes_preparees") continue;
						$this->genereFichierContenuLots($lot, $lot["id_lot"], $champ, $this->config->fichier->liste_lots . '_' . str_replace('_csv', '', $champ) . '.csv', $initFichiers);
					}
				}
				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_lots . '.csv', $contenu);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierLots - exit -");
		return $retour;
	}

	private function genereFichierContenuLots($lot, $idLot, $type, $fichier, &$initFichiers)
	{

		$lignes = $lot[$type];
		if (count($lignes) <= 0) {
			return;
		}

		$contenu = '';

		if (!in_array($fichier, $initFichiers)) { // entête
			$contenu = 'id_lot;';
			foreach ($lignes as $k => $ligne) {
				if ($type == 'elements') {
					$contenu .= $k . ';';
				} else {
					if ($type == "equipements") {
						$equipementTitres = $ligne[$type][0];
						foreach ($equipementTitres as $champ => $valeur) {
							if (!is_array($valeur)) {
								$contenu .= $champ . ';';
							}
						}
					} else {
						foreach ($ligne as $champ => $valeur) {
							$contenu .= $champ . ';';
						}
					}
					break;
				}
			}
			$contenu .= PHP_EOL;
			$initFichiers[] = $fichier;
		}

		if ($type == 'elements') {
			$contenu .= $idLot . ';';
		}

		foreach ($lignes as $k => $ligne) {

			if ($type == 'elements') {
				$contenu .= str_replace(';', '', preg_replace('/\n/', '', nl2br($ligne))) . ';';
			} else {
				$contenu .= $idLot . ';';
				foreach ($ligne as $champ => $valeur) {
					if (!is_array($valeur) && $champ != "nom_type_emplacement") {
						$contenu .= str_replace(';', '', preg_replace('/\n/', '', nl2br($valeur))) . ';';
					} else if ($champ == "equipements") {
						$equipementValeurs = $ligne[$champ][0];
						foreach ($equipementValeurs as $champ => $valeur) {
							if (!is_array($valeur)) {
								$contenu .= str_replace(';', '', preg_replace('/\n/', '', nl2br($valeur))) . ';';
							}
						}
					}
				}
				$contenu .= PHP_EOL;
			}
		}

		if ($type == 'elements') {
			$contenu .= PHP_EOL;
		}

		Bral_Util_Fichier::ecrire($fichier, $contenu, 'a+');
	}
}
