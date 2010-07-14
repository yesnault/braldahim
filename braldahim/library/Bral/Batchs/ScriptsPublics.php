<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id$
 * $Author$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */
class Bral_Batchs_ScriptsPublics extends Bral_Batchs_Batch {

	public function calculBatchImpl() {
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
		$retour .= $this->genereFichierEquipement();

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - calculBatchImpl - exit -");
		return $retour;
	}

	private function genereFichierBralduns() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierBralduns - enter -");
		$retour = "";
		Zend_Loader::loadClass("Bral_Util_Fichier");

		$braldunTable = new Braldun();
		$bralduns = $braldunTable->findAllJoueursAvecPnj();

		$contenu = "id_braldun;prenom_braldun;nom_braldun;niveau_braldun;";
		$contenu .= "nb_ko_braldun;nb_braldun_ko_braldun;nb_plaque_braldun;nb_braldun_plaquage_braldun;";
		$contenu .= "nb_monstre_kill_braldun;id_fk_mere_braldun;id_fk_pere_braldun;id_fk_communaute_braldun;";
		$contenu .= "id_fk_rang_communaute_braldun;url_blason_braldun;url_avatar_braldun;est_pnj_braldun";

		$contenu .= PHP_EOL;

		if (count($bralduns) > 0) {
			foreach ($bralduns as $h) {
				$contenu .= $h["id_braldun"].';';
				$contenu .= $h["prenom_braldun"].';';
				$contenu .= $h["nom_braldun"].';';
				$contenu .= $h["niveau_braldun"].';';
				$contenu .= $h["nb_ko_braldun"].';';
				$contenu .= $h["nb_braldun_ko_braldun"].';';
				$contenu .= $h["nb_plaque_braldun"].';';
				$contenu .= $h["nb_braldun_plaquage_braldun"].';';
				$contenu .= $h["nb_monstre_kill_braldun"].';';
				$contenu .= $h["id_fk_mere_braldun"].';';
				$contenu .= $h["id_fk_pere_braldun"].';';
				$contenu .= $h["id_fk_communaute_braldun"].';';
				$contenu .= $h["id_fk_rang_communaute_braldun"].';';
				$contenu .= $h["url_blason_braldun"].';';
				$contenu .= $h["url_avatar_braldun"].';';
				$contenu .= $h["est_pnj_braldun"];

				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_bralduns, $contenu);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierBralduns - exit -");
		return $retour;
	}

