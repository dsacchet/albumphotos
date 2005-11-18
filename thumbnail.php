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
		return;
	}

/* On formate correctement les répertoires */

	$current_album=stripslashes($_GET['album']);
	$photo=stripslashes($_GET['photo']);

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

/* On vérifie qu'il existe une thumbnail, si oui, on vérifie la taille *
 * si la thumbnail n'existe pas ou si elle a la mauvaise taille, on    *
 * en recrée une */

	$recreate=false;

 	if(file_exists($album_dir.$current_album.".cache/".$photo.".thumbnail")) {
		$size=getimagesize($album_dir.$current_album.".cache/".$photo.".thumbnail");
		if($size[0] != $thumbnail_size && $size[1] != $thumbnail_size) {
			$recreate=true;
		}
	} else {
		$recreate=true;
	}
	if($recreate) {
		$command=$cmd_convert;
		$command .= " -size " . $thumbnail_size . "x" . $thumbnail_size;
		if($rotate) {
			$command .= " -rotate " . $rotate;
		}
		$command .= " \"" . $album_dir.$current_album.$photo;
		$command .= "\" -resize " . $thumbnail_size . "x" . $thumbnail_size;
		$command .= " +profile \"0\" \"";
		$command .= $album_dir.$current_album.".cache/".$photo.".thumbnail";
		$command .= "\"";
		system($command);
	}

/* Affichage de l'image */

	header("Content-type: image/jpeg");
	$filename=substr($photo,0,-4)."-thumbnail.JPG";
	header('Content-Disposition: inline; filename=\"'.$filename.'\"');
	header('Content-Length: '.filesize($album_dir.$current_album.".cache/".$photo.".thumbnail"));
	$filestat=stat($album_dir.$current_album.".cache/".$photo.".thumbnail");
	header('Last-Modified: '.date("r",$filestat[9]));
	passthru($cmd_cat . " \"" . $album_dir.$current_album.".cache/".$photo.".thumbnail"."\"");

?>
