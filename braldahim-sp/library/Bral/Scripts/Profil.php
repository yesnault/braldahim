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
class Bral_Scripts_Profil extends Bral_Scripts_Script
{

	public function getType()
	{
		return self::TYPE_DYNAMIQUE;
	}

	public function getEtatService()
	{
		return self::SERVICE_ACTIVE;
	}

	public function getVersion()
	{
		return 2;
	}

	public function calculScriptImpl()
	{
		Bral_Util_Log::scripts()->trace("Bral_Scripts_Profil - calculScriptImpl - enter -");

		$retour = null;
		$this->calculProfil($retour);

		Bral_Util_Log::scripts()->trace("Bral_Scripts_Profil - calculScriptImpl - exit -");
		return $retour;
	}

	private function calculProfil(&$retour)
	{

		$retour1 = 'idBraldun;prenom;nom;x;y;z;';
		$retour2 = $this->braldun->id_braldun . ';' . $this->braldun->prenom_braldun . ';' . $this->braldun->nom_braldun . ';' . $this->braldun->x_braldun . ';' . $this->braldun->y_braldun . ';' . $this->braldun->z_braldun . ';';
		$retour1 .= 'paRestant;DLA;DureeProchainTour;';
		$retour2 .= $this->braldun->pa_braldun . ';' . $this->braldun->date_fin_tour_braldun . ';' . $this->braldun->duree_prochain_tour_braldun . ';';
		$retour1 .= 'dateDebutTour;dateFinTour;dateFinLatence;';
		$retour2 .= $this->braldun->date_debut_tour_braldun . ';' . $this->braldun->date_fin_tour_braldun . ';' . $this->braldun->date_fin_latence_braldun . ';';
		$retour1 .= 'dateDebutCumul;dureeCourantTour;dureeBmTour;';
		$retour2 .= $this->braldun->date_debut_cumul_braldun . ';' . $this->braldun->duree_courant_tour_braldun . ';' . $this->braldun->duree_bm_tour_braldun . ';';
		$retour1 .= 'PvRestant;bmPVmax;bbdf;';
		$retour2 .= $this->braldun->pv_restant_braldun . ';' . $this->braldun->pv_max_bm_braldun . ';' . $this->braldun->balance_faim_braldun . ';';
		$retour1 .= 'nivAgilite;nivForce;nivVigueur;nivSagesse;';
		$retour2 .= $this->braldun->agilite_base_braldun . ';' . $this->braldun->force_base_braldun . ';' . $this->braldun->vigueur_base_braldun . ';' . $this->braldun->sagesse_base_braldun . ';';
		$retour1 .= 'bmAgilite;bmForce;bmVigueur;bmSagesse;';
		$retour2 .= $this->braldun->agilite_bm_braldun . ';' . $this->braldun->force_bm_braldun . ';' . $this->braldun->vigueur_bm_braldun . ';' . $this->braldun->sagesse_bm_braldun . ';';
		$retour1 .= 'bmBddfAgilite;bmBddfForce;bmBddfVigueur;bmBddfSagesse;';
		$retour2 .= $this->braldun->agilite_bbdf_braldun . ';' . $this->braldun->force_bbdf_braldun . ';' . $this->braldun->vigueur_bbdf_braldun . ';' . $this->braldun->sagesse_bbdf_braldun . ';';
		$retour1 .= 'bmVue;regeneration;bmRegeneration;';
		$retour2 .= $this->braldun->vue_bm_braldun . ';' . $this->braldun->regeneration_braldun . ';' . $this->braldun->regeneration_bm_braldun . ';';
		$retour1 .= 'pxPerso;pxCommun;pi;niveau;';
		$retour2 .= $this->braldun->px_perso_braldun . ';' . $this->braldun->px_commun_braldun . ';' . $this->braldun->pi_braldun . ';' . $this->braldun->niveau_braldun . ';';
		$retour1 .= 'poidsTransportable;poidsTransporte;';
		$retour2 .= $this->braldun->poids_transportable_braldun . ';' . $this->braldun->poids_transporte_braldun . ';';
		$retour1 .= 'armureNaturelle;armureEquipement;';
		$retour2 .= $this->braldun->armure_naturelle_braldun . ';' . $this->braldun->armure_equipement_braldun . ';';
		$retour1 .= 'bmAttaque;bmDegat;bmDefense;';
		$retour2 .= $this->braldun->bm_attaque_braldun . ';' . $this->braldun->bm_degat_braldun . ';' . $this->braldun->bm_defense_braldun . ';';
		$retour1 .= 'nbKo;nbKill;nbKoBraldun;';
		$retour2 .= $this->braldun->nb_ko_braldun . ';' . $this->braldun->nb_monstre_kill_braldun . ';' . $this->braldun->nb_braldun_ko_braldun . ';';
		$retour1 .= 'estEngage;estEngageProchainTour;estIntangible;';
		$retour2 .= $this->braldun->est_engage_braldun . ';' . $this->braldun->est_engage_next_dla_braldun . ';' . $this->braldun->est_intangible_braldun . ';';
		$retour1 .= 'nbPlaquagesSubis;nbPlaquagesEffectues' . PHP_EOL;
		$retour2 .= $this->braldun->nb_plaque_braldun . ';' . $this->braldun->nb_braldun_plaquage_braldun;
		$retour2 .= PHP_EOL;

		$retour .= $retour1;
		$retour .= $retour2;

	}
}