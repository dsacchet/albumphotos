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

/* Récupération des paramètres de configuration */

	include_once("config.php");
	include_once("functions.php");

/* On vérifie que les paramètres sont mis */

	if(!isset($_GET['album']) || !isset($_GET['photo'])) {
		header("Location: " . $http_base);
	}

/* On récupère les paramètres de l'URL */

	$current_album=stripslashes($_GET['album']);
	$photo=stripslashes($_GET['photo']);

/* On met à jour l'url avec la taille par défaut si aucune taille n'est *
 * spécifiée                                                            */

	if(!isset($_GET['size'])) {
		$url=$PHP_SELF."?";
		while(list($key,$value)=each($_GET)) {
			$url .= $key."=".$value."&";
		}
		$url .= "size=". $default_size;
		header('Location: ' . $url);
	}

/* On vérifie les slashs au début et à la fin des paths */

	$album_dir=verif_path($album_dir,1);
	$current_album=verif_path($current_album,3);
	$photo=verif_path($photo,0);

/* On récupère l'utilisateur courant et les groupes d'utilisateurs auxquels *
 * il appartient                                                            */

	$user=$_SERVER['PHP_AUTH_USER'];
	$groups=get_groups($user,$groups_file);

/* On vérifie vite fait si la photo existe et qu'on a le droit de la voir */

	if(!file_exists($album_dir.$current_album.$photo)) {
		error_img("Cette photo n'existe pas",400,55,$font_arial);
		return;
	}

	if(!have_the_right($album_dir.$current_album.".users/".$photo,
	                   $album_dir.$current_album.".groups/".$photo,
	                   $user,$groups)) {
		error_img("Vous n'avez pas le droit d'accéder à cette ressource",400,55,$font_arial);
		return;
	}

/* On récupère l'information sur l'orientation */

	$exif_datas=exif_read_data($album_dir.$current_album.$photo);
	switch($exif_datas['Orientation']) {
		case '1':
			$rotate=false;
			break;
		case '3':
			$rotate=180;
			break;
		case '6':
			$rotate=90;
			break;
		case '8':
			$rotate=270;
			break;
		default:
			$rotate=false;
			$break;
	}

/* Si la taille vaut originale, on affiche directement la photo */

	if($_GET['size'] == 'originale' ) {
		if($rotate) {
			$command=$cmd_convert . " -rotate " . $rotate;
		} else {
			$command=$cmd_cat;
		}
		$command .= " \"" . $album_dir.$current_album.$photo . "\" -";
		header('Content-type: image/jpeg');
		header('Content-Disposition: inline; filename="'.$photo.'"');
		header('Content-Length: '.filesize($album_dir.$current_album.$photo));
		$filestat=stat($album_dir.$current_album.$photo);
		header('Last-Modified: '.date("r",$filestat[9]));
		passthru($command);
		return;
	}

/* On regarde si la taille spécifiée fait partie des tailles "standards" */

	$standard_size=false;
	$size=stripslashes($_GET['size']);
	$nb_sizes=count($standard_sizes);

 	for($i=0;$i<$nb_sizes;$i++) {
		if($size == $standard_sizes[$i]) {
			$standard_size=true;
			break;
		}
	}

/* Si c'est pas une taille standard, pas besoin de faire de cache, on *
 * affiche direct */

	if(!$standard_size) {
		$command=$cmd_convert;
		$command .= " -size " . $size.'x'.$size;
		if($rotate) {
			$command .= " -rotate " . $rotate;
		}
		$command .= " \"" . $album_dir.$current_album.$photo . "\"";
		$command .= " -resize " . $size.'x'.$size;
		$command .= " +profile \"0\"";
		$command .= " -";
		header("Content-type: image/jpeg");
		if($size=="originale") {
			$filename=$photo;
		} else {
			$filename=substr($photo,0,-4)."-".$size.".JPG";
		}
		header('Content-Disposition: inline; filename=\"'.$filename.'\"');
		passthru($command);
		return;
	}

/* Si c'est une taille standard, on vérifie l'existence de l'image *
 * dans le cache, et si elle n'existe pas, on la crée.             */

 	if(!file_exists($album_dir.$current_album.".cache/".$photo.".".$size)) {
		$command=$cmd_convert;
		$command .= " -size " . $size.'x'.$size;
		if($rotate) {
			$command .= " -rotate " . $rotate;
		}
		$command .= " \"" . $album_dir.$current_album.$photo;
		$command .= "\" -resize " . $size.'x'.$size . " +profile \"0\" \"";
		$command .= $album_dir.$current_album.".cache/".$photo.".".$size;
		$command .= "\"";
		system($command);
	}

/* Affichage de l'image à partir du cache */

	header("Content-type: image/jpeg");
	$filename=substr($photo,0,-4)."-".$size.".JPG";
	header('Content-Disposition: inline; filename=\"'.$filename.'\"');
	header('Content-Length: '.filesize($album_dir.$current_album.".cache/".$photo."." . $size));
	$filestat=stat($album_dir.$current_album.$photo);
	header('Last-Modified: '.date("r",$filestat[9]));
	passthru($cmd_cat . " \"". $album_dir.$current_album.".cache/".$photo."." . $size . "\"");

?>
