<?php
// metrics.php -- the admin "Metrics" page (admin -> Metrics).
//
// What it answers: "did actual people look at my site, and what did they look at?"
//
// Vocabulary:
//   hit    one row in `sitehits` (one page load)
//   visit  one person's run of hits: same PHP session id, split wherever
//          there was more than 30 minutes of quiet
//   engaged visit  a visit that looked at 2 or more pages
//   robot  a user agent that is a search spider, uptime monitor, scraper
//          library, or that get_browser() says is a crawler
//
// Needs MariaDB 10.2+ (window functions). Read-only: no schema changes.

// How long a pause (minutes) makes the same session a new visit
define('METRICS_VISIT_GAP_MIN', 30);
// How far back to look. The lists and the "30 days" column use this.
define('METRICS_DAYS', 30);
// Your own visits (testing at home) are left out. Exact addresses, then address prefixes.
define('METRICS_MY_IPS', '63.224.44.134');
define('METRICS_MY_IP_PREFIXES', '192.168.1.');
// Only these hostnames count as "the website". The wildcard DNS sends every other *.irev.net here
// (Baidu invents subdomains), so those are tallied separately instead of as visitors.
define('METRICS_HOSTS', 'irev.net,www.irev.net,gallery.irev.net,paul.irev.net,media.irev.net');
// A "visit" longer than this many pages is a robot that keeps cookies, not a person.
define('METRICS_MAX_HUMAN_PAGES', 40);
// User agents containing any of these words are robots (case-insensitive, MariaDB REGEXP).
// get_browser() catches more; this is the cheap first pass.
define('METRICS_BOT_WORDS', 'bot|crawl|spider|slurp|scrape|probe|statping|uptime|monitor|audit|scanner|facebookexternalhit|bingpreview|ahrefs|semrush|mj12|petalbot|bytespider|seokicks|curl|wget|python|go-http|java/|okhttp|axios|libwww|httpclient|http-client|scrapy|headless|phantom');

function MetricsEsc($s) {
	global $conn;
	return mysqli_real_escape_string($conn, $s);
}

function MetricsRows($sql) {
	// run a query, return all rows. On failure return a string (the error) so the page can say why.
	global $conn;
	try {
		$result = mysqli_query($conn, $sql);
	} catch (Throwable $e) {
		return $e->getMessage();
	}
	if ($result === FALSE) {
		return mysqli_error($conn);
	}
	$rows = array();
	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$rows[] = $row;
	}
	return $rows;
}

function MetricsAgo($sqldate) {
	$s = time() - strtotime($sqldate);
	if ($s < 90) { return "just now"; }
	if ($s < 5400) { return round($s / 60) . " min ago"; }
	if ($s < 129600) { return round($s / 3600) . " hours ago"; }
	return round($s / 86400) . " days ago";
}

function MetricsDevice($ua) {
	// quick human-readable device from a user agent (no browscap needed)
	if (preg_match('/iPhone|iPad/i', $ua)) { return "iOS"; }
	if (preg_match('/Android/i', $ua)) { return "Android"; }
	if (preg_match('/Windows/i', $ua)) { return "Windows"; }
	if (preg_match('/Macintosh|Mac OS X/i', $ua)) { return "Mac"; }
	if (preg_match('/Linux|X11|FreeBSD/i', $ua)) { return "Linux/BSD"; }
	return "?";
}

function MetricsBotName($ua) {
	// a short label for a robot's user agent
	if ($ua === '' || $ua === NULL) { return "(no user agent)"; }
	if (preg_match('/([A-Za-z0-9_.-]*(bot|crawler|spider|probe|slurp)[A-Za-z0-9_.-]*)/i', $ua, $m)) { return $m[1]; }
	if (preg_match('/^([A-Za-z0-9_.-]+)\/[0-9.]+/', $ua, $m) && stripos($ua, 'Mozilla') !== 0) { return $m[1]; }
	return mb_substr($ua, 0, 40);
}

