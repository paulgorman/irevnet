<?

function htmlHeader($dataArray) {
	// $dataArray has:
	//  $title // 70 chars
	//  $description // 155 chars meta description call to action
	//  $keywords
	//  $image // full URL
	//  $url
	//  $jsinclude array of filenames
	//	$cssinclude array of filenames
	//  $bc array of breadcrumbs 'name' / 'url'
	?>
<!DOCTYPE html>
<html lang="en">
   <head>

		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta http-equiv="Content-Language" content="en" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="Cache-Control" content="private, max-age=5400, pre-check=5400" />
		<meta http-equiv="Expires" content="<?= date(DATE_RFC822,strtotime("1 day")); ?>" />
		<title><?= $dataArray['title']; ?></title>
		<meta name="robots" content="all" /> 
		<meta name="description" content="<?= $dataArray['description']; ?>" />
		<meta name="keywords" content="<?= $dataArray['keywords']; ?>" />
		<meta name="copyright" content="&copy; <?= date("Y"); ?> iRev.net" />
		<meta name="viewport" content="width=device-width; initial-scale=1.0" />
		<meta property="og:title" content="<?= $dataArray['title']; ?>" />
		<meta property="og:type" content="article" />
		<meta property="og:image" content="<?= $dataArray['image']; ?>" />
		<meta property="og:url" content="<?= $dataArray['url']; ?>" />
		<meta property="og:description" content="<?= $dataArray['description']; ?>" />
		<meta property="og:site_name" content="iRev.net" />
		<meta name="twitter:card" content="summary" />
		<meta name="twitter:title" content="<?= $dataArray['title']; ?>" />
		<meta name="twitter:description" content="<?= $dataArray['description']; ?>" />
		<meta name="twitter:image" content="<?= $dataArray['image']; ?>" />
		<link rel="author" href="/humans.txt">
		<link rel="canonical" href="<?= $dataArray['url']; ?>" />
		<link rel="stylesheet" type="text/css" href="/templates/css/responsiveboilerplate.css">
		<link rel="stylesheet" type="text/css" href="/templates/css/irev.css">
		<? if (isset($dataArray['css'])) { foreach ($dataArray['css'] as $css) { ?> 
			<link rel="stylesheet" href="/templates/css/<?= $css; ?>" />
		<? } } ?>
		<script src="/templates/js/jquery.js"></script>
		<? if (isset($dataArray['js'])) { foreach ($dataArray['js'] as $js) { ?> 
			<script type="text/javascript" src="/templates/js/<?= $js; ?>"></script>
		<? } } ?>
		<link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
		<link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
		<link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
		<link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
		<link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
		<link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
		<link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
		<link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
		<link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
		<link rel="icon" type="image/png" sizes="192x192"  href="/android-icon-192x192.png">
		<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
		<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
		<link rel="manifest" href="/manifest.json">
		<meta name="msapplication-TileColor" content="#ffffff">
		<meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
		<meta name="theme-color" content="#ffffff">
		<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
		<link rel="icon" href="/favicon.ico" type="image/x-icon">

	</head>
	<body>
		<div class="content">
	<?
}

function htmlMasthead($meta) {
	?>
		<header class="col12">
			<img class="logo" src="/templates/irev/irev-logo.png" title="Portfolio of Paul Gorman" alt="Paul Gorman / iRev.net" />
			<div class="swoosh"></div>
		</header>
	<?
}

function htmlNavigation($meta) {
	?>
			<nav class="nav col12">
				<ul>
					<li><a href="/" title="Home Page">Home</a></li>
					<li><a href="/portfolio/" title="Samples of Projects Portfolio">Portfolio</a></li>
					<li><a href="/about/" title="About Paul Gorman, Contact Info">About</a></li>
					<li><a href="/irev/" title="iRev.net Hosting Services">iRev.net</a></li>
				</ul>
			</nav>
			<nav class="menu col12">
				<ul>
					<li><img src="/templates/irev/more.png" />&nbsp;Menu
						<ul class="subMenu">
							<li><a href="/" title="Home Page">Home</a></li>
							<li><a href="/portfolio/" title="Samples of Projects Portfolio">Portfolio</a></li>
							<li><a href="/about/" title="About Paul Gorman, Contact Info">About</a></li>
							<li><a href="/irev/" title="iRev.net Hosting Services">iRev.net</a></li>
						</ul>
					</li>
				</ul>
			</nav>
	<?
}

