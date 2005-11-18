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

function album() {
	html_header();
	html_title();
	html_album_navlink();
	html_album_subdirs();
	html_album_download();
	if($_SESSION['nb_photos'] != 0 ) {
		html_album_photos();
	}	
	html_footer();
}

function browse() {
	html_header();
	html_title();
	html_browse_nav();
	html_browse_photo();
	html_footer();
}

/* ************************************************************************** *
/* Commence la page HTML avec le titre $title et la feuille de style $css     *
 * ************************************************************************** */

	function html_header() {
		echo '<?xml version="1.0" encoding="ISO-8859-1"?>'."\n";
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//FR" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n";
		echo ''."\n";
		echo '<!-- html_header start -->'."\n";
		echo ''."\n";
		echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">'."\n";
		echo ' <head>'."\n";
		echo '  <title>albumphotos version '.$_SESSION['version'].' - '.$_SESSION['album'].' - '.$_SESSION['album_comments'].'</title>'."\n";
		echo '  <link type="text/css" rel="stylesheet" href="'.$_SESSION['css'].'" />'."\n";
		echo '  <meta http-equiv="Content-type" content="text/html; charset=ISO-8859-1" />'."\n";
		echo ' </head>'."\n";
		echo ' <body>'."\n";
		echo "\n";
		echo '<!-- html_header end -->'."\n";
		echo "\n";
	}

/* ************************************************************************* *
/* Crée une section <div> de la classe "title" contenant le texte $text      *
 * ************************************************************************* */

	function html_title() {
		echo "\n";
		echo '<!-- html_title start -->'."\n";
		echo "\n";
		echo '  <div class="title">'."\n";
		echo '   '.$_SESSION['album'].' - '.$_SESSION['album_comments']."\n";
		echo '  </div>'."\n";
		echo "\n";
		echo '<!-- html_title end -->'."\n";
		echo "\n";
	}

/* ************************************************************************* *
 * Crée une section <div> de la classe "album_download" contenant un lien    *
 * pour télécharger l'album photo courant dans son intégralité               *
 * ************************************************************************* */

	function html_album_download() {
		echo "\n";
		echo '<!-- html_album_download start -->'."\n";
		echo "\n";
		echo '  <div class="albumdownload">'."\n";
		echo '   <a href="download.php?album='.$_SESSION['album'].'">Télécharger cette album au format tar</a>'."\n";
		echo '  </div>'."\n";
		echo "\n";
		echo '<!-- html_album_download end -->'."\n";
		echo "\n";
	}
/* ************************************************************************* *
/* A partir de l'album courant, crée une section <div> de classe "navlink"   *
/* contenant des liens vers l'arborescence précédent l'album courant pour    *
/* faciliter la navigation. $album_script contient le nom du script PHP qui  *
/* gère l'affichage de l'album.                                              *
 * ************************************************************************* */

	function html_album_navlink() {
		echo "\n";
		echo '<!-- html_album_navlink start -->'."\n";
		echo "\n";
		echo '  <div class="albumnavlink">'."\n";

	/* On crée le lien pour la racine */

		echo '   Navlink : <a href="album.php';
		echo '?album='.'/'.'">/</a>'."\n";
	
	/* On divise le chemin vers l'album courant en fonction des / */
	
		$prev_dirs = explode("/",$_SESSION['album']);
		$album = '/';
		$nb_dirs = count($prev_dirs);

	/* On boucle sur le nombre de répetoire et à chaque fois on ajoute le *
	 * nouveau répertoire à la variable $album                            */
		
		for($i=0;$i<$nb_dirs;$i++) {
			if(strlen($prev_dirs[$i]) != 0) {
				$album .= $prev_dirs[$i] . '/';
				echo '   <a href="album.php?album='.$album;
				echo '">'.$prev_dirs[$i].'/</a>'."\n";
			}
		}
		echo '  </div>'."\n";
		echo "\n";
		echo '<!-- html_album_navlink end -->'."\n";
		echo "\n";
	}

/* ************************************************************************* *
 * Crée des liens vers les sous-répertoires contenu dans le tableau $subdirs *
 * Ces répertoires sont des sous-répertoires de $current_album.              *
 * $album_script contient le nom du script PHP qui gère l'affichage de       *
 * l'album.                                                                  *
 * ************************************************************************* */

	function html_album_subdirs() {
		echo "\n";
		echo '<!-- html_album_subdirs start -->'."\n";
		echo "\n";
	
	/* On calcule le nombre de sous-répertoire et s'il n'est pas égal à 0, *
	 * on commence la boucle d'affichage                                   */
	
		$nb_subdirs = count($_SESSION['subdirs']);
		if($nb_subdirs > 0) {
			echo '  <div class="albumsubdirs">'."\n";
			echo '   Subdirs :<br />'."\n";
			for($i=0;$i<$nb_subdirs;$i++) {
				echo '   - <a href="album.php';
				echo '?album='.$_SESSION['album'].$_SESSION['subdirs'][$i].'">';
				echo $_SESSION['subdirs'][$i].'</a> - ';
				echo $_SESSION['subdirs_comments'][$i];
				echo '<br />'."\n";
			}
			echo '  </div>'."\n";
		}
		echo "\n";
		echo '<!-- html_album_subdirs end -->'."\n";
		echo "\n";
	}

