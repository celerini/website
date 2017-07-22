<?php

define('IN_WESNOTH_LANGSTATS', true);

include('config.php');
include('functions.php');
include('functions-web.php');
include('langs.php');
include('wesmere.php');

$existing_packs         = explode(' ', $packages);
$existing_corepacks     = explode(' ', $corepackages);
$existing_extra_packs_t = explode(' ', $extratpackages);
$existing_extra_packs_b = explode(' ', $extrabpackages);

sort($existing_extra_packs_t);
sort($existing_extra_packs_b);

$stats = [];

//
// Process URL parameters
//

// Set the default starting point when calling gettext.wesnoth.org:
//   'branch': show stats from the current stable branch
//   'master': show stats from master
$version = isset($_GET['version']) ? parameter_get('version') : 'branch';

$lang = isset($_GET['lang']) ? parameter_get('lang') : '';

$nostats = false;

if (!empty($lang))
{
	for ($i = 0; $i < 2; ++$i)
	{
		$official = $i == 0;

		if ($official)
		{
			$packs = $existing_packs;
		}
		else
		{
			$packs = ($version == 'master') ? $existing_extra_packs_t : $existing_extra_packs_b;
		}

		foreach ($packs as $pack)
		{
			if (!$official)
			{
				$pack = getdomain($pack);
			}

			$statsfile = 'stats/' . $pack . '/' . $version . 'stats';

			if (!file_exists($statsfile))
			{
				continue;
			}

			$serialized = file_get_contents($statsfile);
			$tmpstats = unserialize($serialized);

			$stat = $tmpstats[$lang];
			$stats[] = [
				$stat[0],	// errors
				$stat[1],	// translated
				$stat[2],	// fuzzy
				$stat[3],	// untranslated
				$pack,		// textdomain name
				$tmpstats['_pot'][1] + $tmpstats['_pot'][2] + $tmpstats['_pot'][3],
				$official,	// is official
			];
		}
	}
}
else
{
	$nostats = true;
	unset($lang);
}

wesmere_emit_header();

?>

<h1>Translation Statistics</h1>

<div id="gettext-display-options"><?php

if (!$nostats)
{
	$firstpack = $existing_packs[0];
	$filestat = stat('stats/' . $firstpack . '/' . $version . 'stats');

	ui_last_update_timestamp($filestat[9]);
}

?><div id="version">Branch:
	<ul class="gettext-switch"
		><li><?php ui_self_link($version == 'branch', 'Stable/1.12', "?version=branch&package=$package&lang=$lang") ?></li
		><li><?php ui_self_link($version != 'branch', 'Development/master', "?version=master&package=$package&lang=$lang") ?></li
	></ul>
</div>

<?php
function ui_package_set_link($package_set, $label)
{
	global $version;

	echo '<a href="' . htmlspecialchars("index.php?package=$package_set&order=trans&version=$version") . '">' . $label . '</a>';
}

?><div id="package-set">Show:
	<ul class="gettext-switch"
		><li><?php ui_package_set_link('alloff',  'All mainline textdomains')   ?></li
		><li><?php ui_package_set_link('allcore', 'Mainline core textdomains')  ?></li
		><li><?php ui_package_set_link('all',     'All textdomains')            ?></li
		><li><?php ui_package_set_link('allun',   'All unofficial textdomains') ?></li
		><li><b>By language</b></li
	></ul>
</div>

<div id="language-teams">Language:
	<ul class="gettext-switch"><?php
		$sorted_langs = $langs;
		asort($sorted_langs);

		foreach ($sorted_langs as $code => $langname)
		{
			echo '<li>';
			ui_self_link($code == $lang, $langname, "?lang=$code&version=$version");
			echo '</li>';
		}
	?></ul>
</div>

</div><!-- gettext-display-options --><?php

