<?php
/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */

// bouton transfert equipement, potion et materiel dans echoppe ==> edit Boule. On ne remet pas quelque chose dans l'échoppe si c'est déjà sorti.
// On ne transbahute pas depuis l'etal
class Bral_Competences_Transbahuter extends Bral_Competences_Competence {

	const ID_ENDROIT_ELEMENT = 1;
	const ID_ENDROIT_LABAN = 2;
	const ID_ENDROIT_MON_COFFRE = 3;
	const ID_ENDROIT_COFFRE_BRALDUN = 4;
	const ID_ENDROIT_ECHOPPE_CAISSE = 5;
	const ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE = 6;
	const ID_ENDROIT_ECHOPPE_ATELIER = 7;
	const ID_ENDROIT_ECHOPPE_ETAL = 8;
	const ID_ENDROIT_HOTEL = 9;
	const ID_ENDROIT_COFFRE_COMMUNAUTE = 10;
	const ID_ENDROIT_HALL_LIEU = 11;
	const ID_ENDROIT_RESERVATION_COMMUNAUTE = 12;
	const ID_ENDROIT_CHARRETTE = 13;
	const ID_ENDROIT_LABAN_BRALDUN = 14;

	const NB_VALEURS = 23;

	function prepareCommun() {
		Zend_Loader::loadClass('Coffre');
		Zend_Loader::loadClass('Lieu');
		Zend_Loader::loadClass('TypeLieu');
		Zend_Loader::loadClass('Charrette');
		Zend_Loader::loadClass('CharrettePartage');
		Zend_Loader::loadClass('Echoppe');
		Zend_Loader::loadClass('Butin');
		Zend_Loader::loadClass('TypeUnite');
		Zend_Loader::loadClass('Bral_Util_Communaute');
		Zend_Loader::loadClass("TypeEvenementCommunaute");
		Zend_Loader::loadClass("Bral_Util_EvenementCommunaute");
		Zend_Loader::loadClass("Bral_Util_Communaute");
		Zend_Loader::loadClass("Bral_Helper_Communaute");

		$this->view->niveauMarcheCommunaute = Bral_Util_Communaute::getNiveauDuLieu($this->view->user->id_fk_communaute_braldun, TypeLieu::ID_TYPE_MARCHE);

		$this->view->idCharretteEtal = $this->request->get('idCharretteEtal');
		$tabEndroit = array();
		if ($this->view->idCharretteEtal != null) {
			$this->prepareCommunEchoppe($tabEndroit);
		} else {
			//liste des endroits
			//On peut essayer de transbahuter pour le sol et le laban
			$tabEndroit[self::ID_ENDROIT_ELEMENT] = array('id_type_endroit' => self::ID_ENDROIT_ELEMENT, 'nom_systeme' => 'Element', 'nom_type_endroit' => 'Le sol', 'est_depart' => true, 'poids_restant' => -1, 'panneau' => true);
			$poidsRestantLaban = $this->view->user->poids_transportable_braldun - $this->view->user->poids_transporte_braldun;
			$tabEndroit[self::ID_ENDROIT_LABAN] = array('id_type_endroit' => self::ID_ENDROIT_LABAN, 'nom_systeme' => 'Laban', 'nom_type_endroit' => 'Votre laban', 'est_depart' => true, 'poids_restant' => $poidsRestantLaban, 'panneau' => true);

			$this->prepareCommunEchoppe($tabEndroit);
			$this->prepareCommunCharrette($tabEndroit);
			$this->prepareCommunLaban($tabEndroit);
			$this->prepareCommunLieu($tabEndroit);
			$this->prepareCommunCommunaute($tabEndroit);
		}

		$this->prepareCommunButins();

		// On récupère la valeur du départ
		$choixDepart = false;
		if ($this->request->get('valeur_1') != '' && $this->request->get('valeur_1') != -1) {
			$id_courant_depart = Bral_Util_Controle::getValeurIntVerif($this->request->get('valeur_1'));
			if ($id_courant_depart < 1 || !array_key_exists($id_courant_depart, $tabEndroit)) {
				throw new Zend_Exception('Bral_Competences_Transbahuter Valeur invalide : id_type_courant_depart=' . $id_courant_depart);
			}
		} else {
			$id_courant_depart = -1;
		}

		//Construction du tableau des départs
		$tabTypeDepart = null;
		foreach ($tabEndroit as $k => $e) {
			//On ne prend que ce qui peut être dans les départs
			if ($e['est_depart'] == true) {
				$this->view->deposerOk = false;
				if ($e['nom_systeme'] == 'Charrette') {
					$this->view->id_charrette_depart = $e['id_charrette'];
				}
				$this->prepareType($e);
				if ($this->view->deposerOk == true) {
					if ($id_courant_depart == $k) {
						$choixDepart = true;
					}
					$tabTypeDepart[$k] = array('id_type_depart' => $e['id_type_endroit'], 'selected' => $id_courant_depart, 'nom_systeme' => $e['nom_systeme'], 'nom_type_depart' => $e['nom_type_endroit'], 'panneau' => $e['panneau']);
				}
			}
		}

		$this->view->typeDepart = $tabTypeDepart;

		if (count($tabTypeDepart) == 1) {
			$keys = array_keys($tabTypeDepart);
			$id_courant_depart = $keys[0];
			$choixDepart = true;
		}

		//Si on a choisi le départ, on peut choisir l'arrivée
		if ($choixDepart === true) {
			$this->prepareCommunChoixArrivee($tabEndroit, $id_courant_depart);
		}
		$this->view->choixDepart = $choixDepart;
		$this->view->tabEndroit = $tabEndroit;
		$this->view->ID_ENDROIT_ECHOPPE_ETAL = self::ID_ENDROIT_ECHOPPE_ETAL;
		$this->view->ID_ENDROIT_HOTEL = self::ID_ENDROIT_HOTEL;
		$this->view->ID_ENDROIT_CHARRETTE = self::ID_ENDROIT_CHARRETTE;
		$this->view->ID_ENDROIT_LABAN_BRALDUN = self::ID_ENDROIT_LABAN_BRALDUN;
		$this->view->ID_ENDROIT_RESERVATION_COMMUNAUTE = self::ID_ENDROIT_RESERVATION_COMMUNAUTE;
		$this->view->textePrixVente = null;
	}

	private function prepareCommunChoixArrivee($tabEndroit, $id_courant_depart) {
		if ($tabEndroit[$id_courant_depart]['nom_systeme'] == 'Charrette') { // positionnement de la charrette choisie
			$this->view->id_charrette_depart = $tabEndroit[$id_courant_depart]['id_charrette'];
			$id_type_courant_depart = self::ID_ENDROIT_CHARRETTE;
		} else {
			$id_type_courant_depart = $id_courant_depart;
		}

		$tabTypeArrivee = null;
		//Si l'arrivée est déjà choisie on récupère la valeur
		if ($this->request->get('valeur_2') != '') {
			$id_courant_arrivee = Bral_Util_Controle::getValeurIntVerif($this->request->get('valeur_2'));
			$choixArrivee = true;
			if ($id_courant_arrivee < 1 || !array_key_exists($id_courant_arrivee, $tabEndroit) || $id_courant_arrivee == $id_type_courant_depart) {
				throw new Zend_Exception('Bral_Competences_Transbahuter Valeur invalide : id_type_courant_arrivee=' . $id_courant_arrivee);
			}
		} else {
			$id_courant_arrivee = -1;
		}

		$uniteAPreparer = false;
		foreach ($tabEndroit as $k => $e) {

			// si l'on choisit ID_ENDROIT_COFFRE_COMMUNAUTE, seul ID_ENDROIT_RESERVATION_COMMUNAUTE est accessible
			if ($id_type_courant_depart == self::ID_ENDROIT_COFFRE_COMMUNAUTE && $e['id_type_endroit'] != self::ID_ENDROIT_RESERVATION_COMMUNAUTE) continue;
			if ($id_type_courant_depart != self::ID_ENDROIT_COFFRE_COMMUNAUTE && $id_type_courant_depart != self::ID_ENDROIT_HALL_LIEU && $e['id_type_endroit'] == self::ID_ENDROIT_RESERVATION_COMMUNAUTE) continue;

			// la caisse n'est pas accessible en depot
			if ($e['id_type_endroit'] == self::ID_ENDROIT_ECHOPPE_CAISSE) continue;
			// l'atelier n'est pas accessible en depot
			if ($e['id_type_endroit'] == self::ID_ENDROIT_ECHOPPE_ATELIER) continue;
			// le coffre (pour réservation) n'est pas accessible en depot
			if ($e['id_type_endroit'] == self::ID_ENDROIT_COFFRE_COMMUNAUTE) continue;

			// l'étal est accessible uniquement depuis l'atelier
			if ($id_type_courant_depart != self::ID_ENDROIT_ECHOPPE_ATELIER && $e['id_type_endroit'] == self::ID_ENDROIT_ECHOPPE_ETAL) continue;
			if ($id_type_courant_depart == self::ID_ENDROIT_ECHOPPE_ATELIER && $e['id_type_endroit'] == self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE) continue;
			if ($id_type_courant_depart == self::ID_ENDROIT_ECHOPPE_ATELIER && $e['id_type_endroit'] == self::ID_ENDROIT_ECHOPPE_CAISSE) continue;
			if ($id_type_courant_depart == self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE && $e['id_type_endroit'] == self::ID_ENDROIT_ECHOPPE_CAISSE) continue;

			if ($k == $id_courant_depart) continue;
			if ($e['poids_restant'] != -1 && $e['poids_restant'] <= 0) continue;

			$tabTypeArrivee[$k] = array('id_type_arrivee' => $e['id_type_endroit'], 'selected' => $id_courant_arrivee, 'nom_systeme' => $e['nom_systeme'], 'nom_type_arrivee' => $e['nom_type_endroit'], 'poids_restant' => $e['poids_restant']);
			if ($e['id_type_endroit'] == self::ID_ENDROIT_CHARRETTE || $e['id_type_endroit'] == self::ID_ENDROIT_LABAN_BRALDUN) {
				$tabTypeArrivee[$k]['id_destination'] = $e['id_destination'];
			}
			if ($e['id_type_endroit'] == self::ID_ENDROIT_ECHOPPE_ETAL || $e['id_type_endroit'] == self::ID_ENDROIT_HOTEL || $e['id_type_endroit'] == self::ID_ENDROIT_RESERVATION_COMMUNAUTE) {
				$uniteAPreparer = true;
			}
		}

		if ($uniteAPreparer) {
			$this->prepareCommunUnites();
		}
		$this->view->typeArrivee = $tabTypeArrivee;
		$this->view->nb_valeurs = self::NB_VALEURS;
		$this->prepareType($tabEndroit[$id_courant_depart]);
	}

	private function prepareCommunUnites() {
		Zend_Loader::loadClass('TypeUnite');
		$unites[TypeUnite::NOM_SYSTEME_TYPE_CASTARS] = array(
			'id_type_unite' => TypeUnite::ID_TYPE_CASTARS,
			'nom_type_unite' => TypeUnite::NOM_TYPE_CASTARS,
			'nom_pluriel_type_unite' => TypeUnite::NOM_TYPE_PLURIEL_CASTARS,
		);
		$this->view->unites = $unites;
	}

	private function prepareUnitesRowPlante($type, $partiePlante, $num, $forme) {
		$nomForme = 'Préparée';
		if ($forme == 'brute') {
			$nomForme = 'Brute';
		}
		return array('id_type_plante' => $type['id_type_plante'],
					 'id_type_partieplante' => $type['id_fk_partieplante' . $num . '_type_plante'],
					 'nom_systeme_type_unite' => 'plantebrute:' . $type['nom_systeme_type_plante'],
					 'nom_type_unite' => 'Plante ' . $nomForme . ': ' . $type['nom_type_plante'] . ' ' . $partiePlante[$type['id_fk_partieplante' . $num . '_type_plante']]['nom_partieplante'],
					 'type_forme' => $forme);
	}

	private function prepareCommunEchoppe(&$tabEndroit) {
		//Si on est sur une echoppe
		$echoppe = new Echoppe();
		$echoppeCase = $echoppe->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
		if (count($echoppeCase) > 0) {
			if ($this->view->idCharretteEtal != null && $echoppeCase[0]['id_braldun'] == $this->view->user->id_braldun) {
				$tabEndroit[self::ID_ENDROIT_ECHOPPE_ETAL] = array('id_type_endroit' => self::ID_ENDROIT_ECHOPPE_ETAL, 'nom_systeme' => 'Lot', 'nom_type_endroit' => 'Votre échoppe : Étal', 'id_braldun_echoppe' => $echoppeCase[0]['id_braldun'], 'est_depart' => false, 'poids_restant' => -1, 'panneau' => true);
				$tabEndroit[self::ID_ENDROIT_ECHOPPE_ATELIER] = array('id_type_endroit' => self::ID_ENDROIT_ECHOPPE_ATELIER, 'nom_systeme' => 'Echoppe', 'nom_type_endroit' => 'Votre échoppe : Atelier', 'id_braldun_echoppe' => $echoppeCase[0]['id_braldun'], 'est_depart' => true, 'poids_restant' => -1, 'panneau' => true);
				$this->view->id_echoppe_depart = $echoppeCase[0]['id_echoppe'];
			} elseif ($echoppeCase[0]['id_braldun'] == $this->view->user->id_braldun) {
				$tabEndroit[self::ID_ENDROIT_HOTEL] = array('id_type_endroit' => self::ID_ENDROIT_HOTEL, 'nom_systeme' => 'Lot', 'nom_type_endroit' => 'Hôtel des Ventes', 'est_depart' => false, 'poids_restant' => -1, 'panneau' => true);
				$tabEndroit[self::ID_ENDROIT_ECHOPPE_CAISSE] = array('id_type_endroit' => self::ID_ENDROIT_ECHOPPE_CAISSE, 'nom_systeme' => 'Echoppe', 'nom_type_endroit' => 'Votre échoppe : Caisse', 'id_braldun_echoppe' => $echoppeCase[0]['id_braldun'], 'est_depart' => true, 'poids_restant' => -1, 'panneau' => true);
				$tabEndroit[self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE] = array('id_type_endroit' => self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE, 'nom_systeme' => 'Echoppe', 'nom_type_endroit' => 'Votre échoppe : Matières Premières', 'id_braldun_echoppe' => $echoppeCase[0]['id_braldun'], 'est_depart' => true, 'poids_restant' => -1, 'panneau' => true);
				$tabEndroit[self::ID_ENDROIT_ECHOPPE_ATELIER] = array('id_type_endroit' => self::ID_ENDROIT_ECHOPPE_ATELIER, 'nom_systeme' => 'Echoppe', 'nom_type_endroit' => 'Votre échoppe : Atelier', 'id_braldun_echoppe' => $echoppeCase[0]['id_braldun'], 'est_depart' => true, 'poids_restant' => -1, 'panneau' => true);
				$tabEndroit[self::ID_ENDROIT_ECHOPPE_ETAL] = array('id_type_endroit' => self::ID_ENDROIT_ECHOPPE_ETAL, 'nom_systeme' => 'Lot', 'nom_type_endroit' => 'Votre échoppe : Étal', 'id_braldun_echoppe' => $echoppeCase[0]['id_braldun'], 'est_depart' => false, 'poids_restant' => -1, 'panneau' => true);

				$this->view->id_echoppe_depart = $echoppeCase[0]['id_echoppe'];
			} else {
				$tabEndroit[self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE] = array('id_type_endroit' => self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE, 'nom_systeme' => 'Echoppe', 'nom_type_endroit' => 'L\'échoppe de ' . $echoppeCase[0]['prenom_braldun'] . ' ' . $echoppeCase[0]['nom_braldun'], 'id_braldun_echoppe' => $echoppeCase[0]['id_braldun'], 'est_depart' => false, 'poids_restant' => -1, 'panneau' => true);
			}
			$this->view->id_echoppe_arrivee = $echoppeCase[0]['id_echoppe'];
		}
	}

	private function prepareCommunLieu(&$tabEndroit) {
		//Si on est sur une banque ou un hall :
		$lieu = new Lieu();
		$lieux = $lieu->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);

