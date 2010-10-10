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

// *******************************************************************
// Title          udde Instant Messages (uddeIM)
// Description    Instant Messages System for Mambo 4.5 / Joomla 1.0 / Joomla 1.5
// Author          2007-2008 Stephan Slabihoud,  2006 Benjamin Zweifel
//                Adapte pour Braldahim par Yvonnick ESNAULT
// License        This is free software and you may redistribute it under the GPL.
//                uddeIM comes with absolutely no warranty.
//                Use at your own risk. For details, see the license at
//                http://www.gnu.org/licenses/gpl.txt
//                Other licenses can be found in LICENSES folder.
//                This code uses portions of the bbcode script from
//                phpBB (C) 2001 The phpBB Group
// *******************************************************************
// Modified by / for Braldahim
// *******************************************************************

class Bral_Util_BBParser {

	public static function bbcodeReplace($string) {

		// replace font formatting [b] [i] [u] [color= [size=
		// bold
		$string = preg_replace("/(\[b\])(.*?)(\[\/b\])/si","<span style=\"font-weight: bold\">\\2</span>",$string);
		
		// underline
		$string = preg_replace("/(\[u\])(.*?)(\[\/u\])/si","<span style=\"text-decoration: underline\">\\2</span>",$string);
		
		// italic
		$string = preg_replace("/(\[i\])(.*?)(\[\/i\])/si","<span style=\"font-style: italic\">\\2</span>",$string);
		
		// size Max size is 7
		$string = preg_replace("/\[size=([1-5])\](.+?)\[\/size\]/si","<font size=\\1\">\\2</font>",$string);
		
		// color
		$string = preg_replace("%\[color=(.*?)\](.*?)\[/color\]%si","<span style=\"color: \\1\">\\2</span>",$string);
		
		$string = preg_replace("/(\[center\])(.*?)(\[\/center\])/si","<center>\\2</center>",$string);
		$string = preg_replace("/(\[justify\])(.*?)(\[\/justify\])/si","<p style=\"text-align: justify\">\\2</p>",$string);
		$string = preg_replace("/(\[left\])(.*?)(\[\/left\])/si","<p style=\"text-align: left\">\\2</p>",$string);
		$string = preg_replace("/(\[right\])(.*?)(\[\/right\])/si","<p style=\"text-align: right\">\\2</p>",$string);
				
		while (substr_count($string,"<span") > substr_count($string,"</span>")) {
			$string.="</span>";
		}
		
		while (substr_count($string,"<span") < substr_count($string,"</span>")) {
			$string="<span>".$string;
		}
	
		// http, https, ftp, mailto
		$string = str_replace("[url=index.php", "#*#LINKINDEX=#*#", $string);
		$string = str_replace("[url=http://", "#*#LINKHTTP=#*#", $string);
		$string = str_replace("[url=ftp://", "#*#LINKFTP=#*#", $string);
		$string = str_replace("[url=https://", "#*#LINKHTTPS=#*#", $string);
		$string = str_replace("[url=mailto:", "#*#LINKMAILTO=#*#", $string);	

		$string = str_replace("[url]index.php", "#*#LINKINDEX]#*#", $string);		
		$string = str_replace("[url]http://", "#*#LINKHTTP]#*#", $string);
		$string = str_replace("[url]ftp://", "#*#LINKFTP]#*#", $string);
		$string = str_replace("[url]https://", "#*#LINKHTTPS]#*#", $string);
		$string = str_replace("[url]mailto:", "#*#LINKMAILTO]#*#", $string);				
		$string = str_replace("[url]", "[url]http://", $string);
		$string = str_replace("[url=", "[url=http://", $string);

		$string = str_replace("#*#LINKHTTP=#*#", "[url=http://", $string);
		$string = str_replace("#*#LINKFTP=#*#", "[url=ftp://", $string);
		$string = str_replace("#*#LINKHTTPS=#*#", "[url=https://", $string);
		$string = str_replace("#*#LINKMAILTO=#*#", "[url=mailto:", $string);			
		$string = str_replace("#*#LINKINDEX=#*#", "[url=index.php", $string);			

		$string = str_replace("#*#LINKHTTP]#*#", "[url]http://", $string);
		$string = str_replace("#*#LINKFTP]#*#", "[url]ftp://", $string);
		$string = str_replace("#*#LINKHTTPS]#*#", "[url]https://", $string);
		$string = str_replace("#*#LINKMAILTO]#*#", "[url]mailto:", $string);
		$string = str_replace("#*#LINKINDEX]#*#", "[url]index.php", $string);		

		$string = preg_replace("/\[img size=([0-9][0-9][0-9])\](http\:\/\/.*?)\[\/img\]/si","[#*#img size=$1]$2[/#*#img]",$string);
		$string = preg_replace("/\[img size=([0-9][0-9])\](http\:\/\/.*?)\[\/img\]/si","[#*#img size=$1]$2[/#*#img]",$string);
		$string = preg_replace("/\[img\](http\:\/\/.*?)\[\/img\]/si","[#*#img]$1[/#*#img]",$string);

		$string = preg_replace("/\[img size=([0-9][0-9][0-9])\](.*?)\[\/img\]/si","[img size=$1]http://$2[/img]",$string);
		$string = preg_replace("/\[img size=([0-9][0-9])\](.*?)\[\/img\]/si","[img size=$1]http://$2[/img]",$string);
		$string = preg_replace("/\[img\](.*?)\[\/img\]/si","[img]http://$1[/img]",$string);

		$string = str_replace("[#*#img", "[img", $string);
		$string = str_replace("[/#*#img", "[/img", $string);

		// ul li replacements
        $string = preg_replace("/(\[ul\])(.*?)(\[\/ul\])/si","<ul>\\2</ul>",$string);
        $string = preg_replace("/(\[ol\])(.*?)(\[\/ol\])/si","<ol type=1>\\2</ol>",$string);
        $string = preg_replace("/(\[li\])(.*?)(\[\/li\])/si","<li>\\2</li>",$string);

		// url replacement
        //make regular HTML URL links targets _blank, bbCode URL translation
        $string = preg_replace('/\[url\](.*?)javascript(.*?)\[\/url\]/si','<span style=\'text-decoration: line-through\'>javascript link</span>',$string);
        $string = preg_replace('/\[url=(.*?)javascript(.*?)\](.*?)\[\/url\]/si','<span style=\'text-decoration: line-through\'>javascript link</span>',$string);

		// now the rest of the links to blank
        $string = preg_replace("/\[url\](.*?)\[\/url\]/si","<a href=\"\\1\" target=\"_blank\">\\1</a>",$string);
        $string = preg_replace("/\[url=(.*?)\](.*?)\[\/url\]/si","<a href=\"\\1\" target=\"_blank\">\\2</a>",$string);	

		// img replacement
        $string = preg_replace("/\[img size=([0-9][0-9][0-9])\](.*?)\[\/img\]/si","<img src=\"$2\" border=\"0\" width=\"$1\" alt=\"\" />",$string);
		$string = preg_replace("/\[img size=([0-9][0-9])\](.*?)\[\/img\]/si","<img src=\"$2\" border=\"0\" width=\"$1\" alt=\"\" />",$string);
		$string = preg_replace("/\[img\](.*?)\[\/img\]/si","<img src=\"$1\" border=\"0\" alt=\"\" />",$string);
		$string = preg_replace("/<img(.*?)javascript(.*?)>/si",'<span style=\'text-decoration: line-through\'>javascript link</span>',$string);	
		
       	$string = nl2br($string);
       	
       	// Rajout Yvo
		$string = self::bbcodeStripPlus($string);
		
        return stripslashes(self::smileReplace($string));
	}
	
