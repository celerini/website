<?php

////////////////////////////////////////////////////////////////////////////
//
// Description: Common functions for GUI statistics grabbing program
//
////////////////////////////////////////////////////////////////////////////

//
// Create a lock file
//
function create_lock()
{
	global $prog;
	@touch("/tmp/$prog.lock");
}

//
// Remove the lock file
//
function remove_lock() {
	global $prog;
	@unlink("/tmp/$prog.lock");
}

//
// Check if the lock file exists
//
function is_locked()
{
	global $prog;
	if (@is_file("/tmp/$prog.lock"))
	{
		return 1;
	}
	else
	{
		return 0;
	}
}

function update($basedir, $lang, $package)
{
	$pofile = $basedir . '/po/' . $lang . '/' . $package . '.po';
	$potfile = $basedir . '/po/' . $package . '.pot';
	echo 'msgmerge --update ' . $pofile . ' ' . $potfile;
	@exec('msgmerge --update ' . $pofile . ' ' . $potfile);
}

//
// Get statistics from .po file
//
function getstats($file)
{
	global $msgfmt;
  
	$translated = 0;
	$untranslated = 0;
	$fuzzy = 0;
	$error = 0;

	$escfile = escapeshellarg($file);
	@exec("$msgfmt -o /dev/null --statistics $escfile 2>&1", $output, $ret);

	if ($ret == 0)
	{
		// new version of msgfmt make life harder :-/
		if (preg_match("/^\s*(\d+)\s*translated[^\d]+(\d+)\s*fuzzy[^\d]+(\d+)\s*untranslated/", $output[0], $m))
		{
			$m[3] = 0;
		}
		else if (preg_match("/^\s*(\d+)\s*translated[^\d]+(\d+)\s*fuzzy[^\d]/", $output[0], $m))
		{
			$m[3] = 0;
		}
		else if (preg_match("/^\s*(\d+)\s*translated[^\d]+(\d+)\s*untranslated[^\d]/", $output[0], $m))
		{
			$m[3] = $m[2];
			$m[2] = 0;
		}
		else if (preg_match("/^\s*(\d+)\s*translated[^\d]+/", $output[0], $m))
		{
			$m[2] = $m[3] = 0;
		}
		else
		{
			return [ 1, 0, 0, 0 ];
		}

		$translated = $m[1] + 0;
		$fuzzy = $m[2] + 0;
		$untranslated = $m[3] + 0;
	}
	else
	{
		$error = 1;
	}

	return [ $error, $translated, $fuzzy, $untranslated ];
}

function getdomain($string)
{
	return 'wesnoth-' . str_replace('-po', '', $string);
}

function getpackage($string)
{
	return str_replace('wesnoth-', '', $string);
}

//
// Get a GET variable cleaned up for possible XSS exploits.
//
function parameter_get($name)
{
	return htmlspecialchars($_GET[$name], ENT_QUOTES, 'UTF-8');
}

function add_textdomain_stats($file, &$stats_ary)
{
	if (!file_exists($file))
	{
		return;
	}

	$raw_td_stats = unserialize(file_get_contents($file));

	foreach ($raw_td_stats as $lang => $lang_stats)
	{
		if (!isset($stats_ary[$lang]))
		{
			$stats_ary[$lang] = [ 0, 0, 0, 0 ];
		}

		for ($i = 0; $i < 4; ++$i)
		{
			$stats_ary[$lang][$i] += $lang_stats[$i];
		}
	}
}
