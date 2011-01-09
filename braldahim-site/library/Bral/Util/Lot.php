<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Util_Lot {

	public static function getLotsByHotel($perimes = false) {
		Zend_Loader::loadClass('Lot');
		$lotTable = new Lot();

		$lots = $lotTable->findByHotel($perimes);
		$retourLots['lots'] = self::prepareLots($lots);
		$retourLots['visiteur'] = true;

		return $retourLots;
	}

	public static function getLotsByIdEchoppe($idEchoppe, $visiteur) {
		Zend_Loader::loadClass('Lot');
		$lotTable = new Lot();

		$lots = $lotTable->findByIdEchoppe($idEchoppe);
		$retourLots['lots'] = self::prepareLots($lots);

		if ($visiteur) {
			$retourLots['visiteur'] = true;
		} else {
			$retourLots['visiteur'] = false;
		}

		return $retourLots;
	}

	public static function getLotByIdsLots($idsLots) {
		Zend_Loader::loadClass('Lot');
		$lotTable = new Lot();

		$lots = $lotTable->findByIdLot($idsLots);
		$tabLots = self::prepareLots($lots);
		if (count($tabLots) != count($idsLots)) {
			throw new Zend_Exception('getLotByIdLot - Lot invalide:'.$idsLots);
		}

		return $tabLots;
	}

	private static function prepareLots($lots) {

		if (count($lots) == 0 || $lots == null) {
			return null;
		}

		$idsLot = null;

		foreach ($lots as $l) {
			$idsLot[] = $l['id_lot'];
		}

		$tabLots = null;

		foreach ($lots as $l) {
			$tabLots[$l['id_lot']] = self::prepareRowLot($l);
		}

		self::prepareLotsContenus($idsLot, $tabLots);

		return $tabLots;
	}

	// TODO améliorer perf si l'on vient d'une échoppe
	private static function prepareLotsContenus($idsLot, &$lots) {

		self::prepareLotEquipement($idsLot, $lots);
		self::prepareLotMateriel($idsLot, $lots);
		self::prepareLotAliment($idsLot, $lots);
		self::prepareLotPotion($idsLot, $lots);
		self::prepareLotIngredient($idsLot, $lots);
		self::prepareLotGraine($idsLot, $lots);
		self::prepareLotMunition($idsLot, $lots);
		self::prepareLotMinerai($idsLot, $lots);
		self::prepareLotPartieplante($idsLot, $lots);
		self::prepareLotRune($idsLot, $lots);
		self::prepareResume($lots);
	}

	private static function prepareResume(&$lots) {

		foreach($lots as $lot) {
			$resume = '';

			if (count($lot['equipements']) > 0) {
				$resume .= count($lot['equipements']). ' équipement'.Bral_Util_String::getPluriel(count($lot['equipements']));
				$resume .= ', ';
			}

			if (count($lot['materiels']) > 0) {
				$resume .= count($lot['materiels']). ' matériel'.Bral_Util_String::getPluriel(count($lot['materiels']));
				$resume .= ', ';
			}

			if (count($lot['aliments']) > 0) {
				$resume .= count($lot['aliments']). ' aliment'.Bral_Util_String::getPluriel(count($lot['aliments']));
				$resume .= ', ';
			}

			if (count($lot['potions']) > 0) {
				$resume .= count($lot['potions']). ' potion'.Bral_Util_String::getPluriel(count($lot['potions']));
				$resume .= ', ';
			}

			if (count($lot['ingredients']) > 0) {
				$resume .= count($lot['ingredients']). ' ingredient'.Bral_Util_String::getPluriel(count($lot['ingredients']));
				$resume .= ', ';
			}

			if (count($lot['runes_non_identifiees']) + count($lot['runes_identifiees']) > 0) {
				$resume .= count($lot['runes_non_identifiees']) + count($lot['runes_identifiees']). ' rune'.Bral_Util_String::getPluriel(count($lot['runes_non_identifiees']) + count($lot['runes_identifiees']));
				$resume .= ', ';
			}

			if (count($lot['munitions']) > 0) {
				$resume .= count($lot['munitions']). ' munition'.Bral_Util_String::getPluriel(count($lot['munitions']));
				$resume .= ', ';
			}

			if (count($lot['graines']) > 0) {
				$resume .= count($lot['graines']). ' graine'.Bral_Util_String::getPluriel(count($lot['graines']));
			}

			if (count($lot['minerais_bruts']) > 0) {
				$resume .= count($lot['minerais_bruts']). ' minerai'.Bral_Util_String::getPluriel(count($lot['minerais_bruts']));
				$resume .= ', ';
			}

			if (count($lot['minerais_lingots']) > 0) {
				$resume .= count($lot['minerais_lingots']). ' lingot'.Bral_Util_String::getPluriel(count($lot['minerais_lingots']));
				$resume .= ', ';
			}

			if (count($lot['partiesplantes_brutes']) > 0) {
				$resume .= count($lot['partiesplantes_brutes']). ' plante'.Bral_Util_String::getPluriel(count($lot['partiesplantes_brutes']));
				$resume .= ', ';
			}

			if (count($lot['partiesplantes_preparees']) > 0) {
				$s = Bral_Util_String::getPluriel(count($lot['partiesplantes_preparees']));
				$resume .= count($lot['partiesplantes_preparees']). ' plante'.$s;
				$resume .= ', ';
			}

			if ($lot['elements'] != null) {

				$details = "";

				if ($lot['elements']['nb_peau'] > 0) {
					$details .= $lot['elements']['nb_peau']. ' peau'.Bral_Util_String::getPluriel($lot['elements']['nb_peau'], 'x');
					$details .= ', ';
				}
				if ($lot['elements']['nb_cuir'] > 0) {
					$details .= $lot['elements']['nb_cuir']. ' cuir'.Bral_Util_String::getPluriel($lot['elements']['nb_cuir']);
					$details .= ', ';
				}
				if ($lot['elements']['nb_fourrure'] > 0) {
					$details .= $lot['elements']['nb_fourrure']. ' fourrure'.Bral_Util_String::getPluriel($lot['elements']['nb_fourrure']);
					$details .= ', ';
				}
				if ($lot['elements']['nb_planche'] > 0) {
					$details .= $lot['elements']['nb_planche']. ' planche'.Bral_Util_String::getPluriel($lot['elements']['nb_planche']);
					$details .= ', ';
				}
				if ($lot['elements']['nb_rondin'] > 0) {
					$details .= $lot['elements']['nb_rondin']. ' rondin'.Bral_Util_String::getPluriel($lot['elements']['nb_rondin']);
					$details .= ', ';
				}
				if ($lot['elements']['nb_viande'] > 0) {
					$details .= $lot['elements']['nb_viande']. ' viande'.Bral_Util_String::getPluriel($lot['elements']['nb_viande']);
					$details .= ', ';
				}

				$resume .= $details;
				$lots[$lot['id_lot']]['details'] .= $details;
			}

			if ($resume != '') {
				$resume = mb_substr($resume, 0, -2);
			}
			$lots[$lot['id_lot']]['resume'] = $resume;

			if ($lots[$lot['id_lot']]['details'] != '') {
				$lots[$lot['id_lot']]['details'] = mb_substr($lots[$lot['id_lot']]['details'], 0, -2);
			}
		}

	}

	private static function prepareLotEquipement($idsLot, &$lots) {
		Zend_Loader::loadClass('LotEquipement');
		Zend_Loader::loadClass('Bral_Util_Equipement');

		$lotEquipementTable = new LotEquipement();

		if ($idsLot != null) {
			$equipements = $lotEquipementTable->findByIdLot($idsLot);
		}

		$tabReturn = array();

		$idEquipements = null;
		if ($equipements != null) {
			foreach ($equipements as $e) {
				$idEquipements[] = $e['id_lot_equipement'];
			}
		}

		if ($idEquipements != null && count($idEquipements) > 0) {
			Zend_Loader::loadClass('EquipementRune');
			$equipementRuneTable = new EquipementRune();
			$equipementRunes = $equipementRuneTable->findByIdsEquipement($idEquipements);

			Zend_Loader::loadClass('EquipementBonus');
			$equipementBonusTable = new EquipementBonus();
			$equipementBonus = $equipementBonusTable->findByIdsEquipement($idEquipements);
		}

		Zend_Loader::loadClass('Bral_Util_Equipement');
		$tabEquipements = Bral_Util_Equipement::prepareTabEquipements($equipements);

		$tabRetour = null;
		if ($tabEquipements != null) {
			foreach($tabEquipements as $e) {
				$lots[$e['id_lot']]['equipements'][$e['id_type_emplacement']]['equipements'][] = $e;
				$lots[$e['id_lot']]['equipements'][$e['id_type_emplacement']]['nom_type_emplacement'] = $e['emplacement'];
				$lots[$e['id_lot']]['details'] .= 'Équipement n°'.$e['id_equipement'].' : '.$e['nom'].', ';
			}
		}
	}

	private static function prepareLotMinerai($idsLot, &$lots) {
		Zend_Loader::loadClass('LotMinerai');

		$lotMineraiTable = new LotMinerai();
		$minerais = $lotMineraiTable->findByIdLot($idsLot);

		$tabReturn = array();

		if (count($minerais) <= 0) {
			return;
		}

		foreach ($minerais as $m) {

			if ($m['quantite_brut_lot_minerai'] > 0) {
				$tabMineraisBruts = array (
					'type' => $m['nom_type_minerai'],
					'id_type_minerai' => $m['id_type_minerai'],
					'estLingot' => false,
					'quantite' => $m['quantite_brut_lot_minerai'],
					'poids' => $m['quantite_brut_lot_minerai'] * Bral_Util_Poids::POIDS_MINERAI,
				);
				$lots[$m['id_fk_lot_lot_minerai']]['minerais_bruts'][] = $tabMineraisBruts;

				$sbrut = '';
				if ($tabMineraisBruts['quantite'] > 1) $sbrut = 's';
				$lots[$m['id_fk_lot_lot_minerai']]['details'] .= $tabMineraisBruts['type']. ' : '.$tabMineraisBruts['quantite']. ' minerai'.$sbrut.' brut'.$sbrut. ', ';
			}
			if ($m['quantite_lingots_lot_minerai'] > 0) {
				$tabLingots = array (
					'type' => $m['nom_type_minerai'],
					'id_type_minerai' => $m['id_type_minerai'],
					'estLingot' => true,
					'quantite' => $m['quantite_lingots_lot_minerai'],
					'poids' => $m['quantite_lingots_lot_minerai'] * Bral_Util_Poids::POIDS_LINGOT,
				);
				$lots[$m['id_fk_lot_lot_minerai']]['minerais_lingots'][] = $tabLingots;
				$slingot = '';
				if ($tabLingots['quantite'] > 1) $slingot = 's';
				$lots[$m['id_fk_lot_lot_minerai']]['details'] .= $tabLingots['type']. ' : '.$tabLingots['quantite'].' lingot'.$slingot. ', ';
			}
		}
	}

	private static function prepareLotPartieplante($idsLot, &$lots) {

		$tabReturn = array();

		Zend_Loader::loadClass('TypePlante');
		$typePlantesTable = new TypePlante();
		$typePlantesRowset = $typePlantesTable->findAll();

		Zend_Loader::loadClass('TypePartieplante');
		$typePartiePlantesTable = new TypePartieplante();
		$typePartiePlantesRowset = $typePartiePlantesTable->fetchall();
		$typePartiePlantesRowset = $typePartiePlantesRowset->toArray();


		$tabTypePlantes = null;
		Zend_Loader::loadClass('LotPartieplante');
		$lotPartieplanteTable = new LotPartieplante();
		$partiePlantes = $lotPartieplanteTable->findByIdLot($idsLot);

		if (count($partiePlantes) <= 0)  {
			return;
		}

		foreach($typePartiePlantesRowset as $p) {
			foreach($typePlantesRowset as $t) {
				$val = false;
				for($i = 1; $i<= 4; $i++) {
					if ($t['id_fk_partieplante'.$i.'_type_plante'] == $p['id_type_partieplante']) {
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

		foreach($lots as $lot) {
			$tabTypePlantesBrutes = $tabTypePlantes;
			$tabTypePlantesPreparees = $tabTypePlantes;
			foreach ($partiePlantes as $p) {
				if ($p['id_fk_lot_lot_partieplante'] != $lot['id_lot']) continue;

				if ($p['quantite_lot_partieplante'] > 0) {
					$tabTypePlantesBrutes[$p['categorie_type_plante']]['a_afficher'] = true;
					$tabTypePlantesBrutes[$p['categorie_type_plante']]['type_plante'][$p['nom_type_plante']]['a_afficher'] = true;
					$tabTypePlantesBrutes[$p['categorie_type_plante']]['type_plante'][$p['nom_type_plante']]['parties'][$p['nom_systeme_type_partieplante']]['quantite'] = $p['quantite_lot_partieplante'];
					$tabTypePlantesBrutes[$p['categorie_type_plante']]['type_plante'][$p['nom_type_plante']]['parties'][$p['nom_systeme_type_partieplante']]['id_type_partieplante'] = $p['id_type_partieplante'];
					$tabTypePlantesBrutes[$p['categorie_type_plante']]['type_plante'][$p['nom_type_plante']]['parties'][$p['nom_systeme_type_partieplante']]['estPreparee'] = false;
					$tabTypePlantesBrutes[$p['categorie_type_plante']]['type_plante'][$p['nom_type_plante']]['parties'][$p['nom_systeme_type_partieplante']]['poids'] = $p['quantite_lot_partieplante'] * Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE;

					$sbrute = '';
					if ($p['quantite_lot_partieplante'] > 1) $sbrute = 's';
					$lots[$p['id_fk_lot_lot_partieplante']]['details'] .= $p['nom_type_plante']. ' : ';
					$lots[$p['id_fk_lot_lot_partieplante']]['details'] .= $p['quantite_lot_partieplante']. ' '.$p['nom_type_plante']. ' brute'.$sbrute;
					$lots[$p['id_fk_lot_lot_partieplante']]['details'] .= ', ';
				}

				if ($p['quantite_preparee_lot_partieplante'] > 0) {
					$tabTypePlantesPreparees[$p['categorie_type_plante']]['a_afficher'] = true;
					$tabTypePlantesPreparees[$p['categorie_type_plante']]['type_plante'][$p['nom_type_plante']]['a_afficher'] = true;
					$tabTypePlantesPreparees[$p['categorie_type_plante']]['type_plante'][$p['nom_type_plante']]['parties'][$p['nom_systeme_type_partieplante']]['quantite'] = $p['quantite_preparee_lot_partieplante'];
					$tabTypePlantesPreparees[$p['categorie_type_plante']]['type_plante'][$p['nom_type_plante']]['parties'][$p['nom_systeme_type_partieplante']]['id_type_partieplante'] = $p['id_type_partieplante'];
					$tabTypePlantesPreparees[$p['categorie_type_plante']]['type_plante'][$p['nom_type_plante']]['parties'][$p['nom_systeme_type_partieplante']]['estPreparee'] = true;
					$tabTypePlantesPreparees[$p['categorie_type_plante']]['type_plante'][$p['nom_type_plante']]['parties'][$p['nom_systeme_type_partieplante']]['poids'] = $p['quantite_preparee_lot_partieplante'] * Bral_Util_Poids::POIDS_PARTIE_PLANTE_PREPAREE;

					$spreparee = '';
					if ($p['quantite_preparee_lot_partieplante'] > 1) $spreparee = 's';
					$lots[$p['id_fk_lot_lot_partieplante']]['details'] .= $p['nom_type_plante']. ' : ';
					$lots[$p['id_fk_lot_lot_partieplante']]['details'] .=  ' et '.$p['quantite_preparee_lot_partieplante']. ' '.$p['nom_type_plante']. ' préparée'.$spreparee;
					$lots[$p['id_fk_lot_lot_partieplante']]['details'] .= ', ';
				}
			}
			$lots[$p['id_fk_lot_lot_partieplante']]['partiesplantes_brutes'] = $tabTypePlantesBrutes;
			$lots[$p['id_fk_lot_lot_partieplante']]['partiesplantes_preparees'] = $tabTypePlantesPreparees;
		}
	}

	private static function prepareLotGraine($idsLot, &$lots) {
		Zend_Loader::loadClass('LotGraine');

		$lotGraineTable = new LotGraine();
		$graines = null;
		$graines = $lotGraineTable->findByIdLot($idsLot);

		$tabReturn = array();

		if (count($graines) <= 0) {
			return;
		}

		foreach ($graines as $g) {
			if ($g['quantite_lot_graine'] > 0) {
				$tabGraines = array(
					'type' => $g['nom_type_graine'],
					'id_type_graine' => $g['id_type_graine'],
					'quantite' => $g['quantite_lot_graine'],
					'poids' => $g['quantite_lot_graine'] * Bral_Util_Poids::POIDS_POIGNEE_GRAINES,
				);
				$lots[$g['id_fk_lot_lot_graine']]['graines'][] = $tabGraines;
				$s = '';
				if ($tabGraines['quantite'] > 1) $s = 's';
				$lots[$g['id_fk_lot_lot_graine']]['details'] .= $tabGraines['type']. ' : '.$tabGraines['quantite']. ' poignée'.$s.' de graines, ';
			}
		}
	}

	private static function prepareLotIngredient($idsLot, &$lots) {
		Zend_Loader::loadClass('LotIngredient');
		Zend_Loader::loadClass('TypeIngredient');

		$lotIngredientTable = new LotIngredient();
		$ingredients = $lotIngredientTable->findByIdLot($idsLot);

		$tabReturn = array();

		if (count($ingredients) <= 0) {
			return;
		}

		foreach ($ingredients as $g) {
			if ($g['quantite_lot_ingredient'] > 0) {
				if ($g['id_type_ingredient'] ==  TypeIngredient::ID_TYPE_VIANDE_FRAICHE) {
					$lots[$g['id_fk_lot_lot_ingredient']]['elements']['nb_viande'] = $g['quantite_lot_ingredient'];
					$lots[$g['id_fk_lot_lot_ingredient']]['elements']['nb_viande_poids_unitaire'] = $g['poids_unitaire_type_ingredient'];
					$s = '';
					if ($g['quantite_lot_ingredient'] > 1) $s = 's';
					$lots[$g['id_fk_lot_lot_ingredient']]['details'] .= $g['quantite_lot_ingredient']. ' viande'.$s.' préparée'.$s;
				} else {
					$tabIngredients = array(
						'type' => $g['nom_type_ingredient'],
						'id_type_ingredient' => $g['id_type_ingredient'],
						'quantite' => $g['quantite_lot_ingredient'],
						'poids' => $g['quantite_lot_ingredient'] * $g['poids_unitaire_type_ingredient'],
					);
					$lots[$g['id_fk_lot_lot_ingredient']]['ingredients'][] = $tabIngredients;

					$s = '';
					if ($tabIngredients['quantite'] > 1) $s = 's';
					$lots[$g['id_fk_lot_lot_ingredient']]['details'] .= $tabIngredients['type']. ' : '.$tabIngredients['quantite'].', ';
				}
			}
		}
	}

	private static function prepareLotElement($lot) {
		$tabObjet = array(
				'nb_peau' => $lot['quantite_peau_lot'],
				'nb_cuir' => $lot['quantite_cuir_lot'],
				'nb_fourrure' => $lot['quantite_fourrure_lot'],
				'nb_planche' => $lot['quantite_planche_lot'],
				'nb_rondin' => $lot['quantite_rondin_lot'],
				'nb_viande' => 0, // remplit dans renderIngredient
				'nb_viande_poids_unitaire' => 0, // remplit dans renderIngredient
		);
		return $tabObjet;
	}

	private static function prepareLotMunition($idsLot, &$lots) {
		Zend_Loader::loadClass('LotMunition');
		Zend_Loader::loadClass('Bral_Util_Equipement');

		$lotMunitionTable = new LotMunition();

		if ($idsLot != null) {
			$munitions = $lotMunitionTable->findByIdLot($idsLot);
		}

		if (count($munitions) <= 0) {
			return;
		}

		foreach ($munitions as $m) {
			$tabMunitions = array(
				'type' => $m['nom_type_munition'],
				'quantite' => $m['quantite_lot_munition'],
				'poids' =>  $m['quantite_lot_munition'] * Bral_Util_Poids::POIDS_MUNITION,
				'type' => $tabMunitions['type'],
				'type_pluriel' => $tabMunitions['type_pluriel'],
			);
			$lots[$m['id_fk_lot_lot_munition']]['munitions'][] = $tabMunitions;
			if ($tabMunitions['quantite'] > 1) {
				$lots[$m['id_fk_lot_lot_munition']]['details'] .= $tabMunitions['quantite'].' '.$tabMunitions['type_pluriel'].', ';
			} else {
				$lots[$m['id_fk_lot_lot_munition']]['details'] .= $tabMunitions['quantite'].' '.$tabMunitions['type'].', ';
			}
		}
	}

	private static function prepareLotAliment($idsLot, &$lots) {
		Zend_Loader::loadClass('LotAliment');
		Zend_Loader::loadClass('Bral_Util_Aliment');

		$lotAlimentTable = new LotAliment();

		if ($idsLot != null) {
			$aliments = $lotAlimentTable->findByIdLot($idsLot);
		}

		if (count($aliments) > 0) {
			foreach($aliments as $e) {
				$tabAliment = array(
					'id_aliment' => $e['id_lot_aliment'],
					'id_lot_aliment' => $e['id_lot_aliment'],
					'id_type_aliment' => $e['id_type_aliment'],
					'nom' => $e['nom_type_aliment'],
					'bbdf' => $e['bbdf_aliment'],
					'qualite' => $e['nom_aliment_type_qualite'],
					'recette' => Bral_Util_Aliment::getNomType($e['type_bbdf_type_aliment']),
				);
				$lots[$e['id_fk_lot_lot_aliment']]['aliments'][] = $tabAliment;
				$lots[$e['id_fk_lot_lot_aliment']]['details'] .= 'Aliment n°'.$tabAliment['id_aliment'].' : '.$tabAliment['nom'].' +'.$tabAliment['bbdf'].'%, ';
			}
		}
	}


	private static function prepareLotMateriel($idsLot, &$lots) {
		Zend_Loader::loadClass('LotMateriel');
		Zend_Loader::loadClass('Bral_Util_Materiel');

		$lotMaterielTable = new LotMateriel();

		if ($idsLot != null) {
			$materiels = $lotMaterielTable->findByIdLot($idsLot);
		}

		$tabReturn = array();

		if (count($materiels) <= 0) {
			return;
		}
		foreach($materiels as $e) {
			if (substr($e['nom_systeme_type_materiel'], 0, 9) == 'charrette') {
				$lots[$e['id_fk_lot_lot_materiel']]['estLotCharrette'] = true;
			}
			$tabMateriel = array(
					'id_lot_materiel' => $e['id_lot_materiel'],
					'id_type_materiel' => $e['id_type_materiel'],
					'nom' => $e['nom_type_materiel'],
					'id_materiel' => $e['id_lot_materiel'],
					'id_type_materiel' => $e['id_type_materiel'],
					'nom_systeme_type_materiel' => $e['nom_systeme_type_materiel'],
					'capacite' => $e['capacite_type_materiel'],
					'durabilite' => $e['durabilite_type_materiel'],
					'usure' => $e['usure_type_materiel'],
					'poids' => $e['poids_type_materiel'],
					'force_base_min_type_materiel' => $e['force_base_min_type_materiel'],
					'agilite_base_min_type_materiel' => $e['agilite_base_min_type_materiel'],
					'sagesse_base_min_type_materiel' => $e['sagesse_base_min_type_materiel'],
					'vigueur_base_min_type_materiel' => $e['vigueur_base_min_type_materiel'],
			);
			$lots[$e['id_fk_lot_lot_materiel']]['materiels'][] = $tabMateriel;
			$lots[$e['id_fk_lot_lot_materiel']]['details'] .= 'Matériel n°'.$tabMateriel['id_materiel'].' : '.$tabMateriel['nom'].', ';
		}
	}

	private static function prepareLotRune($idsLot, &$lots) {
		Zend_Loader::loadClass('LotRune');

		$lotRuneTable = new LotRune();
		if ($idsLot != null) {
			$runes = $lotRuneTable->findByIdLot($idsLot);
		}

		$tabReturn = array();

		if (count($runes) <= 0) {
			return;
		}

		foreach ($runes as $r) {
			if ($r['est_identifiee_rune'] == 'oui') {
				$tabRunesIdentifiees = array(
					'id_rune' => $r['id_rune_lot_rune'],
					'type' => $r['nom_type_rune'],
					'image' => $r['image_type_rune'],
					'est_identifiee' => $r['est_identifiee_rune'],
					'effet_type_rune' => $r['effet_type_rune'],
				);
				$lots[$r['id_fk_lot_lot_rune']]['runes_identifiees'][$r['id_rune_lot_rune']] = $tabRunesIdentifiees;
				$rune = $tabRunesIdentifiees;
			} else {
				$tabRunesNonIdentifiees = array(
					'id_rune' => $r['id_rune_lot_rune'],
					'type' => $r['nom_type_rune'],
					'image' => 'rune_inconnue.png',
					'est_identifiee' => $r['est_identifiee_rune'],
					'effet_type_rune' => $r['effet_type_rune'],
				);
				$lots[$r['id_fk_lot_lot_rune']]['runes_non_identifiees'][$r['id_rune_lot_rune']] = $tabRunesNonIdentifiees;
				$rune = $tabRunesNonIdentifiees;
			}

			$nomRune = 'non identifiée';
			if ($rune['est_identifiee'] == 'oui') {
				$nomRune = $rune['type'];
			}
			$lots[$r['id_fk_lot_lot_rune']]['details'] .= 'Rune n°'.$rune['id_rune'].' : '.$nomRune.', ';
		}
	}

	private static function prepareLotPotion($idsLot, &$lots) {

		Zend_Loader::loadClass('LotPotion');
		Zend_Loader::loadClass('Bral_Util_Potion');
		Zend_Loader::loadClass('Bral_Helper_DetailPotion');

		$lotPotionTable = new LotPotion();

		if ($idsLot != null) {
			$potions = $lotPotionTable->findByIdLot($idsLot);
		}

		$tabReturn = array();

		if (count($potions) <= 0) {
			return;
		}

		foreach($potions as $e) {
			$tabPotion = array(
				'id_lot_potion' => $e['id_lot_potion'],
				'id_type_potion' => $e['id_type_potion'],
				'nom' => $e['nom_type_potion'],
				'id_potion' => $e['id_lot_potion'],
				'qualite' => $e['nom_type_qualite'],
				'niveau' => $e['niveau_potion'],
				'caracteristique' => $e['caract_type_potion'],
				'bm_type' => $e['bm_type_potion'],
				'caracteristique2' => $e['caract2_type_potion'],
				'bm2_type' => $e['bm2_type_potion'],
				'nom_type' => Bral_Util_Potion::getNomType($e['type_potion']),
			);
			$lots[$e['id_fk_lot_lot_potion']]['potions'][] = $tabPotion;
			$lots[$e['id_fk_lot_lot_potion']]['details'] .= $tabPotion['nom_type'].' '.$tabPotion['nom']. ' n°'.$tabPotion['id_potion'].', ';
		}
	}

	private static function prepareRowLot($r) {

		$tab = array('id_lot' => $r['id_lot'],
				'unite_1_lot' => $r['unite_1_lot'],
				'prix_1_lot' => $r['prix_1_lot'],
				'date_debut_lot' => $r['date_debut_lot'],
				'id_fk_vendeur_braldun_lot' => $r['id_fk_vendeur_braldun_lot'],
				'commentaire_lot' => $r['commentaire_lot'],
				'poids_lot' => $r['poids_lot'],
				'estLotCharrette' => false,
				'equipements' => null,
				'materiels' => null,
				'aliments' => null,
				'elements' => self::prepareLotElement($r),
				'potions' => null,
				'ingredients' => null,
				'runes_non_identifiees' => null,
				'runes_identifiees' => null,	
				'munitions' => null,
				'graines' => null,
				'minerais_bruts' => null,
				'minerais_lingots' => null,
				'partiesplantes_brutes' => null,
				'partiesplantes_preparees' => null,
				'details' => '',
				'resume' => '',
		);

		if ($r['date_fin_lot'] != null) {
			$tab['date_fin_lot'] = Bral_Util_ConvertDate::get_datetime_mysql_datetime('\l\e d/m/y à H\h ', $r['date_fin_lot']);
		}

		return $tab;
	}

	public static function transfertLot($idLot, $destination, $idDestination) {

		if ($destination != 'caisse_echoppe'
		&& $destination != 'arriere_echoppe'
		&& $destination != 'laban'
		&& $destination != 'charrette'
		&& $destination != 'coffre') {
			throw new Zend_exception('Erreur Appel Bral_Util_Lot::transfertLot : idLot:'.$idLot.' destination'.$destination);
		}

		$preSuffixe = '';
		if ($destination == 'caisse_echoppe') {
			$preSuffixe = 'caisse_';
			$destination = 'echoppe';
		} elseif ($destination == 'arriere_echoppe') {
			$preSuffixe = 'arriere_';
			$destination = 'echoppe';
		}

		$suffixe1 = strtolower($destination);
		$nomTable = Bral_Util_String::firstToUpper($destination);

		$suffixe2 = $suffixe1.'_';
		if ($destination == 'laban') {
			$suffixe2 = 'braldun_';
		} elseif ($destination == 'charrette') {
			$suffixe2 = '';
		}

		self::transfertLotElement($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination, $preSuffixe);

		self::transfertLotEquipement($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination);
		self::transfertLotMateriel($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination);

		self::transfertLotAliment($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination);

		self::transfertLotGraine($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination);
		self::transfertLotIngredient($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination);

		self::transfertLotMunition($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination);
		self::transfertLotMinerai($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination, $preSuffixe);
		self::transfertLotPartieplante($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination, $preSuffixe);
		self::transfertLotPotion($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination);

		self::transfertLotRune($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination);

		self::supprimeLot($idLot);
	}

	public static function supprimeLot($idLot) {
		Zend_Loader::loadClass('Lot');
		$lotTable = new Lot();
		$where = 'id_lot = '.intval($idLot);
		$lotTable->delete($where);
	}

	private static function transfertLotEquipement($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination) {
		Zend_Loader::loadClass('LotEquipement');

		$lotEquipementTable = new LotEquipement();
		$lots = $lotEquipementTable->findByIdLot($idLot);

		if ($lots == null || count($lots) < 1) {
			return;
		}

		$table = $nomTable.'Equipement';
		Zend_Loader::loadClass($table);
		$equipementTable = new $table();

		foreach($lots as $lot) {
			$data = array(
				'id_'.$suffixe1.'_equipement' => $lot['id_lot_equipement'], //idEquipement,
				'id_fk_'.$suffixe2.$suffixe1.'_equipement' => $idDestination, //idDestination
			);

			$equipementTable->insert($data);
		}
	}

	private static function transfertLotMateriel($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination) {

		Zend_Loader::loadClass('LotMateriel');

		$lotMaterielTable = new LotMateriel();
		$lots = $lotMaterielTable->findByIdLot($idLot);

		if ($lots == null || count($lots) < 1) {
			return;
		}

		$table = $nomTable.'Materiel';
		Zend_Loader::loadClass($table);
		$materielTable = new $table();

		foreach($lots as $lot) {
			$data = array(
				'id_'.$suffixe1.'_materiel' => $lot['id_lot_materiel'], //idMateriel,
				'id_fk_'.$suffixe2.$suffixe1.'_materiel' => $idDestination, //idDestination
			);

			$materielTable->insert($data);
		}

	}

	private static function transfertLotAliment($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination) {
		Zend_Loader::loadClass('LotAliment');

		$lotAlimentTable = new LotAliment();
		$lots = $lotAlimentTable->findByIdLot($idLot);

		if ($lots == null || count($lots) < 1) {
			return;
		}

		$table = $nomTable.'Aliment';
		Zend_Loader::loadClass($table);
		$alimentTable = new $table();

		foreach($lots as $lot) {
			$data = array(
				'id_'.$suffixe1.'_aliment' => $lot['id_lot_aliment'], //idAliment,
				'id_fk_'.$suffixe2.$suffixe1.'_aliment' => $idDestination, //idDestination
			);

			$alimentTable->insert($data);
		}
	}

	private static function transfertLotElement($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination, $preSuffixe) {
		Zend_Loader::loadClass('Lot');

		$lotTable = new Lot();
		$lots = $lotTable->findByIdLot($idLot);

		if ($lots == null || count($lots) < 1) {
			return;
		}

		$table = $nomTable;
		Zend_Loader::loadClass($table);
		$elementTable = new $table();

		foreach($lots as $lot) {
			$data = array(
				'quantite_peau_'.$preSuffixe.$suffixe1 => $lot['quantite_peau_lot'],
				'quantite_cuir_'.$preSuffixe.$suffixe1 => $lot['quantite_cuir_lot'],
				'quantite_fourrure_'.$preSuffixe.$suffixe1 => $lot['quantite_fourrure_lot'],
				'quantite_planche_'.$preSuffixe.$suffixe1 => $lot['quantite_planche_lot'],
				'quantite_rondin_'.$preSuffixe.$suffixe1 => $lot['quantite_rondin_lot'],
			);

			if ($preSuffixe != 'arriere_') {
				$data['quantite_castar_'.$preSuffixe.$suffixe1] = $lot['quantite_castar_lot'];
			}

			if ($suffixe1 == 'laban') {
				$data['id_fk_braldun_laban'] = $idDestination;
			} else {
				$data['id_'.$suffixe1] = $idDestination;
			}

			$elementTable->insertOrUpdate($data);
		}
	}

	private static function transfertLotGraine($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination) {
		Zend_Loader::loadClass('LotGraine');

		$lotGraineTable = new LotGraine();
		$lots = $lotGraineTable->findByIdLot($idLot);

		if ($lots == null || count($lots) < 1) {
			return;
		}

		$table = $nomTable.'Graine';
		Zend_Loader::loadClass($table);
		$graineTable = new $table();

		foreach($lots as $lot) {
			$data = array(
				'quantite_'.$suffixe1.'_graine' => $lot['quantite_lot_graine'],
				'id_fk_type_'.$suffixe1.'_graine' => $lot['id_fk_type_lot_graine'], 
				'id_fk_'.$suffixe2.$suffixe1.'_graine' => $idDestination, 
			);

			$graineTable->insertOrUpdate($data);
		}
	}

	private static function transfertLotIngredient($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination) {
		Zend_Loader::loadClass('LotIngredient');

		$lotIngredientTable = new LotIngredient();
		$lots = $lotIngredientTable->findByIdLot($idLot);

		if ($lots == null || count($lots) < 1) {
			return;
		}

		$table = $nomTable.'Ingredient';
		Zend_Loader::loadClass($table);
		$ingredientTable = new $table();

		foreach($lots as $lot) {
			$data = array(
				'quantite_'.$suffixe1.'_ingredient' => $lot['quantite_lot_ingredient'],
				'id_fk_type_'.$suffixe1.'_ingredient' => $lot['id_fk_type_lot_ingredient'], 
				'id_fk_'.$suffixe2.$suffixe1.'_ingredient' => $idDestination, 
			);

			$ingredientTable->insertOrUpdate($data);
		}
	}

	private static function transfertLotMunition($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination) {
		Zend_Loader::loadClass('LotMunition');

		$lotMunitionTable = new LotMunition();
		$lots = $lotMunitionTable->findByIdLot($idLot);

		if ($lots == null || count($lots) < 1) {
			return;
		}

		$table = $nomTable.'Munition';
		Zend_Loader::loadClass($table);
		$munitionTable = new $table();

		foreach($lots as $lot) {
			$data = array(
				'quantite_'.$suffixe1.'_munition' => $lot['quantite_lot_munition'],
				'id_fk_type_'.$suffixe1.'_munition' => $lot['id_fk_type_lot_munition'], 
				'id_fk_'.$suffixe2.$suffixe1.'_munition' => $idDestination, 
			);

			$munitionTable->insertOrUpdate($data);
		}
	}

	private static function transfertLotPartieplante($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination, $preSuffixe) {
		Zend_Loader::loadClass('LotPartieplante');

		$lotPartieplanteTable = new LotPartieplante();
		$lots = $lotPartieplanteTable->findByIdLot($idLot);

		if ($lots == null || count($lots) < 1) {
			return;
		}

		$table = $nomTable.'Partieplante';
		Zend_Loader::loadClass($table);
		$partieplanteTable = new $table();

		foreach($lots as $lot) {
			$data = array(
				'quantite_'.$preSuffixe.$suffixe1.'_partieplante' => $lot['quantite_lot_partieplante'],
				'quantite_preparee_'.$suffixe1.'_partieplante' => $lot['quantite_preparee_lot_partieplante'],
				'id_fk_type_plante_'.$suffixe1.'_partieplante' => $lot['id_fk_type_plante_lot_partieplante'],
				'id_fk_type_'.$suffixe1.'_partieplante' => $lot['id_fk_type_lot_partieplante'], 
				'id_fk_'.$suffixe2.$suffixe1.'_partieplante' => $idDestination, 
			);

			$partieplanteTable->insertOrUpdate($data);
		}
	}

	private static function transfertLotMinerai($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination, $preSuffixe) {
		Zend_Loader::loadClass('LotMinerai');

		$lotMineraiTable = new LotMinerai();
		$lots = $lotMineraiTable->findByIdLot($idLot);

		if ($lots == null || count($lots) < 1) {
			return;
		}

		$table = $nomTable.'Minerai';
		Zend_Loader::loadClass($table);
		$mineraiTable = new $table();

		foreach($lots as $lot) {
			$data = array(
				'quantite_brut_'.$preSuffixe.$suffixe1.'_minerai' => $lot['quantite_brut_lot_minerai'],
				'quantite_lingots_'.$suffixe1.'_minerai' => $lot['quantite_lingots_lot_minerai'],
				'id_fk_type_'.$suffixe1.'_minerai' => $lot['id_fk_type_lot_minerai'], 
				'id_fk_'.$suffixe2.$suffixe1.'_minerai' => $idDestination, 
			);

			$mineraiTable->insertOrUpdate($data);
		}
	}

	private static function transfertLotPotion($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination) {
		Zend_Loader::loadClass('LotPotion');

		$lotPotionTable = new LotPotion();
		$lots = $lotPotionTable->findByIdLot($idLot);

		if ($lots == null || count($lots) < 1) {
			return;
		}

		$table = $nomTable.'Potion';
		Zend_Loader::loadClass($table);
		$potionTable = new $table();

		foreach($lots as $lot) {
			$data = array(
				'id_'.$suffixe1.'_potion' => $lot['id_lot_potion'], //idPotion,
				'id_fk_'.$suffixe2.$suffixe1.'_potion' => $idDestination, //idDestination
			);

			$potionTable->insert($data);
		}
	}

	private static function transfertLotRune($idLot, $nomTable, $suffixe1, $suffixe2, $idDestination) {
		Zend_Loader::loadClass('LotRune');

		$lotRuneTable = new LotRune();
		$lots = $lotRuneTable->findByIdLot($idLot);

		if ($lots == null || count($lots) < 1) {
			return;
		}

		$table = $nomTable.'Rune';
		Zend_Loader::loadClass($table);
		$runeTable = new $table();

		foreach($lots as $lot) {
			$data = array(
				'id_rune_'.$suffixe1.'_rune' => $lot['id_rune_lot_rune'], //idRune,
				'id_fk_'.$suffixe2.$suffixe1.'_rune' => $idDestination, //idDestination
			);

			$runeTable->insert($data);
		}
	}
}