		if (count($lieux) == 1) {


			if ($lieux[0]['id_type_lieu'] == TypeLieu::ID_TYPE_BANQUE || $this->view->niveauMarcheCommunaute >= Bral_Util_Communaute::NIVEAU_MARCHE_COFFRE_PERSO_COMMUN) {
				$tabEndroit[self::ID_ENDROIT_MON_COFFRE] = array('id_type_endroit' => self::ID_ENDROIT_MON_COFFRE, 'nom_systeme' => 'Coffre', 'nom_type_endroit' => 'Votre coffre', 'est_depart' => true, 'poids_restant' => -1, 'panneau' => true);
			}

			if ($lieux[0]['id_type_lieu'] == TypeLieu::ID_TYPE_BANQUE || $this->view->niveauMarcheCommunaute >= Bral_Util_Communaute::NIVEAU_MARCHE_COFFRE_PERSO_VERS_AUTRE) {
				$tabEndroit[self::ID_ENDROIT_COFFRE_BRALDUN] = array('id_type_endroit' => self::ID_ENDROIT_COFFRE_BRALDUN, 'nom_systeme' => 'Coffre', 'nom_type_endroit' => 'Le coffre d\'un autre Braldûn', 'est_depart' => false, 'poids_restant' => -1, 'panneau' => true);
			}

			if ($lieux[0]['id_type_lieu'] == TypeLieu::ID_TYPE_HOTEL || $this->view->niveauMarcheCommunaute >= Bral_Util_Communaute::NIVEAU_MARCHE_COFFRE_COMMUN_VERS_HOTEL) {
				$tabEndroit[self::ID_ENDROIT_HOTEL] = array('id_type_endroit' => self::ID_ENDROIT_HOTEL, 'nom_systeme' => 'Lot', 'nom_type_endroit' => 'Hôtel des Ventes', 'est_depart' => false, 'poids_restant' => -1, 'panneau' => true);
			}

			if ($lieux[0]['id_type_lieu'] == TypeLieu::ID_TYPE_BANQUE || $this->view->user->est_pnj_braldun == 'oui') {
				$tabEndroit[self::ID_ENDROIT_MON_COFFRE] = array('id_type_endroit' => self::ID_ENDROIT_MON_COFFRE, 'nom_systeme' => 'Coffre', 'nom_type_endroit' => 'Votre coffre', 'est_depart' => true, 'poids_restant' => -1, 'panneau' => true);
				$tabEndroit[self::ID_ENDROIT_COFFRE_BRALDUN] = array('id_type_endroit' => self::ID_ENDROIT_COFFRE_BRALDUN, 'nom_systeme' => 'Coffre', 'nom_type_endroit' => 'Le coffre d\'un autre Braldûn', 'est_depart' => false, 'poids_restant' => -1, 'panneau' => true);
			}

			if ($lieux[0]['id_type_lieu'] == TypeLieu::ID_TYPE_HALL || $this->view->niveauMarcheCommunaute >= Bral_Util_Communaute::NIVEAU_MARCHE_COFFRE_PERSO_COMMUN) {
				$estDepart = false;
				if ($lieux[0]['id_fk_communaute_lieu'] == $this->view->user->id_fk_communaute_braldun
					&& $this->view->user->rangCommunaute <= Bral_Util_Communaute::ID_RANG_TENANCIER
				) {
					$estDepart = true;
				}
				$tabEndroit[self::ID_ENDROIT_HALL_LIEU] = array('id_type_endroit' => self::ID_ENDROIT_HALL_LIEU, 'nom_systeme' => 'Coffre', 'nom_type_endroit' => 'Coffre de Communauté (Hall)', 'est_depart' => $estDepart, 'poids_restant' => -1, 'panneau' => true, 'id_communaute' => $this->view->user->id_fk_communaute_braldun);
			}
		}
	}

	private function prepareCommunCommunaute(&$tabEndroit) {
		if ($this->view->user->id_fk_communaute_braldun == null) {
			return;
		}

		if ($this->view->user->rangCommunaute > Bral_Util_Communaute::ID_RANG_TENANCIER) {
			return;
		}

		if (!array_key_exists(self::ID_ENDROIT_HALL_LIEU, $tabEndroit)) {
			$estDepart = true;
			$tabEndroit[self::ID_ENDROIT_COFFRE_COMMUNAUTE] = array('id_type_endroit' => self::ID_ENDROIT_COFFRE_COMMUNAUTE, 'nom_systeme' => 'Coffre', 'nom_type_endroit' => 'Coffre de ma Communauté (pour réservations)', 'est_depart' => $estDepart, 'poids_restant' => -1, 'panneau' => true, 'id_communaute' => $this->view->user->id_fk_communaute_braldun);
		}
		$tabEndroit[self::ID_ENDROIT_RESERVATION_COMMUNAUTE] = array('id_type_endroit' => self::ID_ENDROIT_RESERVATION_COMMUNAUTE, 'nom_systeme' => 'Lot', 'nom_type_endroit' => 'Coffre de Communauté (réservations et ventes)', 'est_depart' => false, 'poids_restant' => -1, 'panneau' => true, 'id_communaute' => $this->view->user->id_fk_communaute_braldun);
	}

	private function prepareCommunCharrette(&$tabEndroit) {

		//Cas des charrettes
		$nbendroit = self::ID_ENDROIT_CHARRETTE;
		$charrette = new Charrette();
		$charrettePartage = new CharrettePartage();

		$tabCharrette = $charrette->findByPositionAvecBraldun($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
		if (count($tabCharrette) < 0) {
			return;
		}

		foreach ($tabCharrette as $c) {
			Zend_Loader::loadClass('Bral_Util_Charrette');
			$tabPoidsCharrette = Bral_Util_Poids::calculPoidsCharrette($c['id_braldun']);
			if ($c['id_braldun'] == $this->view->user->id_braldun) {
				$panneau = Bral_Util_Charrette::possedePanneauAmovible($c['id_charrette']);
				$tabEndroit[$nbendroit] = array('id_type_endroit' => self::ID_ENDROIT_CHARRETTE, 'nom_systeme' => 'Charrette', 'id_charrette' => $c['id_charrette'], 'id_destination' => $c['id_charrette'], 'id_braldun_charrette' => $c['id_fk_braldun_charrette'], 'panneau' => $panneau, 'nom_type_endroit' => 'Votre charrette', 'est_depart' => true, 'poids_restant' => $tabPoidsCharrette['place_restante']);
				//$this->view->id_charrette_depart = $c['id_charrette'];
			} else {
				$estDepart = false;

				if ($c['est_partage_bralduns_charrette'] == 'oui') { // tous les bralduns
					$estDepart = true;
				} else {
					if ($c['est_partage_communaute_charrette'] == 'oui' &&
						$c['id_fk_communaute_braldun'] == $this->view->user->id_fk_communaute_braldun &&
						$this->view->user->rangCommunaute < Bral_Util_Communaute::ID_RANG_NOUVEAU
					) { // bralduns de la communaute
						$estDepart = true;
					}

					if ($estDepart == false) { // on regarde dans les partages bralduns
						$partage = $charrettePartage->findByIdCharretteAndIdBraldun($c['id_charrette'], $this->view->user->id_braldun);
						if ($partage != null && count($partage) > 0) {
							$estDepart = true;
						}
					}
				}

				if ($estDepart == true) {
					$panneau = Bral_Util_Charrette::possedePanneauAmovible($c['id_charrette']);
				} else {
					$panneau = false;
				}

				$tabEndroit[$nbendroit] = array('id_type_endroit' => self::ID_ENDROIT_CHARRETTE, 'nom_systeme' => 'Charrette', 'id_charrette' => $c['id_charrette'], 'id_destination' => $c['id_charrette'], 'id_braldun_charrette' => $c['id_fk_braldun_charrette'], 'nom_type_endroit' => 'La charrette de ' . $c['prenom_braldun'] . ' ' . $c['nom_braldun'] . ' (n°' . $c['id_braldun'] . ')', 'est_depart' => $estDepart, 'panneau' => $panneau, 'poids_restant' => $tabPoidsCharrette['place_restante']);
			}
			$nbendroit++;
		}

	}

	private function prepareCommunLaban(&$tabEndroit) {

		//Cas des charrettes
		$nbendroit = self::ID_ENDROIT_LABAN_BRALDUN;
		$braldunTable = new Braldun();

		$tabBralduns = $braldunTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, $this->view->user->id_braldun, true);
		if (count($tabBralduns) < 0) {
			return;
		}

		foreach ($tabBralduns as $b) {
			$poidsRestantLaban = $b["poids_transportable_braldun"] - $b["poids_transporte_braldun"];
			$tabEndroit[self::ID_ENDROIT_LABAN_BRALDUN] = array('id_type_endroit' => self::ID_ENDROIT_LABAN_BRALDUN, 'nom_systeme' => 'Laban', 'nom_type_endroit' => 'Laban de ' . $b['prenom_braldun'] . ' ' . $b['nom_braldun'] . ' (n°' . $b['id_braldun'] . ')', 'est_depart' => true, 'poids_restant' => $poidsRestantLaban, 'panneau' => true, 'id_destination' => $b['id_braldun']);
			$nbendroit++;
		}

	}

	function prepareFormulaire() {
		if ($this->view->assezDePa == false) {
			return;
		}
	}

	function prepareResultat() {

		$idDepart = Bral_Util_Controle::getValeurIntVerif($this->request->get('valeur_1'));
		$idArrivee = Bral_Util_Controle::getValeurIntVerif($this->request->get('valeur_2'));
		$endroitDepart = null;
		$endroitArrivee = null;
		$idDestination = null;
		foreach ($this->view->tabEndroit as $k => $e) {
			if ($idDepart == $k) {
				$endroitDepart = $e;
				$this->view->a_panneau = $e['panneau'];
			}
			if ($k == $idArrivee && $idArrivee < self::ID_ENDROIT_CHARRETTE) {
				$endroitArrivee = $e;
				$this->view->poidsRestant = $e['poids_restant'];
			} elseif ($k == $idArrivee && $idArrivee >= self::ID_ENDROIT_CHARRETTE) {
				$idDestination = Bral_Util_Controle::getValeurIntVerif($this->request->get('valeur_3'));
				if ($e['nom_systeme'] == 'Charrette' && $idDestination == $e['id_destination']) {
					$endroitArrivee = $e;
					$this->view->id_charrette_arrivee = $idDestination;
					$this->view->poidsRestant = $e['poids_restant'];
				} else if ($e['nom_systeme'] == 'Laban' && $idDestination == $e['id_destination']) {
					$endroitArrivee = $e;
					$this->view->poidsRestant = $e['poids_restant'];
				}
			}
		}
		if ($endroitDepart === null) {
			throw new Zend_Exception(get_class($this) . ' Endroit depart invalide = ' . $idDepart);
		}
		if ($endroitArrivee === null) {
			throw new Zend_Exception(get_class($this) . ' Endroit arrivee invalide = ' . $idArrivee . ' idDestination=' . $idDestination);
		}

		if ($endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_COFFRE_BRALDUN
			|| $endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_MON_COFFRE
			|| $endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_ECHOPPE_ETAL
			|| $endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_RESERVATION_COMMUNAUTE
			|| $endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_LABAN_BRALDUN
		) {
			$idBraldunDestinataire = Bral_Util_Controle::getValeurIntVerif($this->request->get('valeur_3'));
			$this->view->id_braldun_destinataire = null;
			$this->view->id_braldun_destinataire_lot = null;
			if ($idBraldunDestinataire == -1) {
				$this->view->id_braldun_destinataire = $this->view->user->id_braldun;
			} else {

				if ($endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_LABAN_BRALDUN) {
					$this->view->id_braldun_destinataire = $idBraldunDestinataire;
				} else {

					if ($endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_LABAN_BRALDUN) {
						$braldunTable = new Braldun();
						$tabBralduns = $braldunTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, -1, true, false, $idBraldunDestinataire);
						if (count($tabBralduns) != 1) {
							throw new Exception("Braldun " . $idBraldunDestinataire . " non present sur la meme case que " . $this->view->user->id_braldun);
						}
					}
					$this->view->id_braldun_destinataire = $idBraldunDestinataire;
				}

				if ($endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_ECHOPPE_ETAL
					|| $endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_RESERVATION_COMMUNAUTE
				) {
					$this->view->id_braldun_destinataire_lot = $idBraldunDestinataire;
				}
			}

		}

		if ($endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_COFFRE_BRALDUN || $endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_MON_COFFRE) {
			if ($this->view->id_braldun_destinataire != null) {
				$coffreTable = new Coffre();
				$coffre = $coffreTable->findByIdBraldun($this->view->id_braldun_destinataire);
				if (count($coffre) != 1) {
					throw new Zend_Exception(get_class($this) . ' Coffre arrivee invalide = ' . $this->view->id_braldun_destinataire);
				}
				$this->view->id_coffre_arrivee = $coffre[0]['id_coffre'];
			}
		} else if ($endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_COFFRE_COMMUNAUTE || $endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_HALL_LIEU) {
			$coffreTable = new Coffre();
			$coffre = $coffreTable->findByIdCommunaute($endroitArrivee['id_communaute']);
			if (count($coffre) != 1) {
				throw new Zend_Exception(get_class($this) . ' Coffre communaute arrivee invalide = ' . $endroitArrivee['id_communaute'] . ' idType:' . $endroitArrivee['id_type_endroit']);
			}
			$this->view->id_coffre_arrivee = $coffre[0]['id_coffre'];
		}

		$this->view->poidsOk = true;
		$this->view->nbelement = 0;
		$this->view->panneau = true;
		$this->view->elementsRetires = '';
		$this->view->elementsNonRetiresPoids = '';
		$this->view->elementsNonRetiresPanneau = '';
		if ($this->view->id_braldun_destinataire == null) {
			$this->view->id_braldun_destinataire = $this->view->user->id_braldun;
		}
		$this->deposeType($endroitDepart, $endroitArrivee);
		$this->view->depart = $endroitDepart['nom_type_endroit'];

		if ($this->view->nbelement <= 0) {
			return;
		}

		if ($endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_COFFRE_BRALDUN || $endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_LABAN_BRALDUN) {
			Zend_Loader::loadClass('Braldun');
			$braldun = new Braldun();
			$nomBraldun = $braldun->findNomById($this->view->id_braldun_destinataire);
			if ($endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_COFFRE_BRALDUN) {
				$this->view->arrivee = 'le coffre de ' . $nomBraldun;
			} else {
				$this->view->arrivee = 'le laban de ' . $nomBraldun;
			}

		} else {
			$this->view->arrivee = $endroitArrivee['nom_type_endroit'];
		}

		if ($this->view->elementsRetires != '') {
			// on enlève la dernière virgule de la chaîne
			$this->view->elementsRetires = mb_substr($this->view->elementsRetires, 0, -2);
		}

		// Historique
		if ($endroitDepart['id_type_endroit'] == self::ID_ENDROIT_CHARRETTE) {
			Bral_Util_Poids::calculPoidsCharrette($endroitDepart['id_braldun_charrette'], true);
			$texte = $this->calculTexte($endroitDepart['nom_systeme'], $endroitArrivee['nom_systeme']);
			$details = '[b' . $this->view->user->id_braldun . '] a transbahuté des choses depuis la [t' . $endroitDepart['id_destination'] . '] (' . $texte['departTexte'] . ' vers ' . $texte['arriveeTexte'] . ')';
			Zend_Loader::loadClass('Bral_Util_Materiel');
			Bral_Util_Materiel::insertHistorique(Bral_Util_Materiel::HISTORIQUE_TRANSBAHUTER_ID, $endroitDepart['id_destination'], $details);
		}

		if ($endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_CHARRETTE) {
			Bral_Util_Poids::calculPoidsCharrette($endroitArrivee['id_braldun_charrette'], true);
			$texte = $this->calculTexte($endroitDepart['nom_systeme'], $endroitArrivee['nom_systeme']);
			$details = '[b' . $this->view->user->id_braldun . '] a transbahuté des choses dans la [t' . $endroitArrivee['id_destination'] . '] (' . $texte['departTexte'] . ' vers ' . $texte['arriveeTexte'] . ')';
			Zend_Loader::loadClass('Bral_Util_Materiel');
			Bral_Util_Materiel::insertHistorique(Bral_Util_Materiel::HISTORIQUE_TRANSBAHUTER_ID, $endroitArrivee['id_destination'], $details);
		}

		// événements
		$this->detailEvenement = '';
		if ($endroitDepart['id_type_endroit'] == self::ID_ENDROIT_ELEMENT) {
			$idEvenement = $this->view->config->game->evenements->type->ramasser;
			$this->detailEvenement = '[b' . $this->view->user->id_braldun . '] a ramassé des éléments à terre ';
		}
		if ($endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_ELEMENT) {
			$idEvenement = $this->view->config->game->evenements->type->deposer;
			$this->detailEvenement = '[b' . $this->view->user->id_braldun . '] a déposé des éléments à terre ';
		}

		if ($endroitDepart['id_type_endroit'] == self::ID_ENDROIT_COFFRE_BRALDUN || $endroitDepart['id_type_endroit'] == self::ID_ENDROIT_MON_COFFRE
			|| $endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_COFFRE_BRALDUN || $endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_MON_COFFRE
		) {
			$idEvenement = $this->view->config->game->evenements->type->service;
			if ($this->view->id_braldun_destinataire != $this->view->user->id_braldun &&
				($endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_COFFRE_BRALDUN || $endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_MON_COFFRE)
			) {
				$messageCible = $this->view->user->prenom_braldun . ' ' . $this->view->user->nom_braldun . ' a transbahuté ces éléments dans votre coffre : ' . PHP_EOL;
				$messageCible .= $this->view->elementsRetires;
				$this->detailEvenement = '[b' . $this->view->user->id_braldun . '] a transbahuté des éléments dans le coffre de [b' . $this->view->id_braldun_destinataire . ']';
				$this->setDetailsEvenementCible($this->view->id_braldun_destinataire, 'braldun', 0, $messageCible);
			}
			else {
				$this->detailEvenement = '[b' . $this->view->user->id_braldun . '] a utilisé les services de la banque ';
			}
		}
		if ($endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_HALL_LIEU) {
			$details = "[b" . $this->view->user->id_braldun . "] a transbahuté des éléments";
			$detailsBot = "[b" . $this->view->user->id_braldun . "] a transbahuté ces éléments dans le coffre de la communauté : " . PHP_EOL;
			$detailsBot .= $this->view->elementsRetires;
			Bral_Util_EvenementCommunaute::ajoutEvenements($endroitArrivee['id_communaute'], TypeEvenementCommunaute::ID_TYPE_DEPOT, $details, $detailsBot, $this->view);
		}
		if ($endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_RESERVATION_COMMUNAUTE) {
			$details = "[b" . $this->view->user->id_braldun . "] a créé une réservation (Lot)";
			$detailsBot = "[b" . $this->view->user->id_braldun . "] a créé une réservation (Lot) : " . PHP_EOL;
			$detailsBot .= "Création du lot n°" . $this->view->idLot . PHP_EOL;
			$detailsBot .= "Prix de vente : " . $this->view->textePrixVente . PHP_EOL;
			if ($this->view->id_braldun_destinataire_lot != null) {
				$detailsBot .= "Destinataire : [b" . $this->view->id_braldun_destinataire_lot . "]" . PHP_EOL;
			} else {
				$detailsBot .= "Destinataire : non renseigné." . PHP_EOL;
			}
			$detailsBot .= "Contenu : " . $this->view->elementsRetires . PHP_EOL;

			Bral_Util_EvenementCommunaute::ajoutEvenements($endroitArrivee['id_communaute'], TypeEvenementCommunaute::ID_TYPE_CREATION_LOT, $details, $detailsBot, $this->view);
		} else  if ($endroitDepart['id_type_endroit'] == self::ID_ENDROIT_COFFRE_COMMUNAUTE || $endroitDepart['id_type_endroit'] == self::ID_ENDROIT_HALL_LIEU) {
			$details = "[b" . $this->view->user->id_braldun . "] a retiré des éléments";
			$detailsBot = "[b" . $this->view->user->id_braldun . "] a retiré ces éléments depuis le coffre de la communauté : " . PHP_EOL;
			$detailsBot .= $this->view->elementsRetires;
			Bral_Util_EvenementCommunaute::ajoutEvenements($this->view->user->id_fk_communaute_braldun, TypeEvenementCommunaute::ID_TYPE_RETRAIT, $details, $detailsBot, $this->view);
		}

		if ($endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_LABAN_BRALDUN) {
			$idEvenement = $this->view->config->game->evenements->type->transbahuter;
			$messageCible = $this->view->user->prenom_braldun . ' ' . $this->view->user->nom_braldun . ' a transbahuté ces éléments dans votre laban : ' . PHP_EOL;
			$messageCible .= $this->view->elementsRetires;
			$this->detailEvenement = '[b' . $this->view->user->id_braldun . '] a transbahuté des éléments dans le laban de [b' . $endroitArrivee['id_destination'] . ']';
			$this->setDetailsEvenementCible($endroitArrivee['id_destination'], 'braldun', 0, $messageCible);
		} else if ($endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_CHARRETTE) {
			$idEvenement = $this->view->config->game->evenements->type->transbahuter;
			if ($endroitArrivee['id_braldun_charrette'] != $this->view->user->id_braldun) {
				$messageCible = $this->view->user->prenom_braldun . ' ' . $this->view->user->nom_braldun . ' a transbahuté ces éléments dans votre charrette : ' . PHP_EOL;
				$messageCible .= $this->view->elementsRetires;
				$this->detailEvenement = '[b' . $this->view->user->id_braldun . '] a transbahuté des éléments dans la charrette de [b' . $endroitArrivee['id_braldun_charrette'] . ']';
				$this->setDetailsEvenementCible($endroitArrivee['id_braldun_charrette'], 'braldun', 0, $messageCible);
			} else {
				$this->detailEvenement = '[b' . $this->view->user->id_braldun . '] a transbahuté des éléments dans sa charrette ';
			}
		} else if ($endroitArrivee['nom_systeme'] == 'Echoppe') {
			$idEvenement = $this->view->config->game->evenements->type->transbahuter;
			if ($endroitArrivee['id_braldun_echoppe'] != $this->view->user->id_braldun) {
				$messageCible = $this->view->user->prenom_braldun . ' ' . $this->view->user->nom_braldun . ' a transbahuté ces éléments dans votre échoppe : ' . PHP_EOL;
				$messageCible .= $this->view->elementsRetires;
				$this->detailEvenement = '[b' . $this->view->user->id_braldun . '] a transbahuté des éléments dans l\'échoppe de [b' . $endroitArrivee['id_braldun_echoppe'] . ']';
				$this->setDetailsEvenementCible($endroitArrivee['id_braldun_echoppe'], 'braldun', 0, $messageCible);
			} else {
				$this->detailEvenement = '[b' . $this->view->user->id_braldun . '] a transbahuté des éléments dans son échoppe ';
			}
		}

		if ($this->detailEvenement == '') {
			$idEvenement = $this->view->config->game->evenements->type->transbahuter;
			$this->detailEvenement = '[b' . $this->view->user->id_braldun . '] a transbahuté des éléments ';
		}

		if ($endroitArrivee['id_type_endroit'] == self::ID_ENDROIT_RESERVATION_COMMUNAUTE) {
			$this->setNbPaSurcharge(0);
			$this->view->assezDePa = true;
		}
		$this->setDetailsEvenement($this->detailEvenement, $idEvenement);
		$this->setEvenementQueSurOkJet1(false);

		Zend_Loader::loadClass('Bral_Util_Quete');
		$this->view->estQueteEvenement = Bral_Util_Quete::etapePosseder($this->view->user);

		$this->calculBalanceFaim();
		$this->calculPoids();
		$this->majBraldun();
	}

	function getListBoxRefresh() {
		$echoppe = new Echoppe();
		$echoppeCase = $echoppe->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);

		if (array_key_exists(self::ID_ENDROIT_MON_COFFRE, $this->view->tabEndroit) && $this->view->user->est_pnj_braldun == 'non') {
			$tab = array('box_vue', 'box_laban', 'box_coffre', 'box_charrette', 'box_banque');
		} elseif (array_key_exists(self::ID_ENDROIT_HOTEL, $this->view->tabEndroit)) {
			$tab = array('box_vue', 'box_laban', 'box_hotel', 'box_charrette');
		} else {
			$tab = array('box_vue', 'box_laban', 'box_charrette');
		}

		if (count($echoppeCase) > 0) {
			$tab[] = 'box_echoppes';
			$tab[] = 'box_echoppe';
		}

		if (array_key_exists(self::ID_ENDROIT_HALL_LIEU, $this->view->tabEndroit)
			|| (array_key_exists(self::ID_ENDROIT_COFFRE_COMMUNAUTE, $this->view->tabEndroit))
			|| (array_key_exists(self::ID_ENDROIT_RESERVATION_COMMUNAUTE, $this->view->tabEndroit))
		) {
			$tab[] = 'box_communaute_coffre';
			$tab[] = 'box_communaute_evenements';
		}

		return $this->constructListBoxRefresh($tab);
	}

	private function controlePoids($poidsAutorise, $quantite, $poidsElt) {
		if (round($poidsAutorise, 4) < intval($quantite) * floatval($poidsElt)) {
			return false;
		} else {
			return true;
		}
	}

	private function prepareType($endroit) {

		$depart = $endroit['nom_systeme'];
		$idTypeDepart = $endroit['id_type_endroit'];

		if ($this->view->idCharretteEtal != null) {
			$this->prepareTypeMateriel($depart, $idTypeDepart);
			return;
		}

		$this->prepareTypeAutres($depart, $idTypeDepart, $endroit);
		$this->prepareTypeEquipements($depart, $idTypeDepart);
		$this->prepareTypeRunes($depart, $idTypeDepart);
		$this->prepareTypePotions($depart, $idTypeDepart);
		$this->prepareTypeAliments($depart, $idTypeDepart);
		$this->prepareTypeMunitions($depart, $idTypeDepart);
		$this->prepareTypePartiesPlantes($depart, $idTypeDepart);
		$this->prepareTypeMinerais($depart, $idTypeDepart);
		$this->prepareTypeGraines($depart, $idTypeDepart);
		$this->prepareTypeIngredients($depart, $idTypeDepart);
		$this->prepareTypeTabac($depart, $idTypeDepart);
		$this->prepareTypeMateriel($depart, $idTypeDepart);
	}

	private function deposeType($depart, $arrivee) {
		$this->idLot = null;

		if ($arrivee['id_type_endroit'] == self::ID_ENDROIT_ECHOPPE_ETAL
			|| $arrivee['id_type_endroit'] == self::ID_ENDROIT_HOTEL
			|| $arrivee['id_type_endroit'] == self::ID_ENDROIT_RESERVATION_COMMUNAUTE
		) {
			$this->idLot = $this->deposeTypeLot($arrivee);
		}

		if ($this->view->idCharretteEtal != null) {
			$this->deposeTypeMateriel($depart['nom_systeme'], $arrivee['nom_systeme'], $depart['id_type_endroit'], $arrivee['id_type_endroit']);
			return;
		}

		$this->deposeTypeAutres($depart['nom_systeme'], $arrivee['nom_systeme'], $depart['id_type_endroit'], $arrivee['id_type_endroit']);
		$this->deposeTypeEquipements($depart['nom_systeme'], $arrivee['nom_systeme'], $depart['id_type_endroit'], $arrivee['id_type_endroit']);
		$this->deposeTypeRunes($depart['nom_systeme'], $arrivee['nom_systeme'], $depart['id_type_endroit'], $arrivee['id_type_endroit']);
		$this->deposeTypePotions($depart['nom_systeme'], $arrivee['nom_systeme'], $depart['id_type_endroit'], $arrivee['id_type_endroit']);
		$this->deposeTypeAliments($depart['nom_systeme'], $arrivee['nom_systeme'], $depart['id_type_endroit'], $arrivee['id_type_endroit']);
		$this->deposeTypeMunitions($depart['nom_systeme'], $arrivee['nom_systeme'], $depart['id_type_endroit'], $arrivee['id_type_endroit']);
		$this->deposeTypePartiesPlantes($depart['nom_systeme'], $arrivee['nom_systeme'], $depart['id_type_endroit'], $arrivee['id_type_endroit']);
		$this->deposeTypeMinerais($depart['nom_systeme'], $arrivee['nom_systeme'], $depart['id_type_endroit'], $arrivee['id_type_endroit']);
		$this->deposeTypeGraines($depart['nom_systeme'], $arrivee['nom_systeme'], $depart['id_type_endroit'], $arrivee['id_type_endroit']);
		$this->deposeTypeIngredients($depart['nom_systeme'], $arrivee['nom_systeme'], $depart['id_type_endroit'], $arrivee['id_type_endroit']);
		$this->deposeTypeTabac($depart['nom_systeme'], $arrivee['nom_systeme'], $depart['id_type_endroit'], $arrivee['id_type_endroit']);
		$this->deposeTypeMateriel($depart['nom_systeme'], $arrivee['nom_systeme'], $depart['id_type_endroit'], $arrivee['id_type_endroit']);

		if ($arrivee['id_type_endroit'] == self::ID_ENDROIT_ECHOPPE_ETAL || $arrivee['id_type_endroit'] == self::ID_ENDROIT_HOTEL || $arrivee['id_type_endroit'] == self::ID_ENDROIT_RESERVATION_COMMUNAUTE) {
			$this->updatePoidsLot();
		}
	}

	private function deposeTypeLot($arrivee) {

		$prix_1 = $this->request->get('valeur_4');
		$unite_1 = $this->request->get('valeur_5');

		if ((int)$prix_1 . '' != $this->request->get('valeur_4') . '') {
			throw new Zend_Exception(get_class($this) . ' prix 1 invalide');
		} else {
			$prix_1 = (int)$prix_1;
		}

		if ((int)$unite_1 . '' != $this->request->get('valeur_5') . '') {
			throw new Zend_Exception(get_class($this) . ' unite 1 invalide');
		} else {
			$unite_1 = (int)$unite_1;
		}

		if ($unite_1 != TypeUnite::ID_TYPE_CASTARS) {
			throw new Zend_Exception(get_class($this) . ' Type 1 invalide');
		}

		Zend_Loader::loadClass('Lot');
		Zend_Loader::loadClass('TypeLot');

		$poidsLot = 10000;

		$lotTable = new Lot();
		$data = array(
			'poids_lot' => $poidsLot,
		);

		$dateDebut = date('Y-m-d H:0:0');
		$dateFin = null;

		$data['id_fk_vendeur_braldun_lot'] = $this->view->user->id_braldun;

		if ($arrivee['id_type_endroit'] == self::ID_ENDROIT_ECHOPPE_ETAL) {
			$data['id_fk_echoppe_lot'] = $this->view->id_echoppe_depart;
			$data['id_fk_type_lot'] = TypeLot::ID_TYPE_VENTE_ECHOPPE_TOUS;
			$data['id_fk_braldun_lot'] = $this->view->id_braldun_destinataire_lot;
		} elseif ($arrivee['id_type_endroit'] == self::ID_ENDROIT_HOTEL) {
			$data['id_fk_type_lot'] = TypeLot::ID_TYPE_VENTE_HOTEL;
			$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateDebut, 60);
		} elseif ($arrivee['id_type_endroit'] == self::ID_ENDROIT_RESERVATION_COMMUNAUTE) {
			$data['id_fk_communaute_lot'] = $arrivee['id_communaute'];
			$data['id_fk_type_lot'] = TypeLot::ID_TYPE_RESERVATION_COMMUNAUTE_TOUS;
			$data['id_fk_braldun_lot'] = $this->view->id_braldun_destinataire_lot;
		}

		Zend_Loader::loadClass("IdsLot");
		$idsLot = new IdsLot();
		$idLot = $idsLot->prepareNext();
		$data['id_lot'] = $idLot;
		$lotTable->insert($data);

		$s = '';
		if ($prix_1 > 1) $s = 's';
		$this->view->textePrixVente = $prix_1 . ' castar' . $s;

		$commentaire = stripslashes(Bral_Util_BBParser::bbcodeStripPlus($this->request->get('valeur_10')));

		$data = array(
			'date_debut_lot' => $dateDebut,
			'date_fin_lot' => $dateFin,
			'commentaire_lot' => $commentaire,
			'unite_1_lot' => $unite_1,
			'prix_1_lot' => $prix_1,
		);

		$where = 'id_lot=' . $idLot;
		$lotTable->update($data, $where);

		$this->view->idLot = $idLot;
		return $idLot;
	}

	private function updatePoidsLot() {
		$poids = Bral_Util_Poids::calculPoidsLot($this->idLot);
		$lotTable = new Lot();
		$data = array('poids_lot' => $poids);
		$where = 'id_lot=' . $this->idLot;
		$lotTable->update($data, $where);
	}

	private function prepareTypeEquipements($depart, $idTypeDepart) {
		Zend_Loader::loadClass($depart . 'Equipement');
		Zend_Loader::loadClass('Bral_Util_Equipement');
		$tabEquipements = null;
		$equipements = null;

		switch ($idTypeDepart) {
			case self::ID_ENDROIT_LABAN :
				$labanEquipementTable = new LabanEquipement();
				$equipements = $labanEquipementTable->findByIdBraldun($this->view->user->id_braldun);
				unset($labanEquipementTable);
				break;
			case self::ID_ENDROIT_ELEMENT :
				$elementEquipementTable = new ElementEquipement();
				$equipements = $elementEquipementTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
				unset($elementEquipementTable);
				break;
			case self::ID_ENDROIT_MON_COFFRE:
			case self::ID_ENDROIT_HALL_LIEU:
			case self::ID_ENDROIT_COFFRE_COMMUNAUTE:
				$coffreEquipementTable = new CoffreEquipement();
				$equipements = $coffreEquipementTable->findByIdCoffre($this->view->id_coffre_depart);
				unset($coffreEquipementTable);
				break;
			case self::ID_ENDROIT_CHARRETTE :
				$charretteEquipementTable = new CharretteEquipement();
				$equipements = $charretteEquipementTable->findByIdCharrette($this->view->id_charrette_depart);
				unset($charretteEquipementTable);
				break;
			case self::ID_ENDROIT_ECHOPPE_ATELIER :
				$echoppeEquipementTable = new EchoppeEquipement();
				$equipements = $echoppeEquipementTable->findByIdEchoppe($this->view->id_echoppe_depart);
				unset($echoppeEquipementTable);
				break;
		}

		if (count($equipements) > 0) {
			foreach ($equipements as $e) {
				$tabEquipements[$e['id_' . strtolower($depart) . '_equipement']] = array(
					'id_equipement' => $e['id_' . strtolower($depart) . '_equipement'],
					'nom' => Bral_Util_Equipement::getNomByIdRegion($e, $e['id_fk_region_equipement']),
					'qualite' => $e['nom_type_qualite'],
					'niveau' => $e['niveau_recette_equipement'],
					'nb_runes' => $e['nb_runes_equipement'],
					'suffixe' => $e['suffixe_mot_runique'],
					'poids' => $e['poids_equipement'],
					'id_fk_mot_runique' => $e['id_fk_mot_runique_equipement'],
					'id_fk_recette' => $e['id_fk_recette_equipement'],
					'id_fk_type_munition_type_equipement' => $e['id_fk_type_munition_type_equipement'],
					'nb_munition_type_equipement' => $e['nb_munition_type_equipement'],
					'nom_systeme_type_piece' => $e['nom_systeme_type_piece'],
					'id_fk_region' => $e['id_fk_region_equipement'],
				);
			}
			$this->view->deposerOk = true;
		}
		$this->view->equipements = $tabEquipements;
	}

	private function deposeTypeEquipements($depart, $arrivee, $idTypeDepart, $idTypeArrivee) {
		if ($idTypeArrivee == self::ID_ENDROIT_ECHOPPE_CAISSE ||
			$idTypeArrivee == self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE ||
			$idTypeArrivee == self::ID_ENDROIT_ECHOPPE_ATELIER
		) {
			return;
		}

		Zend_Loader::loadClass($depart . 'Equipement');
		Zend_Loader::loadClass($arrivee . 'Equipement');

		$equipements = array();
		$equipements = $this->request->get('valeur_19');

		if ($equipements == 0 || count($equipements) == 0) {
			return; // pas d'equipement selectionné
		}

		if ($depart == 'Charrette' && $this->view->a_panneau === false && $this->view->nbelement > 0) {
			$this->view->panneau = false;
			foreach ($equipements as $idEquipement) {
				if (!array_key_exists($idEquipement, $this->view->equipements)) {
					throw new Zend_Exception(get_class($this) . ' ID Equipement invalide : ' . $idEquipement);
				}
				$equipement = $this->view->equipements[$idEquipement];
				$this->view->elementsNonRetiresPanneau .= 'Equipement n°' . $equipement['id_equipement'] . ' : ' . $equipement['nom'] . ', ';
			}

			return; // pas de panneau et nb. equipements > 0 => d'equipement retiré
		}

		foreach ($equipements as $idEquipement) {
			if (!array_key_exists($idEquipement, $this->view->equipements)) {
				throw new Zend_Exception(get_class($this) . ' ID Equipement invalide : ' . $idEquipement);
			}

			$equipement = $this->view->equipements[$idEquipement];
			$poidsOk = true;
			if ($arrivee == 'Laban' || $arrivee == 'Charrette') {
				$poidsOk = $this->controlePoids($this->view->poidsRestant, 1, $equipement['poids']);
				if ($poidsOk == false) {
					$this->view->poidsOk = false;
					$this->view->elementsNonRetiresPoids .= 'Equipement n°' . $equipement['id_equipement'] . ' : ' . $equipement['nom'] . ', ';
				}
			}
			if ($poidsOk == true) {
				$this->view->nbelement = $this->view->nbelement + 1;

				$where = 'id_' . strtolower($depart) . '_equipement=' . $idEquipement;
				switch ($idTypeDepart) {
					case self::ID_ENDROIT_LABAN :
						$departEquipementTable = new LabanEquipement();
						break;
					case self::ID_ENDROIT_ELEMENT :
						$departEquipementTable = new ElementEquipement();
						break;
					case self::ID_ENDROIT_MON_COFFRE :
					case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
					case self::ID_ENDROIT_HALL_LIEU :
						$departEquipementTable = new CoffreEquipement();
						break;
					case self::ID_ENDROIT_CHARRETTE :
						$departEquipementTable = new CharretteEquipement();
						break;
					case self::ID_ENDROIT_ECHOPPE_ATELIER :
						$departEquipementTable = new EchoppeEquipement();
						break;
					default:
						throw new Zend_Exception('Depart Equipement invalide : ' . $depart . ' id:' . $idTypeDepart);
				}

				$departEquipementTable->delete($where);
				unset($departEquipementTable);

				switch ($idTypeArrivee) {
					case self::ID_ENDROIT_LABAN :
					case self::ID_ENDROIT_LABAN_BRALDUN :
						if ($equipement['nom_systeme_type_piece'] == 'munition') {
							Zend_Loader::loadClass('LabanMunition');
							$arriveeEquipementTable = new LabanMunition();
							$data = array(
								'id_fk_braldun_laban_munition' => $this->view->id_braldun_destinataire,
								'id_fk_type_laban_munition' => $equipement['id_fk_type_munition_type_equipement'],
								'quantite_laban_munition' => $equipement['nb_munition_type_equipement'],
							);
							$arriveeEquipementTable->insertOrUpdate($data);
						}
						else {
							$arriveeEquipementTable = new LabanEquipement();
							$data = array(
								'id_laban_equipement' => $equipement['id_equipement'],
								'id_fk_braldun_laban_equipement' => $this->view->id_braldun_destinataire,
							);
							$arriveeEquipementTable->insert($data);
						}
						$this->view->poidsRestant = $this->view->poidsRestant - $equipement['poids'];
						break;
					case self::ID_ENDROIT_ELEMENT :
						$dateCreation = date('Y-m-d H:i:s');
						$nbJours = Bral_Util_De::get_2d10();
						$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

						if ($equipement['nom_systeme_type_piece'] == 'munition') {
							Zend_Loader::loadClass('ElementMunition');
							$arriveeEquipementTable = new ElementMunition();
							$data = array(
								'x_element_munition' => $this->view->user->x_braldun,
								'y_element_munition' => $this->view->user->y_braldun,
								'z_element_munition' => $this->view->user->z_braldun,
								'date_fin_element_munition' => $dateFin,
								'id_fk_type_element_munition' => $equipement['id_fk_type_munition_type_equipement'],
								'quantite_element_munition' => $equipement['nb_munition_type_equipement'],
							);
							$arriveeEquipementTable->insertOrUpdate($data);
						} else {
							$arriveeEquipementTable = new ElementEquipement();
							$data = array(
								'id_element_equipement' => $equipement['id_equipement'],
								'x_element_equipement' => $this->view->user->x_braldun,
								'y_element_equipement' => $this->view->user->y_braldun,
								'z_element_equipement' => $this->view->user->z_braldun,
								'date_fin_element_equipement' => $dateFin,
							);
							$arriveeEquipementTable->insert($data);
						}
						break;
					case self::ID_ENDROIT_MON_COFFRE :
					case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
					case self::ID_ENDROIT_HALL_LIEU :
					case self::ID_ENDROIT_COFFRE_BRALDUN :
						$arriveeEquipementTable = new CoffreEquipement();
						$data = array(
							'id_coffre_equipement' => $equipement['id_equipement'],
							'id_fk_coffre_coffre_equipement' => $this->view->id_coffre_arrivee,
						);
						$arriveeEquipementTable->insert($data);
						break;
					case self::ID_ENDROIT_HOTEL :
					case self::ID_ENDROIT_ECHOPPE_ETAL :
					case self::ID_ENDROIT_RESERVATION_COMMUNAUTE :
						$arriveeEquipementTable = new LotEquipement();
						$data = array(
							'id_lot_equipement' => $equipement['id_equipement'],
							'id_fk_lot_lot_equipement' => $this->idLot,
						);
						$arriveeEquipementTable->insert($data);
						break;
					case self::ID_ENDROIT_CHARRETTE :
						if ($equipement['nom_systeme_type_piece'] == 'munition') {
							Zend_Loader::loadClass('CharretteMunition');
							$arriveeEquipementTable = new CharretteMunition();
							$data = array(
								'id_fk_charrette_munition' => $this->view->id_charrette_arrivee,
								'id_fk_type_charrette_munition' => $equipement['id_fk_type_munition_type_equipement'],
								'quantite_charrette_munition' => $equipement['nb_munition_type_equipement'],
							);
							$arriveeEquipementTable->insertOrUpdate($data);
						}
						else {
							$arriveeEquipementTable = new CharretteEquipement();
							$data = array(
								'id_charrette_equipement' => $equipement['id_equipement'],
								'id_fk_charrette_equipement' => $this->view->id_charrette_arrivee,
							);
							$arriveeEquipementTable->insert($data);
						}
						break;
					/* On remet pas de piece dans la charrette*/
				}
				unset($arriveeEquipementTable);
				$this->view->elementsRetires .= 'Equipement n°' . $equipement['id_equipement'] . ' : ' . $equipement['nom'] . ', ';

				$texte = $this->calculTexte($depart, $arrivee);
				$details = '[b' . $this->view->user->id_braldun . '] a transbahuté la pièce d\'équipement n°' . $equipement['id_equipement'] . ' (' . $texte['departTexte'] . ' vers ' . $texte['arriveeTexte'] . ')';
				Bral_Util_Equipement::insertHistorique(Bral_Util_Equipement::HISTORIQUE_TRANSBAHUTER_ID, $equipement['id_equipement'], $details);
			}
		}
	}

	private function prepareTypeRunes($depart, $idTypeDepart) {
		if ($idTypeDepart != self::ID_ENDROIT_ECHOPPE_CAISSE &&
			$idTypeDepart != self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE &&
			$idTypeDepart != self::ID_ENDROIT_ECHOPPE_ATELIER &&
			$idTypeDepart != self::ID_ENDROIT_ECHOPPE_ETAL
		) {

			Zend_Loader::loadClass($depart . 'Rune');
			$tabRunes = null;
			$runes = null;

			switch ($idTypeDepart) {
				case self::ID_ENDROIT_LABAN :
					$labanRuneTable = new LabanRune();
					$runes = $labanRuneTable->findByIdBraldun($this->view->user->id_braldun, null, "est_identifiee_rune");
					unset($labanRuneTable);
					break;
				case self::ID_ENDROIT_ELEMENT :
					$elementRuneTable = new ElementRune();
					$runes = $elementRuneTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, true, $this->tabButins);
					unset($elementruneTable);
					break;
				case self::ID_ENDROIT_MON_COFFRE:
				case self::ID_ENDROIT_HALL_LIEU:
				case self::ID_ENDROIT_COFFRE_COMMUNAUTE:
					$coffreRuneTable = new CoffreRune();
					$runes = $coffreRuneTable->findByIdCoffre($this->view->id_coffre_depart, null, "est_identifiee_rune");
					unset($coffreRuneTable);
					break;
				case self::ID_ENDROIT_CHARRETTE :
					$charretteRuneTable = new CharretteRune();
					$runes = $charretteRuneTable->findByIdCharrette($this->view->id_charrette_depart, null, "est_identifiee_rune");
					unset($charretteRuneTable);
					break;
			}

			if (count($runes) > 0) {
				foreach ($runes as $r) {
					$tabRunes[$r['id_rune_' . strtolower($depart) . '_rune']] = array(
						'id_rune' => $r['id_rune_' . strtolower($depart) . '_rune'],
						'type' => $r['nom_type_rune'],
						'image' => $r['image_type_rune'],
						'est_identifiee' => $r['est_identifiee_rune'],
						'effet_type_rune' => $r['effet_type_rune'],
						'id_fk_type_rune' => $r['id_fk_type_rune'],
						'info' => '',
					);
					if ($depart == 'Element' && $r['id_fk_butin_element_rune'] != null) {
						$tabRunes[$r['id_rune_' . strtolower($depart) . '_rune']]['info'] = ' (Butin n°' . $r['id_fk_butin_element_rune'] . ')';
					}
				}
				$this->view->deposerOk = true;
			}
			$this->view->runes = $tabRunes;
		}
		else {
			$this->view->runes = null;
		}
	}

	private function deposeTypeRunes($depart, $arrivee, $idTypeDepart, $idTypeArrivee) {
		if ($idTypeDepart == self::ID_ENDROIT_ECHOPPE_CAISSE ||
			$idTypeDepart == self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE ||
			$idTypeDepart == self::ID_ENDROIT_ECHOPPE_ATELIER ||
			$idTypeDepart == self::ID_ENDROIT_ECHOPPE_ETAL ||
			$idTypeArrivee == self::ID_ENDROIT_ECHOPPE_CAISSE ||
			$idTypeArrivee == self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE ||
			$idTypeArrivee == self::ID_ENDROIT_ECHOPPE_ATELIER ||
			$idTypeArrivee == self::ID_ENDROIT_ECHOPPE_ETAL
		) {
			return;
		}

		Zend_Loader::loadClass($depart . 'Rune');
		Zend_Loader::loadClass($arrivee . 'Rune');
		Zend_Loader::loadClass('Bral_Util_Rune');

		$runes = array();
		$runes = $this->request->get('valeur_21');
		if (count($runes) == 0 || $runes == 0) {
			return; // pas de rune selectionnée
		}

		if ($depart == 'Charrette' && $this->view->a_panneau === false && $this->view->nbelement > 0) {
			$this->view->panneau = false;
			foreach ($runes as $idRune) {
				if (!array_key_exists($idRune, $this->view->runes)) {
					throw new Zend_Exception(get_class($this) . ' ID Rune invalide : ' . $idRune);
				}
				$rune = $this->view->runes[$idRune];
				$nomRune = 'non identifiée';
				if ($rune['est_identifiee'] == 'oui') {
					$nomRune = $rune['type'];
				}
				$this->view->elementsNonRetiresPanneau .= 'Rune n°' . $rune['id_rune'] . ' : ' . $nomRune . ', ';
			}

			return; // pas de panneau
		}

		foreach ($runes as $idRune) {
			if (!array_key_exists($idRune, $this->view->runes)) {
				throw new Zend_Exception(get_class($this) . ' ID Rune invalide : ' . $idRune);
			}

			$rune = $this->view->runes[$idRune];
			$poidsOk = true;
			if ($arrivee == 'Laban' || $arrivee == 'Charrette') {
				$poidsOk = $this->controlePoids($this->view->poidsRestant, 1, Bral_Util_Poids::POIDS_RUNE);
				if ($poidsOk == false) {
					$this->view->poidsOk = false;
					$nomRune = 'non identifiée';
					if ($rune['est_identifiee'] == 'oui') {
						$nomRune = $rune['type'];
					}
					$this->view->elementsNonRetiresPoids .= 'Rune n°' . $rune['id_rune'] . ' : ' . $nomRune . ', ';
				}
			}
			if ($poidsOk == true) {
				$this->view->nbelement = $this->view->nbelement + 1;

				$where = 'id_rune_' . strtolower($depart) . '_rune=' . $idRune;

				switch ($idTypeDepart) {
					case self::ID_ENDROIT_LABAN :
						$departRuneTable = new LabanRune();
						break;
					case self::ID_ENDROIT_ELEMENT :
						$departRuneTable = new ElementRune();
						break;
					case self::ID_ENDROIT_MON_COFFRE :
					case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
					case self::ID_ENDROIT_HALL_LIEU :
						$departRuneTable = new CoffreRune();
						break;
					case self::ID_ENDROIT_CHARRETTE :
						$departRuneTable = new CharretteRune();
						break;
					default:
						throw new Zend_Exception('Depart Rune invalide : ' . $depart . ' id:' . $idTypeDepart);
				}

				$departRuneTable->delete($where);
				unset($departRuneTable);


				$arriveeRuneTable = null;

				switch ($idTypeArrivee) {
					case self::ID_ENDROIT_LABAN :
					case self::ID_ENDROIT_LABAN_BRALDUN :
						$arriveeRuneTable = new LabanRune();
						$data = array(
							'id_rune_laban_rune' => $rune['id_rune'],
							'id_fk_braldun_laban_rune' => $this->view->id_braldun_destinataire,
						);
						$this->view->poidsRestant = $this->view->poidsRestant - Bral_Util_Poids::POIDS_RUNE;
						break;
					case self::ID_ENDROIT_ELEMENT :
						$dateCreation = date('Y-m-d H:i:s');
						$nbJours = Bral_Util_De::get_2d10();
						$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

						$arriveeRuneTable = new ElementRune();
						$data = array(
							'id_rune_element_rune' => $rune['id_rune'],
							'x_element_rune' => $this->view->user->x_braldun,
							'y_element_rune' => $this->view->user->y_braldun,
							'z_element_rune' => $this->view->user->z_braldun,
							'date_fin_element_rune' => $dateFin,
						);
						break;
					case self::ID_ENDROIT_MON_COFFRE :
					case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
					case self::ID_ENDROIT_HALL_LIEU :
					case self::ID_ENDROIT_COFFRE_BRALDUN :
						$arriveeRuneTable = new CoffreRune();
						$data = array(
							'id_rune_coffre_rune' => $rune['id_rune'],
							'id_fk_coffre_coffre_rune' => $this->view->id_coffre_arrivee,
						);
						break;
					case self::ID_ENDROIT_HOTEL :
					case self::ID_ENDROIT_ECHOPPE_ETAL :
					case self::ID_ENDROIT_RESERVATION_COMMUNAUTE :
						$arriveeRuneTable = new LotRune();
						$data = array(
							'id_rune_lot_rune' => $rune['id_rune'],
							'id_fk_lot_lot_rune' => $this->idLot,
						);
						break;
					case self::ID_ENDROIT_CHARRETTE :
						$arriveeRuneTable = new CharretteRune();
						$data = array(
							'id_rune_charrette_rune' => $rune['id_rune'],
							'id_fk_charrette_rune' => $this->view->id_charrette_arrivee,
						);
						break;
					default:
						throw new Zend_Exception('Erreur idTypeArrivee:' . $idTypeArrivee);
				}
				$arriveeRuneTable->insert($data);
				unset($arriveeRuneTable);
				$nomRune = 'non identifiée';
				if ($rune['est_identifiee'] == 'oui') {
					$nomRune = $rune['type'];
				}
				$this->view->elementsRetires .= 'Rune n°' . $rune['id_rune'] . ' : ' . $nomRune . ', ';

				$texte = $this->calculTexte($depart, $arrivee);
				$details = '[b' . $this->view->user->id_braldun . '] a transbahuté la rune n°' . $rune['id_rune'] . ' (' . $texte['departTexte'] . ' vers ' . $texte['arriveeTexte'] . ')';
				Bral_Util_Rune::insertHistorique(Bral_Util_Rune::HISTORIQUE_TRANSBAHUTER_ID, $rune['id_rune'], $details);
			}
		}
	}

	private function prepareTypePotions($depart, $idTypeDepart) {
		Zend_Loader::loadClass($depart . 'Potion');
		Zend_Loader::loadClass('Bral_Util_Potion');
		$tabPotions = null;
		$potions = null;

		switch ($idTypeDepart) {
			case self::ID_ENDROIT_LABAN :
				$labanPotionTable = new LabanPotion();
				$potions = $labanPotionTable->findByIdBraldun($this->view->user->id_braldun);
				unset($labanPotionTable);
				break;
			case self::ID_ENDROIT_ELEMENT :
				$elementPotionTable = new ElementPotion();
				$potions = $elementPotionTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
				unset($elementPotionTable);
				break;
			case self::ID_ENDROIT_MON_COFFRE:
			case self::ID_ENDROIT_HALL_LIEU:
			case self::ID_ENDROIT_COFFRE_COMMUNAUTE:
				$coffrePotionTable = new CoffrePotion();
				$potions = $coffrePotionTable->findByIdCoffre($this->view->id_coffre_depart);
				unset($coffrePotionTable);
				break;
			case self::ID_ENDROIT_CHARRETTE :
				$charrettePotionTable = new CharrettePotion();
				$potions = $charrettePotionTable->findByIdCharrette($this->view->id_charrette_depart);
				unset($charrettePotionTable);
				break;
			case self::ID_ENDROIT_ECHOPPE_ATELIER :
				$echoppePotionTable = new EchoppePotion();
				$potions = $echoppePotionTable->findByIdEchoppe($this->view->id_echoppe_depart);
				unset($echoppePotionTable);
				break;
		}

		$retour = $this->calculEchoppe('cuisinier');

		if (count($potions) > 0) {
			foreach ($potions as $p) {
				$tabPotions[$p['id_' . strtolower($depart) . '_potion']] = array(
					'id_potion' => $p['id_' . strtolower($depart) . '_potion'],
					'nom' => $p['nom_type_potion'],
					'qualite' => $p['nom_type_qualite'],
					'niveau' => $p['niveau_potion'],
					'caracteristique' => $p['caract_type_potion'],
					'bm_type' => $p['bm_type_potion'],
					'caracteristique2' => $p['caract2_type_potion'],
					'bm2_type' => $p['bm2_type_potion'],
					'nom_type' => Bral_Util_Potion::getNomType($p['type_potion']),
					'id_fk_type_qualite' => $p['id_fk_type_qualite_potion'],
					'id_fk_type' => $p['id_fk_type_potion']
				);
			}
			$this->view->deposerOk = true;
		}
		$this->view->potions = $tabPotions;
	}

	private function deposeTypePotions($depart, $arrivee, $idTypeDepart, $idTypeArrivee) {

		if ($idTypeArrivee == self::ID_ENDROIT_ECHOPPE_CAISSE) {
			return;
		}

		if ($idTypeArrivee == self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE
			&& $this->view->idEchoppe == null
		) { // echoppe cuisinier calcule dans prepare{
			return;
		}

		Zend_Loader::loadClass($depart . 'Potion');
		Zend_Loader::loadClass($arrivee . 'Potion');
		$potions = array();
		$potions = $this->request->get('valeur_22');
		if (count($potions) == 0 || $potions == 0) {
			return;
		}

		if ($depart == 'Charrette' && $this->view->a_panneau === false && $this->view->nbelement > 0) {
			$this->view->panneau = false;
			foreach ($potions as $idPotion) {
				if (!array_key_exists($idPotion, $this->view->potions)) {
					throw new Zend_Exception(get_class($this) . ' ID Potion invalide : ' . $idPotion);
				}
				$potion = $this->view->potions[$idPotion];
				$this->view->elementsNonRetiresPanneau .= $potion['nom_type'] . ' ' . $potion['nom'] . ' n°' . $potion['id_potion'] . ', ';
			}
			return; // pas de panneau
		}

		foreach ($potions as $idPotion) {
			if (!array_key_exists($idPotion, $this->view->potions)) {
				throw new Zend_Exception(get_class($this) . ' ID Potion invalide : ' . $idPotion);
			}

			$potion = $this->view->potions[$idPotion];
			$poidsOk = true;
			if ($arrivee == 'Laban' || $arrivee == 'Charrette') {
				$poidsOk = $this->controlePoids($this->view->poidsRestant, 1, Bral_Util_Poids::POIDS_POTION);
				if ($poidsOk == false) {
					$this->view->poidsOk = false;
					$this->view->elementsNonRetiresPoids .= $potion['nom_type'] . ' ' . $potion['nom'] . ' n°' . $potion['id_potion'] . ', ';
				}
			}
			if ($poidsOk == true) {
				$this->view->nbelement = $this->view->nbelement + 1;
				$where = 'id_' . strtolower($depart) . '_potion=' . $idPotion;
				switch ($idTypeDepart) {
					case self::ID_ENDROIT_LABAN :
						$departPotionTable = new LabanPotion();
						break;
					case self::ID_ENDROIT_ELEMENT :
						$departPotionTable = new ElementPotion();
						break;
					case self::ID_ENDROIT_MON_COFFRE :
					case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
					case self::ID_ENDROIT_HALL_LIEU :
						$departPotionTable = new CoffrePotion();
						break;
					case self::ID_ENDROIT_CHARRETTE :
						$departPotionTable = new CharrettePotion();
						break;
					case self::ID_ENDROIT_ECHOPPE_ATELIER :
						$departPotionTable = new EchoppePotion();
						break;
					default:
						throw new Zend_Exception('Depart Potion invalide : ' . $depart . ' id:' . $idTypeDepart);
				}

				$departPotionTable->delete($where);
				unset($departPotionTable);

				switch ($idTypeArrivee) {
					case self::ID_ENDROIT_LABAN :
					case self::ID_ENDROIT_LABAN_BRALDUN :
						$arriveePotionTable = new LabanPotion();
						$data = array(
							'id_laban_potion' => $potion['id_potion'],
							'id_fk_braldun_laban_potion' => $this->view->id_braldun_destinataire,
						);
						$this->view->poidsRestant = $this->view->poidsRestant - Bral_Util_Poids::POIDS_POTION;
						break;
					case self::ID_ENDROIT_ELEMENT :
						$dateCreation = date('Y-m-d H:i:s');
						$nbJours = Bral_Util_De::get_2d10();
						$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

						$arriveePotionTable = new ElementPotion();
						$data = array(
							'id_element_potion' => $potion['id_potion'],
							'x_element_potion' => $this->view->user->x_braldun,
							'y_element_potion' => $this->view->user->y_braldun,
							'z_element_potion' => $this->view->user->z_braldun,
							'date_fin_element_potion' => $dateFin,
						);
						break;
					case self::ID_ENDROIT_MON_COFFRE :
					case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
					case self::ID_ENDROIT_HALL_LIEU :
					case self::ID_ENDROIT_COFFRE_BRALDUN :
						$arriveePotionTable = new CoffrePotion();
						$data = array(
							'id_coffre_potion' => $potion['id_potion'],
							'id_fk_coffre_coffre_potion' => $this->view->id_coffre_arrivee,
						);
						break;
					case self::ID_ENDROIT_HOTEL :
					case self::ID_ENDROIT_ECHOPPE_ETAL :
					case self::ID_ENDROIT_RESERVATION_COMMUNAUTE :
						$arriveePotionTable = new LotPotion();
						$data = array(
							'id_lot_potion' => $potion['id_potion'],
							'id_fk_lot_lot_potion' => $this->idLot,
						);
						break;
					case self::ID_ENDROIT_CHARRETTE :
						$arriveePotionTable = new CharrettePotion();
						$data = array(
							'id_charrette_potion' => $potion['id_potion'],
							'id_fk_charrette_potion' => $this->view->id_charrette_arrivee,
						);
						break;
					case self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE :
						// si le joueur est sur son echoppe de cuisinier
						if ($this->calculEchoppe('cuisinier')) {
							$arriveePotionTable = new EchoppePotion();
							$data = array(
								'id_echoppe_potion' => $potion['id_potion'],
								'id_fk_echoppe_echoppe_potion' => $this->view->id_echoppe_arrivee,
							);
							break;
						}
				}
				$arriveePotionTable->insert($data);
				unset($arriveePotionTable);
				$this->view->elementsRetires .= $potion['nom_type'] . ' ' . $potion['nom'] . ' n°' . $potion['id_potion'] . ', ';

				$texte = $this->calculTexte($depart, $arrivee);
				$details = '[b' . $this->view->user->id_braldun . '] a transbahuté ' . $potion['nom_type'] . ' ' . $potion['nom'] . ' n°' . $potion['id_potion'] . ' (' . $texte['departTexte'] . ' vers ' . $texte['arriveeTexte'] . ')';
				Bral_Util_Potion::insertHistorique(Bral_Util_Potion::HISTORIQUE_TRANSBAHUTER_ID, $potion['id_potion'], $details);
			}
		}
	}

	private function prepareTypeAliments($depart, $idTypeDepart) {
		if ($idTypeDepart != self::ID_ENDROIT_ECHOPPE_CAISSE &&
			$idTypeDepart != self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE &&
			$idTypeDepart != self::ID_ENDROIT_ECHOPPE_ETAL
		) {

			Zend_Loader::loadClass($depart . 'Aliment');
			$tabAliments = null;
			$aliments = null;

			switch ($idTypeDepart) {
				case self::ID_ENDROIT_LABAN :
					$labanAlimentTable = new LabanAliment();
					$aliments = $labanAlimentTable->findByIdBraldun($this->view->user->id_braldun);
					unset($labanAlimentTable);
					break;
				case self::ID_ENDROIT_ELEMENT :
					$elementAlimentTable = new ElementAliment();
					$aliments = $elementAlimentTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
					unset($elementAlimentTable);
					break;
				case self::ID_ENDROIT_MON_COFFRE:
				case self::ID_ENDROIT_HALL_LIEU:
				case self::ID_ENDROIT_COFFRE_COMMUNAUTE:
					$coffreAlimentTable = new CoffreAliment();
					$aliments = $coffreAlimentTable->findByIdCoffre($this->view->id_coffre_depart);
					unset($coffreAlimentTable);
					break;
				case self::ID_ENDROIT_CHARRETTE :
					$charretteAlimentTable = new CharretteAliment();
					$aliments = $charretteAlimentTable->findByIdCharrette($this->view->id_charrette_depart);
					unset($charretteAlimentTable);
					break;
				case self::ID_ENDROIT_ECHOPPE_ATELIER :
					$echoppeAlimentTable = new EchoppeAliment();
					$aliments = $echoppeAlimentTable->findByIdEchoppe($this->view->id_echoppe_depart);
					unset($echoppeAlimentTable);
					break;
			}

			if (count($aliments) > 0) {
				foreach ($aliments as $p) {
					$tabAliments[$p['id_' . strtolower($depart) . '_aliment']] = array(
						'id_aliment' => $p['id_' . strtolower($depart) . '_aliment'],
						'nom' => $p['nom_type_aliment'],
						'qualite' => $p['nom_type_qualite'],
						'bbdf' => $p['bbdf_aliment'],
						'id_fk_type_qualite' => $p['id_fk_type_qualite_aliment'],
						'id_fk_type' => $p['id_fk_type_aliment'],
						'poids' => $p['poids_unitaire_type_aliment'],
					);
				}
				$this->view->deposerOk = true;
			}
			$this->view->aliments = $tabAliments;
		} else {
			$this->view->aliments = null;
		}
	}

	private function deposeTypeAliments($depart, $arrivee, $idTypeDepart, $idTypeArrivee) {
		if ($idTypeDepart == self::ID_ENDROIT_ECHOPPE_CAISSE ||
			$idTypeDepart == self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE ||
			$idTypeDepart == self::ID_ENDROIT_ECHOPPE_ETAL ||
			$idTypeArrivee == self::ID_ENDROIT_ECHOPPE_CAISSE ||
			$idTypeArrivee == self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE ||
			$idTypeArrivee == self::ID_ENDROIT_ECHOPPE_ATELIER
		) {
			return;
		}

		Zend_Loader::loadClass($depart . 'Aliment');
		Zend_Loader::loadClass($arrivee . 'Aliment');

		$aliments = array();
		$aliments = $this->request->get('valeur_20');
		if (count($aliments) == 0 || $aliments == 0) {
			return;
		}

		if ($depart == 'Charrette' && $this->view->a_panneau === false && $this->view->nbelement > 0) {
			$this->view->panneau = false;
			foreach ($aliments as $idAliment) {
				if (!array_key_exists($idAliment, $this->view->aliments)) {
					throw new Zend_Exception(get_class($this) . ' ID Aliment invalide : ' . $idAliment);
				}
				$aliment = $this->view->aliments[$idAliment];
				$this->view->elementsNonRetiresPanneau .= 'Aliment n°' . $aliment['id_aliment'] . ' : ' . $aliment['nom'] . ' +' . $aliment['bbdf'] . '%, ';
			}
			return; // pas de panneau
		}

		foreach ($aliments as $idAliment) {
			if (!array_key_exists($idAliment, $this->view->aliments)) {
				throw new Zend_Exception(get_class($this) . ' ID Aliment invalide : ' . $idAliment);
			}

			$aliment = $this->view->aliments[$idAliment];
			$poidsOk = true;
			if ($arrivee == 'Laban' || $arrivee == 'Charrette') {
				$poidsOk = $this->controlePoids($this->view->poidsRestant, 1, $aliment['poids']);
				if ($poidsOk == false) {
					$this->view->poidsOk = false;
					$this->view->elementsNonRetiresPoids .= 'Aliment n°' . $aliment['id_aliment'] . ' : ' . $aliment['nom'] . ' +' . $aliment['bbdf'] . '%, ';
				}
			}
			if ($poidsOk == true) {
				$this->view->nbelement = $this->view->nbelement + 1;
				$where = 'id_' . strtolower($depart) . '_aliment=' . $idAliment;
				switch ($idTypeDepart) {
					case self::ID_ENDROIT_LABAN :
						$departAlimentTable = new LabanAliment();
						break;
					case self::ID_ENDROIT_ELEMENT :
						$departAlimentTable = new ElementAliment();
						break;
					case self::ID_ENDROIT_MON_COFFRE :
					case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
					case self::ID_ENDROIT_HALL_LIEU :
						$departAlimentTable = new CoffreAliment();
						break;
					case self::ID_ENDROIT_CHARRETTE :
						$departAlimentTable = new CharretteAliment();
						break;
					case self::ID_ENDROIT_ECHOPPE_ATELIER :
						$departAlimentTable = new EchoppeAliment();
						break;
					default:
						throw new Zend_Exception('Depart Aliments invalide : ' . $depart . ' id:' . $idTypeDepart);
				}
				$departAlimentTable->delete($where);
				unset($departAlimentTable);

				switch ($idTypeArrivee) {
					case self::ID_ENDROIT_LABAN :
					case self::ID_ENDROIT_LABAN_BRALDUN :
						$arriveeAlimentTable = new LabanAliment();
						$data = array(
							'id_laban_aliment' => $aliment['id_aliment'],
							'id_fk_braldun_laban_aliment' => $this->view->id_braldun_destinataire,
						);
						$this->view->poidsRestant = $this->view->poidsRestant - $aliment['poids'];
						break;
					case self::ID_ENDROIT_ELEMENT :
						$dateCreation = date('Y-m-d H:i:s');
						$nbJours = Bral_Util_De::get_2d10();
						$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

						$arriveeAlimentTable = new ElementAliment();
						$data = array(
							'id_element_aliment' => $aliment['id_aliment'],
							'x_element_aliment' => $this->view->user->x_braldun,
							'y_element_aliment' => $this->view->user->y_braldun,
							'z_element_aliment' => $this->view->user->z_braldun,
							'date_fin_element_aliment' => $dateFin,
						);
						break;
					case self::ID_ENDROIT_MON_COFFRE :
					case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
					case self::ID_ENDROIT_HALL_LIEU :
					case self::ID_ENDROIT_COFFRE_BRALDUN :
						$arriveeAlimentTable = new CoffreAliment();
						$data = array(
							'id_coffre_aliment' => $aliment['id_aliment'],
							'id_fk_coffre_coffre_aliment' => $this->view->id_coffre_arrivee,
						);
						break;
					case self::ID_ENDROIT_HOTEL :
					case self::ID_ENDROIT_ECHOPPE_ETAL :
					case self::ID_ENDROIT_RESERVATION_COMMUNAUTE :
						$arriveeAlimentTable = new LotAliment();
						$data = array(
							'id_lot_aliment' => $aliment['id_aliment'],
							'id_fk_lot_lot_aliment' => $this->idLot,
						);
						break;
					case self::ID_ENDROIT_CHARRETTE :
						$arriveeAlimentTable = new CharretteAliment();
						$data = array(
							'id_charrette_aliment' => $aliment['id_aliment'],
							'id_fk_charrette_aliment' => $this->view->id_charrette_arrivee,
						);
						break;
					default:
						throw new Zend_Exception('Erreur idTypeArrivee:' . $idTypeArrivee);
				}
				$arriveeAlimentTable->insert($data);
				unset($arriveeAlimentTable);
				$this->view->elementsRetires .= 'Aliment n°' . $aliment['id_aliment'] . ' : ' . $aliment['nom'] . ' +' . $aliment['bbdf'] . '%, ';
			}
		}
	}

	private function prepareTypeMunitions($depart, $idTypeDepart) {
		if ($idTypeDepart != self::ID_ENDROIT_ECHOPPE_CAISSE &&
			$idTypeDepart != self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE &&
			$idTypeDepart != self::ID_ENDROIT_ECHOPPE_ETAL
		) {

			Zend_Loader::loadClass($depart . 'Munition');
			$tabMunitions = null;
			$munitions = null;

			switch ($idTypeDepart) {
				case self::ID_ENDROIT_LABAN :
					$labanMunitionTable = new LabanMunition();
					$munitions = $labanMunitionTable->findByIdBraldun($this->view->user->id_braldun);
					unset($labanMunitionTable);
					break;
				case self::ID_ENDROIT_ELEMENT :
					$elementMunitionTable = new ElementMunition();
					$munitions = $elementMunitionTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
					unset($elementMunitionTable);
					break;
				case self::ID_ENDROIT_MON_COFFRE:
				case self::ID_ENDROIT_HALL_LIEU:
				case self::ID_ENDROIT_COFFRE_COMMUNAUTE:
					$coffreMunitionTable = new CoffreMunition();
					$munitions = $coffreMunitionTable->findByIdCoffre($this->view->id_coffre_depart);
					unset($coffreMunitionTable);
					break;
				case self::ID_ENDROIT_CHARRETTE :
					$charretteMunitionTable = new CharretteMunition();
					$munitions = $charretteMunitionTable->findByIdCharrette($this->view->id_charrette_depart);
					unset($charretteMunitionTable);
					break;
				case self::ID_ENDROIT_ECHOPPE_ATELIER :
					$echoppeMunitionTable = new EchoppeMunition();
					$munitions = $echoppeMunitionTable->findByIdEchoppe($this->view->id_echoppe_depart);
					unset($echoppeMunitionTable);
					break;
			}

			if (count($munitions) > 0) {
				foreach ($munitions as $m) {
					if ($m['quantite_' . strtolower($depart) . '_munition'] > 0) {
						$this->view->nb_valeurs = $this->view->nb_valeurs + 1;
						$tabMunitions[$this->view->nb_valeurs] = array(
							'id_type_munition' => $m['id_fk_type_' . strtolower($depart) . '_munition'],
							'type' => $m['nom_type_munition'],
							'type_pluriel' => $m['nom_pluriel_type_munition'],
							'quantite' => $m['quantite_' . strtolower($depart) . '_munition'],
							'indice_valeur' => $this->view->nb_valeurs,
						);
					}
				}
				$this->view->deposerOk = true;
			}
			$this->view->valeur_fin_munitions = $this->view->nb_valeurs;
			$this->view->munitions = $tabMunitions;
		}
		else {
			$this->view->valeur_fin_munitions = $this->view->nb_valeurs;
			$this->view->munitions = null;
		}
	}

	private function deposeTypeMunitions($depart, $arrivee, $idTypeDepart, $idTypeArrivee) {
		if ($idTypeDepart == self::ID_ENDROIT_ECHOPPE_CAISSE ||
			$idTypeDepart == self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE ||
			$idTypeDepart == self::ID_ENDROIT_ECHOPPE_ETAL ||
			$idTypeArrivee == self::ID_ENDROIT_ECHOPPE_CAISSE ||
			$idTypeArrivee == self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE ||
			$idTypeArrivee == self::ID_ENDROIT_ECHOPPE_ATELIER
		) {
			return;
		}

		Zend_Loader::loadClass($depart . 'Munition');
		Zend_Loader::loadClass($arrivee . 'Munition');

		if (count($this->view->munitions) == 0) {
			return;
		}

		$idMunition = null;
		$nbMunition = null;

		for ($i = 24; $i <= $this->view->valeur_fin_munitions; $i++) {

			if ($this->request->get('valeur_' . $i) <= 0) {
				continue;
			}
			$nbMunition = Bral_Util_Controle::getValeurIntVerif($this->request->get('valeur_' . $i));

			$munition = $this->view->munitions[$i];

			if ($nbMunition > $munition['quantite'] || $nbMunition < 0) {
				throw new Zend_Exception(get_class($this) . ' Quantite Munition invalide : ' . $nbMunition);
			}

			if ($depart == 'Charrette' && $this->view->a_panneau === false && $this->view->nbelement > 0) {
				$this->view->panneau = false;
				$this->view->elementsNonRetiresPanneau .= $nbMunition . ' ' . $munition['type_pluriel'] . ', ';
			}

			$poidsOk = true;
			if ($arrivee == 'Laban' || $arrivee == 'Charrette') {
				$poidsOk = $this->controlePoids($this->view->poidsRestant, $nbMunition, Bral_Util_Poids::POIDS_MUNITION);
				if ($poidsOk == false) {
					$this->view->poidsOk = false;
					$this->view->elementsNonRetiresPoids .= $nbMunition . ' ' . $munition['type_pluriel'] . ', ';
				}
			}

			if ($poidsOk != true || $this->view->panneau == false) {
				continue;
			}

			$this->view->nbelement = $this->view->nbelement + 1;

			switch ($idTypeDepart) {
				case self::ID_ENDROIT_LABAN :
					$departMunitionTable = new LabanMunition();
					$data = array(
						'quantite_laban_munition' => -$nbMunition,
						'id_fk_type_laban_munition' => $munition['id_type_munition'],
						'id_fk_braldun_laban_munition' => $this->view->user->id_braldun,
					);
					break;
				case self::ID_ENDROIT_ELEMENT :
					$departMunitionTable = new ElementMunition();
					$data = array(
						'x_element_munition' => $this->view->user->x_braldun,
						'y_element_munition' => $this->view->user->y_braldun,
						'z_element_munition' => $this->view->user->z_braldun,
						'id_fk_type_element_munition' => $munition['id_type_munition'],
						'quantite_element_munition' => -$nbMunition,
					);
					break;
				case self::ID_ENDROIT_MON_COFFRE :
				case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
				case self::ID_ENDROIT_HALL_LIEU :
					$departMunitionTable = new CoffreMunition();
					$data = array(
						'quantite_coffre_munition' => -$nbMunition,
						'id_fk_type_coffre_munition' => $munition['id_type_munition'],
						'id_fk_coffre_coffre_munition' => $this->view->id_coffre_depart,
					);
					break;
				case self::ID_ENDROIT_CHARRETTE :
					$departMunitionTable = new CharretteMunition();
					$data = array(
						'quantite_charrette_munition' => -$nbMunition,
						'id_fk_type_charrette_munition' => $munition['id_type_munition'],
						'id_fk_charrette_munition' => $this->view->id_charrette_depart,
					);
					break;
				case self::ID_ENDROIT_ECHOPPE_ATELIER :
					$departMunitionTable = new EchoppeMunition();
					$data = array(
						'quantite_echoppe_munition' => -$nbMunition,
						'id_fk_type_echoppe_munition' => $munition['id_type_munition'],
						'id_fk_echoppe_echoppe_munition' => $this->view->id_echoppe_depart,
					);
					break;
				default:
					throw new Zend_Exception('Depart Munition invalide : ' . $depart . ' id:' . $idTypeDepart);
			}

			$departMunitionTable->insertOrUpdate($data);
			unset ($departMunitionTable);

			switch ($idTypeArrivee) {
				case self::ID_ENDROIT_LABAN :
				case self::ID_ENDROIT_LABAN_BRALDUN :
					$arriveeMunitionTable = new LabanMunition();
					$data = array(
						'quantite_laban_munition' => $nbMunition,
						'id_fk_type_laban_munition' => $munition['id_type_munition'],
						'id_fk_braldun_laban_munition' => $this->view->id_braldun_destinataire,
					);
					$this->view->poidsRestant = $this->view->poidsRestant - Bral_Util_Poids::POIDS_MUNITION * $nbMunition;
					break;
				case self::ID_ENDROIT_ELEMENT :
					$dateCreation = date('Y-m-d H:i:s');
					$nbJours = Bral_Util_De::get_2d10();
					$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

					$arriveeMunitionTable = new ElementMunition();
					$data = array(
						'x_element_munition' => $this->view->user->x_braldun,
						'y_element_munition' => $this->view->user->y_braldun,
						'z_element_munition' => $this->view->user->z_braldun,
						'id_fk_type_element_munition' => $munition['id_type_munition'],
						'quantite_element_munition' => $nbMunition,
						'date_fin_element_munition' => $dateFin,
					);
					break;
				case self::ID_ENDROIT_MON_COFFRE :
				case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
				case self::ID_ENDROIT_HALL_LIEU :
				case self::ID_ENDROIT_COFFRE_BRALDUN :
					$arriveeMunitionTable = new CoffreMunition();
					$data = array(
						'quantite_coffre_munition' => $nbMunition,
						'id_fk_type_coffre_munition' => $munition['id_type_munition'],
						'id_fk_coffre_coffre_munition' => $this->view->id_coffre_arrivee,
					);
					break;
				case self::ID_ENDROIT_HOTEL :
				case self::ID_ENDROIT_ECHOPPE_ETAL :
				case self::ID_ENDROIT_RESERVATION_COMMUNAUTE :
					$arriveeMunitionTable = new LotMunition();
					$data = array(
						'quantite_lot_munition' => $nbMunition,
						'id_fk_type_lot_munition' => $munition['id_type_munition'],
						'id_fk_lot_lot_munition' => $this->idLot,
					);
					break;
				case self::ID_ENDROIT_CHARRETTE :
					$arriveeMunitionTable = new CharretteMunition();
					$data = array(
						'quantite_charrette_munition' => $nbMunition,
						'id_fk_type_charrette_munition' => $munition['id_type_munition'],
						'id_fk_charrette_munition' => $this->view->id_charrette_arrivee,
					);
					break;
			}
			$arriveeMunitionTable->insertOrUpdate($data);
			unset($arriveeMunitionTable);
			if ($nbMunition > 1) {
				$this->view->elementsRetires .= $nbMunition . ' ' . $munition['type_pluriel'] . ', ';
			} else {
				$this->view->elementsRetires .= $nbMunition . ' ' . $munition['type'] . ', ';
			}
		}
	}

	private function prepareTypeMinerais($depart, $idTypeDepart) {
		Zend_Loader::loadClass($depart . 'Minerai');
		$tabMinerais = null;
		$minerais = null;

		if ($idTypeDepart == self::ID_ENDROIT_ECHOPPE_CAISSE) {
			return;
		}

		switch ($idTypeDepart) {
			case self::ID_ENDROIT_LABAN :
				$labanMineraiTable = new labanMinerai();
				$minerais = $labanMineraiTable->findByIdBraldun($this->view->user->id_braldun);
				unset($labanMineraiTable);
				break;
			case self::ID_ENDROIT_ELEMENT :
				$elementMineraiTable = new ElementMinerai();
				$minerais = $elementMineraiTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
				unset($elementMineraiTable);
				break;
			case self::ID_ENDROIT_MON_COFFRE:
			case self::ID_ENDROIT_HALL_LIEU:
			case self::ID_ENDROIT_COFFRE_COMMUNAUTE:
				$coffreMineraiTable = new CoffreMinerai();
				$minerais = $coffreMineraiTable->findByIdCoffre($this->view->id_coffre_depart);
				unset($coffreMineraiTable);
				break;
			case self::ID_ENDROIT_CHARRETTE :
				$charretteMineraiTable = new CharretteMinerai();
				$minerais = $charretteMineraiTable->findByIdCharrette($this->view->id_charrette_depart);
				unset($charretteMineraiTable);
				break;
			case self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE :
				$echoppeMineraiTable = new EchoppeMinerai();
				$minerais = $echoppeMineraiTable->findByIdEchoppe($this->view->id_echoppe_depart);
				unset($echoppeMineraiTable);
				break;
		}

		$this->view->nb_minerai_brut = 0;
		$this->view->nb_minerai_lingot = 0;

		if ($minerais != null) {
			if ($idTypeDepart == self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE) {
				$strqte = 'arriere_echoppe';
			} elseif ($idTypeDepart == self::ID_ENDROIT_ECHOPPE_CAISSE) {
				$strqte = 'caisse_echoppe';
			} else {
				$strqte = $depart;
			}

			foreach ($minerais as $m) {
				if ($m['quantite_brut_' . strtolower($strqte) . '_minerai'] > 0 || $m['quantite_lingots_' . strtolower($depart) . '_minerai'] > 0) {
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // brut
					$tabMinerais[$this->view->nb_valeurs] = array(
						'type' => $m['nom_type_minerai'],
						'id_fk_type_minerai' => $m['id_fk_type_' . strtolower($depart) . '_minerai'],
						'quantite_brut_minerai' => $m['quantite_brut_' . strtolower($strqte) . '_minerai'],
						'quantite_lingots_minerai' => $m['quantite_lingots_' . strtolower($depart) . '_minerai'],
						'indice_valeur' => $this->view->nb_valeurs,
					);
					$this->view->deposerOk = true;
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // lingot
					$this->view->nb_minerai_brut = $this->view->nb_minerai_brut + $m['quantite_brut_' . strtolower($strqte) . '_minerai'];
					$this->view->nb_minerai_lingot = $this->view->nb_minerai_lingot + $m['quantite_lingots_' . strtolower($depart) . '_minerai'];
				}
			}
		}
		$this->view->valeur_fin_minerais = $this->view->nb_valeurs;
		$this->view->minerais = $tabMinerais;
	}

	private function deposeTypeMinerais($depart, $arrivee, $idTypeDepart, $idTypeArrivee) {
		Zend_Loader::loadClass($depart . 'Minerai');
		Zend_Loader::loadClass($arrivee . 'Minerai');

		if ($idTypeDepart == self::ID_ENDROIT_ECHOPPE_CAISSE || $idTypeArrivee == self::ID_ENDROIT_ECHOPPE_CAISSE) {
			return;
		}

		for ($i = $this->view->valeur_fin_partieplantes + 1; $i <= $this->view->valeur_fin_minerais; $i = $i + 2) {
			$indice = $i;
			$indiceBrut = $i;
			$indiceLingot = $i + 1;
			$nbBrut = $this->request->get('valeur_' . $indiceBrut);
			$nbLingot = $this->request->get('valeur_' . $indiceLingot);

			if ((int)$nbBrut . '' != $this->request->get('valeur_' . $indiceBrut) . '') {
				throw new Zend_Exception(get_class($this) . ' NB Minerai brut invalide=' . $nbBrut . ' indice=' . $indiceBrut);
			} else {
				$nbBrut = (int)$nbBrut;
			}
			if ($nbBrut > $this->view->minerais[$indice]['quantite_brut_minerai']) {
				throw new Zend_Exception(get_class($this) . ' NB Minerai brut interdit=' . $nbBrut);
			}

			if ((int)$nbLingot . '' != $this->request->get('valeur_' . $indiceLingot) . '') {
				throw new Zend_Exception(get_class($this) . ' NB Minerai lingot invalide=' . $nbLingot . ' indice=' . $indiceLingot);
			} else {
				$nbLingot = (int)$nbLingot;
			}
			if ($nbLingot > $this->view->minerais[$indice]['quantite_lingots_minerai']) {
				throw new Zend_Exception(get_class($this) . ' NB Minerai lingot interdit=' . $nbLingot);
			}
			$sbrut = '';
			$slingot = '';
			if ($nbBrut > 1) $sbrut = 's';
			if ($nbLingot > 1) $slingot = 's';

			if ($nbBrut < 0) $nbBrut = 0;
			if ($nbLingot < 0) $nbLingot = 0;

			if ($nbBrut > 0 || $nbLingot > 0) {
				if ($depart == 'Charrette' && $this->view->a_panneau === false && ($this->view->nbelement > 0 || ($nbBrut > 0 && $nbLingot > 0))) {
					$this->view->panneau = false;
					$this->view->elementsNonRetiresPanneau .= $this->view->minerais[$indice]['type'] . ' : ' . $nbBrut . ' minerai' . $sbrut . ' brut' . $sbrut . ' et ' . $nbLingot . ' lingot' . $slingot . ',';
				}

				$poidsOk = true;
				if ($arrivee == 'Laban' || $arrivee == 'Charrette') {
					$poidsOk1 = $this->controlePoids($this->view->poidsRestant, $nbBrut, Bral_Util_Poids::POIDS_MINERAI);
					$poidsOk2 = $this->controlePoids($this->view->poidsRestant, $nbLingot, Bral_Util_Poids::POIDS_LINGOT);
					if ($poidsOk1 == false || $poidsOk2 == false) {
						$this->view->poidsOk = false;
						$poidsOk = false;
						$this->view->elementsNonRetiresPoids .= $this->view->minerais[$indice]['type'] . ' : ' . $nbBrut . ' minerai' . $sbrut . ' brut' . $sbrut . ' et ' . $nbLingot . ' lingot' . $slingot . ',';
					}
				}

				if ($poidsOk == true && $this->view->panneau != false) {

					$this->view->nbelement = $this->view->nbelement + 1;

					switch ($idTypeDepart) {
						case self::ID_ENDROIT_LABAN :
							$departMineraiTable = new LabanMinerai();
							$data = array(
								'id_fk_type_laban_minerai' => $this->view->minerais[$indice]['id_fk_type_minerai'],
								'id_fk_braldun_laban_minerai' => $this->view->user->id_braldun,
								'quantite_brut_laban_minerai' => -$nbBrut,
								'quantite_lingots_laban_minerai' => -$nbLingot,
							);
							break;
						case self::ID_ENDROIT_ELEMENT :
							$departMineraiTable = new ElementMinerai();
							$data = array(
								'x_element_minerai' => $this->view->user->x_braldun,
								'y_element_minerai' => $this->view->user->y_braldun,
								'z_element_minerai' => $this->view->user->z_braldun,
								'id_fk_type_element_minerai' => $this->view->minerais[$indice]['id_fk_type_minerai'],
								'quantite_brut_element_minerai' => -$nbBrut,
								'quantite_lingots_element_minerai' => -$nbLingot,
							);
							break;
						case self::ID_ENDROIT_MON_COFFRE :
						case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
						case self::ID_ENDROIT_HALL_LIEU :
							$departMineraiTable = new CoffreMinerai();
							$data = array(
								'id_fk_coffre_coffre_minerai' => $this->view->id_coffre_depart,
								'id_fk_type_coffre_minerai' => $this->view->minerais[$indice]['id_fk_type_minerai'],
								'quantite_brut_coffre_minerai' => -$nbBrut,
								'quantite_lingots_coffre_minerai' => -$nbLingot,
							);
							break;
						case self::ID_ENDROIT_CHARRETTE :
							$departMineraiTable = new CharretteMinerai();
							$data = array(
								'id_fk_charrette_minerai' => $this->view->id_charrette_depart,
								'id_fk_type_charrette_minerai' => $this->view->minerais[$indice]['id_fk_type_minerai'],
								'quantite_brut_charrette_minerai' => -$nbBrut,
								'quantite_lingots_charrette_minerai' => -$nbLingot,
							);
							break;
						case self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE :
							$departMineraiTable = new EchoppeMinerai();
							$data = array(
								'id_fk_echoppe_echoppe_minerai' => $this->view->id_echoppe_depart,
								'id_fk_type_echoppe_minerai' => $this->view->minerais[$indice]['id_fk_type_minerai'],
								'quantite_brut_arriere_echoppe_minerai' => -$nbBrut,
								'quantite_lingots_echoppe_minerai' => -$nbLingot,
							);
							break;
						default:
							throw new Zend_Exception('Depart Minerai invalide : ' . $depart . ' id:' . $idTypeDepart);
					}
					$departMineraiTable->insertOrUpdate($data);
					unset ($departMineraiTable);

					switch ($idTypeArrivee) {
						case self::ID_ENDROIT_LABAN :
						case self::ID_ENDROIT_LABAN_BRALDUN :

							$arriveeMineraiTable = new LabanMinerai();
							$data = array(
								'quantite_brut_laban_minerai' => $nbBrut,
								'quantite_lingots_laban_minerai' => $nbLingot,
								'id_fk_type_laban_minerai' => $this->view->minerais[$indice]['id_fk_type_minerai'],
								'id_fk_braldun_laban_minerai' => $this->view->id_braldun_destinataire,
							);
							$this->view->poidsRestant = $this->view->poidsRestant - Bral_Util_Poids::POIDS_MINERAI * $nbBrut - Bral_Util_Poids::POIDS_LINGOT * $nbLingot;
							break;
						case self::ID_ENDROIT_ELEMENT :
							$dateCreation = date('Y-m-d H:i:s');
							$nbJours = Bral_Util_De::get_2d10();
							$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

							$arriveeMineraiTable = new ElementMinerai();
							$data = array('x_element_minerai' => $this->view->user->x_braldun,
										  'y_element_minerai' => $this->view->user->y_braldun,
										  'z_element_minerai' => $this->view->user->z_braldun,
										  'quantite_brut_element_minerai' => $nbBrut,
										  'quantite_lingots_element_minerai' => $nbLingot,
										  'id_fk_type_element_minerai' => $this->view->minerais[$indice]['id_fk_type_minerai'],
										  'date_fin_element_minerai' => $dateFin,
							);
							break;
						case self::ID_ENDROIT_MON_COFFRE :
						case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
						case self::ID_ENDROIT_HALL_LIEU :
						case self::ID_ENDROIT_COFFRE_BRALDUN :
							$arriveeMineraiTable = new CoffreMinerai();
							$data = array(
								'id_fk_coffre_coffre_minerai' => $this->view->id_coffre_arrivee,
								'id_fk_type_coffre_minerai' => $this->view->minerais[$indice]['id_fk_type_minerai'],
								'quantite_brut_coffre_minerai' => $nbBrut,
								'quantite_lingots_coffre_minerai' => $nbLingot,
							);
							break;
						case self::ID_ENDROIT_HOTEL :
						case self::ID_ENDROIT_ECHOPPE_ETAL :
						case self::ID_ENDROIT_RESERVATION_COMMUNAUTE :
							$arriveeMineraiTable = new LotMinerai();
							$data = array(
								'id_fk_lot_lot_minerai' => $this->idLot,
								'id_fk_type_lot_minerai' => $this->view->minerais[$indice]['id_fk_type_minerai'],
								'quantite_brut_lot_minerai' => $nbBrut,
								'quantite_lingots_lot_minerai' => $nbLingot,
							);
							break;
						case self::ID_ENDROIT_CHARRETTE :
							$arriveeMineraiTable = new CharretteMinerai();
							$data = array(
								'id_fk_charrette_minerai' => $this->view->id_charrette_arrivee,
								'id_fk_type_charrette_minerai' => $this->view->minerais[$indice]['id_fk_type_minerai'],
								'quantite_brut_charrette_minerai' => $nbBrut,
								'quantite_lingots_charrette_minerai' => $nbLingot,
							);
							break;
						case self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE :
							$arriveeMineraiTable = new EchoppeMinerai();
							$data = array(
								'id_fk_echoppe_echoppe_minerai' => $this->view->id_echoppe_arrivee,
								'id_fk_type_echoppe_minerai' => $this->view->minerais[$indice]['id_fk_type_minerai'],
								'quantite_brut_arriere_echoppe_minerai' => $nbBrut,
								'quantite_lingots_echoppe_minerai' => $nbLingot,
							);
							break;
					}
					$arriveeMineraiTable->insertOrUpdate($data);
					unset ($arriveeMineraiTable);

					$this->view->elementsRetires .= $this->view->minerais[$indice]['type'] . ' : ' . $nbBrut . ' minerai' . $sbrut . ' brut' . $sbrut . ' et ' . $nbLingot . ' lingot' . $slingot . ', ';
				}
			}
		}
	}

	private function prepareTypePartiesPlantes($depart, $idTypeDepart) {
		Zend_Loader::loadClass($depart . 'Partieplante');
		$tabPartiePlantes = null;
		$partiePlantes = null;

		if ($idTypeDepart == self::ID_ENDROIT_ECHOPPE_CAISSE) {
			return;
		}

		switch ($idTypeDepart) {
			case self::ID_ENDROIT_LABAN :
				$labanPartiePlanteTable = new LabanPartieplante();
				$partiePlantes = $labanPartiePlanteTable->findByIdBraldun($this->view->user->id_braldun);
				unset($labanPartiePlanteTable);
				break;
			case self::ID_ENDROIT_ELEMENT :
				$elementPartiePlanteTable = new ElementPartieplante();
				$partiePlantes = $elementPartiePlanteTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
				unset($elementPartiePlanteTable);
				break;
			case self::ID_ENDROIT_MON_COFFRE:
			case self::ID_ENDROIT_HALL_LIEU:
			case self::ID_ENDROIT_COFFRE_COMMUNAUTE:
				$coffrePartiePlanteTable = new CoffrePartieplante();
				$partiePlantes = $coffrePartiePlanteTable->findByIdCoffre($this->view->id_coffre_depart);
				unset($coffrePartiePlanteTable);
				break;
			case self::ID_ENDROIT_CHARRETTE :
				$charrettePartiePlanteTable = new CharrettePartiePlante();
				$partiePlantes = $charrettePartiePlanteTable->findByIdCharrette($this->view->id_charrette_depart);
				unset($charrettePartiePlanteTable);
				break;
			case self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE :
				$echoppePartiePlanteTable = new EchoppePartiePlante();
				$partiePlantes = $echoppePartiePlanteTable->findByIdEchoppe($this->view->id_echoppe_depart);
				unset($echoppePartiePlanteTable);
				break;
		}

		$this->view->nb_partiePlantes = 0;
		$this->view->nb_prepareesPartiePlantes = 0;

		if ($partiePlantes != null) {
			if ($idTypeDepart == self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE) {
				$strqte = 'arriere_echoppe';
			} else {
				$strqte = $depart;
			}
			foreach ($partiePlantes as $p) {
				if ($p['quantite_' . strtolower($strqte) . '_partieplante'] > 0 || $p['quantite_preparee_' . strtolower($depart) . '_partieplante'] > 0) {
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // brute
					$tabPartiePlantes[$this->view->nb_valeurs] = array(
						'nom_type' => $p['nom_type_partieplante'],
						'nom_plante' => $p['nom_type_plante'],
						'id_fk_type_partieplante' => $p['id_fk_type_' . strtolower($depart) . '_partieplante'],
						'id_fk_type_plante_partieplante' => $p['id_fk_type_plante_' . strtolower($depart) . '_partieplante'],
						'quantite_partieplante' => $p['quantite_' . strtolower($strqte) . '_partieplante'],
						'quantite_preparee_partieplante' => $p['quantite_preparee_' . strtolower($depart) . '_partieplante'],
						'indice_valeur' => $this->view->nb_valeurs,
					);
					$this->view->deposerOk = true;
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1; // préparée
					$this->view->nb_partiePlantes = $this->view->nb_partiePlantes + $p['quantite_' . strtolower($strqte) . '_partieplante'];
					$this->view->nb_prepareesPartiePlantes = $this->view->nb_prepareesPartiePlantes + $p['quantite_preparee_' . strtolower($depart) . '_partieplante'];
				}
			}
		}

		$this->view->valeur_fin_partieplantes = $this->view->nb_valeurs;
		$this->view->partieplantes = $tabPartiePlantes;
	}

	private function deposeTypePartiesPlantes($depart, $arrivee, $idTypeDepart, $idTypeArrivee) {

		if ($idTypeDepart == self::ID_ENDROIT_ECHOPPE_CAISSE || $idTypeArrivee == self::ID_ENDROIT_ECHOPPE_CAISSE) {
			return;
		}

		Zend_Loader::loadClass($depart . 'Partieplante');
		Zend_Loader::loadClass($arrivee . 'Partieplante');

		for ($i = $this->view->valeur_fin_munitions + 1; $i <= $this->view->valeur_fin_partieplantes; $i = $i + 2) {
			$indice = $i;
			$indiceBrutes = $i;
			$indicePreparees = $i + 1;
			$nbBrutes = $this->request->get('valeur_' . $indiceBrutes);
			$nbPreparees = $this->request->get('valeur_' . $indicePreparees);

			if ((int)$nbBrutes . '' != $this->request->get('valeur_' . $indiceBrutes) . '') {
				throw new Zend_Exception(get_class($this) . ' NB Partie Plante Brute invalide=' . $nbBrutes);
			} else {
				$nbBrutes = (int)$nbBrutes;
			}
			if ($nbBrutes > $this->view->partieplantes[$indice]['quantite_partieplante']) {
				throw new Zend_Exception(get_class($this) . ' NB Partie Plante Brute interdit=' . $nbBrutes);
			}
			if ((int)$nbPreparees . '' != $this->request->get('valeur_' . $indicePreparees) . '') {
				throw new Zend_Exception(get_class($this) . ' NB Partie Plante Preparee invalide=' . $nbPreparees);
			} else {
				$nbPreparees = (int)$nbPreparees;
			}
			if ($nbPreparees > $this->view->partieplantes[$indice]['quantite_preparee_partieplante']) {
				throw new Zend_Exception(get_class($this) . ' NB Partie Plante Preparee interdit=' . $nbPreparees);
			}

			$sbrute = '';
			$spreparee = '';
			if ($nbBrutes > 1) $sbrute = 's';
			if ($nbPreparees > 1) $spreparee = 's';

			if ($nbBrutes > 0 || $nbPreparees > 0) {

				if ($depart == 'Charrette' && $this->view->a_panneau === false && ($this->view->nbelement > 0 || ($nbBrutes > 0 && $nbPreparees > 0))) {
					$this->view->panneau = false;
					$this->view->elementsNonRetiresPanneau .= $this->view->partieplantes[$indice]['nom_plante'] . ' : ';
					$this->view->elementsNonRetiresPanneau .= $nbBrutes . ' ' . $this->view->partieplantes[$indice]['nom_type'] . ' brute' . $sbrute;
					$this->view->elementsNonRetiresPanneau .= ' et ' . $nbPreparees . ' ' . $this->view->partieplantes[$indice]['nom_type'] . ' préparée' . $spreparee;
					$this->view->elementsNonRetiresPanneau .= ', ';
				}

				$poidsOk = true;
				if ($arrivee == 'Laban' || $arrivee == 'Charrette') {
					$poidsOk1 = $this->controlePoids($this->view->poidsRestant, $nbBrutes, Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE);
					$poidsOk2 = $this->controlePoids($this->view->poidsRestant, $nbPreparees, Bral_Util_Poids::POIDS_PARTIE_PLANTE_PREPAREE);
					if ($poidsOk1 == false || $poidsOk2 == false) {
						$this->view->poidsOk = false;
						$poidsOk = false;
						$this->view->elementsNonRetiresPoids .= $this->view->partieplantes[$indice]['nom_plante'] . ' : ';
						$this->view->elementsNonRetiresPoids .= $nbBrutes . ' ' . $this->view->partieplantes[$indice]['nom_type'] . ' brute' . $sbrute;
						$this->view->elementsNonRetiresPoids .= ' et ' . $nbPreparees . ' ' . $this->view->partieplantes[$indice]['nom_type'] . ' préparée' . $spreparee;
						$this->view->elementsNonRetiresPoids .= ', ';
					}
				}

				if ($poidsOk == true && $this->view->panneau != false) {

					$this->view->nbelement = $this->view->nbelement + 1;

					switch ($idTypeDepart) {
						case self::ID_ENDROIT_LABAN :
							$departPartiePlanteTable = new LabanPartieplante();
							$data = array(
								'id_fk_type_laban_partieplante' => $this->view->partieplantes[$indice]['id_fk_type_partieplante'],
								'id_fk_type_plante_laban_partieplante' => $this->view->partieplantes[$indice]['id_fk_type_plante_partieplante'],
								'id_fk_braldun_laban_partieplante' => $this->view->user->id_braldun,
								'quantite_laban_partieplante' => -$nbBrutes,
								'quantite_preparee_laban_partieplante' => -$nbPreparees
							);
							break;
						case self::ID_ENDROIT_ELEMENT :
							$departPartiePlanteTable = new ElementPartieplante();
							$data = array(
								'x_element_partieplante' => $this->view->user->x_braldun,
								'y_element_partieplante' => $this->view->user->y_braldun,
								'z_element_partieplante' => $this->view->user->z_braldun,
								'id_fk_type_element_partieplante' => $this->view->partieplantes[$indice]['id_fk_type_partieplante'],
								'id_fk_type_plante_element_partieplante' => $this->view->partieplantes[$indice]['id_fk_type_plante_partieplante'],
								'quantite_element_partieplante' => -$nbBrutes,
								'quantite_preparee_element_partieplante' => -$nbPreparees,
							);
							break;
						case self::ID_ENDROIT_MON_COFFRE :
						case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
						case self::ID_ENDROIT_HALL_LIEU :
							$departPartiePlanteTable = new CoffrePartieplante();
							$data = array(
								'id_fk_type_coffre_partieplante' => $this->view->partieplantes[$indice]['id_fk_type_partieplante'],
								'id_fk_type_plante_coffre_partieplante' => $this->view->partieplantes[$indice]['id_fk_type_plante_partieplante'],
								'id_fk_coffre_coffre_partieplante' => $this->view->id_coffre_depart,
								'quantite_coffre_partieplante' => -$nbBrutes,
								'quantite_preparee_coffre_partieplante' => -$nbPreparees
							);
							break;
						case self::ID_ENDROIT_CHARRETTE :
							$departPartiePlanteTable = new CharrettePartieplante();
							$data = array(
								'id_fk_type_charrette_partieplante' => $this->view->partieplantes[$indice]['id_fk_type_partieplante'],
								'id_fk_type_plante_charrette_partieplante' => $this->view->partieplantes[$indice]['id_fk_type_plante_partieplante'],
								'id_fk_charrette_partieplante' => $this->view->id_charrette_depart,
								'quantite_charrette_partieplante' => -$nbBrutes,
								'quantite_preparee_charrette_partieplante' => -$nbPreparees
							);
							break;
						case self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE :
							$departPartiePlanteTable = new EchoppePartieplante();
							$data = array(
								'id_fk_type_echoppe_partieplante' => $this->view->partieplantes[$indice]['id_fk_type_partieplante'],
								'id_fk_type_plante_echoppe_partieplante' => $this->view->partieplantes[$indice]['id_fk_type_plante_partieplante'],
								'id_fk_echoppe_echoppe_partieplante' => $this->view->id_echoppe_depart,
								'quantite_arriere_echoppe_partieplante' => -$nbBrutes,
								'quantite_preparee_echoppe_partieplante' => -$nbPreparees
							);
							break;
						default:
							throw new Zend_Exception('Depart plante invalide : ' . $depart . ' id:' . $idTypeDepart);
					}

					$departPartiePlanteTable->insertOrUpdate($data);
					unset ($departPartiePlanteTable);

					switch ($idTypeArrivee) {
						case self::ID_ENDROIT_LABAN :
						case self::ID_ENDROIT_LABAN_BRALDUN :
							$arriveePartiePlanteTable = new LabanPartieplante();
							$data = array(
								'id_fk_braldun_laban_partieplante' => $this->view->id_braldun_destinataire,
								'id_fk_type_laban_partieplante' => $this->view->partieplantes[$indice]['id_fk_type_partieplante'],
								'id_fk_type_plante_laban_partieplante' => $this->view->partieplantes[$indice]['id_fk_type_plante_partieplante'],
								'quantite_laban_partieplante' => $nbBrutes,
								'quantite_preparee_laban_partieplante' => $nbPreparees,
							);
							$this->view->poidsRestant = $this->view->poidsRestant - Bral_Util_Poids::POIDS_PARTIE_PLANTE_BRUTE * $nbBrutes - Bral_Util_Poids::POIDS_PARTIE_PLANTE_PREPAREE * $nbPreparees;
							break;
						case self::ID_ENDROIT_ELEMENT :
							$dateCreation = date('Y-m-d H:i:s');
							$nbJours = Bral_Util_De::get_2d10();
							$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

							$arriveePartiePlanteTable = new ElementPartieplante();
							$data = array('x_element_partieplante' => $this->view->user->x_braldun,
										  'y_element_partieplante' => $this->view->user->y_braldun,
										  'z_element_partieplante' => $this->view->user->z_braldun,
										  'quantite_element_partieplante' => $nbBrutes,
										  'quantite_preparee_element_partieplante' => $nbPreparees,
										  'id_fk_type_element_partieplante' => $this->view->partieplantes[$indice]['id_fk_type_partieplante'],
										  'id_fk_type_plante_element_partieplante' => $this->view->partieplantes[$indice]['id_fk_type_plante_partieplante'],
										  'date_fin_element_partieplante' => $dateFin,
							);
							break;
						case self::ID_ENDROIT_MON_COFFRE :
						case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
						case self::ID_ENDROIT_HALL_LIEU :
						case self::ID_ENDROIT_COFFRE_BRALDUN :
							$arriveePartiePlanteTable = new CoffrePartieplante();
							$data = array(
								'id_fk_coffre_coffre_partieplante' => $this->view->id_coffre_arrivee,
								'id_fk_type_coffre_partieplante' => $this->view->partieplantes[$indice]['id_fk_type_partieplante'],
								'id_fk_type_plante_coffre_partieplante' => $this->view->partieplantes[$indice]['id_fk_type_plante_partieplante'],
								'quantite_coffre_partieplante' => $nbBrutes,
								'quantite_preparee_coffre_partieplante' => $nbPreparees,
							);
							break;
						case self::ID_ENDROIT_HOTEL :
						case self::ID_ENDROIT_ECHOPPE_ETAL :
						case self::ID_ENDROIT_RESERVATION_COMMUNAUTE :
							$arriveePartiePlanteTable = new LotPartieplante();
							$data = array(
								'id_fk_lot_lot_partieplante' => $this->idLot,
								'id_fk_type_lot_partieplante' => $this->view->partieplantes[$indice]['id_fk_type_partieplante'],
								'id_fk_type_plante_lot_partieplante' => $this->view->partieplantes[$indice]['id_fk_type_plante_partieplante'],
								'quantite_lot_partieplante' => $nbBrutes,
								'quantite_preparee_lot_partieplante' => $nbPreparees,
							);
							break;
						case self::ID_ENDROIT_CHARRETTE :
							$arriveePartiePlanteTable = new CharrettePartieplante();
							$data = array(
								'id_fk_charrette_partieplante' => $this->view->id_charrette_arrivee,
								'id_fk_type_charrette_partieplante' => $this->view->partieplantes[$indice]['id_fk_type_partieplante'],
								'id_fk_type_plante_charrette_partieplante' => $this->view->partieplantes[$indice]['id_fk_type_plante_partieplante'],
								'quantite_charrette_partieplante' => $nbBrutes,
								'quantite_preparee_charrette_partieplante' => $nbPreparees,
							);
							break;
						case self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE :
							$arriveePartiePlanteTable = new EchoppePartieplante();
							$data = array(
								'id_fk_echoppe_echoppe_partieplante' => $this->view->id_echoppe_arrivee,
								'id_fk_type_echoppe_partieplante' => $this->view->partieplantes[$indice]['id_fk_type_partieplante'],
								'id_fk_type_plante_echoppe_partieplante' => $this->view->partieplantes[$indice]['id_fk_type_plante_partieplante'],
								'quantite_arriere_echoppe_partieplante' => $nbBrutes,
								'quantite_preparee_echoppe_partieplante' => $nbPreparees,
							);
							break;
					}
					$arriveePartiePlanteTable->insertOrUpdate($data);
					unset ($arriveePartiePlanteTable);
					$this->view->elementsRetires .= $this->view->partieplantes[$indice]['nom_plante'] . ' : ';
					$this->view->elementsRetires .= $nbBrutes . ' ' . $this->view->partieplantes[$indice]['nom_type'] . ' brute' . $sbrute;
					$this->view->elementsRetires .= ' et ' . $nbPreparees . ' ' . $this->view->partieplantes[$indice]['nom_type'] . ' préparée' . $spreparee;
					$this->view->elementsRetires .= ', ';
				}
			}
		}
	}

	private function prepareTypeTabac($depart, $idTypeDepart) {

		if ($idTypeDepart != self::ID_ENDROIT_ECHOPPE_CAISSE &&
			$idTypeDepart != self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE &&
			$idTypeDepart != self::ID_ENDROIT_ECHOPPE_ATELIER &&
			$idTypeDepart != self::ID_ENDROIT_ECHOPPE_ETAL
		) {

			Zend_Loader::loadClass($depart . 'Tabac');
			$tabTabacs = null;
			$tabacs = null;

			switch ($idTypeDepart) {
				case self::ID_ENDROIT_LABAN :
					$labanTabacTable = new LabanTabac();
					$tabacs = $labanTabacTable->findByIdBraldun($this->view->user->id_braldun);
					unset($labanTabacTable);
					break;
				case self::ID_ENDROIT_ELEMENT :
					$elementTabacTable = new ElementTabac();
					$tabacs = $elementTabacTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
					unset($elementTabacTable);
					break;
				case self::ID_ENDROIT_MON_COFFRE:
				case self::ID_ENDROIT_HALL_LIEU:
				case self::ID_ENDROIT_COFFRE_COMMUNAUTE:
					$coffreTabacTable = new CoffreTabac();
					$tabacs = $coffreTabacTable->findByIdCoffre($this->view->id_coffre_depart);
					unset($coffreTabacTable);
					break;
				case self::ID_ENDROIT_CHARRETTE :
					$charretteTabacTable = new CharretteTabac();
					$tabacs = $charretteTabacTable->findByIdCharrette($this->view->id_charrette_depart);
					unset($charretteTabacTable);
					break;
			}

			if (count($tabacs) > 0) {
				foreach ($tabacs as $m) {
					if ($m['quantite_feuille_' . strtolower($depart) . '_tabac'] > 0) {
						$this->view->nb_valeurs = $this->view->nb_valeurs + 1;
						$tabTabacs[$this->view->nb_valeurs] = array(
							'id_type_tabac' => $m['id_fk_type_' . strtolower($depart) . '_tabac'],
							'type' => $m['nom_type_tabac'],
							'quantite' => $m['quantite_feuille_' . strtolower($depart) . '_tabac'],
							'indice_valeur' => $this->view->nb_valeurs,
						);
					}
				}
				$this->view->deposerOk = true;
			}
			$this->view->valeur_fin_tabacs = $this->view->nb_valeurs;
			$this->view->tabacs = $tabTabacs;
		} else {
			$this->view->valeur_fin_tabacs = $this->view->nb_valeurs;
			$this->view->tabacs = null;
		}
	}

	private function deposeTypeTabac($depart, $arrivee, $idTypeDepart, $idTypeArrivee) {
		if ($idTypeDepart == self::ID_ENDROIT_ECHOPPE_CAISSE ||
			$idTypeDepart == self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE ||
			$idTypeDepart == self::ID_ENDROIT_ECHOPPE_ATELIER ||
			$idTypeDepart == self::ID_ENDROIT_ECHOPPE_ETAL ||
			$idTypeArrivee == self::ID_ENDROIT_ECHOPPE_CAISSE ||
			$idTypeArrivee == self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE ||
			$idTypeArrivee == self::ID_ENDROIT_ECHOPPE_ATELIER ||
			$idTypeArrivee == self::ID_ENDROIT_ECHOPPE_ETAL
		) {
			return;
		}
		Zend_Loader::loadClass($depart . 'Tabac');
		Zend_Loader::loadClass($arrivee . 'Tabac');

		if (count($this->view->tabacs) == 0) {
			return;
		}

		$idTabac = null;
		$nbTabac = null;

		for ($i = $this->view->valeur_fin_ingredients + 1; $i <= $this->view->valeur_fin_tabacs; $i++) {

			if ($this->request->get('valeur_' . $i) <= 0) {
				continue;
			}
			$nbTabac = Bral_Util_Controle::getValeurIntVerif($this->request->get('valeur_' . $i));

			$tabac = $this->view->tabacs[$i];

			if ($nbTabac > $tabac['quantite'] || $nbTabac < 0) {
				throw new Zend_Exception(get_class($this) . ' Quantite Tabac invalide : ' . $nbTabac . ' i=' . $i);
			}

			if ($depart == 'Charrette' && $this->view->a_panneau === false && $this->view->nbelement > 0) {
				$this->view->panneau = false;
				$this->view->elementsNonRetiresPanneau .= $nbTabac . ' feuille' . $stabac . ' de ' . $tabac['type'] . ', ';
			}

			$poidsOk = true;
			if ($arrivee == 'Laban' || $arrivee == 'Charrette') {
				$poidsOk = $this->controlePoids($this->view->poidsRestant, $nbTabac, Bral_Util_Poids::POIDS_TABAC);
				if ($poidsOk == false) {
					$this->view->poidsOk = false;
					$this->view->elementsNonRetiresPoids .= $nbTabac . ' feuille' . $stabac . ' de ' . $tabac['type'] . ', ';
				}
			}

			if ($poidsOk == true && $this->view->panneau != false) {

				$this->view->nbelement = $this->view->nbelement + 1;
				switch ($idTypeDepart) {
					case self::ID_ENDROIT_LABAN :
						$departTabacTable = new LabanTabac();
						$data = array(
							'quantite_feuille_laban_tabac' => -$nbTabac,
							'id_fk_type_laban_tabac' => $tabac['id_type_tabac'],
							'id_fk_braldun_laban_tabac' => $this->view->user->id_braldun,
						);
						break;
					case self::ID_ENDROIT_ELEMENT :
						$departTabacTable = new ElementTabac();
						$data = array(
							'x_element_tabac' => $this->view->user->x_braldun,
							'y_element_tabac' => $this->view->user->y_braldun,
							'z_element_tabac' => $this->view->user->z_braldun,
							'id_fk_type_element_tabac' => $tabac['id_type_tabac'],
							'quantite_feuille_element_tabac' => -$nbTabac,
						);
						break;
					case self::ID_ENDROIT_MON_COFFRE :
					case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
					case self::ID_ENDROIT_HALL_LIEU :
						$departTabacTable = new CoffreTabac();
						$data = array(
							'quantite_feuille_coffre_tabac' => -$nbTabac,
							'id_fk_type_coffre_tabac' => $tabac['id_type_tabac'],
							'id_fk_coffre_coffre_tabac' => $this->view->id_coffre_depart,
						);
						break;
					case self::ID_ENDROIT_CHARRETTE :
						$departTabacTable = new CharretteTabac();
						$data = array(
							'quantite_feuille_charrette_tabac' => -$nbTabac,
							'id_fk_type_charrette_tabac' => $tabac['id_type_tabac'],
							'id_fk_charrette_tabac' => $this->view->id_charrette_depart,
						);
						break;
					default:
						throw new Zend_Exception('Depart Tabac invalide : ' . $depart . ' id:' . $idTypeDepart);
				}

				$departTabacTable->insertOrUpdate($data);
				unset ($departTabacTable);

				switch ($idTypeArrivee) {
					case self::ID_ENDROIT_LABAN :
					case self::ID_ENDROIT_LABAN_BRALDUN :
						$arriveeTabacTable = new LabanTabac();
						$data = array(
							'quantite_feuille_laban_tabac' => $nbTabac,
							'id_fk_type_laban_tabac' => $tabac['id_type_tabac'],
							'id_fk_braldun_laban_tabac' => $this->view->id_braldun_destinataire,
						);
						$this->view->poidsRestant = $this->view->poidsRestant - Bral_Util_Poids::POIDS_MUNITION * $nbTabac;
						break;
					case self::ID_ENDROIT_ELEMENT :
						$arriveeTabacTable = new ElementTabac();
						$data = array(
							'x_element_tabac' => $this->view->user->x_braldun,
							'y_element_tabac' => $this->view->user->y_braldun,
							'z_element_tabac' => $this->view->user->z_braldun,
							'id_fk_type_element_tabac' => $tabac['id_type_tabac'],
							'quantite_feuille_element_tabac' => $nbTabac,
						);
						break;
					case self::ID_ENDROIT_MON_COFFRE :
					case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
					case self::ID_ENDROIT_HALL_LIEU :
					case self::ID_ENDROIT_COFFRE_BRALDUN :
						$arriveeTabacTable = new CoffreTabac();
						$data = array(
							'quantite_feuille_coffre_tabac' => $nbTabac,
							'id_fk_type_coffre_tabac' => $tabac['id_type_tabac'],
							'id_fk_coffre_coffre_tabac' => $this->view->id_coffre_arrivee,
						);
						break;
					case self::ID_ENDROIT_HOTEL :
					case self::ID_ENDROIT_ECHOPPE_ETAL :
					case self::ID_ENDROIT_RESERVATION_COMMUNAUTE :
						$arriveeTabacTable = new LotTabac();
						$data = array(
							'quantite_feuille_lot_tabac' => $nbTabac,
							'id_fk_type_lot_tabac' => $tabac['id_type_tabac'],
							'id_fk_lot_lot_tabac' => $this->idLot,
						);
						break;
					case self::ID_ENDROIT_CHARRETTE :
						$arriveeTabacTable = new CharretteTabac();
						$data = array(
							'quantite_feuille_charrette_tabac' => $nbTabac,
							'id_fk_type_charrette_tabac' => $tabac['id_type_tabac'],
							'id_fk_charrette_tabac' => $this->view->id_charrette_arrivee,
						);
						break;
				}
				$arriveeTabacTable->insertOrUpdate($data);
				unset($arriveeTabacTable);
				$stabac = '';
				if ($nbTabac > 1) $stabac = 's';
				$this->view->elementsRetires .= $nbTabac . ' feuille' . $stabac . ' de ' . $tabac['type'] . ', ';
			}
		}
	}

	private function prepareTypeMateriel($depart, $idTypeDepart) {
		Zend_Loader::loadClass($depart . 'Materiel');
		$tabMateriels = null;
		$materiels = null;

		switch ($idTypeDepart) {
			case self::ID_ENDROIT_LABAN :
				$labanMaterielTable = new LabanMateriel();
				$materiels = $labanMaterielTable->findByIdBraldun($this->view->user->id_braldun);
				unset($labanMaterielTable);
				break;
			case self::ID_ENDROIT_ELEMENT :
				$elementMaterielTable = new ElementMateriel();
				$materiels = $elementMaterielTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
				unset($elementMaterielTable);
				break;
			case self::ID_ENDROIT_MON_COFFRE:
			case self::ID_ENDROIT_HALL_LIEU:
			case self::ID_ENDROIT_COFFRE_COMMUNAUTE:
				$coffreMaterielTable = new CoffreMateriel();
				$materiels = $coffreMaterielTable->findByIdCoffre($this->view->id_coffre_depart);
				unset($coffreMaterielTable);
				break;
			case self::ID_ENDROIT_CHARRETTE :
				$charretteMaterielTable = new CharretteMateriel();
				$materiels = $charretteMaterielTable->findByIdCharrette($this->view->id_charrette_depart);
				unset($charretteMaterielTable);
				break;
			case self::ID_ENDROIT_ECHOPPE_ATELIER :
				$echoppeMaterielTable = new EchoppeMateriel();
				$materiels = $echoppeMaterielTable->findByIdEchoppe($this->view->id_echoppe_depart);
				unset($echoppeMaterielTable);
				break;
		}

		if (count($materiels) > 0) {
			foreach ($materiels as $e) {
				$possible = false;

				if (substr($e['nom_systeme_type_materiel'], 0, 9) == 'charrette') {
					if ($this->view->idCharretteEtal != null && $e['id_' . strtolower($depart) . '_materiel'] == $this->view->idCharretteEtal) {
						$possible = true;
					} else {
						$possible = false;
					}
				} else if ($this->view->idCharretteEtal == null) {
					$possible = true;
				}

				if ($possible) {
					$tabMateriels[$e['id_' . strtolower($depart) . '_materiel']] = array(
						'id_materiel' => $e['id_' . strtolower($depart) . '_materiel'],
						'id_fk_type_materiel' => $e['id_fk_type_materiel'],
						'nom' => $e['nom_type_materiel'],
						'poids' => $e['poids_type_materiel'],
					);
				}
			}
			$this->view->deposerOk = true;
		}
		$this->view->materiels = $tabMateriels;
	}

	private function deposeTypeMateriel($depart, $arrivee, $idTypeDepart, $idTypeArrivee) {
		if ($idTypeArrivee == self::ID_ENDROIT_ECHOPPE_CAISSE ||
			$idTypeArrivee == self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE ||
			$idTypeArrivee == self::ID_ENDROIT_ECHOPPE_ATELIER
		) {
			return;
		}

		Zend_Loader::loadClass($depart . 'Materiel');
		Zend_Loader::loadClass($arrivee . 'Materiel');
		Zend_Loader::loadClass('Bral_Util_Materiel');

		$materiels = array();
		$materiels = $this->request->get('valeur_23');

		if (count($materiels) == 0 || $materiels == 0) {
			return;
		}

		if ($depart == 'Charrette' && $this->view->a_panneau === false && $this->view->nbelement > 0) {
			$this->view->panneau = false;
			foreach ($materiels as $idMateriel) {
				if (!array_key_exists($idMateriel, $this->view->materiels)) {
					throw new Zend_Exception(get_class($this) . ' ID Materiel invalide : ' . $idMateriel);
				}

				$materiel = $this->view->materiels[$idMateriel];
				$this->view->elementsNonRetiresPanneau .= 'Matériel n°' . $materiel['id_materiel'] . ' : ' . $materiel['nom'] . ', ';
			}
			return; // pas de panneau
		}

		foreach ($materiels as $idMateriel) {
			if (!array_key_exists($idMateriel, $this->view->materiels)) {
				throw new Zend_Exception(get_class($this) . ' ID Materiel invalide : ' . $idMateriel);
			}

			$materiel = $this->view->materiels[$idMateriel];

			$poidsOk = true;
			if ($arrivee == 'Laban' || $arrivee == 'Charrette') {
				$poidsOk = $this->controlePoids($this->view->poidsRestant, 1, $materiel['poids']);
				if ($poidsOk == false) {
					$this->view->poidsOk = false;
					$this->view->elementsNonRetiresPoids .= 'Matériel n°' . $materiel['id_materiel'] . ' : ' . $materiel['nom'] . ', ';
				}
			}

			if ($poidsOk == true) {

				$this->view->nbelement = $this->view->nbelement + 1;
				$where = 'id_' . strtolower($depart) . '_materiel=' . $idMateriel;
				switch ($idTypeDepart) {
					case self::ID_ENDROIT_LABAN :
						$departMaterielTable = new LabanMateriel();
						break;
					case self::ID_ENDROIT_ELEMENT :
						$departMaterielTable = new ElementMateriel();
						break;
					case self::ID_ENDROIT_MON_COFFRE :
					case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
					case self::ID_ENDROIT_HALL_LIEU :
						$departMaterielTable = new CoffreMateriel();
						break;
					case self::ID_ENDROIT_CHARRETTE :
						$departMaterielTable = new CharretteMateriel();
						break;
					case self::ID_ENDROIT_ECHOPPE_ATELIER :
						$departMaterielTable = new EchoppeMateriel();
						break;
					default:
						throw new Zend_Exception('Depart Materiel invalide : ' . $depart . ' id:' . $idTypeDepart);
				}

				$departMaterielTable->delete($where);
				unset($departMaterielTable);

				switch ($idTypeArrivee) {
					case self::ID_ENDROIT_LABAN :
					case self::ID_ENDROIT_LABAN_BRALDUN :
						$arriveeMaterielTable = new LabanMateriel();
						$data = array(
							'id_laban_materiel' => $materiel['id_materiel'],
							'id_fk_braldun_laban_materiel' => $this->view->id_braldun_destinataire,
						);
						$this->view->poidsRestant = $this->view->poidsRestant - $materiel['poids'];
						break;
					case self::ID_ENDROIT_ELEMENT :
						$dateCreation = date('Y-m-d H:i:s');
						$nbJours = Bral_Util_De::get_2d10();
						$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

						$arriveeMaterielTable = new ElementMateriel();
						$data = array(
							'id_element_materiel' => $materiel['id_materiel'],
							'x_element_materiel' => $this->view->user->x_braldun,
							'y_element_materiel' => $this->view->user->y_braldun,
							'z_element_materiel' => $this->view->user->z_braldun,
							'date_fin_element_materiel' => $dateFin,
						);
						break;
					case self::ID_ENDROIT_MON_COFFRE :
					case self::ID_ENDROIT_COFFRE_BRALDUN :
					case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
					case self::ID_ENDROIT_HALL_LIEU :
						$arriveeMaterielTable = new CoffreMateriel();
						$data = array(
							'id_coffre_materiel' => $materiel['id_materiel'],
							'id_fk_coffre_coffre_materiel' => $this->view->id_coffre_arrivee,
						);
						break;
					case self::ID_ENDROIT_HOTEL :
					case self::ID_ENDROIT_ECHOPPE_ETAL :
					case self::ID_ENDROIT_RESERVATION_COMMUNAUTE :
						$arriveeMaterielTable = new LotMateriel();
						$data = array(
							'id_lot_materiel' => $materiel['id_materiel'],
							'id_fk_lot_lot_materiel' => $this->idLot,
						);
						break;
					case self::ID_ENDROIT_CHARRETTE :
						$arriveeMaterielTable = new CharretteMateriel();
						$data = array(
							'id_charrette_materiel' => $materiel['id_materiel'],
							'id_fk_charrette_materiel' => $this->view->id_charrette_arrivee,
						);
						break;
					/* On ne remet pas de piece dans l'echoppe*/
				}
				$arriveeMaterielTable->insert($data);
				unset($arriveeMaterielTable);
				$this->view->elementsRetires .= 'Matériel n°' . $materiel['id_materiel'] . ' : ' . $materiel['nom'] . ', ';

				$texte = $this->calculTexte($depart, $arrivee);
				$details = '[b' . $this->view->user->id_braldun . '] a transbahuté le matériel n°' . $materiel['id_materiel'] . ' (' . $texte['departTexte'] . ' vers ' . $texte['arriveeTexte'] . ')';
				Bral_Util_Materiel::insertHistorique(Bral_Util_Materiel::HISTORIQUE_TRANSBAHUTER_ID, $materiel['id_materiel'], $details);
			}
		}
	}

	private function prepareTypeGraines($depart, $idTypeDepart) {

		if ($idTypeDepart == self::ID_ENDROIT_ECHOPPE_CAISSE) {
			return;
		}

		Zend_Loader::loadClass($depart . 'Graine');
		$tabGraines = null;
		$graines = null;

		switch ($idTypeDepart) {
			case self::ID_ENDROIT_LABAN :
				$labanGraineTable = new LabanGraine();
				$graines = $labanGraineTable->findByIdBraldun($this->view->user->id_braldun);
				unset($labanGraineTable);
				break;
			case self::ID_ENDROIT_ELEMENT :
				$elementGraineTable = new ElementGraine();
				$graines = $elementGraineTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
				unset($elementGraineTable);
				break;
			case self::ID_ENDROIT_MON_COFFRE:
			case self::ID_ENDROIT_HALL_LIEU:
			case self::ID_ENDROIT_COFFRE_COMMUNAUTE:
				$coffreGraineTable = new CoffreGraine();
				$graines = $coffreGraineTable->findByIdCoffre($this->view->id_coffre_depart);
				unset($coffreGraineTable);
				break;
			case self::ID_ENDROIT_CHARRETTE :
				$charretteGraineTable = new CharretteGraine();
				$graines = $charretteGraineTable->findByIdCharrette($this->view->id_charrette_depart);
				unset($charretteGraineTable);
				break;
			case self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE :
				$echoppeGraineTable = new EchoppeGraine();
				$graines = $echoppeGraineTable->findByIdEchoppe($this->view->id_echoppe_depart);
				unset($echoppeGraineTable);
				break;
		}

		$this->view->nb_graine = 0;

		if ($graines != null) {
			if ($depart == 'Echoppe') {
				$strqte = 'arriere_echoppe';
			} else {
				$strqte = $depart;
			}
			foreach ($graines as $m) {
				if ($m['quantite_' . strtolower($strqte) . '_graine'] > 0) {
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1;
					$tabGraines[$this->view->nb_valeurs] = array(
						'type' => $m['nom_type_graine'],
						'id_fk_type_graine' => $m['id_fk_type_' . strtolower($depart) . '_graine'],
						'quantite_graine' => $m['quantite_' . strtolower($strqte) . '_graine'],
						'indice_valeur' => $this->view->nb_valeurs,
					);
					$this->view->deposerOk = true;
					$this->view->nb_graine = $this->view->nb_graine + $m['quantite_' . strtolower($strqte) . '_graine'];
				}
			}
		}
		$this->view->valeur_fin_graines = $this->view->nb_valeurs;
		$this->view->graines = $tabGraines;
	}

	private function deposeTypeGraines($depart, $arrivee, $idTypeDepart, $idTypeArrivee) {

		if ($idTypeDepart == self::ID_ENDROIT_ECHOPPE_CAISSE
			|| $idTypeArrivee == self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE
			|| $idTypeArrivee == self::ID_ENDROIT_ECHOPPE_CAISSE
			|| $idTypeArrivee == self::ID_ENDROIT_ECHOPPE_ETAL
			|| $idTypeArrivee == self::ID_ENDROIT_ECHOPPE_ATELIER
		) {
			return;
		}

		Zend_Loader::loadClass($depart . 'Graine');
		Zend_Loader::loadClass($arrivee . 'Graine');

		for ($i = $this->view->valeur_fin_minerais + 1; $i <= $this->view->valeur_fin_graines; $i++) {
			$indice = $i;
			$nb = $this->request->get('valeur_' . $indice);

			if ((int)$nb . '' != $this->request->get('valeur_' . $indice) . '') {
				throw new Zend_Exception(get_class($this) . ' NB Graine invalide=' . $nb . ' indice=' . $indice);
			} else {
				$nb = (int)$nb;
			}
			if ($nb > $this->view->graines[$indice]['quantite_graine']) {
				throw new Zend_Exception(get_class($this) . ' NB Graine interdit=' . $nb);
			}

			if ($nb <= 0) {
				continue;
			}

			if ($depart == 'Charrette' && $this->view->a_panneau === false && $this->view->nbelement > 0) {
				$this->view->panneau = false;
				$this->view->elementsNonRetiresPanneau .= $this->view->graines[$indice]['type'] . ' : ' . $nb . ' poignée' . $s . ' de graines, ';
			}

			$poidsOk = true;
			if ($arrivee == 'Laban' || $arrivee == 'Charrette') {
				$poidsOk = $this->controlePoids($this->view->poidsRestant, $nb, Bral_Util_Poids::POIDS_POIGNEE_GRAINES);
				if ($poidsOk == false) {
					$this->view->poidsOk = false;
					$this->view->elementsNonRetiresPoids .= $this->view->graines[$indice]['type'] . ' : ' . $nb . ' poignée' . $s . ' de graines, ';
				}
			}

			if ($poidsOk != true || $this->view->panneau == false) {
				continue;
			}

			$this->view->nbelement = $this->view->nbelement + 1;
			switch ($idTypeDepart) {
				case self::ID_ENDROIT_LABAN :
					$departGraineTable = new LabanGraine();
					$data = array(
						'id_fk_type_laban_graine' => $this->view->graines[$indice]['id_fk_type_graine'],
						'id_fk_braldun_laban_graine' => $this->view->user->id_braldun,
						'quantite_laban_graine' => -$nb,
					);
					break;
				case self::ID_ENDROIT_ELEMENT :
					$departGraineTable = new ElementGraine();
					$data = array(
						'x_element_graine' => $this->view->user->x_braldun,
						'y_element_graine' => $this->view->user->y_braldun,
						'z_element_graine' => $this->view->user->z_braldun,
						'id_fk_type_element_graine' => $this->view->graines[$indice]['id_fk_type_graine'],
						'quantite_element_graine' => -$nb,
					);
					break;
				case self::ID_ENDROIT_MON_COFFRE :
				case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
				case self::ID_ENDROIT_HALL_LIEU :
					$departGraineTable = new CoffreGraine();
					$data = array(
						'id_fk_coffre_coffre_graine' => $this->view->id_coffre_depart,
						'id_fk_type_coffre_graine' => $this->view->graines[$indice]['id_fk_type_graine'],
						'quantite_coffre_graine' => -$nb,
					);
					break;
				case self::ID_ENDROIT_CHARRETTE :
					$departGraineTable = new CharretteGraine();
					$data = array(
						'id_fk_charrette_graine' => $this->view->id_charrette_depart,
						'id_fk_type_charrette_graine' => $this->view->graines[$indice]['id_fk_type_graine'],
						'quantite_charrette_graine' => -$nb,
					);
					break;
				case self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE :
					$departGraineTable = new EchoppeGraine();
					$data = array(
						'id_fk_echoppe_echoppe_graine' => $this->view->id_echoppe_depart,
						'id_fk_type_echoppe_graine' => $this->view->graines[$indice]['id_fk_type_graine'],
						'quantite_arriere_echoppe_graine' => -$nb,
					);
					break;
				default:
					throw new Zend_Exception('Depart Graine invalide : ' . $depart . ' id:' . $idTypeDepart);
			}
			$departGraineTable->insertOrUpdate($data);
			unset ($departGraineTable);

			switch ($idTypeArrivee) {
				case self::ID_ENDROIT_LABAN :
				case self::ID_ENDROIT_LABAN_BRALDUN :

					$arriveeGraineTable = new LabanGraine();
					$data = array(
						'quantite_laban_graine' => $nb,
						'id_fk_type_laban_graine' => $this->view->graines[$indice]['id_fk_type_graine'],
						'id_fk_braldun_laban_graine' => $this->view->id_braldun_destinataire,
					);
					$this->view->poidsRestant = $this->view->poidsRestant - Bral_Util_Poids::POIDS_POIGNEE_GRAINES * $nb;
					break;
				case self::ID_ENDROIT_ELEMENT :
					$dateCreation = date('Y-m-d H:i:s');
					$nbJours = Bral_Util_De::get_2d10();
					$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

					$arriveeGraineTable = new ElementGraine();
					$data = array('x_element_graine' => $this->view->user->x_braldun,
								  'y_element_graine' => $this->view->user->y_braldun,
								  'z_element_graine' => $this->view->user->z_braldun,
								  'quantite_element_graine' => $nb,
								  'id_fk_type_element_graine' => $this->view->graines[$indice]['id_fk_type_graine'],
								  'date_fin_element_graine' => $dateFin,
					);
					break;
				case self::ID_ENDROIT_MON_COFFRE :
				case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
				case self::ID_ENDROIT_HALL_LIEU :
				case self::ID_ENDROIT_COFFRE_BRALDUN :
					$arriveeGraineTable = new CoffreGraine();
					$data = array(
						'id_fk_coffre_coffre_graine' => $this->view->id_coffre_arrivee,
						'id_fk_type_coffre_graine' => $this->view->graines[$indice]['id_fk_type_graine'],
						'quantite_coffre_graine' => $nb,
					);
					break;
				case self::ID_ENDROIT_HOTEL :
				case self::ID_ENDROIT_ECHOPPE_ETAL :
				case self::ID_ENDROIT_RESERVATION_COMMUNAUTE :
					$arriveeGraineTable = new LotGraine();
					$data = array(
						'id_fk_lot_lot_graine' => $this->idLot,
						'id_fk_type_lot_graine' => $this->view->graines[$indice]['id_fk_type_graine'],
						'quantite_lot_graine' => $nb,
					);
					break;
				case self::ID_ENDROIT_CHARRETTE :
					$arriveeGraineTable = new CharretteGraine();
					$data = array(
						'id_fk_charrette_graine' => $this->view->id_charrette_arrivee,
						'id_fk_type_charrette_graine' => $this->view->graines[$indice]['id_fk_type_graine'],
						'quantite_charrette_graine' => $nb,
					);
					break;
				/* On ne met pas de graine dans l'echoppe. */
			}
			$arriveeGraineTable->insertOrUpdate($data);
			unset ($arriveeGraineTable);
			$s = '';
			if ($nb > 1) $s = 's';
			$this->view->elementsRetires .= $this->view->graines[$indice]['type'] . ' : ' . $nb . ' poignée' . $s . ' de graines, ';
		}
	}

	private function prepareTypeIngredients($depart, $idTypeDepart) {

		if ($idTypeDepart == self::ID_ENDROIT_ECHOPPE_CAISSE) {
			return;
		}

		Zend_Loader::loadClass($depart . 'Ingredient');
		$tabIngredients = null;
		$ingredients = null;

		switch ($idTypeDepart) {
			case self::ID_ENDROIT_LABAN :
				$labanIngredientTable = new LabanIngredient();
				$ingredients = $labanIngredientTable->findByIdBraldun($this->view->user->id_braldun);
				unset($labanIngredientTable);
				break;
			case self::ID_ENDROIT_ELEMENT :
				$elementIngredientTable = new ElementIngredient();
				$ingredients = $elementIngredientTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun);
				unset($elementIngredientTable);
				break;
			case self::ID_ENDROIT_MON_COFFRE:
			case self::ID_ENDROIT_HALL_LIEU:
			case self::ID_ENDROIT_COFFRE_COMMUNAUTE:
				$coffreIngredientTable = new CoffreIngredient();
				$ingredients = $coffreIngredientTable->findByIdCoffre($this->view->id_coffre_depart);
				unset($coffreIngredientTable);
				break;
			case self::ID_ENDROIT_CHARRETTE :
				$charretteIngredientTable = new CharretteIngredient();
				$ingredients = $charretteIngredientTable->findByIdCharrette($this->view->id_charrette_depart);
				unset($charretteIngredientTable);
				break;
			case self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE :
				$echoppeIngredientTable = new EchoppeIngredient();
				$ingredients = $echoppeIngredientTable->findByIdEchoppe($this->view->id_echoppe_depart);
				unset($echoppeIngredientTable);
				break;
		}

		$this->view->nb_ingredient = 0;

		if ($ingredients != null) {
			if ($idTypeDepart == self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE) {
				$strqte = 'arriere_echoppe';
			} else {
				$strqte = $depart;
			}
			foreach ($ingredients as $m) {
				if ($m['quantite_' . strtolower($strqte) . '_ingredient'] > 0) {
					$this->view->nb_valeurs = $this->view->nb_valeurs + 1;
					$tabIngredients[$this->view->nb_valeurs] = array(
						'type' => $m['nom_type_ingredient'],
						'id_fk_type_ingredient' => $m['id_fk_type_' . strtolower($depart) . '_ingredient'],
						'quantite_ingredient' => $m['quantite_' . strtolower($strqte) . '_ingredient'],
						'poids_unitaire_ingredient' => $m['poids_unitaire_type_ingredient'],
						'indice_valeur' => $this->view->nb_valeurs,
					);
					$this->view->deposerOk = true;
					$this->view->nb_ingredient = $this->view->nb_ingredient + $m['quantite_' . strtolower($strqte) . '_ingredient'];
				}
			}
		}
		$this->view->valeur_fin_ingredients = $this->view->nb_valeurs;
		$this->view->ingredients = $tabIngredients;
	}

	private function deposeTypeIngredients($depart, $arrivee, $idTypeDepart, $idTypeArrivee) {

		if ($idTypeDepart == self::ID_ENDROIT_ECHOPPE_CAISSE || $idTypeArrivee == self::ID_ENDROIT_ECHOPPE_CAISSE) {
			return;
		}

		Zend_Loader::loadClass($depart . 'Ingredient');
		Zend_Loader::loadClass($arrivee . 'Ingredient');

		for ($i = $this->view->valeur_fin_graines + 1; $i <= $this->view->valeur_fin_ingredients; $i++) {
			$indice = $i;
			$nb = $this->request->get('valeur_' . $indice);

			if ((int)$nb . '' != $this->request->get('valeur_' . $indice) . '') {
				throw new Zend_Exception(get_class($this) . ' NB Ingredient invalide=' . $nb . ' indice=' . $indice . ' valeur_fin_graines:' . $this->view->valeur_fin_graines . ' valeur_fin_ingredients:' . $this->view->valeur_fin_ingredients);
			} else {
				$nb = (int)$nb;
			}
			if ($nb > $this->view->ingredients[$indice]['quantite_ingredient']) {
				throw new Zend_Exception(get_class($this) . ' NB Ingredient interdit=' . $nb);
			}

			if ($nb <= 0) {
				continue;
			}

			if ($depart == 'Charrette' && $this->view->a_panneau === false && $this->view->nbelement > 0) {
				$this->view->panneau = false;
				$this->view->elementsNonRetiresPanneau .= $this->view->ingredients[$indice]['type'] . ' : ' . $nb . ', ';
			}

			$poidsOk = true;
			if ($arrivee == 'Laban' || $arrivee == 'Charrette') {
				$poidsOk = $this->controlePoids($this->view->poidsRestant, $nb, Bral_Util_Poids::POIDS_POIGNEE_GRAINES);
				if ($poidsOk == false) {
					$this->view->poidsOk = false;
					$this->view->elementsNonRetiresPoids .= $this->view->ingredients[$indice]['type'] . ' : ' . $nb . ', ';
				}
			}

			if ($poidsOk != true && $this->view->panneau == false) {
				continue;
			}

			$this->view->nbelement = $this->view->nbelement + 1;
			switch ($idTypeDepart) {
				case self::ID_ENDROIT_LABAN :
					$departIngredientTable = new LabanIngredient();
					$data = array(
						'id_fk_type_laban_ingredient' => $this->view->ingredients[$indice]['id_fk_type_ingredient'],
						'id_fk_braldun_laban_ingredient' => $this->view->user->id_braldun,
						'quantite_laban_ingredient' => -$nb,
					);
					break;
				case self::ID_ENDROIT_ELEMENT :
					$departIngredientTable = new ElementIngredient();
					$data = array(
						'x_element_ingredient' => $this->view->user->x_braldun,
						'y_element_ingredient' => $this->view->user->y_braldun,
						'z_element_ingredient' => $this->view->user->z_braldun,
						'id_fk_type_element_ingredient' => $this->view->ingredients[$indice]['id_fk_type_ingredient'],
						'quantite_element_ingredient' => -$nb,
					);
					break;
				case self::ID_ENDROIT_MON_COFFRE :
				case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
				case self::ID_ENDROIT_HALL_LIEU :
					$departIngredientTable = new CoffreIngredient();
					$data = array(
						'id_fk_coffre_coffre_ingredient' => $this->view->id_coffre_depart,
						'id_fk_type_coffre_ingredient' => $this->view->ingredients[$indice]['id_fk_type_ingredient'],
						'quantite_coffre_ingredient' => -$nb,
					);
					break;
				case self::ID_ENDROIT_CHARRETTE :
					$departIngredientTable = new CharretteIngredient();
					$data = array(
						'id_fk_charrette_ingredient' => $this->view->id_charrette_depart,
						'id_fk_type_charrette_ingredient' => $this->view->ingredients[$indice]['id_fk_type_ingredient'],
						'quantite_charrette_ingredient' => -$nb,
					);
					break;
				case self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE :
					$departIngredientTable = new EchoppeIngredient();
					$data = array(
						'id_fk_echoppe_echoppe_ingredient' => $this->view->id_echoppe_depart,
						'id_fk_type_echoppe_ingredient' => $this->view->ingredients[$indice]['id_fk_type_ingredient'],
						'quantite_arriere_echoppe_ingredient' => -$nb,
					);
					break;
				default:
					throw new Zend_Exception('Depart Ingredient invalide : ' . $depart . ' id:' . $idTypeDepart);
			}
			$departIngredientTable->insertOrUpdate($data);
			unset ($departIngredientTable);

			switch ($idTypeArrivee) {
				case self::ID_ENDROIT_LABAN :
				case self::ID_ENDROIT_LABAN_BRALDUN :
					$arriveeIngredientTable = new LabanIngredient();
					$data = array(
						'quantite_laban_ingredient' => $nb,
						'id_fk_type_laban_ingredient' => $this->view->ingredients[$indice]['id_fk_type_ingredient'],
						'id_fk_braldun_laban_ingredient' => $this->view->id_braldun_destinataire,
					);
					$this->view->poidsRestant = $this->view->poidsRestant - Bral_Util_Poids::POIDS_POIGNEE_GRAINES * $nb;
					break;
				case self::ID_ENDROIT_ELEMENT :
					$dateCreation = date('Y-m-d H:i:s');
					$nbJours = Bral_Util_De::get_2d10();
					$dateFin = Bral_Util_ConvertDate::get_date_add_day_to_date($dateCreation, $nbJours);

					$arriveeIngredientTable = new ElementIngredient();
					$data = array('x_element_ingredient' => $this->view->user->x_braldun,
								  'y_element_ingredient' => $this->view->user->y_braldun,
								  'z_element_ingredient' => $this->view->user->z_braldun,
								  'quantite_element_ingredient' => $nb,
								  'id_fk_type_element_ingredient' => $this->view->ingredients[$indice]['id_fk_type_ingredient'],
								  'date_fin_element_ingredient' => $dateFin,
					);
					break;
				case self::ID_ENDROIT_MON_COFFRE :
				case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
				case self::ID_ENDROIT_HALL_LIEU :
				case self::ID_ENDROIT_COFFRE_BRALDUN :
					$arriveeIngredientTable = new CoffreIngredient();
					$data = array(
						'id_fk_coffre_coffre_ingredient' => $this->view->id_coffre_arrivee,
						'id_fk_type_coffre_ingredient' => $this->view->ingredients[$indice]['id_fk_type_ingredient'],
						'quantite_coffre_ingredient' => $nb,
					);
					break;
				case self::ID_ENDROIT_HOTEL :
				case self::ID_ENDROIT_ECHOPPE_ETAL :
				case self::ID_ENDROIT_RESERVATION_COMMUNAUTE :
					$arriveeIngredientTable = new LotIngredient();
					$data = array(
						'id_fk_lot_lot_ingredient' => $this->idLot,
						'id_fk_type_lot_ingredient' => $this->view->ingredients[$indice]['id_fk_type_ingredient'],
						'quantite_lot_ingredient' => $nb,
					);
					break;
				case self::ID_ENDROIT_CHARRETTE :
					$arriveeIngredientTable = new CharretteIngredient();
					$data = array(
						'id_fk_charrette_ingredient' => $this->view->id_charrette_arrivee,
						'id_fk_type_charrette_ingredient' => $this->view->ingredients[$indice]['id_fk_type_ingredient'],
						'quantite_charrette_ingredient' => $nb,
					);
					break;
				case self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE :
					$arriveeIngredientTable = new EchoppeIngredient();
					$data = array(
						'id_fk_echoppe_echoppe_ingredient' => $this->view->id_echoppe_arrivee,
						'id_fk_type_echoppe_ingredient' => $this->view->ingredients[$indice]['id_fk_type_ingredient'],
						'quantite_arriere_echoppe_ingredient' => $nb,
					);
					break;
			}
			$arriveeIngredientTable->insertOrUpdate($data);
			unset ($arriveeIngredientTable);
			$s = '';
			if ($nb > 1) $s = 's';
			$this->view->elementsRetires .= $this->view->ingredients[$indice]['type'] . ' : ' . $nb . ', ';
		}
	}

	private function prepareTypeAutres($depart, $idTypeDepart, $endroit) {
		Zend_Loader::loadClass($depart);

		$tabAutres['nb_castar'] = 0;
		$tabAutres['nb_peau'] = 0;
		$tabAutres['nb_cuir'] = 0;
		$tabAutres['nb_fourrure'] = 0;
		$tabAutres['nb_planche'] = 0;
		$tabAutres['nb_rondin'] = 0;

		$autres = null;
		switch ($idTypeDepart) {
			case self::ID_ENDROIT_LABAN:
				$labanTable = new Laban();
				$autres = $labanTable->findByIdBraldun($this->view->user->id_braldun);
				if ($autres == null) { // si l'on a pas de laban
					$autres [0] = array(
						'quantite_castar_' . strtolower($depart) => 0,
						'quantite_peau_' . strtolower($depart) => 0,
						'quantite_cuir_' . strtolower($depart) => 0,
						'quantite_fourrure_' . strtolower($depart) => 0,
						'quantite_planche_' . strtolower($depart) => 0,
						'quantite_rondin_' . strtolower($depart) => 0,
					);
				}
				if ($this->view->user->castars_braldun > 0) {
					$autres[0]['quantite_castar_laban'] = $this->view->user->castars_braldun;
				}
				unset($labanTable);
				break;
			case self::ID_ENDROIT_ELEMENT :
				$elementTable = new Element();
				$autres = $elementTable->findByCase($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, true, $this->tabButins);
				unset($elementTable);
				break;
			case self::ID_ENDROIT_MON_COFFRE:
				$coffreTable = new Coffre();
				$autres = $coffreTable->findByIdBraldun($this->view->user->id_braldun);
				if (count($autres) != 1) {
					throw new Zend_Exception(get_class($this) . ' Coffre depart invalide = idb:' . $this->view->user->id_braldun);
				}
				$this->view->id_coffre_depart = $autres[0]['id_coffre'];
				unset($coffreTable);
				break;
			case self::ID_ENDROIT_HALL_LIEU:
			case self::ID_ENDROIT_COFFRE_COMMUNAUTE:
				$coffreTable = new Coffre();
				$autres = $coffreTable->findByIdCommunaute($endroit['id_communaute']);
				if (count($autres) == 1) {
					$this->view->id_coffre_depart = $autres[0]['id_coffre'];
				}
				unset($coffreTable);
				break;
			case self::ID_ENDROIT_CHARRETTE :
				$charretteTable = new Charrette();
				$autres = $charretteTable->findByIdCharrette($this->view->id_charrette_depart);
				unset($charretteTable);
				break;
			case self::ID_ENDROIT_ECHOPPE_CAISSE :
			case self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE :
				$echoppeTable = new Echoppe();
				$autres = $echoppeTable->findById($this->view->id_echoppe_depart);
				unset($echoppeTable);
				break;
		}

		$autresButin = null;

		if (count($autres) >= 1) {
			$tabAutres = array(
				'nb_castar' => 0,
				'nb_peau' => 0,
				'nb_cuir' => 0,
				'nb_fourrure' => 0,
				'nb_planche' => 0,
				'nb_rondin' => 0,
				'info_castar' => '',
				'info_peau' => '',
				'info_cuir' => '',
				'info_fourrure' => '',
				'info_planche' => '',
				'info_rondin' => '',
			);

			if ($idTypeDepart == self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE) {
				$strqte = 'arriere_echoppe';
			} elseif ($idTypeDepart == self::ID_ENDROIT_ECHOPPE_CAISSE) {
				$strqte = 'caisse_echoppe';
			} else {
				$strqte = $depart;
			}

			foreach ($autres as $p) {
				if ($idTypeDepart == self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE) {
					$tabAutres['nb_castar'] = 0;
				} else {
					$tabAutres['nb_castar'] = $tabAutres['nb_castar'] + $p['quantite_castar_' . strtolower($strqte)];
				}

				if ($idTypeDepart != self::ID_ENDROIT_ECHOPPE_CAISSE) {
					$tabAutres['nb_peau'] = $tabAutres['nb_peau'] + $p['quantite_peau_' . strtolower($strqte)];
					$tabAutres['nb_cuir'] = $tabAutres['nb_cuir'] + $p['quantite_cuir_' . strtolower($strqte)];
					$tabAutres['nb_fourrure'] = $tabAutres['nb_fourrure'] + $p['quantite_fourrure_' . strtolower($strqte)];
					$tabAutres['nb_planche'] = $tabAutres['nb_planche'] + $p['quantite_planche_' . strtolower($strqte)];
					$tabAutres['nb_rondin'] = $tabAutres['nb_rondin'] + $p['quantite_rondin_' . strtolower($strqte)];
				}
				if ($depart == 'Element') {
					if ($p['id_fk_butin_element'] != null) {
						$autresButin[] = $p;
						if ($p['quantite_castar_' . strtolower($strqte)] > 0) $tabAutres['info_castar'] .= ' Butin n°' . $p['id_fk_butin_element'];
						if ($idTypeDepart != self::ID_ENDROIT_ECHOPPE_CAISSE) {
							if ($p['quantite_peau_' . strtolower($strqte)] > 0) $tabAutres['info_peau'] .= ' Butin n°' . $p['id_fk_butin_element'];
							if ($p['quantite_cuir_' . strtolower($strqte)] > 0) $tabAutres['info_cuir'] .= ' Butin n°' . $p['id_fk_butin_element'];
							if ($p['quantite_fourrure_' . strtolower($strqte)] > 0) $tabAutres['info_fourrure'] .= ' Butin n°' . $p['id_fk_butin_element'];
							if ($p['quantite_planche_' . strtolower($strqte)] > 0) $tabAutres['info_planche'] .= ' Butin n°' . $p['id_fk_butin_element'];
							if ($p['quantite_rondin_' . strtolower($strqte)] > 0) $tabAutres['info_rondin'] .= ' Butin n°' . $p['id_fk_butin_element'];
						}
					}


				}
			}

			if ($tabAutres['nb_castar'] != 0 || $tabAutres['nb_peau'] != 0 ||
				$tabAutres['nb_cuir'] != 0 || $tabAutres['nb_fourrure'] != 0 ||
				$tabAutres['nb_planche'] != 0 || $tabAutres['nb_rondin'] != 0
			) {
				$this->view->deposerOk = true;

				if ($tabAutres['info_castar'] != '') $tabAutres['info_castar'] = ' (dont ' . $tabAutres['info_castar'] . ')';
				if ($tabAutres['info_peau'] != '') $tabAutres['info_peau'] = ' (dont ' . $tabAutres['info_castar'] . ')';
				if ($tabAutres['info_cuir'] != '') $tabAutres['info_cuir'] = ' (dont ' . $tabAutres['info_castar'] . ')';
				if ($tabAutres['info_fourrure'] != '') $tabAutres['info_fourrure'] = ' (dont ' . $tabAutres['info_castar'] . ')';
				if ($tabAutres['info_planche'] != '') $tabAutres['info_planche'] = ' (dont ' . $tabAutres['info_castar'] . ')';
				if ($tabAutres['info_rondin'] != '') $tabAutres['info_rondin'] = ' (dont ' . $tabAutres['info_castar'] . ')';
			}
		}
		$this->view->autres = $tabAutres;
		$this->autresButin = $autresButin;
	}

	private function deposeTypeAutres($depart, $arrivee, $idTypeDepart, $idTypeArrivee) {

		if ($idTypeArrivee == self::ID_ENDROIT_ECHOPPE_CAISSE ||
			$idTypeArrivee == self::ID_ENDROIT_ECHOPPE_ETAL ||
			$idTypeArrivee == self::ID_ENDROIT_ECHOPPE_ATELIER
		) {
			return;
		}

		Zend_Loader::loadClass($depart);
		Zend_Loader::loadClass($arrivee);

		$nbCastar = Bral_Util_Controle::getValeurIntVerif($this->request->get('valeur_11'));
		$nbPeau = Bral_Util_Controle::getValeurIntVerif($this->request->get('valeur_12'));
		$nbCuir = Bral_Util_Controle::getValeurIntVerif($this->request->get('valeur_13'));
		$nbFourrure = Bral_Util_Controle::getValeurIntVerif($this->request->get('valeur_14'));
		$nbPlanche = Bral_Util_Controle::getValeurIntVerif($this->request->get('valeur_15'));
		$nbRondin = Bral_Util_Controle::getValeurIntVerif($this->request->get('valeur_16'));

		if ($idTypeArrivee == self::ID_ENDROIT_HOTEL || $idTypeArrivee == self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE) {
			$nbCastar = 0;
		}

		$tabElement[1] = array('nom_systeme' => 'castar', 'nb' => $nbCastar, 'poids' => Bral_Util_Poids::POIDS_CASTARS);
		$tabElement[2] = array('nom_systeme' => 'peau', 'nb' => $nbPeau, 'poids' => Bral_Util_Poids::POIDS_PEAU);
		$tabElement[3] = array('nom_systeme' => 'cuir', 'nb' => $nbCuir, 'poids' => Bral_Util_Poids::POIDS_CUIR);
		$tabElement[4] = array('nom_systeme' => 'fourrure', 'nb' => $nbFourrure, 'poids' => Bral_Util_Poids::POIDS_FOURRURE);
		$tabElement[5] = array('nom_systeme' => 'planche', 'nb' => $nbPlanche, 'poids' => Bral_Util_Poids::POIDS_PLANCHE);
		$tabElement[6] = array('nom_systeme' => 'rondin', 'nb' => $nbRondin, 'poids' => Bral_Util_Poids::POIDS_RONDIN);

		foreach ($tabElement as $t) {
			$nb = $t['nb'];
			$nom_systeme = $t['nom_systeme'];
			$poids = $t['poids'];
			if ($nb < 0) {
				throw new Zend_Exception(get_class($this) . ' Nb ' . $nom_systeme . ' : ' . $nb);
			}

			if ($nb <= 0) {
				continue;
			}
			if ($nb > $this->view->autres['nb_' . $nom_systeme]) {
				$nb = $this->view->autres['nb_' . $nom_systeme];
			}
			if (($depart == 'Echoppe' || $arrivee == 'Echoppe') && ($nom_systeme == 'viande' || $nom_systeme == 'viande_preparee')) {
				$nb = 0;
			}

			if ($idTypeArrivee == self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE && $nom_systeme == 'castar') {
				$nb = 0;
			}

			if ($depart == 'Charrette' && $this->view->a_panneau === false && $this->view->nbelement > 0) {
				$this->view->panneau = false;
				$this->view->elementsNonRetiresPanneau .= $nb . ' ' . str_replace('_preparee', ' préparée', $nom_systeme) . ', ';
			}

			$poidsOk = true;
			if ($arrivee == 'Laban' || $arrivee == 'Charrette') {
				$poidsOk = $this->controlePoids($this->view->poidsRestant, $nb, $poids);
				if ($poidsOk == false) {
					$this->view->poidsOk = false;
					$this->view->elementsNonRetiresPoids .= $nb . ' ' . str_replace('_preparee', ' préparée', $nom_systeme) . ', ';
				}
			}

			if ($poidsOk != true || $this->view->panneau == false) {
				continue;
			}

			$this->view->nbelement = $this->view->nbelement + 1;
			$data = array(
				'quantite_' . $nom_systeme . '_' . strtolower($depart) => -$nb,
				'id_fk_braldun_' . strtolower($depart) => $this->view->user->id_braldun,
			);
			$departTable = null;
			switch ($idTypeDepart) {
				case self::ID_ENDROIT_LABAN :
					if ($nom_systeme == 'castar') {
						if ($nb > $this->view->user->castars_braldun) {
							$nb = $this->view->user->castars_braldun;
						}
						$this->view->user->castars_braldun = $this->view->user->castars_braldun - $nb;
					} else {
						$departTable = new Laban();
					}
					break;
				case self::ID_ENDROIT_ELEMENT :
					$departTable = new Element();

					$nbAEnlever = $nb;

					// on supprime les butins en premier
					if ($this->autresButin != null) {
						foreach ($this->autresButin as $b) {

							$nbAEnleverCourant = $nbAEnlever;
							if ($b['quantite_' . $nom_systeme . '_element'] <= $nbAEnlever) {
								$nbAEnleverCourant = $b['quantite_' . $nom_systeme . '_element'];
							}

							$data = array(
								'quantite_' . $nom_systeme . '_element' => -$nbAEnleverCourant,
								'x_element' => $this->view->user->x_braldun,
								'y_element' => $this->view->user->y_braldun,
								'z_element' => $this->view->user->z_braldun,
								'id_fk_butin_element' => $b['id_fk_butin_element'],
							);

							$departTable->insertOrUpdate($data);

							$nbAEnlever = $nbAEnlever - $nbAEnleverCourant;

							if ($nbAEnlever <= 0) {
								break;
							}
						}
					}

					if ($nbAEnlever > 0) {
						$data = array(
							'quantite_' . $nom_systeme . '_element' => -$nb,
							'x_element' => $this->view->user->x_braldun,
							'y_element' => $this->view->user->y_braldun,
							'z_element' => $this->view->user->z_braldun,
							'id_fk_butin_element' => null,
						);
						$departTable->insertOrUpdate($data);
					}
					break;
				case self::ID_ENDROIT_MON_COFFRE :
				case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
				case self::ID_ENDROIT_HALL_LIEU :
					$departTable = new Coffre();
					$data = array(
						'id_coffre' => $this->view->id_coffre_depart,
						'quantite_' . $nom_systeme . '_' . strtolower($depart) => -$nb,
					);
					break;
				case self::ID_ENDROIT_CHARRETTE :
					$departTable = new Charrette();
					$data = array(
						'id_charrette' => $this->view->id_charrette_depart,
						'quantite_' . $nom_systeme . '_' . strtolower($depart) => -$nb,
					);
					break;
				case self::ID_ENDROIT_ECHOPPE_ATELIER :
				case self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE :
					$departTable = new Echoppe();
					$data = array(
						'quantite_' . $nom_systeme . '_arriere_' . strtolower($depart) => -$nb,
						'id_' . strtolower($depart) => $this->view->id_echoppe_depart,
					);
					break;
				case self::ID_ENDROIT_ECHOPPE_CAISSE :
					$departTable = new Echoppe();
					$data = array(
						'quantite_' . $nom_systeme . '_caisse_' . strtolower($depart) => -$nb,
						'id_' . strtolower($depart) => $this->view->id_echoppe_depart,
					);
					break;
				default:
					throw new Zend_Exception('Depart invalide : ' . $depart . ' id:' . $idTypeDepart);
			}
			if ($departTable) {
				if ($depart != 'Element') {
					$departTable->insertOrUpdate($data);
					unset($departTable);
				}
			}

			$arriveeTable = null;
			switch ($idTypeArrivee) {
				case self::ID_ENDROIT_LABAN :
				case self::ID_ENDROIT_LABAN_BRALDUN :
					if ($nom_systeme == 'castar') {
						$this->view->user->castars_braldun = $this->view->user->castars_braldun + $nb;
						$this->view->elementsRetires .= $nb . ' castar';
						if ($nb > 1) $this->view->elementsRetires .= 's';
						$this->view->elementsRetires .= ', ';
					} else {
						$data = array(
							'quantite_' . $nom_systeme . '_laban' => $nb,
							'id_fk_braldun_laban' => $this->view->id_braldun_destinataire,
						);
						$arriveeTable = new Laban();
					}
					$this->view->poidsRestant = $this->view->poidsRestant - $poids * $nb;
					break;
				case self::ID_ENDROIT_ELEMENT :
					$data = array(
						'quantite_' . $nom_systeme . '_element' => $nb,
						'x_element' => $this->view->user->x_braldun,
						'y_element' => $this->view->user->y_braldun,
						'z_element' => $this->view->user->z_braldun,
					);
					$arriveeTable = new Element();
					break;
				case self::ID_ENDROIT_MON_COFFRE :
				case self::ID_ENDROIT_COFFRE_COMMUNAUTE :
				case self::ID_ENDROIT_HALL_LIEU :
				case self::ID_ENDROIT_COFFRE_BRALDUN :
					$data = array(
						'quantite_' . $nom_systeme . '_coffre' => $nb,
						'id_coffre' => $this->view->id_coffre_arrivee,
					);
					$arriveeTable = new Coffre();
					break;
				case self::ID_ENDROIT_HOTEL :
				case self::ID_ENDROIT_ECHOPPE_ETAL :
				case self::ID_ENDROIT_RESERVATION_COMMUNAUTE :
					$data = array(
						'quantite_' . $nom_systeme . '_lot' => $nb,
						'id_lot' => $this->idLot,
					);
					$arriveeTable = new Lot();
					break;
				case self::ID_ENDROIT_CHARRETTE :
					$data = array(
						'quantite_' . $nom_systeme . '_charrette' => $nb,
						'id_charrette' => $this->view->id_charrette_arrivee,
					);
					$arriveeTable = new Charrette();
					break;
				case self::ID_ENDROIT_ECHOPPE_MATIERE_PREMIERE :
					$data = array(
						'quantite_' . $nom_systeme . '_arriere_echoppe' => $nb,
						'id_echoppe' => $this->view->id_echoppe_arrivee,
					);
					$arriveeTable = new Echoppe();
					break;
			}
			if ($arriveeTable) {
				$arriveeTable->insertOrUpdate($data);
				unset($arriveeTable);
				if ($nom_systeme == 'peau') {
					$this->view->elementsRetires .= $nb . ' peau';
					if ($nb > 1) $this->view->elementsRetires .= 'x';
					$this->view->elementsRetires .= ', ';
				} else {
					$this->view->elementsRetires .= $nb . ' ' . str_replace('_preparee', ' préparée', $nom_systeme);
					if ($nb > 1) $this->view->elementsRetires .= 's';
					$this->view->elementsRetires .= ', ';
				}
			}
		}
	}

	private function calculTexte($depart, $arrivee) {
		$departTexte = $tabRetour['departTexte'] = $depart;
		$arriveeTexte = $tabRetour['arriveeTexte'] = $arrivee;
		if ($depart == 'Element') $tabRetour['departTexte'] = 'Sol';
		if ($arrivee == 'Element') $tabRetour['arriveeTexte'] = 'Sol';
		return $tabRetour;
	}

	private function prepareCommunButins() {
		Zend_Loader::loadClass('ButinPartage');
		$butinPartageTable = new ButinPartage();

		$partage = $butinPartageTable->findByIdBraldunAutorise($this->view->user->id_braldun);
		$proprietaires[$this->view->user->id_braldun] = $this->view->user->id_braldun;
		foreach ($partage as $p) {
			$proprietaires[$p['id_fk_braldun_butin_partage']] = $p['id_fk_braldun_butin_partage'];
		}

		$tabButinsATerreAutorises = null;
		$butinTable = new Butin();
		$butinsATerreAutorises = $butinTable->findByCaseAndProprietaires($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, $proprietaires);
		foreach ($butinsATerreAutorises as $b) {
			$tabButinsATerreAutorises[$b['id_butin']] = $b['id_butin'];
		}

		if ($this->view->user->id_fk_communaute_braldun != null && $this->view->user->rangCommunaute < Bral_Util_Communaute::ID_RANG_NOUVEAU) {
			$butinsCommunaute = $butinTable->findByCaseAndIdCommunaute($this->view->user->x_braldun, $this->view->user->y_braldun, $this->view->user->z_braldun, $this->view->user->id_fk_communaute_braldun);
			if ($butinsCommunaute != null) {
				foreach ($butinsCommunaute as $b) {
					$tabButinsATerreAutorises[$b['id_butin']] = $b['id_butin'];
				}
			}
		}

		$this->tabButins = $tabButinsATerreAutorises;
	}

}