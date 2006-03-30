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
 * Fonction indiquant si un utilisateur appartenant à certains groupes sont   *
 * autorisés à voir le fichier protégé par ces fichiers                       *
 * ************************************************************************** */

	function have_the_right($allowed_users_file,$allowed_groups_file,$user,$groups) {

	/* Si le nom d'utilisateur est vide, alors c'est anonymous */
		if($user=="") {
			$user="anonymous";
		}

	/* Par défaut, on n'a pas le droit */

		$have_the_right = false;

	/* On vérifie d'abord par rapport à l'utilisateur */

		$fuser = @fopen($allowed_users_file,"r");
		if($fuser != false) {

		/* Si on réussit à ouvrir le fichier, on le parcours ... */

			while(!feof($fuser)) {

			/* On récupère une ligne, on trim la fin, on compare, et
			 * si c'est bon on arrête la boucle */

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

	/* Si on n'a toujours pas le droit, on vérifie par rapport au *
	 * groupe */

		if(!$have_the_right && $user != "anonymous") {

			$nb_groups=count($groups);
			$fgroup = @fopen($allowed_groups_file,"r");
			if($fgroup != false) {

			/* Si on réussit à ouvrir le fichier, on le parcours ... */

				while(!feof($fgroup)) {

			/* On récupère une ligne, on trim la fin, on compare à *
			 * tous les groupes auxquels l'utilisateur appartient, *
			 * et si c'est bon on arrête la boucle */

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
 * Fonction créant la liste des photos accessibles par un utilisateur données *
 * dans un sous répertoire donné de l'album photo                             *
 * ************************************************************************** */

	function listing($album_dir,$album,$user,$groups) {

	/* Ouverture du répertoire */

		$pdir = @dir($album_dir . $album);
		if($pdir == false) {
			return;
		}

	/* Initialisation de quelques variables */

		$i=0;$j=0;

	/* Parcours tous les fichiers du répertoire */

		while ($file = $pdir->read()) {

		/* Si c'est un fichier on vérifie les droits */

			if(is_file($album_dir.$album.$file)) {

			/* On regarde si on a le droit de voir ce fichier */

				$have_the_right = have_the_right($album_dir.$album.'.users/'.$file,$album_dir.$album.'.groups/'.$file,$user,$groups);

			/* Finalement si on a le droit (soit par l'utilisateur soit par *
			 * le groupe, on ajoute le fichier à la liste des photos  et on *
			 * on essaye de récupérer les commentaires associées aux photos */

				if ($have_the_right) {
					$photos[$i] = $file;
					$photos_comments[$i] = file_comments($album_dir.$album.$file);
					$i++;
				}
			}

		/* Si c'est un répertoire, on l'ajoute à la liste des répertoires *
		 * sauf si c'est un répertoire caché */

			if(is_dir($album_dir.$album.$file)) {
				if($file[0] != "." && have_the_right($album_dir.$album.$file."/.this_users",$album_dir.$album.$file."/.this_groups",$user,$groups)) {
					$subdirs[$j] = $file;
					$subdirs_comments[$j] = dir_comments($album_dir.$album.$file."/");
					$j++;
				}
			}

		/* Sinon on ne fait rien du tout et on passe à l'entrée suivante :) */

		}

	/* On ferme le répertoire et on retourne les trois tableaux, si un des *
	 * tableaux n'existe pas, on en crée un vide à la place                */

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
 * Retourne une chaîne de caractère contenant le commentaire relatif à un     *
 * fichier donné.                                                             *
 * Si le fichier de commentaire n'existe pas ou n'est pas accessible en       *
 * lecture, on retourne une chaine vide.                                      *
 * Sinon on retourne le contenu du fichier en supprimant les tags et en       *
 * metttant tout le contenu du fichier sur une seule et même ligne.           *
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
 * Retourne une chaîne de caractère contenant le commentaire relatif à un     *
 * répertoire donnné.                                                         *
 * Si le fichier de commentaire n'existe pas ou n'est pas accessible en       *
 * lecture, on retourne une chaine vide.                                      *
 * Sinon on retourne le contenu du fichier en supprimant les tags et en       *
 * metttant tout le contenu du fichier sur une seule et même ligne.           *
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
 * Ajoute ou supprime  un '/' au début et/ou à la fin d'une chaine de         *
 * caractères                                                                 *
 *  0 => Pas de '/' ni au début ni à la fin                                   *
 *  1 => Un '/' au début                                                      *
 *  2 => Un '/' à la fin                                                      *
 *  3 => Un '/' au début et à la fin                                          *
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
 * pointée par $link                                                          *
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
 * pointée par $link                                                          *
 * ************************************************************************** */

	function error_img($msg,$x,$y,$font) {

		header("Content-type: image/png");
		$im     = imagecreate($x,$y);
		$black  = imagecolorallocate($im,0,0,0);
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

		/* On récupère une ligne, on enlève les espaces au début et à la fin, * 
		 * si après la ligne est vide ou commence par un '#' (commentaire),   *
		 * on passe à la ligne suivante. */

			$buffer = fgets($fgroups_file,1024);
			$buffer = trim(substr($buffer,0,strlen($buffer)-1));
			if((strlen($buffer) == 0) || preg_match("/^#.*$/",$buffer)) {
				continue;
			}

		/* La ligne contient à priori une ligne de groupe, si $group est *
		 * identique à $buffer, y'a une erreur de syntaxe, on passe à la *
		 * ligne suivante                                                */

			list($group,$users) = explode(":",$buffer,2);
			if($group == $buffer) {
				continue;
			}

		/* $users est une chaine contenant les utilisateurs faisant partis *
		 * du groupe $group séparés par le caractère ",". On explose cette *
		 * chaîne pour récupérer tous les utilisateurs dans un tableau     */

			$users = explode(",",$users);

		/* On regarde sur l'utilisateur est présent, et si c'est le cas, on *
		 * l'ajoute à la liste des groupes résultats et on arrête la boucle */

			$nb_users = count($users);
			for($j=0;$j<$nb_users;$j++) {
				if($users[$j] == $user) {
					$result[$i] = $group;
					$i++;
					break;
				}
			}
		}

	/* On retourne le tableau de résultat */

		return $result;
	}

/* ************************************************************************** *
 * Retourne l'index de la photo nommé par la variables $photos dans le        *
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

/* ************************************************************************** *
 * Est ce que la photo contient une information de rotation                   *
 * ************************************************************************** */

	function get_rotation($file) {
		$exif_datas=exif_read_data($file);
		if($exif_datas===false) {
			return false;
		}
		switch($exif_datas['Orientation']) {
			case '1':
				return false;
			case '3':
				return 180;
			case '6':
				return 90;
			case '8':
				return 270;
			default:
				return false;
		}
	}

/* ************************************************************************** *
 * Traitement d'une photo pour l'affichage                                    *
 *                                                                            *
 * Cette fonction permet :                                                    *
 * - le redimensionnement et la rotation                                      *
 * - l'ajout d'un copyright sur le côté droit de la photo                     *
 * - l'ajout d'un commentaire en bas de l'image                               *
 * ************************************************************************** */

	function photo_resize($infile,$outfile,$newsize,$rotation,$copyright,$comments,$keepprofile) {
		global $cmd_convert;
		global $photo_font;

		// On vérifie les paramètres en entrée

		if(file_exists($infile) === FALSE) {
			return FALSE;
		}

		if(touch($outfile) === FALSE) {
			return FALSE;
		}

		// On récupère les dimensions de l'image initiale
		$initial_sizes=getimagesize($infile);

		if($newsize==$initial_sizes[0] ||
		   $newsize==$initial_sizes[1] ||
		   $newsize===FALSE) {
		// Pas de redimmensionnement nécessaire
			$newsize=max($initial_sizes[0],$initial_sizes[1]);
			$resize=FALSE;

		// Redimensionnement nécessaire
		} else {
			$resize=TRUE;
			$final_sizes[0]=$newsize;
			$final_sizes[1]=round($initial_sizes[1]*$newsize/$initial_sizes[0]);
			if($initial_sizes[0] < $initial_sizes[1]) {
				$temp=$final_sizes[1];
				$final_sizes[1]=$final_sizes[0];
				$final_sizes[0]=$temp;
			}
		}

		// On inverse les dimensions en fonctions de la rotation
		if($rotation == 90 || $rotation == 270) {
			$temp=$final_sizes[1];
			$final_sizes[1]=$final_sizes[0];
			$final_sizes[0]=$temp;
		}

		// On calcule la taille des polices pour que ce soit lisible
		$copyright_fontsize=round($final_sizes[0]*0.015);
		$comments_fontsize=round($final_sizes[1]*0.026);
		if($comments !== FALSE) {
			$copyright_offset=$comments_fontsize+20;
		} else {
			$copyright_offset=0;
		}

		// On écrit la commande
		$command=$cmd_convert." ";
		$command.=$infile." ";

		// On redimmensionne ???
		if($resize === TRUE) {
			$command.="-resize ".$final_sizes[0]."x".$final_sizes[1]." ";
		}

		// On tourne ???
		if($rotation !== FALSE) {
			$commant.="-rotate ".$rotation." ";
		}

		// On a un commentaire ou un copyright ???
		if($copyright!==FALSE || $comments!==FALSE) {
			$command.="-font ".$photo_font." ";
		}

		// On a un copyright ???
		if($copyright!==FALSE) {
			$command.="-pointsize ".$copyright_fontsize." ";
			$command.="-rotate 90 -gravity Southwest ";
			$command.="-fill white ";
			$command.="-stroke black ";
			$command.="-draw \"text ".$copyright_offset.",0 '".$copyright."'\" ";
			$command.="-rotate -90 ";
		}

		// On a des commentaires ???
		if($comments!==FALSE) {
			$command.="-pointsize ".$comments_fontsize." ";
			$command.="-gravity South ";
			$command.="-fill '#0008' ";
			$command.="-stroke none ";
			$command.="-draw \"rectangle 0,".($final_sizes[1]-$copyright_offset+10).",".$final_sizes[0].",".$final_sizes[1]."\"  ";
			$command.="-fill white ";
			$command.="-stroke black ";
			$command.="-draw \"text 0,0 '".$comments."'\" ";
		}

		// Si keepprofile est égal à false, on reset les informations exif
		if($keepprofile === FALSE) {
			$command.="-strip ";
		}

		// On ajoute le fichier de sortie
		$command.=$outfile;
		echo $command;
	}

?>