	private function genereFichierCommunautes() {
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
				$contenu .= $c["id_communaute"].';';
				$contenu .= $c["nom_communaute"].';';
				$contenu .= $c["id_fk_braldun_gestionnaire_communaute"].';';
				$contenu .= $c["site_web_communaute"];
				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_communautes, $contenu);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierCommunautes - exit -");
		return $retour;
	}

	private function genereFichierCommunautesRangs() {
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
				$contenu .= $r["id_fk_communaute_rang_communaute"].';';
				$contenu .= $r["id_rang_communaute"].';';
				$contenu .= $r["ordre_rang_communaute"].';';
				$contenu .= $r["nom_rang_communaute"];
				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_rangs_communautes, $contenu);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierCommunautesRangs - exit -");
		return $retour;
	}

	private function genereFichierCompetences() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierCommunautesRangs - enter -");
		$retour = "";
		Zend_Loader::loadClass("Bral_Util_Fichier");

		Zend_Loader::loadClass("Competence");
		$competenceTable = new Competence();
		$competences = $competenceTable->findAll();

		$contenu = "id_competence;nom_systeme_competence;nom_competence;niveau_requis_competence;type_competence";
		$contenu .= PHP_EOL;

		if (count($competences) > 0) {
			foreach ($competences as $c) {
				$contenu .= $c["id_competence"].';';
				$contenu .= $c["nom_systeme_competence"].';';
				$contenu .= $c["nom_competence"].';';
				$contenu .= $c["niveau_requis_competence"].';';
				$contenu .= $c["type_competence"];
				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_competences, $contenu);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierCompetences - exit -");
		return $retour;
	}

	private function genereFichierMetiers() {
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
				$contenu .= $c["id_metier"].';';
				$contenu .= $c["nom_masculin_metier"].';';
				$contenu .= $c["nom_feminin_metier"].';';
				$contenu .= $c["nom_systeme_metier"].';';
				$contenu .= $c["construction_echoppe_metier"].';';
				$contenu .= $c["niveau_min_metier"];
				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_metiers, $contenu);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierMetiers - exit -");
		return $retour;
	}

	private function genereFichierVilles() {
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
				$contenu .= $v["id_ville"].';';
				$contenu .= $v["nom_ville"].';';
				$contenu .= $v["est_capitale_ville"].';';
				$contenu .= $v["x_min_ville"].';';
				$contenu .= $v["y_min_ville"].';';
				$contenu .= $v["x_max_ville"].';';
				$contenu .= $v["y_max_ville"].';';
				$contenu .= $v["id_region"].';';
				$contenu .= $v["nom_region"];
				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_villes, $contenu);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierVilles - exit -");
		return $retour;
	}

	private function genereFichierLieuxVille() {
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
				$contenu .= $v["id_lieu"].';';
				$contenu .= $v["nom_lieu"].';';
				$contenu .= $v["nom_systeme_type_lieu"].';';
				$contenu .= $v["nom_type_lieu"].';';
				$contenu .= $v["id_type_lieu"].';';
				$contenu .= $v["x_lieu"].';';
				$contenu .= $v["y_lieu"].';';
				$contenu .= $v["id_ville"].';';
				$contenu .= $v["id_region"].';';
				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_lieux_villes, $contenu);

		Bral_Util_Log::batchs()->trace("genereFichierBatiments - genereFichierLieuxVille - exit -");
		return $retour;
	}

	private function genereFichierRegions() {
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
				$contenu .= $v["id_region"].';';
				$contenu .= $v["nom_region"].';';
				$contenu .= $v["x_min_region"].';';
				$contenu .= $v["y_min_region"].';';
				$contenu .= $v["x_max_region"].';';
				$contenu .= $v["y_max_region"].';';
				$contenu .= $v["est_pvp_region"];
				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_regions, $contenu);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierRegions - exit -");
		return $retour;
	}

	private function genereFichierEquipement() {
		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierEquipement - enter -");
		$retour = "";
		Zend_Loader::loadClass("Bral_Util_Fichier");
		Zend_Loader::loadClass("Bral_Util_Equipement");

		Zend_Loader::loadClass("Equipement");
		$equipementTable = new Equipement();
		$equipements = $equipementTable->findByIdEquipementWithDetails("all");

		$contenu = "id_equipement;";
		$contenu .= "nom;qualite;emplacement;niveau;id_type_equipement;id_type_emplacement;";
		$contenu .= "nom_type_emplacement;nom_systeme_type_emplacement;nb_runes;armure;force;agilite;vigueur;sagesse;vue;attaque;degat;defense;suffixe;poids;etat_courant;etat_initial;ingredient;nom_systeme_type_ingredient";
		$contenu .= PHP_EOL;

		if (count($equipements) > 0) {
			foreach ($equipements as $e) {
				$contenu .= $e["id_equipement"].';';

				$contenu .= Bral_Util_Equipement::getNomByIdRegion($e, $e["id_fk_region_equipement"]).';';
				$contenu .= $e["nom_type_qualite"].';';
				$contenu .= $e["nom_type_emplacement"].';';
				$contenu .= $e["niveau_recette_equipement"].';';
				$contenu .= $e["id_type_equipement"].';';
				$contenu .= $e["id_type_emplacement"].';';
				$contenu .= $e["nom_type_emplacement"].';';
				$contenu .= $e["nom_systeme_type_emplacement"].';';
				$contenu .= $e["nb_runes_equipement"].';';
				$contenu .= $e["armure_equipement"].';';
				$contenu .= $e["force_equipement"].';';
				$contenu .= $e["agilite_equipement"].';';
				$contenu .= $e["vigueur_equipement"].';';
				$contenu .= $e["sagesse_equipement"].';';
				$contenu .= $e["vue_recette_equipement"].';';
				$contenu .= $e["attaque_equipement"].';';
				$contenu .= $e["degat_equipement"].';';
				$contenu .= $e["defense_equipement"].';';
				$contenu .= $e["suffixe_mot_runique"].';';
				$contenu .= $e["poids_equipement"].';';
				$contenu .= $e["etat_courant_equipement"].';';
				$contenu .= $e["etat_initial_equipement"].';';
				$contenu .= $e["nom_type_ingredient"].';';
				$contenu .= $e["nom_systeme_type_ingredient"];

				$contenu .= PHP_EOL;
			}
		}

		Bral_Util_Fichier::ecrire($this->config->fichier->liste_equipements, $contenu);

		Bral_Util_Log::batchs()->trace("Bral_Batchs_ScriptsPublics - genereFichierEquipement - exit -");
		return $retour;
	}

}
