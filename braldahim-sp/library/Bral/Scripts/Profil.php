<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 *
 * $Id: $
 * $Author: $
 * $LastChangedDate: $
 * $LastChangedRevision: $
 * $LastChangedBy: $
 */
class Bral_Scripts_Profil extends Bral_Scripts_Script {

	public function getType() {
		return self::TYPE_DYNAMIQUE;
	}

	public function getEtatService() {
		return self::SERVICE_ACTIVE;
	}

	public function getVersion() {
		return 1;
	}

	public function calculScriptImpl() {
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Profil - calculScriptImpl - enter -");

		$retour = null;
		$this->calculProfil($retour);

		Bral_Util_Log::scripts()->trace("Bral_Scripts_Profil - calculScriptImpl - exit -");
		return $retour;
	}

	private function calculProfil(&$retour) {

		$retour1 = 'idHobbit;prenom;nom;x;y;z;';
		$retour2 = $this->hobbit->id_hobbit.';'.$this->hobbit->prenom_hobbit.';'.$this->hobbit->nom_hobbit.';'.$this->hobbit->x_hobbit.';'.$this->hobbit->y_hobbit.';'.$this->hobbit->z_hobbit.';';
		$retour1 .= 'paRestant;DLA;DureeProchainTour;';
		$retour2 .= $this->hobbit->pa_hobbit.';'.$this->hobbit->date_fin_tour_hobbit.';'.$this->hobbit->duree_prochain_tour_hobbit.';';
		$retour1 .= 'PvRestant;bmPVmax;bbdf;';
		$retour2 .= $this->hobbit->pv_restant_hobbit.';'.$this->hobbit->pv_max_bm_hobbit.';'.$this->hobbit->balance_faim_hobbit.';';
		$retour1 .= 'nivAgilite;nivForce;nivVigueur;nivSagesse;';
		$retour2 .= $this->hobbit->agilite_base_hobbit.';'.$this->hobbit->force_base_hobbit.';'.$this->hobbit->vigueur_base_hobbit.';'.$this->hobbit->sagesse_base_hobbit.';';
		$retour1 .= 'bmAgilite;bmForce;bmVigueur;bmSagesse;';
		$retour2 .= $this->hobbit->agilite_bm_hobbit.';'.$this->hobbit->force_bm_hobbit.';'.$this->hobbit->vigueur_bm_hobbit.';'.$this->hobbit->sagesse_bm_hobbit.';';
		$retour1 .= 'bmBddfAgilite;bmBddfForce;bmBddfVigueur;bmBddfSagesse;';
		$retour2 .= $this->hobbit->agilite_bbdf_hobbit.';'.$this->hobbit->force_bbdf_hobbit.';'.$this->hobbit->vigueur_bbdf_hobbit.';'.$this->hobbit->sagesse_bbdf_hobbit.';';
		$retour1 .= 'bmVue;regeneration;bmRegeneration;';
		$retour2 .= $this->hobbit->vue_bm_hobbit.';'.$this->hobbit->regeneration_hobbit.';'.$this->hobbit->regeneration_malus_hobbit.';';
		$retour1 .= 'pxPerso;pxCommun;pi;niveau;';
		$retour2 .= $this->hobbit->px_perso_hobbit.';'.$this->hobbit->px_commun_hobbit.';'.$this->hobbit->pi_hobbit.';'.$this->hobbit->niveau_hobbit.';';
		$retour1 .= 'poidsTransportable;poidsTransporte;';
		$retour2 .= $this->hobbit->poids_transportable_hobbit.';'.$this->hobbit->poids_transporte_hobbit.';';
		$retour1 .= 'armureNaturelle;armureEquipement;';
		$retour2 .= $this->hobbit->armure_naturelle_hobbit.';'.$this->hobbit->armure_equipement_hobbit.';';
		$retour1 .= 'bmAttaque;bmDegat;bmDefense;';
		$retour2 .= $this->hobbit->bm_attaque_hobbit.';'.$this->hobbit->bm_degat_hobbit.';'.$this->hobbit->bm_defense_hobbit.';';
		$retour1 .= 'nbKo;nbKill;nbKoHobbit;';
		$retour2 .= $this->hobbit->nb_ko_hobbit.';'.$this->hobbit->nb_monstre_kill_hobbit.';'.$this->hobbit->nb_hobbit_ko_hobbit.';';
		$retour1 .= 'estEngage;estEngageProchainTour;estIntangible;';
		$retour2 .= $this->hobbit->est_engage_hobbit.';'.$this->hobbit->est_engage_next_dla_hobbit.';'.$this->hobbit->est_intangible_hobbit.';';
		$retour1 .= 'nbPlaquagesSubis;nbPlaquagesEffectues'.PHP_EOL;
		$retour2 .= $this->hobbit->nb_plaque_hobbit.';'.$this->hobbit->nb_hobbit_plaquage_hobbit;
		$retour2 .= PHP_EOL;
		
		$retour .= $retour1;
		$retour .= $retour2;
		
	}
}