function htmlDropDownNavigationFull($navdata) {
	// Use this dropdown if including subcategories and samples
	?>
		<!-- Standard Navigation -->
		<nav class="nav col12">
			<ul>
				<li><a href="/" title="Home Page">Home</a></li>
				<li><a href="/portfolio/" title="Samples of Projects Portfolio">Portfolio</a>
					<ul class="sub1">
						<?
							foreach (array_keys($navdata) as $categorykey) {
								foreach (array_keys($navdata[$categorykey]) as $subcatkey) {
									if ($subcatkey == 0) { 
										$line = sprintf("
											<li><a href=\"%s\" title=\"%s\">%s</a><span class=\"arrow\">&#x25b6;</span>
											<ul class=\"sub2\">",
											$navdata[$categorykey][0]['url'],
											$navdata[$categorykey][0]['description'],
											$navdata[$categorykey][0]['name']
										);
										echo $line;
									} else {
										$line = sprintf("
												<li><a href=\"%s\" title=\"%s\">%s</a></li>",
											$navdata[$categorykey][$subcatkey]['url'],
											$navdata[$categorykey][$subcatkey]['description'],
											$navdata[$categorykey][$subcatkey]['name']
										);
										echo $line;
									}
								}
								echo "</ul>\n";
							}
						?>
					</ul>
				</li>
				<li><a href="/about/" title="About Paul Gorman, Contact Info">About</a></li>
				<li><a href="/irev/" title="iRev.net Hosting Services">iRev.net</a></li>
			</ul>
		</nav>
		<!-- end Standard Navigation -->
		<!-- Mobile (compressed menu) Navigation -->
		<nav class="menu nav col12">
			<ul>
				<li><img src="/templates/irev/more.png">&nbsp;Menu
					<ul class="sub1">
						<li><a href="/" title="Home Page">Home</a></li>
						<li><a href="/Portfolio/" title="Samples of Projects Portfolio">Portfolio</a><span class="arrow">&#x25b6;</span>
							<!-- Sub-level 2 -->
							<ul class="sub2">
								<?
									foreach (array_keys($navdata) as $categorykey) {
										foreach (array_keys($navdata[$categorykey]) as $subcatkey) {
											if ($subcatkey == 0) { 
												$line = sprintf("
													<li><a href=\"%s\" title=\"%s\">%s</a><span class=\"arrow\">&#x25b6;</span><ul class=\"sub3\">",
													$navdata[$categorykey][0]['url'],
													$navdata[$categorykey][0]['description'],
													$navdata[$categorykey][0]['name']
												);
												echo $line;
											} else {
												$line = sprintf("
													<li><a href=\"%s\" title=\"%s\">%s</a></li>",
													$navdata[$categorykey][$subcatkey]['url'],
													$navdata[$categorykey][$subcatkey]['description'],
													$navdata[$categorykey][$subcatkey]['name']
												);
												echo $line;
											}
										}
										echo "</ul>\n";
									}
								?>
							</ul>
						</li>
						<li><a href="/about/" title="About Paul Gorman, Contact Info">About</a></li>
						<li><a href="/irev/" title="iRev.net Hosting Services">iRev.net</a></li>
					</ul>
				</li>
			</ul>
		</nav>
		<!-- end Mobile (compressed menu) Navigation -->
	<?
}

