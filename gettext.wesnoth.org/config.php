<?php

////////////////////////////////////////////////////////////////////////////
//
// Description: Configuration file for GUI statistics grabbing program
//
////////////////////////////////////////////////////////////////////////////

// the msgfmt program path
$msgfmt = '/usr/bin/msgfmt';

$branch = '1.12'; //version of current stable (folder name of the checkout folder)
$wescampbranchversion = '1.12';
$wescamptrunkversion = '1.12';

$masterbasedir = '/usr/src/wesnoth/master/';
$branchbasedir = '/usr/src/wesnoth/' . $branch . '/';
$extratbasedir = '/home/wesnoth/wescamp-i18n/branch-' . $wescamptrunkversion . '/'; //master addon server
$extrabbasedir = '/home/wesnoth/wescamp-i18n/branch-' . $wescampbranchversion . '/'; //branch addon server

//$packages = file_get_contents($basedir . "/po/DOMAINS");
//$packages = substr($packages, 0, strlen($packages)-1);
$corepackages = 'wesnoth wesnoth-lib wesnoth-editor wesnoth-help wesnoth-ai wesnoth-units wesnoth-multiplayer wesnoth-tutorial';
//$packages = trim(system("grep ^SUBDIRS " . $basedir . "/po/Makefile.am | cut -d= -f2"));
$packages = 'wesnoth wesnoth-lib wesnoth-editor wesnoth-help wesnoth-ai wesnoth-units wesnoth-multiplayer wesnoth-test wesnoth-anl wesnoth-tutorial wesnoth-manpages wesnoth-manual wesnoth-aoi wesnoth-did wesnoth-dm wesnoth-dw wesnoth-ei wesnoth-httt wesnoth-l wesnoth-low wesnoth-nr wesnoth-sof wesnoth-sota wesnoth-sotbe wesnoth-tb wesnoth-thot wesnoth-trow wesnoth-tsg wesnoth-utbs';

$ignore_langs = 'sr@ijekavianlatin sr@latin sr@ijekavian';

//
// Get unofficial packages
//

// master

$packarray = [];

if ($dir = opendir($extratbasedir))
{
	// PHP manual says readdir returns false on failure and that
	// false-evaluating non-booleans may be returned on successful execution.
	// Testing shows that readdir may return NULL on failure, causing infinite loops.
	// For our purposes, an empty string is equivalent to a failure anyway.
	while (false != ($file = readdir($dir)))
	{
		if ($file[0] != '.')
		{
			$packarray[] = $file;
		}
	}
	closedir($dir);
	sort($packarray);
	$extratpackages = implode(' ', $packarray);
}

// branch

$packarray = [];

if ($dir = opendir($extrabbasedir))
{
	// PHP manual says readdir returns false on failure and that
	// false-evaluating non-booleans may be returned on successful execution.
	// Testing shows that readdir may return NULL on failure, causing infinite loops.
	// For our purposes, an empty string is equivalent to a failure anyway.
	while (false != ($file = readdir($dir)))
	{
		if ($file[0] != '.')
		{
			$packarray[] = $file;
		}
	}
	closedir($dir);
	sort($packarray);
	$extrabpackages = implode(' ', $packarray);
}

$ignore_langs = explode(' ', $ignore_langs);

//$languages = file_get_contents($basedir . '/po/LINGUAS');
//$languages = substr($languages, 0, strlen($languages)-1);

$prog = 'grab-stats';
