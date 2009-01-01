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
class Bral_Helper_BBBoutons {

	public static function affiche($champ, $nbMax = 2500) {
	
		$retour ='		<div id="uddeim-bbemobox">';
		$retour .='				<table border="0" cellpadding="0" cellspacing="4" align="center">';
		$retour .='					<tbody>';
		$retour .='					<tr>';
		$retour .='						<td><img alt="bold" src="/public/images/uddeim/format_bold.gif" style="cursor: pointer;" name="addbbcode0" onclick="bbstyle(0, $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir du texte en gras. Utilisation : [b]bold[/b]"></td>';
		$retour .='						<td><img alt="italic" src="/public/images/uddeim/format_italic.gif" style="cursor: pointer;" name="addbbcode2" onclick="bbstyle(2, $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir du texte en italique. Utilisation : [i]italic[/i]"></td>';
		$retour .='						<td><img alt="underline" src="/public/images/uddeim/format_underline.gif" style="cursor: pointer;" name="addbbcode4" onclick="bbstyle(4, $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir du texte souligné. Utilisation : [u]underline[/u]"></td>';
		$retour .='						<td>&nbsp;</td>';
		$retour .='						<td><img alt="red" src="/public/images/uddeim/format_red.gif" style="cursor: pointer;" name="addbbcode6" onclick="bbstyle(6, $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir la couleur du texte. Utilisation : [color=#XXXXXX]en couleurs[/color] où XXXXXX est le code hexadécimal de la couleur que vous voulez, par exemple FF0000 pour du rouge."></td>';
		$retour .='						<td><img alt="green" src="/public/images/uddeim/format_green.gif" style="cursor: pointer;" name="addbbcode8" onclick="bbstyle(8, $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir la couleur du texte. Utilisation : [color=#XXXXXX]en couleurs[/color] où XXXXXX est le code hexadécimal de la couleur que vous voulez, par exemple 00FF00 pour du vert."></td>';
		$retour .='						<td><img alt="blue" src="/public/images/uddeim/format_blue.gif" style="cursor: pointer;" name="addbbcode10" onclick="bbstyle(10, $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir la couleur du texte. Utilisation : [color=#XXXXXX]en couleurs[/color] où XXXXXX est le code hexadécimal de la couleur que vous voulez, par exemple 0000FF pour du bleu."></td>';
		$retour .='						<td>&nbsp;</td>';
		$retour .='						<td><img alt="very small" src="/public/images/uddeim/format_size1.gif" style="cursor: pointer;" name="addbbcode12" onclick="bbstyle(12, $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir du texte de très petite taille. Utilisation : [size=1]texte de très petite taille.[/size]"></td>';
		$retour .='						<td><img alt="small" src="/public/images/uddeim/format_size2.gif" style="cursor: pointer;" name="addbbcode14" onclick="bbstyle(14, $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir du texte de petite taille. Utilisation : [size=2]texte de petite taille.[/size]"></td>';
		$retour .='						<td><img alt="large" src="/public/images/uddeim/format_size4.gif" style="cursor: pointer;" name="addbbcode16" onclick="bbstyle(16, $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir du texte de grande taille. Utilisation : [size=4]texte de grande taille.[/size]"></td>';
		$retour .='						<td><img alt="very large" src="/public/images/uddeim/format_size5.gif" style="cursor: pointer;" name="addbbcode18" onclick="bbstyle(18, $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" title="Tags BB Code pour définir du texte de très grande taille. Utilisation : [size=5]texte de très grande taille.[/size]"></td>';
		$retour .='						<td>&nbsp;</td>';
		$retour .='						<td><img alt="close tags" src="/public/images/uddeim/format_closeall.gif" style="cursor: pointer;" onclick="bbstyle(-1, $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" title="Fermer tous les tags BBcode."></td>';
		$retour .='					</tr>';
		$retour .='					</tbody>';
		$retour .='				</table>';
		$retour .='			</div>';
		$retour .='			<div id="uddeim-smileybox">';
		$retour .='				<table border="0" cellpadding="2" cellspacing="0" align="center">';
		$retour .='					<tbody>';
		$retour .='					<tr>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\':) \', $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" src="/public/images/uddeim/emoticon_smile.gif" alt=":)" title=":)"></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\':( \', $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" src="/public/images/uddeim/emoticon_sad.gif" alt=":(" title=":("></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\':P \', $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" src="/public/images/uddeim/emoticon_tongue.gif" alt=":P" title=":P"></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\':x \', $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" src="/public/images/uddeim/emoticon_crossed.gif" alt=":x" title=":x"></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\':angry: \', $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" src="/public/images/uddeim/emoticon_angry.gif" alt=":angry:" title=":angry:"></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\':blush: \', $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" src="/public/images/uddeim/emoticon_blush.gif" alt=":blush:" title=":blush:"></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\'B) \', $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" src="/public/images/uddeim/emoticon_cool.gif" alt="B)" title="B)"></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\':* \', $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" src="/public/images/uddeim/emoticon_heart.gif" alt=":*" title=":*"></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\':kiss: \', $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" src="/public/images/uddeim/emoticon_kiss.gif" alt=":kiss:" title=":kiss:"></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\':laugh: \', $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" src="/public/images/uddeim/emoticon_laughing.gif" alt=":laugh:" title=":laugh:"></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\':ohmy: \', $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" src="/public/images/uddeim/emoticon_shocked.gif" alt=":ohmy:" title=":ohmy:"></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\';) \', $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" src="/public/images/uddeim/emoticon_wink.gif" alt=";)" title=";)"></td>';
		$retour .='						<td><img style="cursor: pointer;" onclick="emo(\':? \', $(\''.$champ.'\')); textCount($(\''.$champ.'\'),$(\'characterstyped\'),'.$nbMax.'); return false;" src="/public/images/uddeim/emoticon_wondering.gif" alt=":?" title=":?"></td>';
		$retour .='					</tr>';
		$retour .='					</tbody>';
		$retour .='				</table>';
		$retour .='			</div>';
		
		return $retour;
	}

}