	public static function smileReplace($string) {	

		$message_emoticons=array(
	      ":))"        => '<img src="/public/images/uddeim/emoticon_laughing.gif"  alt="" border="0" align="middle" />',		
		  ":D"         => '<img src="/public/images/uddeim/emoticon_laughing.gif"  alt="" border="0" align="middle" />',		
	      ":*"         => '<img src="/public/images/uddeim/emoticon_heart.gif"     alt="" border="0" align="middle" />',
	      ":?"         => '<img src="/public/images/uddeim/emoticon_wondering.gif" alt="" border="0" align="middle" />',
	      ":x"         => '<img src="/public/images/uddeim/emoticon_crossed.gif"   alt="" border="0" align="middle" />',
	      "B)"         => '<img src="/public/images/uddeim/emoticon_cool.gif"      alt="" border="0" align="middle" />',
	      ":("         => '<img src="/public/images/uddeim/emoticon_sad.gif"       alt="" border="0" align="middle" />',
	      ":)"         => '<img src="/public/images/uddeim/emoticon_smile.gif"     alt="" border="0" align="middle" />',
	      ":-("        => '<img src="/public/images/uddeim/emoticon_sad.gif"       alt="" border="0" align="middle" />',
	      ":-)"        => '<img src="/public/images/uddeim/emoticon_smile.gif"     alt="" border="0" align="middle" />',
	      ":laugh:"    => '<img src="/public/images/uddeim/emoticon_laughing.gif"  alt="" border="0" align="middle" />',
	      ":grin:"     => '<img src="/public/images/uddeim/emoticon_laughing.gif"  alt="" border="0" align="middle" />',
	      ";)"         => '<img src="/public/images/uddeim/emoticon_wink.gif"      alt="" border="0" align="middle" />',
	      ";-)"        => '<img src="/public/images/uddeim/emoticon_wink.gif"      alt="" border="0" align="middle" />',
	      ":P"         => '<img src="/public/images/uddeim/emoticon_tongue.gif"    alt="" border="0" align="middle" />',
	      ":mad:"      => '<img src="/public/images/uddeim/emoticon_angry.gif"     alt="" border="0" align="middle" />',
	      ":angry:"    => '<img src="/public/images/uddeim/emoticon_angry.gif"     alt="" border="0" align="middle" />',
	      ":ohmy:"     => '<img src="/public/images/uddeim/emoticon_shocked.gif"   alt="" border="0" align="middle" />',
		  ":o"         => '<img src="/public/images/uddeim/emoticon_shocked.gif"   alt="" border="0" align="middle" />',
	      ":shock:"    => '<img src="/public/images/uddeim/emoticon_shocked.gif"   alt="" border="0" align="middle" />',
	      ":blush:"    => '<img src="/public/images/uddeim/emoticon_blush.gif"     alt="" border="0" align="middle" />',
	      ":kiss:"     => '<img src="/public/images/uddeim/emoticon_kiss.gif"      alt="" border="0" align="middle" />',
	    );
	
	/*	if ($config->animatedex) { 
			$iconfolder="animated-extended";
			$smileys = $pathtouser."/templates/".$config->templatedir."/".$iconfolder."/";
			if (is_dir($smileys)) {
				$folder=opendir ($smileys); 
				while ($file = readdir ($folder)) {
					if($file != "." && $file != ".." && (substr($file, strrpos($file, '.'))=='.gif')) {
						$ext = strrchr($file, '.');
						if($ext !== false) {
							$noextname = substr($file, 0, -strlen($ext));
						} else {
							$noextname = $file;
						}
						$name = ":".$noextname.":";
						$message_emoticons[$name] = '<img src="/public/images/uddeim/'.$noextname.'.gif" alt="" border="0" align="middle" />';
					}
				}
				closedir($folder);
			}
		}
*/
	      
		reset($message_emoticons);
		while (list($emo_txt,$emo_src)=each($message_emoticons)) {
			$string = str_replace($emo_txt,$emo_src,$string);
		}
		return $string;
	}
	
