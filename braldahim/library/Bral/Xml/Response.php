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
class Bral_Xml_Response {

	/* add an new entry in the list */
	public function add_entry($p) {
		$this->list[] = $p;
	}

	public function get_list() {
		return $this->list;
	}

	private function echo_xml() {
		echo  '<?xml version="1.0" encoding="utf-8" ?>';
		echo "<root>\n";
		$memoire1 = memory_get_usage();
		echo "<entrie>\n";
		echo "<type>display</type>\n";
		echo "<valeur>date_heure</valeur>\n";
		echo "<data>";
		echo new Zend_Date();
		echo " | </data>\n";
		echo "</entrie>\n";
		$this->XmlNbConnecte();
		
		ob_flush();
		foreach ($this->list as $k => $e) {
			echo "<entrie>\n";
			$e->echo_xml();//, 9);//$e->get_xml();
			echo "</entrie>\n";
			ob_flush();
			unset($this->list[$k]);
		}
		unset($this->list);
		$memoire2 = memory_get_usage();
		$this->xmlAdmin("admin_info_1","mem1:".$memoire1. " mem2:".$memoire2. " allouée:".memory_get_peak_usage(false));
		echo "</root>\n";
	}
	
	public function xmlAdmin($id, $texte) {
		if (Zend_Auth::getInstance()->getIdentity()->sysgroupe_braldun == "admin") {
			echo "<entrie>\n";
			echo "<type>display</type>\n";
			echo "<valeur>".$id."</valeur>\n";
			echo "<data>";
			echo $texte;
			echo " | </data>\n";
			echo "</entrie>\n";
		}
	}
	
	public function render() {
		header("Content-Type: text/xml");
		$this->echo_xml();
	}
	
	public function __construct() {
	}
	
	private function XmlNbConnecte() {
		$session = new Session();
		$nb = $session->count();
		$s = "";
		if ($nb > 1) {
			$s = "s";
		}
		unset($session);
		echo "<entrie>\n";
		echo "<type>display</type>\n";
		echo "<valeur>nb_connectes</valeur>\n";
		echo "<data>";
		echo "Il y a actuellement ".$nb." Braldûn".$s." connecté".$s;
		echo "</data>\n";
		echo "</entrie>\n";
	}
}
