<?php

/* ************************************************************************** *
 * Copyright (c) 2003, Denis Sacchet                                          *
 * All rights reserved.                                                       *
 *                                                                            *
 * Redistribution and use in source and binary forms, with or without         *
 * modification, are permitted provided that the following conditions are     *
 * met:                                                                       *
 *                                                                            *
 *  * Redistributions of source code must retain the above copyright notice,  *
 *    this list of conditions and the following disclaimer.                   *
 *  * Redistributions in binary form must reproduce the above copyright       *
 *    notice, this list of conditions and the following disclaimer in the     *
 *    documentation and/or other materials provided with the distribution.    *
 *  * Neither the name of 'Denis Sacchet' nor the names of its contributors   *
 *    may be used to endorse or promote products derived from this software   *
 *    without specific prior written permission.                              *
 *                                                                            *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS    *
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,  *
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR     *
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR           *
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,      *
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,        *
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR         *
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF     *
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING       *
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS         *
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.               *
 * ************************************************************************** */

/* ************************************************************************** *
 * This routine removes all attributes from a given tag except the attributes *
 * specified in the array $attr. (from PHP Manual and joris878@hotmail.com).  *
 * ************************************************************************** */

	function filter($haystack,$firstneedle,$secondneedle) {
		$firstpositionx = strpos(strtoupper($haystack),
		                         strtoupper($firstneedle));
		$secondpositionx = strpos(strtoupper($haystack),
		                          strtoupper($secondneedle),
		                          $firstpositionx);
		$haystack = substr($haystack,0,$secondpositionx);
		$resultx = substr($haystack,$firstpositionx+strlen($firstneedle));
		return $resultx;
	}

	function stripeentag($msg,$tag,$attr) {
		$lengthfirst = 0;
		//$msg = stripslashes(stripslashes($msg));
		while (strstr(substr($msg,$lengthfirst),"<$tag ")!="") {
			$imgstart = $lengthfirst+strpos(substr($msg,$lengthfirst),"<$tag ");
			$partafterwith = substr($msg,$imgstart);
			$img = substr($partafterwith,0,strpos($partafterwith,">")+1);
			$d1 = substr($img,1,strlen($img)-2);
			if(strstr($d1,"<")!="" && strstr($d1,">")!="") break;

			for($i=1;$i<=count($attr);$i++) {
				$val = "";
				//if(strstr($img,$attr[$i]."=\"")!="") {
				if(strstr($img,$attr[$i]."=\\\"")!="") {
					$val = filter($img."~~",$attr[$i]."=\\\"","~~");
					$val = filter("~~".$val,"~~","\\\"");
				} elseif(strstr($img,$attr[$i]."=")!="") {
					$val = filter($img."~~",$attr[$i]."=","~~");
					$val = filter("~~".$val,"~~"," ");
				}
				if(strlen($val)>0) $out .= " ".$attr[$i]."=\"".$val."\"";
			}
			$msg = substr($msg,0,$imgstart)."<".$tag.$out.">".substr($partafterwith,strpos($partafterwith,">")+1);
			$lengthfirst = $imgstart+3;
		}
		return $msg;
	}


/* ************************************************************************** *
 * Fonction indiquant si un utilisateur appartenant � certains groupes sont   *
 * autoris�s � voir le fichier prot�g� par ces fichiers                       *
 * ************************************************************************** */

	function have_the_right($allowed_users_file,$allowed_groups_file,$user,$groups) {

	/* Si le nom d'utilisateur est vide, alors c'est anonymous */
		if($user=="") {
			$user="anonymous";
		}

	/* Par d�faut, on n'a pas le droit */

		$have_the_right = false;

	/* On v�rifie d'abord par rapport � l'utilisateur */

		$fuser = @fopen($allowed_users_file,"r");
		if($fuser != false) {

		/* Si on r�ussit � ouvrir le fichier, on le parcours ... */

			while(!feof($fuser)) {

			/* On r�cup�re une ligne, on trim la fin, on compare, et *
			 * si c'est bon on arr�te la boucle */

				$buffer=fgets($fuser,4096);
				if($buffer == "") continue;
				$buffer=substr($buffer,0,strlen($buffer)-1);
				if($buffer == $user ||
				   ($buffer == "everybody" && $user != "anonymous") ) {
					$have_the_right = true;
					break;
				}
			}
			fclose($fuser);
		}

	/* Si on n'a toujours pas le droit, on v�rifie par rapport au *
	 * groupe */

		if(!$have_the_right && $user != "anonymous") {

			$nb_groups=count($groups);
			$fgroup = @fopen($allowed_groups_file,"r");
			if($fgroup != false) {

			/* Si on r�ussit � ouvrir le fichier, on le parcours ... */

				while(!feof($fgroup)) {

			/* On r�cup�re une ligne, on trim la fin, on compare � *
			 * tous les groupes auxquels l'utilisateur appartient, *
			 * et si c'est bon on arr�te la boucle */

					$buffer=fgets($fgroup,4096);
					if($buffer == "") continue;
					$buffer=substr($buffer,0,strlen($buffer)-1);
					for($k=0;$k<$nb_groups;$k++) {
						if($buffer == $groups[$k] || $buffer == "everybody") {
							$have_the_right = true;
							break;
						}
					}
				}
				fclose($fgroup);
			}
		}
		return $have_the_right;
	}

