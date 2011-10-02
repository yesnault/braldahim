<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3.
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class FeedadminController extends Zend_Controller_Action
{

	function init()
	{
		$this->initView();
		Zend_Loader::loadClass("Bral_Util_Securite");
		Bral_Util_Securite::controlFeedAdmin($this->_request);
	}

	public function blablaAction()
	{

		Zend_Loader::loadClass("Zend_Feed");
		Zend_Loader::loadClass("Blabla");

		$blablaTable = new Blabla();
		$blasblas = $blablaTable->selectAll();

		$feedArray = array(
			'title' => "Braldahim - Admin - Blabla",
			'link' => 'http://www.braldahim.com',
			'charset' => 'utf-8',
			'description' => "Braldahim - Admin - Blabla",
			'author' => 'Thains - Braldahim',
			'email' => 'webmaster@braldahim.com',
			'copyright' => 'Braldahim.com',
			'generator' => 'Zend Framework Zend_Feed',
			'language' => 'fr',
			'entries' => array()
		);

		$pubDate = "";
		$lien = "http://www.braldahim.com";
		foreach ($blasblas as $blabla) {
			$titre = $blabla["prenom_braldun"] . " " . $blabla["nom_braldun"] . " (" . $blabla["id_fk_braldun_blabla"] . ") en ";
			$titre .= $blabla["x_blabla"] . " / " . $blabla["y_blabla"] . " / " . $blabla["z_blabla"];
			$texte = Bral_Util_BBParser::bbcodeReplace($blabla["message_blabla"]);
			if ($blabla["est_censure_blabla"] == "oui") {
				$texte .= PHP_EOL . "<br /><b>Censuré : " . $blabla["est_censure_blabla"] . "</b>";
			}
			$guid = $blabla["id_blabla"];
			$feedArray['entries'][] = array(
				'title' => $titre,
				'link' => $lien,
				'description' => $texte,
				'content' => $texte,
				'lastUpdate' => Bral_Util_ConvertDate::get_epoch_mysql_datetime($blabla["date_blabla"]),
				'guid' => $lien,
			);

			if ($pubDate == "") {
				$pubDate = $blabla["date_blabla"];
			} else {
				if ($pubDate < $blabla["date_blabla"]) {
					$pubDate = $blabla["date_blabla"];
				}
			}
		}

		$feedArray["lastUpdate"] = Bral_Util_ConvertDate::get_epoch_mysql_datetime($pubDate);

		$feed = Zend_Feed::importArray($feedArray, 'rss');
		$feed->send();
	}
}