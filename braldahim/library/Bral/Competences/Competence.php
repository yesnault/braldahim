<?php

abstract class Bral_Competences_Competence {
	function getNomInterne() {
		return "box_action";
	}

	abstract function prepareFormulaire();
	abstract function prepareResultat();
	abstract function render();
}