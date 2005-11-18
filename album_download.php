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

	if(!isset($_GET['album'])) {
		return;
	}

/* On formate correctement les répertoires */

	$current_album = $_GET['album'];

	$album_dir = verif_path($album_dir,1);
	$current_album = verif_path($current_album,3);

/* On récupère l'utilisateur courant et les groupes d'utilisateurs auxquels *
 * il appartient                                                            */

	$user = $_SERVER['PHP_AUTH_USER'];
	$groups = get_groups($user,$groups_file);

/* On vérifie vite fait si l'album existe et qu'on a le droit de le voir */

	if(!file_exists($album_dir.$current_album)) {
		error("Cet album n'existe pas",400,55,$font_arial);
		return;
	}

	if(!have_the_right($album_dir.$current_album.".this_users",
	                   $album_dir.$current_album.".this_groups",
	                   $user,$groups)) {
		error_img("Vous n'avez pas le droit d'accéder à cet album",400,55,$font_arial);
		return;
	}

/* On construit la liste des photos, sous répertoires et commentaires */

	list($photos,$subdirs,$comments) = listing($current_album,$user,$groups);

/* On construit la commande pour faire une archive de toutes les photos *
 * contenu dans ce répertoire visible par l'utilisateur courant         */

	$command = $cmd_tar . " -c -";
	$current_album = substr($current_album,1,strlen($current_album)-2);
	while(list($key,$value) = each($photos)) {
		$command .= ' "' . $current_album . "/" . $value . '"';
	}
	chdir($album_dir);
	header("Content-type: application/x-tar");
	header("Content-Disposition: attachment; filename=\"" . str_replace("/"," - ",$current_album).".tar\"");
	passthru($command);

?>