	public static function bbcodeStrip($string) {
	
		// bold
	    $string = preg_replace("/(\[b\])(.*?)(\[\/b\])/si","\\2",$string);
	
		// underline
	    $string = preg_replace("/(\[u\])(.*?)(\[\/u\])/si","\\2",$string);
	
		// italic
		$string = preg_replace("/(\[i\])(.*?)(\[\/i\])/si","\\2",$string);
	
		// size Max size is 7
		$string = preg_replace("/\[size=([1-7])\](.+?)\[\/size\]/si","\\2",$string);
	
		// color
		$string = preg_replace("%\[color=(.*?)\](.*?)\[/color\]%si","\\2",$string);
		
		// ul li replacements
		
		// lists
		$string = preg_replace("/(\[ul\])(.*?)(\[\/ul\])/si","\\2",$string);
		$string = preg_replace("/(\[ol\])(.*?)(\[\/ol\])/si","\\2",$string);
		$string = preg_replace("/(\[li\])(.*?)(\[\/li\])/si","\\2\\n",$string);
		
		// url replacement
		$string = preg_replace('/\[url\](.*?)javascript(.*?)\[\/url\]/si','',$string);
		$string = preg_replace('/\[url=(.*?)javascript(.*?)\](.*?)\[\/url\]/si','',$string);
		$string = preg_replace("/\[url\](.*?)\[\/url\]/si","\\1",$string);
		$string = preg_replace("/\[url=(.*?)\](.*?)\[\/url\]/si","\\2 (\\1)",$string);	
		
		// only front tag present
		$string = preg_replace("/\[url=(.*?)\]/si","",$string);	
		
		// img replacement
		// img
		$string = preg_replace("/\[img size=([0-9][0-9][0-9])\](.*?)\[\/img\]/si","",$string);
		$string = preg_replace("/\[img size=([0-9][0-9])\](.*?)\[\/img\]/si","",$string);
		$string = preg_replace("/\[img\](.*?)\[\/img\]/si","",$string);
		$string = preg_replace("/<img(.*?)javascript(.*?)>/si",'',$string);	
	
		// only front tag present
		$string = preg_replace("/\[img size=([0-9][0-9][0-9])\]]/si","",$string);
	
		// cut remaining single tags
		$string = str_replace("[i]", "", $string);
		$string = str_replace("[/i]", "", $string);
		$string = str_replace("[b]", "", $string);
		$string = str_replace("[/b]", "", $string);
		$string = str_replace("[u]", "", $string);
		$string = str_replace("[/u]", "", $string);
		$string = str_replace("[ul]", "", $string);
		$string = str_replace("[/ul]", "", $string);
		$string = str_replace("[ol]", "", $string);
		$string = str_replace("[/ol]", "", $string);
		$string = str_replace("[li]", "", $string);
		$string = str_replace("[/li]", "", $string);
	
	    $string = preg_replace('/\[url=(.*?)javascript(.*?)\]/si','',$string);	
	    $string = preg_replace("/\[img size=([0-9][0-9][0-9])\]/si","",$string);
	    $string = preg_replace("/\[img size=([0-9][0-9])\]/si","",$string);
	    $string = preg_replace("/\[size=([1-7])\]/si","",$string);
	    $string = preg_replace("%\[color=(.*?)\]%si","",$string);
		$string = str_replace("[img]", "", $string);
		$string = str_replace("[/img]", "", $string);
		$string = str_replace("[url]", "", $string);
		$string = str_replace("[/url]", "", $string);		
		$string = str_replace("[/color]", "", $string);
		$string = str_replace("[/size]", "", $string);	
		
		// Rajout Yvo
		$string = self::bbcodeStripPlus($string);		
	
		return stripslashes($string);
	}
	
	public static function bbcodeStripPlus($string) {
		$string = str_replace("[plus]", "+", $string);		
		return $string;
	}
}


