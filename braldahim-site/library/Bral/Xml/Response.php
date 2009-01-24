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
		echo "<entrie>\n";
		echo "<type>display</type>\n";
		echo "<valeur>date_heure</valeur>\n";
		echo "<data>";
		echo new Zend_Date();
		echo " | </data>\n";
		echo "</entrie>\n";
		
		ob_flush();
		foreach ($this->list as $k => $e) {
			echo "<entrie>\n";
			$e->echo_xml();
			echo "</entrie>\n";
			ob_flush();
			unset($this->list[$k]);
		}
		unset($this->list);
		echo "</root>\n";
	}
	
	public function render() {
		header("Content-Type: text/xml");
		$this->echo_xml();
	}
	
	public function __construct() {
	}
}