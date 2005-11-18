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
 * D�but de l'album                                                           *
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
	unset($_SESSION['photos_comments']);
	unset($_SESSION['subdirs']);
	unset($_SESSION['subdirs_comments']);
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

/* Si l'album n'est pas pr�cis�, on le rajoute � la session */

	if(!isset($_SESSION['album'])) {
		$_SESSION['album']='/';
	}

/* Si l'album est pr�cis� dans l'url, on le prend */

	if(isset($_GET['album'])) {
		$_SESSION['album']=stripslashes($_GET['album']);
		unset($_GET['album']);
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
 * dans les pages                                                 */
	
	unset($_SESSION['nb_photos']);
	unset($_SESSION['nb_pages']);
	unset($_SESSION['next_page']);
	unset($_SESSION['prev_page']);
	unset($_SESSION['first_photo']);
	unset($_SESSION['last_photo']);

/* Calcul du nombre de photos et r�cup�ration de quelques param�tres *
 * dans les fichiers de configuration ou dans l'URL                  */

	$_SESSION['nb_photos']=count($_SESSION['photos']);

	if(!isset($_SESSION['photos_per_row'])) {
		$_SESSION['photos_per_row']=$default_photos_per_row;
	}
	if(isset($_GET['photos_per_row'])) {
		$_SESSION['photos_per_row']=stripslashes($_GET['photos_per_row']);
		unset($_GET['photos_per_row']);
	}

	if(!isset($_SESSION['rows_of_photos'])) {
		$_SESSION['rows_of_photos']=$default_rows_of_photos;
	}
	if(isset($_GET['rows_of_photos'])) {
		$_SESSION['rows_of_photos']=stripslashes($_GET['rows_of_photos']);
		unset($_GET['rows_of_photos']);
	}

	$_SESSION['photos_per_page']=$_SESSION['rows_of_photos']*$_SESSION['photos_per_row'];

/* S'il n'y a pas de photos et que la variable contenant la page *
 * courante existe, on supprime cette variable qui n'a pas lieu  *
 * d'�tre                                                        */
	
	if($_SESSION['nb_photos']==0 && isset($_SESSION['current_page'])) {
		unset($_SESSION['current_page']);
	} else {

/* Sinon, on recalcule le nombre de page en fonction du nombre de *
 * de photos par page                                             */
	
		$_SESSION['nb_pages']=ceil($_SESSION['nb_photos']/$_SESSION['photos_per_page']);

/* Si la page courante n'existe pas, c'est la page 1 */

		if(!isset($_SESSION['current_page'])) {
			$_SESSION['current_page'] = 1;
		}

/* Si on vient du browser et qu'on �tait sur une photo, on essaye de *
 * trouver la page correspondante                                    */

		if(isset($_GET['current_photo'])) {
			$_SESSION['current_photo']=stripslashes($_GET['current_photo']);
		}
		if(isset($_SESSION['current_photo'])) {
			$_SESSION['current_page']=ceil(($_SESSION['current_photo']+1)/$_SESSION['photos_per_page']);
		}

/* Sinon on v�rifie que la page courante correspond bien � *
 * quelque chose de coh�rent par rapport � des changements *
 * depuis le derni�r changement de la page                 */

	/* On change de page si demand� dans l'URL */

		if(isset($_GET['current_page'])) {
			$_SESSION['current_page']=stripslashes($_GET['current_page']);
			unset($_GET['current_page']);
		}

	/* On v�rifie que le num�ro de page n'est pas trop grand *
	 * si c'est le cas on remet le num�ro maximum            */
		
		if($_SESSION['current_page'] > $_SESSION['nb_pages']) {
			$_SESSION['current_page']=$_SESSION['nb_pages'];
		}

	/* On v�rifie que le num�ro de page n'est pas trop petit *
	 * si c'est le cas on remet le num�ro minimum            */

		if($_SESSION['current_page'] < 1) {
			$_SESSION['current_page']=1;
		}

	/* On calcul la page suivante si on n'est pas sur la derni�re *
	 * page                                                       */

		if($_SESSION['current_page']<$_SESSION['nb_pages']) {
			$_SESSION['next_page']=$_SESSION['current_page']+1;
		}

	/* On calcul la page pr�c�dente si on n'est pas sur la premi�re *
	 * page                                                         */

		if($_SESSION['current_page']>1) {
			$_SESSION['prev_page']=$_SESSION['current_page']-1;
		}
		$_SESSION['first_photo']=$_SESSION['photos_per_page']*($_SESSION['current_page']-1);
		$_SESSION['last_photo']=min($_SESSION['nb_photos']-1,$_SESSION['photos_per_page']*($_SESSION['current_page'])-1);
	}

/* On renseigne un certain nombre de variable pouvant �tre utilis�es *
 * lors du dessin de la page                                         */

	if(!isset($_SESSION['version'])) {
		$_SESSION['version'] .= substr(@fgets(@fopen("VERSION","r"),1024),0,-1);
	}

	if(!isset($_SESSION['css'])) {
		$_SESSION['css']=$default_css;
	}
	if(isset($_GET['css'])) {
		$_SESSION['css']=stripslashes($_GET['css']);
		unset($_GET['css']);
	}

	$_SESSION['album_comments'] = dir_comments($_SESSION['album_dir'].$_SESSION['album']);

/* On appelle la fonction de g�n�ration du html */

	album();

?>