function htmlDropDownNavigationSingle($navdata) {
	// use this dropdown if only want primary categories
	?>
		<!-- Standard Navigation -->
		<nav class="nav col12">
			<ul>
				<li><a href="/" title="Home Page">Home</a></li>
				<li><a href="/portfolio/" title="Samples of Projects Portfolio">Portfolio</a>
					<ul class="sub1">
						<?
							foreach (array_keys($navdata) as $categorykey) {
								foreach (array_keys($navdata[$categorykey]) as $subcatkey) {
									if ($subcatkey == 0) { 
										$line = sprintf("
											<li><a href=\"%s\" title=\"%s\">%s</a>",
											$navdata[$categorykey][0]['url'],
											$navdata[$categorykey][0]['description'],
											$navdata[$categorykey][0]['name']
										);
										echo $line;
									}
								}
							}
						?>
					</ul>
				</li>
				<li><a href="/about/" title="About Paul Gorman, Contact Info">About</a></li>
				<li><a href="/irev/" title="iRev.net Hosting Services">iRev.net</a></li>
			</ul>
		</nav>
		<!-- end Standard Navigation -->
		<!-- Mobile (compressed menu) Navigation -->
		<nav class="menu nav col12">
			<ul>
				<li><img src="/templates/irev/more.png">&nbsp;Menu
					<ul class="sub1">
						<li><a href="/" title="Home Page">Home</a></li>
						<li><a href="/portfolio/" title="Samples of Projects Portfolio">Portfolio</a><span class="arrow">&#x25b6;</span>
							<!-- Sub-level 2 -->
							<ul class="sub2">
								<?
									foreach (array_keys($navdata) as $categorykey) {
										foreach (array_keys($navdata[$categorykey]) as $subcatkey) {
											if ($subcatkey == 0) { 
												$line = sprintf("
													<li><a href=\"%s\" title=\"%s\">%s</a></li>",
													$navdata[$categorykey][0]['url'],
													$navdata[$categorykey][0]['description'],
													$navdata[$categorykey][0]['name']
												);
												echo $line;
											}
										}
									}
								?>
							</ul>
						</li>
						<li><a href="/about/" title="About Paul Gorman, Contact Info">About</a></li>
						<li><a href="/irev/" title="iRev.net Hosting Services">iRev.net</a></li>
					</ul>
				</li>
			</ul>
		</nav>
		<!-- end Mobile (compressed menu) Navigation -->
	<?
}

function htmlWavesStart() {
	?>
	<!-- dark waves -->
	<div class="dark carousel col12">
	<?
}

function htmlWavesStartShort() {
	?>
	<!-- dark waves -->
	<div class="dark carouselFull col12">
	<?
}

function htmlWavesFullStart() {
	?>
	<!-- dark waves sample page -->
	<div class="dark carouselFull col12">
	<?
}

function htmlWavesShortStart() {
	?>
	<!-- dark waves -->
	<div class="dark carousel carouselShort col12">
	<?
}

function htmlBodyStart() {
	?>
	</div> <!-- / dark waves -->
	<!-- purple body -->
	<div class="purple col12"> 
	<?
}

function htmlBreadcrumb($meta) {
	echo "\t<div class=\"breadcrumb\">\n";
	echo "\t\t\t" . '<div class="breadcrumbitem"><a href="' . curServerURL() . '" title="Paul Gorman\'s Gallery of Projects, Photos, and Videos / iRev.net Web, Shell, and Email Hosting">Home</a></div>';
	foreach ($meta['breadcrumb'] as $bc) { 
		echo "\n\t\t\t". '<div class="breadcrumbitem"><a href="' . $bc['url'] . '" title="' . $bc['name'] . '">' . $bc['name'] . '</a></div>';
	}
	echo "\n\t\t</div><!-- /breadcrumb -->\n\n";
}


function htmlFooter($meta) {
	?>
			</div><!-- /purple body -->
			<footer class="col12 footergo">
				<p>
					Designed and <a href="https://github.com/paulgorman/irevnet">Coded</a> by Paul Gorman &copy; 2017<br>
					<a href="mailto:paul@irev.net">paul@irev.net</a> | (877) 463-1337<br>
				</p>
			</footer>
		</div>
	</body>
</html>
	<?
}

function htmlContent($content) {
	?>
		<div class="content" style="max-width: 960px; margin: 0px auto 0px auto;">
			<div class="col12 homeBody">
				<?= $content; ?>
			</div>
		</div>
		<br>
	<?
}