/* ************************************************************************* *
 * Crée la liste des photos sous forme de thumbnail avec les commentaires et *
 * les informations EXIF (si elles existent).                                *
 * ************************************************************************* */

	function html_album_photos() {
		echo "\n";
		echo '<!-- html_album_photos start -->'."\n";
		echo "\n";
		if($_SESSION['nb_photos'] == 0) {
			return;
		}
		if(isset($_SESSION['prev_page'])) {
			echo '  <a href="album.php?current_page='.$_SESSION['prev_page'].'">Page précédente</a>'."</n>";
		} else {
			echo '  Page précédente'."\n";
		}
		if(isset($_SESSION['next_page'])) {
			echo '  <a href="album.php?current_page='.$_SESSION['next_page'].'">Page suivante</a>'."</n>";
		} else {
			echo '  Page suivante'."\n";
		}
		$nb_sizes=count($_SESSION['sizes']);
		for($i=$_SESSION['first_photo'];$i<=$_SESSION['last_photo'];$i++) {
			$exif_datas = exif_read_data($_SESSION['album_dir'].$_SESSION['album'].$_SESSION['photos'][$i]);
			echo '  <table class="photos" cellspacing="0" cellpadding="0">'."\n";
			echo '   <tr class="name">'."\n";
			echo '    <td class="name" colspan="3">'."\n";
			echo '     '.$_SESSION['photos'][$i]."\n";
			echo '    </td>'."\n";
			echo '   </tr>'."\n";
			echo '   <tr class="photos">'."\n";
			echo '    <td class="thumbnail" style="width:';
			echo ($thumbnail_size+10).'px;">'."\n";
			echo '     <a href="browse.php?album='.$_SESSION['album'];
			echo '&amp;photo='.$_SESSION['photos'][$i].'&amp;size='.$_SESSION['size'].'">'."\n";
			echo '      <img src="thumbnail.php?album='.$_SESSION['album'];
			echo '&amp;photo='.$_SESSION['photos'][$i].'" alt="'.$_SESSION['photos'][$i].'" />'."\n";
			echo '     </a>'."\n";
			echo '    </td>'."\n";
			echo '    <td class="comments">'.$_SESSION['photos_comments'][$i].'</td>'."\n";
			echo '    <td class="exif" style="width:250px;">'."\n";
			if(
				$exif_datas === false ||
				(
					!isset($exif_datas['DateTime']) &&
					!isset($exif_datas['ExposureTime']) &&
					!isset($exif_datas['Flash']) &&
					!isset($exif_datas['FocalLength']) &&
					!isset($exif_datas['SubjectDistance']) &&
					!isset($exif_datas['ShutterSpeedValue'])
				)
			) {
				echo '     N/A'."\n";
			} else {
				if(isset($exif_datas['DateTime'])) {
					echo '     Date : '.$exif_datas['DateTime'].'<br />'."\n";
				}
				if(isset($exif_datas['ExposureTime'])) {
					echo '     Temps d\'exposition : '.$exif_datas['ExposureTime'];
					echo ' sec.<br />'."\n";
				}
				if(isset($exif_datas['Flash'])) {
					echo '     Flash : ';
					echo ($exif_datas['Flash'])?('oui'):('non');
					echo '<br />'."\n";
				}
				if(isset($exif_datas['FocalLength'])) {
					list($numerateur,$denominateur) = explode("/",$exif_datas['FocalLength']);
					echo '     Longueur focale : ';
					echo (round($numerateur/$denominateur,1));
					echo ' mm<br />'."\n";
				}
				if(isset($exif_datas['SubjectDistance'])) {
					list($numerateur,$denominateur) = explode("/",$exif_datas['SubjectDistance']);
					echo '     Distance du sujet : ';
					echo (round($numerateur/$denominateur,1));
					echo ' m<br />'."\n";
				}
				if(isset($exif_datas['ShutterSpeedValue'])) {
					echo '     Vitesse d\'obturation : ';
					echo $exif_datas['ShutterSpeedValue'].' sec.<br />'."\n";
					list($numerateur,$denominateur) = explode("/",$exif_datas['FNumber']);
					echo '     FNumber : f';
					echo (round($numerateur/$denominateur,1));
					echo '<br />'."\n";
				}
			}
			echo '    </td>'."\n";
			echo '   </tr>'."\n";
			echo '   <tr class="size">'."\n";
			echo '    <td class="size" colspan="3">'."\n";

			for($j=0;$j<$nb_sizes;$j++) {
				if($_SESSION['size'] == $_SESSION['sizes'][$j]) {
					echo '     &nbsp;[&nbsp;'.$_SESSION['sizes'][$j].'&nbsp;]&nbsp;'."\n";
				} else {
					echo '     <a href="browse.php';
					echo '?album='.$_SESSION['album'].'&amp;photo='.$_SESSION['photos'][$i];
					echo '&amp;size='.$_SESSION['sizes'][$j].'">';
					echo '&nbsp;[&nbsp;'.$_SESSION['sizes'][$j];
					echo '&nbsp;]&nbsp;</a>'."\n";
				}
			}
			echo '    </td>'."\n";
			echo '   </tr>'."\n";
			echo '  </table>'."\n";
		}
		echo "\n";
		echo '<!-- html_album_photos end -->'."\n";
		echo "\n";
	}

