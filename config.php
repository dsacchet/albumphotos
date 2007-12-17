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

/* Comportement de l'interface */

	$default_css="album.css";
	$char_encoding="";
	$http_base="/albumphotos/album.php";
	$thumbnail_size="120";
	$thumbnail_square="true";
	$default_size="800";
	$template="ouba.org.php";
	$standard_sizes=array(
		"640",
		"800",
		"1024",
		"1280",
		"1600",
		"2048"
	);
	$default_rows_of_photos="2";
	$default_photos_per_row="3";

/* Chemins */

	$album_dir="/var/www/www.ouba.org/var/albumphotos";
	$groups_file="/var/www/www.ouba.org/etc/photos.groups";

/* Emplacement des commandes */

	$cmd_convert="/usr/bin/convert";
	$cmd_cat="/bin/cat";
	$cmd_tar="/bin/tar";

/* Définitions des paramètres de polices */

	$photo_font="Bookman-DemiItalic";

/* Définitions des chemins vers les fontes truetype */

	$font_arial   = array(
		"normal"      => "/usr/share/fonts/corefonts/arial.ttf",
		"bold"        => "/usr/share/fonts/corefonts/arialbd.ttf",
		"italic"      => "/usr/share/fonts/corefonts/ariali.ttf",
		"bold_italic" => "/usr/share/fonts/corefonts/arialbi.ttf"
	);

	$font_verdana = array(
		"normal"      => "/usr/share/fonts/corefonts/verdana.ttf",
		"bold"        => "/usr/share/fonts/corefonts/verdanab.ttf",
		"italic"      => "/usr/share/fonts/corefonts/verdanai.ttf",
		"bold_italic" => "/usr/share/fonts/corefonts/verdanaz.ttf"
	);

	$font_courier = array(
		"normal"      => "/usr/share/fonts/corefonts/cour.ttf",
		"bold"        => "/usr/share/fonts/corefonts/courbd.ttf",
		"italic"      => "/usr/share/fonts/corefonts/couri.ttf",
		"bold_italic" => "/usr/share/fonts/corefonts/courbi.ttf"
	);

?>