function MetricsNotMineSQL() {
	// SQL condition: the hit did not come from one of my own addresses
	$ips = array();
	foreach (explode(',', METRICS_MY_IPS) as $ip) { if (trim($ip) !== '') { $ips[] = "'" . MetricsEsc(trim($ip)) . "'"; } }
	$sql = count($ips) ? "hit_ip NOT IN (" . implode(",", $ips) . ")" : "1 = 1";
	foreach (explode(',', METRICS_MY_IP_PREFIXES) as $pre) {
		if (trim($pre) !== '') { $sql .= " AND hit_ip NOT LIKE '" . MetricsEsc(trim($pre)) . "%'"; }
	}
	return $sql;
}

function MetricsRealHostSQL() {
	// SQL condition: the hit was for the real website, not a wildcard subdomain or a proxy-style "GET http://other.host/" request
	$hosts = array();
	foreach (explode(',', METRICS_HOSTS) as $h) { $hosts[] = "'" . MetricsEsc(trim($h)) . "'"; }
	return "SUBSTRING_INDEX(hit_url, '/', 1) IN (" . implode(",", $hosts) . ") AND SUBSTRING(hit_url, LOCATE('/', hit_url), 2) <> '//'";
}

function MetricsHumanAgents($sinceSQL, $timeBudget = 2.0) {
	// Sort every distinct user agent seen since $sinceSQL into "looks like a person's browser" or robot.
	// A robot is: no user agent, a word from METRICS_BOT_WORDS, or something get_browser() (browscap) calls a crawler.
	// Done once per distinct user agent in PHP, because running the word list over every hit in SQL is slow.
	// get_browser() answers are remembered in a small file so each UA is only looked up once, because under
	// CGI every request is a fresh PHP and browscap.ini is slow to load.
	// Returns array('humans' => [user agent strings], 'crawlers' => how many get_browser() caught).
	$cache = array();
	$cachefile = sys_get_temp_dir() . '/irevnet-ua-cache.json';
	$haveBrowscap = function_exists('get_browser') && ini_get('browscap');
	if ($haveBrowscap) {
		$raw = @file_get_contents($cachefile);
		if ($raw !== FALSE) {
			$cache = json_decode($raw, TRUE);
			if (!is_array($cache)) { $cache = array(); }
		}
	}
	$rows = MetricsRows(sprintf(
		"SELECT user_agent, COUNT(*) AS n FROM sitehits WHERE hit_datetime >= '%s' AND hit_url LIKE '%%irev.net%%'
			AND COALESCE(user_agent,'') <> '' GROUP BY user_agent ORDER BY n DESC LIMIT 3000", MetricsEsc($sinceSQL)));
	if (!is_array($rows)) { return array('humans' => array(), 'crawlers' => 0); }
	$start = microtime(TRUE);
	$dirty = FALSE;
	$humans = array();
	$crawlers = 0;
	foreach ($rows as $row) {
		$ua = $row['user_agent'];
		if (preg_match('~' . METRICS_BOT_WORDS . '~i', $ua)) { continue; }
		if ($haveBrowscap) {
			$key = md5($ua);
			if (!isset($cache[$key])) {
				if ((microtime(TRUE) - $start) > $timeBudget) { $humans[] = $ua; continue; } // the rest get looked up on a later page load
				$b = @get_browser($ua, TRUE);
				$cache[$key] = (is_array($b) && !empty($b['crawler'])) ? 1 : 0;
				$dirty = TRUE;
			}
			if ($cache[$key]) { $crawlers++; continue; }
		}
		$humans[] = $ua;
	}
	if ($dirty) {
		if (count($cache) > 5000) { $cache = array_slice($cache, -4000, NULL, TRUE); }
		@file_put_contents($cachefile, json_encode($cache), LOCK_EX);
	}
	return array('humans' => $humans, 'crawlers' => $crawlers);
}

function MetricsHumanSQL($humans) {
	// SQL condition that is true for hits whose user agent was judged to be a person's browser
	if (count($humans) == 0) { return "1 = 0"; }
	$list = array();
	foreach ($humans as $ua) { $list[] = "'" . MetricsEsc($ua) . "'"; }
	return "COALESCE(user_agent,'') IN (" . implode(",", $list) . ")";
}

function MetricsVisits($sinceSQL, $humanSQL) {
	// One row per visit (people only), newest first.
	// Pages of one visit come back joined by newlines in `path`.
	mysqli_query($GLOBALS['conn'], "SET SESSION group_concat_max_len = 65536");
	$gap = (int)METRICS_VISIT_GAP_MIN;
	$sql = sprintf("
		WITH h AS (
			SELECT sessionid, hit_datetime, hit_url, referrer, user_agent,
				LAG(hit_datetime) OVER (PARTITION BY sessionid ORDER BY hit_datetime) AS prev
			FROM sitehits
			WHERE hit_datetime >= '%s' AND hit_url LIKE '%%irev.net%%' AND %s AND %s AND %s
		), v AS (
			SELECT *, SUM(prev IS NULL OR TIMESTAMPDIFF(MINUTE, prev, hit_datetime) > %d)
				OVER (PARTITION BY sessionid ORDER BY hit_datetime) AS visit_no
			FROM h
		)
		SELECT MIN(hit_datetime) AS started, MAX(hit_datetime) AS ended, COUNT(*) AS pages,
			MIN(user_agent) AS ua,
			GROUP_CONCAT(SUBSTRING(hit_url, LOCATE('/', hit_url)) ORDER BY hit_datetime SEPARATOR '\\n') AS path,
			SUBSTRING_INDEX(GROUP_CONCAT(
				CASE WHEN COALESCE(referrer,'') = '' OR referrer REGEXP '^https?://([a-z0-9-]+[.])?irev[.]net' THEN NULL ELSE referrer END
				ORDER BY hit_datetime SEPARATOR '\\n'), '\\n', 1) AS came_from
		FROM v GROUP BY sessionid, visit_no
		ORDER BY started DESC",
		MetricsEsc($sinceSQL), $humanSQL, MetricsNotMineSQL(), MetricsRealHostSQL(), $gap);
	return MetricsRows($sql);
}

function MetricsSentence($eng, $linked, $best, $label) {
	// plain-English summary of one time window
	// $eng = people who looked at 2+ pages, $linked = followed a link here and saw 1 page
	if ($eng == 0 && $linked == 0) { return "$label: no living visitors."; }
	if ($eng == 0) {
		return "$label: $linked " . ($linked == 1 ? "person" : "people") . " followed a link here, but nobody looked past the first page.";
	}
	$s = ($eng == 1) ? "$label: one person looked at $best pages" : "$label: $eng people looked around (the longest visit was $best pages)";
	if ($linked > 0) { $s .= ", and $linked more followed a link and left after one page"; }
	return $s . ".";
}

function AdminDisplaySiteStats() {
	$now = time();
	$since = DatePHPtoSQL(strtotime('-' . METRICS_DAYS . ' days'));
	$edges = array(
		"Today" => strtotime('today'),
		"7 days" => strtotime('-7 days'),
		(METRICS_DAYS . " days") => strtotime('-' . METRICS_DAYS . ' days'),
	);

	$agents = MetricsHumanAgents($since);
	$crawlers = $agents['crawlers'];
	$humanSQL = MetricsHumanSQL($agents['humans']);
	$visits = MetricsVisits($since, $humanSQL);
	if (!is_array($visits)) {
		?>
		<div class="metricsPage"><p class="metricsNote">Couldn't read the hit log: <?= htmlspecialchars($visits); ?><br>
		(This page needs MariaDB 10.2 or newer.)</p></div>
		<?php
		return;
	}

	// --- roll the visits up per window
	$win = array();
	foreach ($edges as $label => $t) {
		$win[$label] = array('engaged' => 0, 'linked' => 0, 'unknown' => 0, 'pageviews' => 0, 'best' => 0, 'robots' => 0, 'marathon' => 0, 'wild' => 0);
	}
	$pages = array();
	$referrers = array();
	$recent = array();
	$engagedTotal = 0;
	foreach ($visits as $v) {
		$t = strtotime($v['started']);
		$n = (int)$v['pages'];
		$ref = ($v['came_from'] === NULL) ? '' : $v['came_from'];
		foreach ($edges as $label => $edge) {
			if ($t >= $edge) {
				if ($n > METRICS_MAX_HUMAN_PAGES) {
					$win[$label]['marathon']++;
					continue; // too many pages to be a person
				}
				if ($n >= 2) {
					$win[$label]['engaged']++;
				} elseif ($ref !== '') {
					$win[$label]['linked']++;
				} else {
					$win[$label]['unknown']++;
					continue; // 1 page, no referrer: could be anyone (or any scraper), so don't count it as a person
				}
				$win[$label]['pageviews'] += $n;
				if ($n > $win[$label]['best']) { $win[$label]['best'] = $n; }
			}
		}
		if (($n < 2 && $ref === '') || $n > METRICS_MAX_HUMAN_PAGES) { continue; } // unknown 1-pagers and marathon robots: not people, not in the lists
		foreach (explode("\n", (string)$v['path']) as $p) {
			if ($p === '') { continue; }
			$p = preg_replace('/[?#].*$/', '', $p);
			$pages[$p] = ($pages[$p] ?? 0) + 1;
		}
		if ($ref !== '') {
			$host = parse_url($ref, PHP_URL_HOST);
			$host = preg_replace('/^www\./i', '', $host ?: $ref);
			$referrers[$host] = ($referrers[$host] ?? 0) + 1;
		}
		// the list: people who looked around, or arrived from a link somewhere
		if ($n >= 2 || $ref !== '') {
			$recent[] = $v;
		}
	}
	arsort($pages);
	arsort($referrers);

	// --- robots: everything the human filter left out
	$botrows = MetricsRows(sprintf(
		"SELECT COALESCE(user_agent,'') AS ua, COUNT(*) AS hits,
			SUM(hit_datetime >= '%s') AS t, SUM(hit_datetime >= '%s') AS w
			FROM sitehits
			WHERE hit_datetime >= '%s' AND hit_url LIKE '%%irev.net%%' AND %s AND %s AND NOT (%s)
			GROUP BY ua ORDER BY hits DESC",
		DatePHPtoSQL($edges["Today"]), DatePHPtoSQL($edges["7 days"]), MetricsEsc($since), MetricsNotMineSQL(), MetricsRealHostSQL(), $humanSQL));
	if (!is_array($botrows)) { $botrows = array(); }
	$labels = array_keys($edges);
	$bots = array();       // label => hits
	$botTotal = 0;
	foreach ($botrows as $b) {
		$name = MetricsBotName($b['ua']);
		$bots[$name] = ($bots[$name] ?? 0) + (int)$b['hits'];
		$botTotal += (int)$b['hits'];
		$win[$labels[0]]['robots'] += (int)$b['t'];
		$win[$labels[1]]['robots'] += (int)$b['w'];
		$win[$labels[2]]['robots'] += (int)$b['hits'];
	}
	arsort($bots);

	// --- wildcard hostnames (everything that isn't the real site)
	$wildrows = MetricsRows(sprintf(
		"SELECT SUBSTRING_INDEX(hit_url, '/', 1) AS host, COUNT(*) AS hits,
			SUM(hit_datetime >= '%s') AS t, SUM(hit_datetime >= '%s') AS w,
			SUM(SUBSTRING(hit_url, LOCATE('/', hit_url), 2) = '//') AS odd
			FROM sitehits
			WHERE hit_datetime >= '%s' AND hit_url LIKE '%%irev.net%%' AND %s AND NOT (%s)
			GROUP BY host ORDER BY hits DESC LIMIT 500",
		DatePHPtoSQL($edges["Today"]), DatePHPtoSQL($edges["7 days"]), MetricsEsc($since), MetricsNotMineSQL(), MetricsRealHostSQL()));
	if (!is_array($wildrows)) { $wildrows = array(); }
	$wild = array();
	$wildTotal = 0;
	foreach ($wildrows as $w) {
		$name = $w['host'];
		if (in_array(strtolower($name), array_map('trim', explode(',', METRICS_HOSTS)))) { $name .= " (proxy-style request)"; }
		$wild[$name] = (int)$w['hits'];
		$wildTotal += (int)$w['hits'];
		$win[$labels[0]]['wild'] += (int)$w['t'];
		$win[$labels[1]]['wild'] += (int)$w['w'];
		$win[$labels[2]]['wild'] += (int)$w['hits'];
	}
	?>
	<div class="metricsPage">

		<div class="metricsHeadline">
			<?php foreach ($labels as $label) { ?>
				<p><?= htmlspecialchars(MetricsSentence($win[$label]['engaged'], $win[$label]['linked'], $win[$label]['best'], $label)); ?></p>
			<?php } ?>
		</div>

		<div class="metricsCard">
			<div class="metricsHeader">At a glance</div>
			<table class="metricsTable">
				<thead><tr><th></th>
					<?php foreach ($labels as $label) { ?><th class="num"><?= htmlspecialchars($label); ?></th><?php } ?>
				</tr></thead>
				<tbody>
					<tr><td>People who looked at 2+ pages</td>
						<?php foreach ($labels as $l) { ?><td class="num strong"><?= $win[$l]['engaged']; ?></td><?php } ?></tr>
					<tr><td>Followed a link here, saw 1 page</td>
						<?php foreach ($labels as $l) { ?><td class="num"><?= $win[$l]['linked']; ?></td><?php } ?></tr>
					<tr><td>Pages those people viewed</td>
						<?php foreach ($labels as $l) { ?><td class="num"><?= $win[$l]['pageviews']; ?></td><?php } ?></tr>
					<tr class="muted"><td>1 page, no referrer (mostly scrapers)</td>
						<?php foreach ($labels as $l) { ?><td class="num"><?= number_format($win[$l]['unknown']); ?></td><?php } ?></tr>
					<tr class="muted"><td>Marathon visits, over <?= METRICS_MAX_HUMAN_PAGES; ?> pages (robots)</td>
						<?php foreach ($labels as $l) { ?><td class="num"><?= number_format($win[$l]['marathon']); ?></td><?php } ?></tr>
					<tr class="muted"><td>Robot &amp; monitor hits (ignored)</td>
						<?php foreach ($labels as $l) { ?><td class="num"><?= number_format($win[$l]['robots']); ?></td><?php } ?></tr>
					<tr class="muted"><td>Hits to wildcard hostnames (ignored)</td>
						<?php foreach ($labels as $l) { ?><td class="num"><?= number_format($win[$l]['wild']); ?></td><?php } ?></tr>
				</tbody>
			</table>
			<p class="metricsNote">A visit is one browser's run of pages; 30 minutes of quiet starts a new one.
				A single page with no referrer can't be told apart from a scraper pretending to be a browser, so it isn't counted as a person. Your own address is left out entirely.</p>
		</div>

		<div class="metricsCard metricsWide">
			<div class="metricsHeader">Recent visits (last <?= METRICS_DAYS; ?> days)</div>
			<?php if (count($recent) == 0) { ?>
				<p class="metricsNote">No visits with 2+ pages or an outside referrer in this period.</p>
			<?php } else { ?>
			<table class="metricsTable metricsVisits">
				<thead><tr><th>When</th><th class="num">Pages</th><th>Path</th><th>Came from</th><th>On</th></tr></thead>
				<tbody>
				<?php foreach (array_slice($recent, 0, 40) as $v) {
					$path = array_filter(explode("\n", (string)$v['path']), 'strlen');
					$ref = ($v['came_from'] === NULL) ? '' : $v['came_from'];
					$rhost = $ref === '' ? '(direct)' : preg_replace('/^www\./i', '', parse_url($ref, PHP_URL_HOST) ?: $ref);
					?>
					<tr>
						<td class="nowrap" title="<?= htmlspecialchars($v['started']); ?>"><?= htmlspecialchars(MetricsAgo($v['started'])); ?></td>
						<td class="num"><?= (int)$v['pages']; ?></td>
						<td class="metricsPath"><?php
							$out = array();
							foreach ($path as $p) {
								$p = preg_replace('/[?#].*$/', '', $p);
								$out[] = "<a href='" . htmlspecialchars($p, ENT_QUOTES) . "'>" . htmlspecialchars($p === '/' ? '/ (home)' : $p) . "</a>";
							}
							echo implode(" &rarr; ", $out);
						?></td>
						<td><?= htmlspecialchars($rhost); ?></td>
						<td><?= htmlspecialchars(MetricsDevice($v['ua'])); ?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
			<?php if (count($recent) > 40) { ?>
				<p class="metricsNote">Showing the newest 40 of <?= count($recent); ?>.</p>
			<?php } ?>
			<?php } ?>
		</div>

		<div class="metricsCard">
			<div class="metricsHeader">Pages people looked at (<?= METRICS_DAYS; ?> days)</div>
			<table class="metricsTable">
				<tbody>
				<?php foreach (array_slice($pages, 0, 12, TRUE) as $p => $n) { ?>
					<tr><td class="metricsPath"><a href="<?= htmlspecialchars($p, ENT_QUOTES); ?>"><?= htmlspecialchars($p === '/' ? '/ (home)' : $p); ?></a></td><td class="num"><?= $n; ?></td></tr>
				<?php } if (count($pages) == 0) { ?><tr><td class="muted">Nothing yet.</td></tr><?php } ?>
				</tbody>
			</table>
		</div>

		<div class="metricsCard">
			<div class="metricsHeader">Where people came from (<?= METRICS_DAYS; ?> days)</div>
			<table class="metricsTable">
				<tbody>
				<?php foreach (array_slice($referrers, 0, 12, TRUE) as $host => $n) { ?>
					<tr><td><?= htmlspecialchars($host); ?></td><td class="num"><?= $n; ?></td></tr>
				<?php } if (count($referrers) == 0) { ?><tr><td class="muted">Nobody followed a link here.</td></tr><?php } ?>
				</tbody>
			</table>
		</div>

		<div class="metricsCard metricsWide">
			<details>
				<summary class="metricsHeader">Robots &amp; monitors (<?= METRICS_DAYS; ?> days: <?= number_format($botTotal); ?> hits, <?= count($bots); ?> kinds) &mdash; click to open</summary>
				<table class="metricsTable">
					<tbody>
					<?php foreach (array_slice($bots, 0, 25, TRUE) as $name => $n) { ?>
						<tr><td><?= htmlspecialchars($name); ?></td><td class="num"><?= number_format($n); ?></td></tr>
					<?php } ?>
					</tbody>
				</table>
				<?php if (count($bots) > 25) { ?><p class="metricsNote">...and <?= count($bots) - 25; ?> smaller ones.</p><?php } ?>
				<p class="metricsNote">Spotted by a word list (bot, crawl, probe, ...)<?= ini_get('browscap') ? "; get_browser() flagged " . $crawlers . " more user agent" . ($crawlers == 1 ? "" : "s") : "; get_browser() isn't configured (php.ini browscap)"; ?>.</p>
			</details>
		</div>

		<div class="metricsCard metricsWide">
			<details>
				<summary class="metricsHeader">Wildcard hostnames (<?= METRICS_DAYS; ?> days: <?= number_format($wildTotal); ?> hits, <?= count($wild); ?> names) &mdash; click to open</summary>
				<table class="metricsTable">
					<tbody>
					<?php foreach (array_slice($wild, 0, 25, TRUE) as $name => $n) { ?>
						<tr><td><?= htmlspecialchars($name); ?></td><td class="num"><?= number_format($n); ?></td></tr>
					<?php } ?>
					</tbody>
				</table>
				<?php if (count($wild) > 25) { ?><p class="metricsNote">...and <?= count($wild) - 25; ?> more names.</p><?php } ?>
				<p class="metricsNote">Anything that isn't <?= htmlspecialchars(METRICS_HOSTS); ?> (the wildcard DNS sends it all here).</p>
			</details>
		</div>

	</div> <!-- class="metricsPage" -->
	<?php
}