if (!$nostats)
{
	?><table class="gettext-stats">
	<thead><tr><?php
		?><th class="title">Textdomain</th>
		<th class="translated">Translated</th>
		<th class="translated percent">%</th>
		<th class="fuzzy">Fuzzy</th>
		<th class="fuzzy percent">%</th>
		<th class="untranslated">Untranslated</th>
		<th class="untranslated percent">%</th>
		<th class="total">Total</th>
		<th class="graph">Graph</th>
	</tr></thead>
	<tbody><?php

	$sumstat = [ 0, 0, 0, 0, 0, 0 ];
	$official = true;

	foreach ($stats as $stat)
	{
		$oldofficial = $official;
		$official = $stat[6];

		$sumstat[1] += $stat[1];
		$sumstat[2] += $stat[2];
		$sumstat[3] += $stat[3];
		$sumstat[5] += $stat[5];

		$total = $stat[1] + $stat[2] + $stat[3];

		if ($oldofficial != $official)
		{
			?><tr class="officialness-separator"><td colspan="9"></td></tr><?php
		}

		?><tr>
			<td class="textdomain-name"><?php
				if ($official)
				{
					$repo = ($version == 'master') ? 'master' : $branch;
					echo "<a class='textdomain-file' href='https://raw.github.com/wesnoth/wesnoth/$repo/po/" . $stat[4]. "/$lang.po'>" . $stat[4] . '</a>';
				}
				else
				{
					$packname = getpackage($stat[4]);
					$repo = ($version == 'master') ? $wescamptrunkversion : $wescampbranchversion;
					$reponame = "$packname-$repo";
					echo "<a class='textdomain-file' href='https://raw.github.com/wescamp/$reponame/master/po/$lang.po'>" . $stat[4] . '</a>';
				}
			?></td><?php

			if (($stat[0] == 1) || ($total == 0) || ($stat[5] == 0))
			{
				?><td class="invalidstats" colspan="8">Error in <?php echo $stat[4] ?> translation files</td><?php
			}
			else
			{
				?><td class="translated"><?php echo $stat[1] ?></td>
				<td class="percent"><?php printf("%0.2f", ($stat[1]*100)/$stat[5]) ?></td>
				<td class="fuzzy"><?php echo $stat[2] ?></td>
				<td class="percent"><?php printf("%0.2f", ($stat[2]*100)/$stat[5]) ?></td>
				<td class="untranslated"><?php echo ($stat[5] - $stat[1] - $stat[2]) ?></td>
				<td class="percent"><?php printf("%0.2f", (($stat[5]-$stat[1]-$stat[2])*100)/$stat[5]) ?></td>
				<td class="strcount"><?php echo $total ?></td><?php

				$graph_width = 240; // px

				$trans = sprintf("%d", ($stat[1] * $graph_width) / $stat[5]);
				$fuzzy = sprintf("%d", ($stat[2] * $graph_width) / $stat[5]);
				$untrans = $graph_width - $trans - $fuzzy;

				?><td class="graph"><span class="stats-bar green-bar" style="width:<?php echo $trans ?>px"></span><span class="stats-bar blue-bar" style="width:<?php echo $fuzzy ?>px"></span><span class="stats-bar red-bar" style="width:<?php echo $untrans ?>px"></span></td><?php
			}

		?></tr><?php
	}
	?></tbody>
	<tfoot>
		<tr>
			<th>Total</th>
			<td class="translated"><?php echo $sumstat[1] ?></td>
			<td></td>
			<td class="fuzzy"><?php echo $sumstat[2] ?></td>
			<td></td>
			<td class="untranslated"><?php echo $sumstat[3] ?></td>
			<td></td>
			<td class="strcount"><?php echo $sumstat[5] ?></td>
			<td></td>
		</tr>
	</tfoot>
	</table><?php
}
else
{
	// TODO: ask user to select a language
	if (isset($lang))
	{
		?><h2>No available stats for language <?php echo $lang ?></h2><?php
	}
}

wesmere_emit_footer();