/* ************************************************************************* *
 * Crée une section <div> contenant les miniatures pour l'image précédente,  *
 * et l'image suivante, des flèches de navigation, le commentaire, et des    *
 * liens pour changer la résolution d'affichage de la photo.                 *
 * ************************************************************************* */

	function html_browse_nav() {
		echo "\n";
		echo '<!-- html_browse_nav start -->'."\n";
		echo "\n";
		$nb_sizes = count($_SESSION['sizes']);
		echo '  <div>'."\n";
		echo '   <table class="browsenav" cellspacing="0" cellpadding="0">'."\n";
		echo '    <tr class="browsenav">'."\n";
		echo '     <td class="browsenavphotoname" colspan="7">'."\n";
		echo '      '.$_SESSION['photos'][$_SESSION['current_photo']]."\n";
		echo '     </td>'."\n";
		echo '    </tr>'."\n";
		echo '    <tr class="browsenav">'."\n";
		echo '     <td class="browsenavarrow">'."\n";
		if(isset($_SESSION['prev_photo'])) {
			echo '      <a class="arrow" href="browse.php?current_photo=0">&lt;&lt;</a>'."\n";
			echo '     </td>'."\n";
			echo '     <td class="browsenavarrow">'."\n";
			echo '      <a class="arrow" href="browse.php?current_photo='.$_SESSION['prev_photo'].'">&lt;</a>'."\n";
			echo '     </td>'."\n";
			echo '     <td class="browsenavthumbnail">'."\n";
			echo '      <a href="browse.php?current_photo='.$_SESSION['prev_photo'].'">'."\n";
			echo '       <img src="thumbnail.php';
			echo '?album='.$_SESSION['album'];
			echo '&amp;photo='.$_SESSION['photos'][$_SESSION['prev_photo']];
			echo '" alt="'.$_SESSION['photos'][$_SESSION['prev_photo']].'" /></a>'."\n";
		} else {
			echo '      &nbsp;'."\n";
			echo '     </td>'."\n";
			echo '     <td class="browsenavarrow">'."\n";
			echo '      &nbsp;'."\n";
			echo '     </td>'."\n";
			echo '     <td class="browsenavthumbnail">'."\n";
			echo '      &nbsp;'."\n";
		}
		echo '     </td>'."\n";
		echo '     <td class="browsenavcomments">'."\n";
		echo '      '.$_SESSION['photos_comments'][$_SESSION['current_photo']]."\n";
		echo '     </td>'."\n";
		echo '     <td class="browsenavthumbnail">'."\n";
		if(isset($_SESSION['next_photo'])) {
			echo '      <a href="browse.php?current_photo='.$_SESSION['next_photo'].'">'."\n";
			echo '       <img src="thumbnail.php';
			echo '?album='.$_SESSION['album'];
			echo '&amp;photo='.$_SESSION['photos'][$_SESSION['next_photo']];
			echo '" alt="'.$_SESSION['photos'][$_SESSION['next_photo']].'" />'."\n";
			echo '      </a>'."\n";
			echo '     </td>'."\n";
			echo '     <td class="browsenavarrow">'."\n";
			echo '      <a class="arrow" href="browse.php?current_photo='.$_SESSION['next_photo'].'">&gt;</a>'."\n";
			echo '     </td>'."\n";
			echo '     <td class="browsenavarrow">'."\n";
			echo '      <a class="arrow" href="browse.php?current_photo='.($_SESSION['nb_photos']-1).'">&gt;&gt;</a>'."\n";
		} else {
			echo '      &nbsp;'."\n";
			echo '     </td>'."\n";
			echo '     <td class="browsenavarrow">'."\n";
			echo '      &nbsp;'."\n";
			echo '     </td>'."\n";
			echo '     <td class="browsenavarrow">'."\n";
			echo '      &nbsp;'."\n";
		}
		echo '     </td>'."\n";
		echo '    </tr>'."\n";
		echo '    <tr class="browsenav">'."\n";
		echo '     <td class="browsenavthumbnailname" colspan="2" rowspan="2">'."\n";
		echo '      &nbsp;'."\n";
		echo '     </td>'."\n";
		echo '     <td class="browsenavthumbnailname" rowspan="2">'."\n";
		if(isset($_SESSION['prev_photo'])) {
			echo '      '.$_SESSION['photos'][$_SESSION['prev_photo']]."\n";
		} else {
			echo '      &nbsp;'."\n";
		}
		echo '     </td>'."\n";
		echo '     <td class="browsenavres">'."\n";
		for($i=0;$i<$nb_sizes;$i++) {
			if($_SESSION['size'] == $_SESSION['sizes'][$i]) {
				echo '&nbsp;[&nbsp;'.$_SESSION['sizes'][$i].'&nbsp;]&nbsp;'."\n";
			} else {
				echo '      <a href="browse.php';
				echo '?current_photo='.$_SESSION['current_photo'];
				echo '&amp;size='.$_SESSION['sizes'][$i];
				echo '">&nbsp;[&nbsp;'.$_SESSION['sizes'][$i];
				echo '&nbsp;]&nbsp;</a>'."\n";
			}
		}
		echo '     </td>'."\n";
		echo '     <td class="browsenavthumbnailname" rowspan="2">'."\n";
		if(isset($_SESSION['next_photo'])) {
			echo '      '.$_SESSION['photos'][$_SESSION['next_photo']]."\n";
		} else {
			echo '      &nbsp;'."\n";
		}
		echo '     </td>'."\n";
		echo '     <td class="browsenavthumbnailname" colspan="2" rowspan="2">'."\n";
		echo '      &nbsp;'."\n";
		echo '     </td>'."\n";
		echo '    </tr>'."\n";
		echo '    <tr class="browsenav">'."\n";
		echo '     <td class="browsenavupdir">'."\n";
		echo '      <a href="album.php">Retour à la liste</a>'."\n";
		echo '     </td>'."\n";
		echo '    </tr>'."\n";
		echo '   </table>'."\n";
		echo '  </div>'."\n";
		echo "\n";
		echo '<!-- html_browse_nav end -->'."\n";
		echo "\n";
		
	}

