<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Box_Laban extends Bral_Box_Box
{

	function getTitreOnglet()
	{
		return 'Laban';
	}

	function getNomInterne()
	{
		return 'box_laban';
	}

	function getChargementInBoxes()
	{
		return false;
	}

	function setDisplay($display)
	{
		$this->view->display = $display;
	}

	function render()
	{
		if ($this->view->affichageInterne) {
			$this->data();
			$this->preparePartage();
			$this->view->pocheNom = 'Poche';
			$this->view->pocheNomSysteme = 'Laban';
			$this->view->nb_castars = $this->view->user->castars_braldun;
		}
		$this->view->nom_interne = $this->getNomInterne();
		return $this->view->render('interface/laban.phtml');
	}

	private function preparePartage()
	{
		Zend_Loader::loadClass('ButinPartage');

		$butinPartageTable = new ButinPartage();
		$partages = $butinPartageTable->findByIdBraldun($this->view->user->id_braldun, true);
		$liste = null;
		if (count($partages) > 0) {
			foreach ($partages as $b) {
				$liste .= "<label class='alabel profil' onclick='ouvrirProfilH(" . $b["id_braldun"] . ");'  title='Voir le profil'>";
				$liste .= addslashes(htmlspecialchars($b["prenom_braldun"])) . " " . addslashes(htmlspecialchars($b["nom_braldun"]));
				$liste .= " (n&deg;" . $b["id_braldun"] . ")</label>, ";
			}
		}

		if ($liste != null) {
			$liste = substr($liste, 0, count($liste) - 3);
		}

		$this->view->partageBralduns = $liste;
	}

	function data()
	{

		Zend_Loader::loadClass('Laban');
		Zend_Loader::loadClass('LabanEquipement');
		Zend_Loader::loadClass('LabanGraine');
		Zend_Loader::loadClass('LabanIngredient');
		Zend_Loader::loadClass('LabanMinerai');
		Zend_Loader::loadClass('LabanMunition');
		Zend_Loader::loadClass('LabanPartieplante');
		Zend_Loader::loadClass('LabanAliment');
		Zend_Loader::loadClass('LabanPotion');
		Zend_Loader::loadClass('LabanMateriel');
		Zend_Loader::loadClass('LabanRune');
		Zend_Loader::loadClass('LabanTabac');
		Zend_Loader::loadClass('BraldunsMetiers');
		Zend_Loader::loadClass('Metier');
		Zend_Loader::loadClass('TypePlante');
		Zend_Loader::loadClass('TypePartieplante');

		Zend_Loader::loadClass('Bral_Helper_DetailRune');

		$braldunsMetiersTable = new BraldunsMetiers();
		$braldunsMetierRowset = $braldunsMetiersTable->findMetiersByBraldunId($this->view->user->id_braldun);
		unset($braldunsMetiersTable);

		$metiersTable = new Metier();
		$metiersRowset = $metiersTable->fetchall(null, 'nom_masculin_metier');
		unset($metiersTable);
		$metiersRowset = $metiersRowset->toArray();
		$tabBraldunMetiers = null;
		$tabMetiers = null;

		foreach ($metiersRowset as $m) {
			if ($this->view->user->sexe_braldun == 'feminin') {
				$nom_metier = $m['nom_feminin_metier'];
			} else {
				$nom_metier = $m['nom_masculin_metier'];
			}

			$possedeMetier = false;
			foreach ($braldunsMetierRowset as $h) {
				if ($h['id_metier'] == $m['id_metier']) {
					$possedeMetier = true;
					break;
				}
			}

			if ($possedeMetier == true) {
				$tabBraldunMetiers[$m['nom_systeme_metier']] = array(
					'id_metier' => $m['id_metier'],
					'nom' => $nom_metier,
					'nom_systeme' => $m['nom_systeme_metier'],
					'a_afficher' => true,
				);
			} else {
				$tabMetiers[$m['nom_systeme_metier']] = array(
					'id_metier' => $m['id_metier'],
					'nom' => $m['nom_masculin_metier'],
					'nom_systeme' => $m['nom_systeme_metier'],
					'a_afficher' => false,
				);
			}
		}
		unset($metiersRowset);

		$tabMineraisBruts = null;
		$tabLingots = null;
		$labanMineraiTable = new LabanMinerai();
		$minerais = $labanMineraiTable->findByIdBraldun($this->view->user->id_braldun);
		unset($labanMineraiTable);

		foreach ($minerais as $m) {
			if ($m['quantite_brut_laban_minerai'] > 0) {
				$tabMineraisBruts[] = array(
					'type' => $m['nom_type_minerai'],
					'id_type_minerai' => $m['id_type_minerai'],
					'estLingot' => false,
					'quantite' => $m['quantite_brut_laban_minerai'],
					'poids' => $m['quantite_brut_laban_minerai'] * Bral_Util_Poids::POIDS_MINERAI,
				);

				if (isset($tabMetiers['mineur'])) {
					$tabMetiers['mineur']['a_afficher'] = true;
				}
			}
			if ($m['quantite_lingots_laban_minerai'] > 0) {
				$tabLingots[] = array(
					'type' => $m['nom_type_minerai'],
					'id_type_minerai' => $m['id_type_minerai'],
					'estLingot' => true,
					'quantite' => $m['quantite_lingots_laban_minerai'],
					'poids' => $m['quantite_lingots_laban_minerai'] * Bral_Util_Poids::POIDS_LINGOT,
				);

				if (isset($tabMetiers['forgeron'])) {
					$tabMetiers['forgeron']['a_afficher'] = true;
				}
			}
		}
		unset($minerais);

		$tabLaban = null;
		$labanTable = new Laban();
		$laban = $labanTable->findByIdBraldun($this->view->user->id_braldun);
		unset($labanTable);

		foreach ($laban as $p) {
			$tabLaban = array(
				'nb_peau' => $p['quantite_peau_laban'],
				'nb_cuir' => $p['quantite_cuir_laban'],
				'nb_fourrure' => $p['quantite_fourrure_laban'],
				'nb_planche' => $p['quantite_planche_laban'],
				'nb_rondin' => $p['quantite_rondin_laban'],
				'nb_viande' => 0, // remplit dans renderIngredient
				'nb_viande_poids_unitaire' => 0, // remplit dans renderIngredient
			);

			if ($p['quantite_peau_laban'] > 0) {
				if (isset($tabMetiers['chasseur'])) {
					$tabMetiers['chasseur']['a_afficher'] = true;
				}
			}

			if ($p['quantite_cuir_laban'] > 0 || $p['quantite_fourrure_laban'] > 0) {
				if (isset($tabMetiers['tanneur'])) {
					$tabMetiers['tanneur']['a_afficher'] = true;
				}
			}

			if ($p['quantite_planche_laban'] > 0) {
				if (isset($tabMetiers['menuisier'])) {
					$tabMetiers['menuisier']['a_afficher'] = true;
				}
			}

			if ($p['quantite_rondin_laban'] > 0) {
				if (isset($tabMetiers['bucheron'])) {
					$tabMetiers['bucheron']['a_afficher'] = true;
				}
			}
		}
		unset($laban);

		if ($tabLaban == null) {
			$tabLaban = array(
				'nb_peau' => 0,
				'nb_cuir' => 0,
				'nb_fourrure' => 0,
				'nb_planche' => 0,
				'nb_rondin' => 0,
				'nb_viande' => 0, // remplit dans renderIngredient
				'nb_viande_poids_unitaire' => 0, // remplit dans renderIngredient
			);
		}

		$tabRunesIdentifiees = null;
		$tabRunesNonIdentifiees = null;
		$labanRuneTable = new LabanRune();
		$runes = $labanRuneTable->findByIdBraldun($this->view->user->id_braldun, null, array('niveau_type_rune', 'nom_type_rune'), true);
		unset($labanRuneTable);

		foreach ($runes as $r) {
			if ($r['est_identifiee_rune'] == 'oui') {
				$tabRunesIdentifiees[$r['id_rune_laban_rune']] = array(
					'id_rune' => $r['id_rune_laban_rune'],
					'type' => $r['nom_type_rune'],
					'image' => $r['image_type_rune'],
					'est_identifiee' => $r['est_identifiee_rune'],
					'effet_type_rune' => $r['effet_type_rune'],
				);
			} else {
				$tabRunesNonIdentifiees[$r['id_rune_laban_rune']] = array(
					'id_rune' => $r['id_rune_laban_rune'],
					'type' => $r['nom_type_rune'],
					'image' => 'rune_inconnue.png',
					'est_identifiee' => $r['est_identifiee_rune'],
					'effet_type_rune' => $r['effet_type_rune'],
					'id_identification_braldun' => $r['id_braldun'],
					'prenom_identification_braldun' => $r['prenom_braldun'],
					'nom_identification_braldun' => $r['nom_braldun'],
				);
			}
		}
		unset($runes);

		if ($tabRunesNonIdentifiees != null) {
			//triage des runes non identifiées par id
			ksort($tabRunesNonIdentifiees);
		}

		$this->view->mineraisBruts = $tabMineraisBruts;
		$this->view->lingots = $tabLingots;

		$this->view->nb_runes = count($tabRunesIdentifiees) + count($tabRunesNonIdentifiees);
		$this->view->runesIdentifiees = $tabRunesIdentifiees;
		$this->view->runesNonIdentifiees = $tabRunesNonIdentifiees;

		$this->renderPlante($tabMetiers);
		$this->renderMateriel();
		$this->renderEquipement();
		$this->renderMunition();
		$this->renderPotion();
		$this->renderAliment();
		$this->renderGraine();
		$this->renderIngredient($tabMetiers, $tabLaban);
		$this->renderTabac();

		$this->view->laban = $tabLaban;
		$this->view->tabBraldunMetiers = $tabBraldunMetiers;
		$this->view->tabMetiers = $tabMetiers;

		$this->view->estElementsEtal = false;
		$this->view->estElementsEtalAchat = false;
		$this->view->estElementsAchat = false;

		$this->view->nom_interne = $this->getNomInterne();

		unset($tabBraldunMetiers);
		unset($tabMetiers);
		unset($tabMineraisBruts);
		unset($tabLingots);
		unset($tabRunesIdentifiees);
		unset($tabRunesNonIdentifiees);
	}

	private function renderTabac()
	{
		$tabTabac = null;
		$labanTabacTable = new LabanTabac();
		$tabacs = $labanTabacTable->findByIdBraldun($this->view->user->id_braldun);
		unset($labanTabacTable);

		foreach ($tabacs as $m) {
			if ($m['quantite_feuille_laban_tabac'] > 0) {
				$tabTabac[] = array(
					'type' => $m['nom_type_tabac'],
					'id_type_tabac' => $m['id_type_tabac'],
					'quantite' => $m['quantite_feuille_laban_tabac'],
				);
			}
		}
		unset($tabacs);
		$this->view->tabac = $tabTabac;
	}

	private function renderPlante(&$tabMetiers)
	{
		$typePlantesTable = new TypePlante();
		$typePlantesRowset = $typePlantesTable->findAll();
		unset($typePlantesTable);

		$typePartiePlantesTable = new TypePartieplante();
		$typePartiePlantesRowset = $typePartiePlantesTable->fetchall();
		unset($typePartiePlantesTable);
		$typePartiePlantesRowset = $typePartiePlantesRowset->toArray();

		$tabTypePlantes = null;
		$labanPartiePlanteTable = new LabanPartieplante();
		$partiePlantes = $labanPartiePlanteTable->findByIdBraldun($this->view->user->id_braldun);
		unset($labanPartiePlanteTable);

		foreach ($typePartiePlantesRowset as $p) {
			foreach ($typePlantesRowset as $t) {
				$val = false;
				for ($i = 1; $i <= 4; $i++) {
					if ($t['id_fk_partieplante' . $i . '_type_plante'] == $p['id_type_partieplante']) {
						$val = true;
					}
				}

				if (!isset($tabTypePlantes[$t['categorie_type_plante']][$t['nom_type_plante']])) {
					$tab = array(
						'nom_type_plante' => $t['nom_type_plante'],
						'nom_systeme_type_plante' => $t['nom_systeme_type_plante'],
						'id_type_plante' => $t['id_type_plante'],
					);
					$tabTypePlantes[$t['categorie_type_plante']][$t['nom_type_plante']] = $tab;
				}

				$tabTypePlantes[$t['categorie_type_plante']]['a_afficher'] = false;
				$tabTypePlantes[$t['categorie_type_plante']]['type_plante'][$t['nom_type_plante']]['a_afficher'] = false;
				$tabTypePlantes[$t['categorie_type_plante']]['type_plante'][$t['nom_type_plante']]['parties'][$p['nom_systeme_type_partieplante']]['possible'] = $val;
				$tabTypePlantes[$t['categorie_type_plante']]['type_plante'][$t['nom_type_plante']]['parties'][$p['nom_systeme_type_partieplante']]['quantite'] = 0;
			}
		}
		unset($typePartiePlantesRowset);
		unset($typePlantesRowset);

		$tabTypePlantesBruts = $tabTypePlantes;
		$tabTypePlantesPrepares = $tabTypePlantes;

		foreach ($partiePlantes as $p) {
			if ($p['quantite_laban_partieplante'] > 0) {
				$tabTypePlantesBruts[$p['categorie_type_plante']]['a_afficher'] = true;
				$tabTypePlantesBruts[$p['categorie_type_plante']]['type_plante'][$p['nom_type_plante']]['a_afficher'] = true;
				$tabTypePlantesBruts[$p['categorie_type_plante']]['type_plante'][$p['nom_type_plante']]['parties'][$p['nom_systeme_type_partieplante']]['quantite'] = $p['quantite_laban_partieplante'];
				$tabTypePlantesBruts[$p['categorie_type_plante']]['type_plante'][$p['nom_type_plante']]['parties'][$p['nom_systeme_type_partieplante']]['id_type_partieplante'] = $p['id_type_partieplante'];
				$tabTypePlantesBruts[$p['categorie_type_plante']]['type_plante'][$p['nom_type_plante']]['parties'][$p['nom_systeme_type_partieplante']]['estPreparee'] = false;
				$tabTypePlantesBruts[$p['categorie_type_plante']]['type_plante'][$p['nom_type_plante']]['parties'][$p['nom_systeme_type_partieplante']]['poids'] = $p['quantite_laban_partieplante'] * Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE;
				if (isset($tabMetiers['herboriste'])) {
					$tabMetiers['herboriste']['a_afficher'] = true;
				}
			}

			if ($p['quantite_preparee_laban_partieplante'] > 0) {
				$tabTypePlantesPrepares[$p['categorie_type_plante']]['a_afficher'] = true;
				$tabTypePlantesPrepares[$p['categorie_type_plante']]['type_plante'][$p['nom_type_plante']]['a_afficher'] = true;
				$tabTypePlantesPrepares[$p['categorie_type_plante']]['type_plante'][$p['nom_type_plante']]['parties'][$p['nom_systeme_type_partieplante']]['quantite'] = $p['quantite_preparee_laban_partieplante'];
				$tabTypePlantesPrepares[$p['categorie_type_plante']]['type_plante'][$p['nom_type_plante']]['parties'][$p['nom_systeme_type_partieplante']]['id_type_partieplante'] = $p['id_type_partieplante'];
				$tabTypePlantesPrepares[$p['categorie_type_plante']]['type_plante'][$p['nom_type_plante']]['parties'][$p['nom_systeme_type_partieplante']]['estPreparee'] = true;
				$tabTypePlantesPrepares[$p['categorie_type_plante']]['type_plante'][$p['nom_type_plante']]['parties'][$p['nom_systeme_type_partieplante']]['poids'] = $p['quantite_preparee_laban_partieplante'] * Bral_Util_Poids::POIDS_PARTIE_PLANTE_PREPAREE;
				if (isset($tabMetiers['apothicaire'])) {
					$tabMetiers['apothicaire']['a_afficher'] = true;
				}
			}
		}
		unset($partiePlantes);

		$this->view->typePlantesBruts = $tabTypePlantesBruts;
		$this->view->typePlantesPrepares = $tabTypePlantesPrepares;
	}

	private function renderEquipement()
	{
		$tabEquipements = null;
		$labanEquipementTable = new LabanEquipement();
		$equipements = $labanEquipementTable->findByIdBraldun($this->view->user->id_braldun);
		unset($labanEquipementTable);

		Zend_Loader::loadClass('Bral_Util_Equipement');
		$tabEquipements = Bral_Util_Equipement::prepareTabEquipements($equipements);

		$tabRetour = null;
		if ($tabEquipements != null) {
			foreach ($tabEquipements as $e) {
				$tabRetour[$e['id_type_emplacement']]['equipements'][] = $e;
				$tabRetour[$e['id_type_emplacement']]['nom_type_emplacement'] = $e['emplacement'];
			}
		}

		$this->view->nb_equipements = count($tabEquipements);
		$this->view->equipements = $tabRetour;
	}

	private function renderMateriel()
	{
		$tabMateriels = null;
		$labanMaterielTable = new LabanMateriel();
		$materiels = $labanMaterielTable->findByIdBraldun($this->view->user->id_braldun);
		unset($labanMaterielTable);

		$tabWhere = null;
		foreach ($materiels as $e) {
			$tabMateriels[$e['id_laban_materiel']] = array(
				'id_materiel' => $e['id_laban_materiel'],
				'id_type_materiel' => $e['id_type_materiel'],
				'nom_systeme_type_materiel' => $e['nom_systeme_type_materiel'],
				'nom' => $e['nom_type_materiel'],
				'capacite' => $e['capacite_type_materiel'],
				'durabilite' => $e['durabilite_type_materiel'],
				'usure' => $e['usure_type_materiel'],
				'poids' => $e['poids_type_materiel'],
			);
			$tabWhere[] = $e['id_laban_materiel'];
		}
		unset($materiels);

		$this->view->nb_materiels = count($tabMateriels);
		$this->view->materiels = $tabMateriels;
	}

	private function renderMunition()
	{
		$tabMunitions = null;
		$labanMunitionTable = new LabanMunition();
		$munitions = $labanMunitionTable->findByIdBraldun($this->view->user->id_braldun);
		unset($labanMunitionTable);

		foreach ($munitions as $m) {
			$tabMunitions[] = array(
				'id_type_munition' => $m['id_type_munition'],
				'type' => $m['nom_type_munition'],
				'quantite' => $m['quantite_laban_munition'],
				'poids' => $m['quantite_laban_munition'] * Bral_Util_Poids::POIDS_MUNITION,
			);
		}
		unset($munitions);

		$this->view->nb_munitions = count($tabMunitions);
		$this->view->munitions = $tabMunitions;
	}

	private function renderPotion()
	{
		Zend_Loader::loadClass('Bral_Util_Potion');
		$tabPotions = null;
		$labanPotionTable = new LabanPotion();
		$potions = $labanPotionTable->findByIdBraldun($this->view->user->id_braldun);
		unset($labanPotionTable);

		foreach ($potions as $p) {
			$tabPotions[$p['id_laban_potion']] = array(
				'id_potion' => $p['id_laban_potion'],
				'id_type_potion' => $p['id_type_potion'],
				'nom' => $p['nom_type_potion'],
				'qualite' => $p['nom_type_qualite'],
				'niveau' => $p['niveau_potion'],
				'caracteristique' => $p['caract_type_potion'],
				'bm_type' => $p['bm_type_potion'],
				'caracteristique2' => $p['caract2_type_potion'],
				'bm2_type' => $p['bm2_type_potion'],
				'nom_type' => Bral_Util_Potion::getNomType($p['type_potion']),
			);
		}
		unset($potions);

		$this->view->nb_potions = count($tabPotions);
		$this->view->potions = $tabPotions;
	}

	private function renderAliment()
	{
		$tabAliments = null;
		$labanAlimentTable = new LabanAliment();
		$aliments = $labanAlimentTable->findByIdBraldun($this->view->user->id_braldun);
		unset($labanAlimentTable);

		Zend_Loader::loadClass('Bral_Util_Aliment');
		foreach ($aliments as $p) {
			$tabAliments[$p['id_laban_aliment']] = array(
				'id_aliment' => $p['id_laban_aliment'],
				'id_type_aliment' => $p['id_type_aliment'],
				'nom' => $p['nom_type_aliment'],
				'qualite' => $p['nom_aliment_type_qualite'],
				'bbdf' => $p['bbdf_aliment'],
				'recette' => Bral_Util_Aliment::getNomType($p['type_bbdf_type_aliment']),
				'poids' => $p['poids_unitaire_type_aliment'],
			);
		}
		unset($aliments);

		$this->view->nb_aliments = count($tabAliments);
		$this->view->aliments = $tabAliments;
	}

	private function renderGraine()
	{
		$tabGraines = null;
		$labanGraineTable = new LabanGraine();
		$graines = $labanGraineTable->findByIdBraldun($this->view->user->id_braldun);
		unset($labanGraineTable);

		foreach ($graines as $g) {
			if ($g['quantite_laban_graine'] > 0) {
				$tabGraines[] = array(
					'type' => $g['nom_type_graine'],
					'id_type_graine' => $g['id_type_graine'],
					'quantite' => $g['quantite_laban_graine'],
					'poids' => $g['quantite_laban_graine'] * Bral_Util_Poids::POIDS_POIGNEE_GRAINES,
				);
			}
		}
		unset($graines);

		$this->view->nb_graines = count($tabGraines);
		$this->view->graines = $tabGraines;
	}

	private function renderIngredient(&$tabMetiers, &$tabLaban)
	{
		$tabIngredients = null;
		$labanIngredientTable = new LabanIngredient();
		$ingredients = $labanIngredientTable->findByIdBraldun($this->view->user->id_braldun);
		unset($labanIngredientTable);

		Zend_Loader::loadClass('TypeIngredient');

		foreach ($ingredients as $g) {
			if ($g['quantite_laban_ingredient'] > 0) {

				if ($g['id_type_ingredient'] == TypeIngredient::ID_TYPE_VIANDE_FRAICHE) {
					if (isset($tabMetiers['chasseur'])) {
						$tabMetiers['chasseur']['a_afficher'] = true;
					}
					$tabLaban['nb_viande'] = $g['quantite_laban_ingredient'];
					$tabLaban['nb_viande_poids_unitaire'] = $g['poids_unitaire_type_ingredient'];
				} else {
					$tabIngredients[] = array(
						'type' => $g['nom_type_ingredient'],
						'id_type_ingredient' => $g['id_type_ingredient'],
						'quantite' => $g['quantite_laban_ingredient'],
						'poids' => $g['quantite_laban_ingredient'] * $g['poids_unitaire_type_ingredient'],
					);
					if (isset($tabMetiers['cuisinier'])) {
						$tabMetiers['cuisinier']['a_afficher'] = true;
					}
				}
			}
		}
		unset($ingredients);

		$this->view->nb_ingredients = count($tabIngredients);
		$this->view->ingredients = $tabIngredients;
	}
}