/* ************************************************************************** *
 * Fonction cr�ant la liste des photos accessibles par un utilisateur donn�es *
 * dans un sous r�pertoire donn� de l'album photo                             *
 * ************************************************************************** */

	function listing($album_dir,$album,$user,$groups) {

	/* Ouverture du r�pertoire */

		$pdir = @dir($album_dir . $album);
		if($pdir == false) {
			return;
		}

	/* Initialisation de quelques variables */

		$i=0;$j=0;

	/* Parcours tous les fichiers du r�pertoire */

		while ($file = $pdir->read()) {

		/* Si c'est un fichier on v�rifie les droits */

			if(is_file($album_dir.$album.$file)) {

			/* On regarde si on a le droit de voir ce fichier */

				$have_the_right = have_the_right($album_dir.$album.'.users/'.$file,$album_dir.$album.'.groups/'.$file,$user,$groups);

			/* Finalement si on a le droit (soit par l'utilisateur soit par *
			 * le groupe, on ajoute le fichier � la liste des photos  et on *
			 * on essaye de r�cup�rer les commentaires associ�es aux photos */

				if ($have_the_right) {
					$photos[$i] = $file;
					$photos_comments[$i] = file_comments($album_dir.$album.$file);
					$i++;
				}
			}

		/* Si c'est un r�pertoire, on l'ajoute � la liste des r�pertoires *
		 * sauf si c'est un r�pertoire cach� */

			if(is_dir($album_dir.$album.$file)) {
				if($file[0] != "." && have_the_right($album_dir.$album.$file."/.this_users",$album_dir.$album.$file."/.this_groups",$user,$groups)) {
					$subdirs[$j] = $file;
					$subdirs_comments[$j] = dir_comments($album_dir.$album.$file."/");
					$j++;
				}
			}

		/* Sinon on ne fait rien du tout et on passe � l'entr�e suivante :) */

		}

	/* On ferme le r�pertoire et on retourne les trois tableaux, si un des *
	 * tableaux n'existe pas, on en cr�e un vide � la place                */

		$pdir->close();
		if(!isset($photos)) {
			$photos=array();
		}
		if(!isset($photos_comments)) {
			$photos_comments=array();
		}
		if(!isset($subdirs)) {
			$subdirs=array();
		}
		if(!isset($subdirs_comments)) {
			$subdirs_comments=array();
		}
		return array($photos,$photos_comments,$subdirs,$subdirs_comments);
	}

/* ************************************************************************** *
 * Retourne une cha�ne de caract�re contenant le commentaire relatif � un     *
 * fichier donn�.                                                             *
 * Si le fichier de commentaire n'existe pas ou n'est pas accessible en       *
 * lecture, on retourne une chaine vide.                                      *
 * Sinon on retourne le contenu du fichier en supprimant les tags et en       *
 * metttant tout le contenu du fichier sur une seule et m�me ligne.           *
 * ************************************************************************** */

	function file_comments($file) {
		
		$dir=substr($file,"0",strrpos($file,"/")+1);
		$filename=substr($file,strrpos($file,"/")+1);
		$fcomments = @fopen($dir.".comments/".$filename,"r");
		if($fcomments === false) {
			return;
		}
		while(!feof($fcomments)) {
			$buffer = fgets($fcomments,4096);
			$buffer = substr($buffer,0,strlen($buffer)-1);
			if(!isset($comments)) {
				$comments = $buffer;
			} else {
				$comments .= " " . $buffer;
			}
		}
		fclose($fcomments);
		$comments = strip_tags($comments,"<a><br>");
		return htmlentities(utf8_decode($comments));
	}

/* ************************************************************************** *
 * Retourne une cha�ne de caract�re contenant le commentaire relatif � un     *
 * r�pertoire donnn�.                                                         *
 * Si le fichier de commentaire n'existe pas ou n'est pas accessible en       *
 * lecture, on retourne une chaine vide.                                      *
 * Sinon on retourne le contenu du fichier en supprimant les tags et en       *
 * metttant tout le contenu du fichier sur une seule et m�me ligne.           *
 * ************************************************************************** */

	function dir_comments($dir) {
		$fcomments = @fopen($dir . ".this_comments","r");
		if($fcomments == FALSE) {
			return;
		}
		while(!feof($fcomments)) {
			$buffer = fgets($fcomments,1024);
			$buffer = substr($buffer,0,strlen($buffer)-1);
			$buffer = strip_tags($buffer);
			if(!isset($comments)) {
				$comments = $buffer;
			} else {
				$comments .= " " . $buffer;
			}
		}
		return htmlentities(utf8_decode($comments));
	}

