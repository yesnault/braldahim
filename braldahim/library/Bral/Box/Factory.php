<?php

class Bral_Box_Factory {
	
	public static function getCompetences($request, $view, $type) {
		 return new Bral_Box_Competences($request, $view, $type);
	}

	public static function getEquipement($request, $view) {
		return new Bral_Box_Equipement($request, $view);
	}
	
	public static function getProfil($request, $view) {
		return new Bral_Box_Profil($request, $view);
	}
	
	public static function getVue($request, $view) {
		return new Bral_Box_Vue($request, $view);
	}
	
}