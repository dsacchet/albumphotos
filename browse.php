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

/* R�cup�ration des param�tres de configuration */

	include_once("config.php");
	include_once("functions.php");
	include_once($template);


/* On v�rifie qu'il ne fasse pas reseter la session */

	if(isset($_GET['reset'])) {
		session_start();
		$_SESSION = array();
		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time()-42000, '/');
		}
		session_destroy();
	}

/* Destruction des variables de sessions � recalculer � chaque chargement *
 * de la page                                                             */

	unset($_SESSION['user']);
	unset($_SESSION['groups']);
	unset($_SESSION['album_dir']);
	unset($_SESSION['photos']);
	unset($_SESSION['comments']);
	unset($_SESSION['subdirs']);
	unset($_SESSION['sizes']);

/* On commence :) */

	session_start();

/* On r�cup�re la variable utilisateur et on calcule la liste des groupes *
 * auxquels il appartient. On utilise pas de cache pour cela, c'est       *
 * refait � chaque appel au script.                                       *
 * Pareil pour l'album_dir au cas o� la configuration change pour cela    */

	$_SESSION['user']=$_SERVER['PHP_AUTH_USER'];
	$_SESSION['groups']=get_groups($user,$groups_file);
	$_SESSION['album_dir']=verif_path($album_dir,1);
	$_SESSION['sizes']=$standard_sizes;

/* Si l'album est pr�cis� dans l'url, on le prend */

	if(isset($_GET['album'])) {
		$_SESSION['album']=stripslashes($_GET['album']);
		unset($_GET['album']);
	}

/* Si l'album n'est pas pr�cis�, on retourne � la page d'accueil */

	if(!isset($_SESSION['album'])) {
		header("Location: album.php");
	}

/* Si la taille n'est pas pr�cis�e, on la rajoute � la session */

	if(!isset($_SESSION['size'])) {
		$_SESSION['size']=$default_size;
	}

/* Si la taille est pr�cis�e dans l'url, on la prend */

	if(isset($_GET['size'])) {
		$_SESSION['size']=stripslashes($_GET['size']);
		unset($_GET['size']);
	}

/* On r�cup�re le r�pertoire courant et on effectue une v�rification sur les *
 * / au d�but et � la fin pour ne pas avoir de surprises                     */

	$_SESSION['album']=verif_path($_SESSION['album'],3);

/* On v�rifie que l'utilisateur courant est bien autoris� � visionner ce *
* r�pertoire                                                            */

	if(!have_the_right($_SESSION['album_dir'].$_SESSION['album'].".this_users",
		$_SESSION['album_dir'].$_SESSION['album'].".this_groups",
		$_SESSION['user'],$_SESSION['groups'])) {
		error("Vous n'avez pas le droit d'acc�der � ce r�pertoire","album.php");
		return;
	}

/* On construit la liste des photos, sous r�pertoires et commentaires */

	list($_SESSION['photos'],
	     $_SESSION['photos_comments'],
	     $_SESSION['subdirs'],
	     $_SESSION['subdirs_comments']) = listing($_SESSION['album_dir'],
	                                              $_SESSION['album'],
	                                              $_SESSION['user'],
	                                              $_SESSION['groups']);


/* On remet � jour l'ensemble des param�tres li�s � la navigation *
 * parmi les photos                                               */

	unset($_SESSION['nb_photos']);
	unset($_SESSION['prev_photo']);
	unset($_SESSION['next_photo']);

/* Calcul du nombre de photos et r�cup�ration de quelques param�tres *
 * dans les fichiers de configuration ou dans l'URL                  */

	$_SESSION['nb_photos']=count($_SESSION['photos']);

/* S'il n'y a pas de photos et que la variable contenant la page *
 * courante existe, on supprime cette variable qui n'a pas lieu  *
 * d'�tre                                                        */

	if($_SESSION['nb_photos']==0 && isset($_SESSION['current_photo'])) {
		unset($_SESSION['current_photo']);
		header("Location: album.php");
	} else {

/* Si la photo courante n'existe pas, c'est la photo 0 */

		if(!isset($_SESSION['current_photo'])) {
			$_SESSION['current_photo'] = 0;
		}

/* On change de photo si demand� dans l'URL */

		if(isset($_GET['current_photo'])) {
			$_SESSION['current_photo']=stripslashes($_GET['current_photo']);
			unset($_GET['current_photo']);
		}

/* On v�rifie que le num�ro de photo n'est pas trop grand *
 * si c'est le cas on remet le num�ro maximum             */

		if($_SESSION['current_photo'] >= $_SESSION['nb_photos']) {
			$_SESSION['current_photo']=$_SESSION['nb_photos']-1;
		}

/* On v�rifie que le num�ro de photo n'est pas trop petit *
 * si c'est le cas on remet le num�ro minimum             */

		if($_SESSION['current_photo'] < 0) {
			$_SESSION['current_photo']=0;
		}

/* On calcul la photo suivante si on n'est pas sur la derni�re *
 * photo                                                       */

		if($_SESSION['current_photo']<$_SESSION['nb_photos']-1) {
			$_SESSION['next_photo']=$_SESSION['current_photo']+1;
		}

/* On calcul la photo pr�c�dente si on n'est pas sur la premi�re *
 * photo                                                         */

		if($_SESSION['current_photo']>0) {
			$_SESSION['prev_photo']=$_SESSION['current_photo']-1;
		}
	}

	browse();


?>