/* ************************************************************************** *
 * Ajoute ou supprime  un '/' au d�but et/ou � la fin d'une chaine de         *
 * caract�res                                                                 *
 *  0 => Pas de '/' ni au d�but ni � la fin                                   *
 *  1 => Un '/' au d�but                                                      *
 *  2 => Un '/' � la fin                                                      *
 *  3 => Un '/' au d�but et � la fin                                          *
 * ************************************************************************** */

	function verif_path($path,$type) {
		if(($path[0] != '/') && ($type & 1)) {
			$path = '/' . $path;
		}
		if(($path[0] == '/') && !($type & 1)) {
			$path = substr($path,1,strlen($path)-1);
		}
		$length = strlen($path);
		if(($path[$length-1] != '/') && ($type & 2)) {
			$path .= '/';
		}
		$length = strlen($path);
		if(($path[$length-1] == '/') && !($type & 2)) {
			$path = substr($path,0,$length-1);
		}
		return $path;
	}

/* ************************************************************************** *
 * Affiche une simple page avec le message $msg et un lien vers la page       *
 * point�e par $link                                                          *
 * ************************************************************************** */

	function error($msg,$link) {

		echo '<html>'."\n";
		echo ' <head>'."\n";
		echo '  <title>Erreur</title>'."\n";
		echo ' </head>'."\n";
		echo ' <body>'."\n";
		echo '  <div>Erreur !</div>'."\n";
		echo '  <div>'."\n";
		echo '   <p>L\'erreur suivante c\'est produite :</p>'."\n";
		echo '   <p>'.$msg.'</p>'."\n";
		echo '   <p>Vous pouvez vous rendre <a href="'.$link.'">ici</a></div>'."\n";
		echo '  </div>'."\n";
		echo ' </body>'."\n";
		echo '</html>'."\n";
		return;
	}

/* ************************************************************************** *
 * Affiche une simple page avec le message $msg et un lien vers la page       *
 * point�e par $link                                                          *
 * ************************************************************************** */

	function error_img($msg,$x,$y,$font) {

		header("Content-type: image/png");
		$im     = imagecreate($x,$y);
		$blakc  = imagecolorallocate($im,0,0,0);
		$white  = imagecolorallocate($im,255,255,255);
		$red    = imagecolorallocate($im,255,0,0);
		imagefilledrectangle($im,0,0,$x-1,$y-1,$black);
		imagefilledrectangle($im,1,1,$x-2,$y-2,$white);
		imagettftext($im,16,0,5,21,$red,$font['bold'],"Erreur !");
		imagettftext($im,12,0,5,44,$black,$font['normal'],$msg);

		imagepng($im);
		imagedestroy($im);
		return;
	}

/* ************************************************************************** *
 * Retourne les groupes auquel appartient l'utilisateur $user par rapport au  *
 * fichier de groupes $groups_file                                            *
 * ************************************************************************** */

	function get_groups($user,$groups_file) {

	/* Initialise le compteur de groupe */

		$i = 0;

	/* Essaye d'ouvrir le fichier de groupe et si une erreur, retourne un *
	 * tableau vide                                                       */

		$fgroups_file = @fopen($groups_file,"r");
		if($fgroups_file == false) {
			return array();
		}

	/* On parcours le fichier */

		while(!feof($fgroups_file)) {

		/* On r�cup�re une ligne, on enl�ve les espaces au d�but et � la fin, * 
		 * si apr�s la ligne est vide ou commence par un '#' (commentaire),   *
		 * on passe � la ligne suivante. */

			$buffer = fgets($fgroups_file,1024);
			$buffer = trim(substr($buffer,0,strlen($buffer)-1));
			if((strlen($buffer) == 0) || preg_match("/^#.*$/",$buffer)) {
				continue;
			}

		/* La ligne contient � priori une ligne de groupe, si $group est *
		 * identique � $buffer, y'a une erreur de syntaxe, on passe � la *
		 * ligne suivante                                                */

			list($group,$users) = explode(":",$buffer,2);
			if($group == $buffer) {
				continue;
			}

		/* $users est une chaine contenant les utilisateurs faisant partis *
		 * du groupe $group s�par�s par le caract�re ",". On explose cette *
		 * cha�ne pour r�cup�rer tous les utilisateurs dans un tableau     */

			$users = explode(",",$users);

		/* On regarde sur l'utilisateur est pr�sent, et si c'est le cas, on *
		 * l'ajoute � la liste des groupes r�sultats et on arr�te la boucle */

			$nb_users = count($users);
			for($j=0;$j<$nb_users;$j++) {
				if($users[$j] == $user) {
					$result[$i] = $group;
					$i++;
					break;
				}
			}
		}

	/* On retourne le tableau de r�sultat */

		return $result;
	}

/* ************************************************************************** *
 * Retourne l'index de la photo nomm� par la variables $photos dans le        *
 * tableau $photos                                                            *
 * ************************************************************************** */

	function get_index($photo,$photos) {
		$nb_photos = count($photos);
		for($i=0;$i<$nb_photos;$i++) {
			if($photos[$i] == $photo) {
				return $i;
			}
		}
	}
	
/* ************************************************************************** *
 * Retourne un flottant contenant le timestamp actuel avec les microsecondes  *
 * ************************************************************************** */

	function getmicrotime() {
		list($usec, $sec) = explode(" ",microtime());
		return ((float)$usec + (float)$sec);
	}

?>
