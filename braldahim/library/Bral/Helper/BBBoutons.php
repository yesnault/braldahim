<?php

/**
 * This file is part of Braldahim, under Gnu Public Licence v3. 
 * See licence.txt or http://www.gnu.org/licenses/gpl-3.0.html
 * Copyright: see http://www.braldahim.com/sources
 */
class Bral_Helper_BBBoutons {

	public static function affiche($champ, $nbMax = 2500) {
		
		$url = Zend_Registry::get('config')->url->static;
	
		$retour ='		<div id="uddeim-bbemobox">';
		$retour .='				<table border="0" cellpadding="0" cellspacing="4" align="center">';
		$retour .='					<tbody>';
		$retour .='					<tr>';
		$retour .='						<td><img alt="gras" src="'.$url.'/images/uddeim/format_bold.gif" style="cursor: pointer;" id="addbbcode0" onclick="bbstyle(0, $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir du texte en gras. Utilisation : [b]bold[/b]"></td>';
		$retour .='						<td><img alt="italic" src="'.$url.'/images/uddeim/format_italic.gif" style="cursor: pointer;" id="addbbcode2" onclick="bbstyle(2, $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir du texte en italique. Utilisation : [i]italic[/i]"></td>';
		$retour .='						<td><img alt="souligné" src="'.$url.'/images/uddeim/format_underline.gif" style="cursor: pointer;" id="addbbcode4" onclick="bbstyle(4, $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir du texte souligné. Utilisation : [u]underline[/u]"></td>';
		$retour .='						<td>&nbsp;</td>';
		$retour .='						<td><img alt="rouge" src="'.$url.'/images/uddeim/format_red.gif" style="cursor: pointer;" id="addbbcode6" onclick="bbstyle(6, $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir la couleur du texte. Utilisation : [color=#XXXXXX]en couleurs[/color] où XXXXXX est le code hexadécimal de la couleur que vous voulez, par exemple FF0000 pour du rouge."></td>';
		$retour .='						<td><img alt="vert" src="'.$url.'/images/uddeim/format_green.gif" style="cursor: pointer;" id="addbbcode8" onclick="bbstyle(8, $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir la couleur du texte. Utilisation : [color=#XXXXXX]en couleurs[/color] où XXXXXX est le code hexadécimal de la couleur que vous voulez, par exemple 00FF00 pour du vert."></td>';
		$retour .='						<td><img alt="bleu" src="'.$url.'/images/uddeim/format_blue.gif" style="cursor: pointer;" id="addbbcode10" onclick="bbstyle(10, $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir la couleur du texte. Utilisation : [color=#XXXXXX]en couleurs[/color] où XXXXXX est le code hexadécimal de la couleur que vous voulez, par exemple 0000FF pour du bleu."></td>';
		$retour .='						<td>&nbsp;</td>';
		$retour .='						<td><img alt="très petit" src="'.$url.'/images/uddeim/format_size1.gif" style="cursor: pointer;" id="addbbcode12" onclick="bbstyle(12, $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir du texte de petite taille. Utilisation : [size=1]texte de petite taille.[/size]"></td>';
		$retour .='						<td><img alt="petit" src="'.$url.'/images/uddeim/format_size2.gif" style="cursor: pointer;" id="addbbcode14" onclick="bbstyle(14, $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir du texte de grande taille 2. Utilisation : [size=2]texte de grande taille 2.[/size]"></td>';
		$retour .='						<td><img alt="normal" src="'.$url.'/images/uddeim/format_size2.gif" style="cursor: pointer;" id="addbbcode16" onclick="bbstyle(16, $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir du texte de grande taille 3. Utilisation : [size=3]texte de grande taille 3.[/size]"></td>';
		$retour .='						<td><img alt="grand" src="'.$url.'/images/uddeim/format_size4.gif" style="cursor: pointer;" id="addbbcode18" onclick="bbstyle(18, $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir du texte de grande taille 4. Utilisation : [size=4]texte de grande taille 4.[/size]"></td>';
		$retour .='						<td><img alt="très grand" src="'.$url.'/images/uddeim/format_size5.gif" style="cursor: pointer;" id="addbbcode20" onclick="bbstyle(20, $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir du texte de grande taille 5. Utilisation : [size=5]texte de grande taille 5.[/size]"></td>';
		$retour .='					</tr>';
		$retour .='					<tr>';
		$retour .='						<td><img alt="gauche" src="'.$url.'/images/uddeim/format_left.gif" style="cursor: pointer;" id="addbbcode0" onclick="bbstyle(32, $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir du texte à gauche. Utilisation : [left]texte[/left]"></td>';
		$retour .='						<td><img alt="centré" src="'.$url.'/images/uddeim/format_center.gif" style="cursor: pointer;" id="addbbcode2" onclick="bbstyle(34, $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir du texte centré. Utilisation : [center]texte[/center]"></td>';
		$retour .='						<td><img alt="droite" src="'.$url.'/images/uddeim/format_right.gif" style="cursor: pointer;" id="addbbcode4" onclick="bbstyle(36, $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir du texte à droite. Utilisation : [right]texte[/right]"></td>';
		$retour .='						<td><img alt="justifié" src="'.$url.'/images/uddeim/format_justify.gif" style="cursor: pointer;" id="addbbcode4" onclick="bbstyle(38, $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir du texte justifié. Utilisation : [justify]texte[/justify]"></td>';
		$retour .='						<td>&nbsp;</td>';
		$retour .='						<td><img alt="liste ul" src="'.$url.'/images/uddeim/format_listul.gif" style="cursor: pointer;" id="addbbcode4" onclick="bbstyle(22, $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir une liste. Utilisation : [ul][li]...[/li][/ul]"></td>';
		$retour .='						<td><img alt="element de liste li" src="'.$url.'/images/uddeim/format_listul.gif" style="cursor: pointer;" id="addbbcode4" onclick="bbstyle(30, $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir une liste. Utilisation : [li]texte[/li]"></td>';
		$retour .='						<td>&nbsp;</td>';
		$retour .='						<td><img alt="adresse" src="'.$url.'/images/uddeim/format_link.gif" style="cursor: pointer;" id="addbbcode28" onclick="bbstyle(28, $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir une url. Utilisation : [url=http://monadresse.com]texte adresse[/url]"></td>';
		$retour .='						<td>&nbsp;</td>';
		$retour .='						<td><img alt="close tags" src="'.$url.'/images/uddeim/format_closeall.gif" style="cursor: pointer;" onclick="bbstyle(-1, $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" title="Fermer tous les tags BBcode."></td>';
		$retour .='					</tr>';
		$retour .='					</tbody>';
		$retour .='				</table>';
		$retour .='			</div>';
		$retour .='			<div id="uddeim-smileybox">';
		$retour .='				<table border="0" cellpadding="2" cellspacing="0" align="center">';
		$retour .='					<tbody>';
		$retour .='					<tr>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\':) \', $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" src="'.$url.'/images/uddeim/emoticon_smile.gif" alt=":)" title=":)"></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\':( \', $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" src="'.$url.'/images/uddeim/emoticon_sad.gif" alt=":(" title=":("></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\':P \', $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" src="'.$url.'/images/uddeim/emoticon_tongue.gif" alt=":P" title=":P"></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\':x \', $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" src="'.$url.'/images/uddeim/emoticon_crossed.gif" alt=":x" title=":x"></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\':angry: \', $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" src="'.$url.'/images/uddeim/emoticon_angry.gif" alt=":angry:" title=":angry:"></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\':blush: \', $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" src="'.$url.'/images/uddeim/emoticon_blush.gif" alt=":blush:" title=":blush:"></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\'B) \', $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" src="'.$url.'/images/uddeim/emoticon_cool.gif" alt="B)" title="B)"></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\':* \', $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" src="'.$url.'/images/uddeim/emoticon_heart.gif" alt=":*" title=":*"></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\':kiss: \', $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" src="'.$url.'/images/uddeim/emoticon_kiss.gif" alt=":kiss:" title=":kiss:"></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\':laugh: \', $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" src="'.$url.'/images/uddeim/emoticon_laughing.gif" alt=":laugh:" title=":laugh:"></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\':ohmy: \', $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" src="'.$url.'/images/uddeim/emoticon_shocked.gif" alt=":ohmy:" title=":ohmy:"></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\';) \', $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" src="'.$url.'/images/uddeim/emoticon_wink.gif" alt=";)" title=";)"></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\':? \', $(\'#'.$champ.'\')); textCount($(\'#'.$champ.'\'),$(\'#characterstyped\'),'.$nbMax.'); return false;" src="'.$url.'/images/uddeim/emoticon_wondering.gif" alt=":?" title=":?"></td>';
		$retour .='					</tr>';
		$retour .='					</tbody>';
		$retour .='				</table>';
		$retour .='			</div>';
		
		return $retour;
	}

}