/* ************************************************************************* *
 * Crée une section <div> de classe "photo" contenant la photo à afficher    *
 * dans une certaine résolution.                                             *
 * ************************************************************************* */

	function html_browse_photo() {
		echo "\n";
		echo '<!-- html_browse_photo start -->'."\n";
		echo "\n";
		echo '     <div class="photo">'."\n";
		echo '      <img src="photo.php?album='.$_SESSION['album'];
		echo '&amp;photo='.$_SESSION['photos'][$_SESSION['current_photo']].'&amp;size='.$_SESSION['size'];
		echo '" alt="'.$_SESSION['photos'][$_SESSION['current_photo']].'" />'."\n";
		echo '     </div>'."\n";
		echo "\n";
		echo '<!-- html_browse_nav start -->'."\n";
		echo "\n";
	}

/* ************************************************************************* *
 * Crée une section <div> de classe "footer" fermant la page HTML et         *
 * affichant le temps qu'il a fallu pour créer la page.                      *
 * ************************************************************************* */

	function html_footer() {
		echo "\n";
		echo '<!-- html_footer start -->'."\n";
		echo "\n";
		echo '  <div class="validator">'."\n";
		echo '   <a href="http://validator.w3.org/check/referer">'."\n";
		echo '    <img style="border:0;width:88px;height:31px"'."\n";
		echo '         src="images/valid-xhtml10.gif"'."\n";
		echo '         alt="Valid XHTML 1.0!" />'."\n";
		echo '   </a>'."\n";
		echo '   <a href="http://jigsaw.w3.org/css-validator/">'."\n";
		echo '    <img style="border:0;width:88px;height:31px"'."\n";
		echo '         src="images/valid-css2.gif" '."\n";
		echo '         alt="Valid CSS 2.0!" />'."\n";
		echo '   </a>'."\n";
		echo '  </div>'."\n";
		echo ' </body>'."\n";
		echo '</html>'."\n";
		echo "\n";
		echo '<!-- html_footer end -->'."\n";
		echo "\n";
	}
