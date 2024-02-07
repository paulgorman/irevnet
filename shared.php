<?
/****************************************
**  Presence's iRev.net
**  Portfolio Gallery and Hosting Services
**
**  Code: Presence
**
**  Last Edit: 20190508
****************************************/

function Init() {
	global $conn;
	global $dirlocation;
	global $pagination;
	global $videowidth;
	error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
//	error_reporting(0);
	date_default_timezone_set('America/Los_Angeles');
	session_start(); // I want to track people thru the site
	if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
		// last request was more than 60 minates ago (3600 seconds)
		session_destroy();   // destroy session data in storage
		session_unset();     // unset $_SESSION variable for the runtime
	} elseif (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] < 3600)) {
		// I am a returning visitor within last hour, update per-page timer
		$_SESSION['last_move'] = $_SESSION['last_activity']; // testing how long page to page
		$_SESSION['last_activity'] = time(); // update last activity time stamp
	} else {
		// I am a brand new visitor
		$_SESSION['last_activity'] = time(); // update last activity time stamp
		$_SESSION['last_move'] = time(); // testing how long page to page
	}
	// lets count how many pages visitor's looked at
	isset($_SESSION['count']) ? $_SESSION['count']++ : $_SESSION['count'] = 0;
	if (!(isset($_SESSION['obfuscate']))) {
		$_SESSION['obfuscate'] = 0; // set this key to something so no complaints
	}
	$dirlocation = "/home/presence/irev.net";	// no trailing slash. // default, overridden in db.php
	require_once("db.php"); // $host, $user, $pass, $db name, $dirlocation no trailing slash
	//mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	mysqli_report(MYSQLI_REPORT_OFF);
	$conn = mysqli_connect($host, $user, $pass, $db, 3306) or die(mysqli_connect_error());
	//printf("Success... %s\n", mysqli_get_host_info($conn));
	$pagination = "20";	// number of entries per "page"
	$videowidth = 600;
	//debugShow();
}

function Pages() {
	$pages = array(
		"home" => "Home",
		"about-top" => "About (Top)",
		"about-bottom" => "About (Bottom)",
		"irev-top" => "iRev.net (Top)",
		"irev-bottom" => "iRev.net (Bottom)",
		"now" => "Now"
	);
	return ($pages);
}

function RecordHit() {
	global $conn;
	if (isset($_SERVER['HTTP_REFERER'])) {
		$referrer = $_SERVER['HTTP_REFERER'];
	} else {
		$referrer = "";
	}
	if (isset($_SERVER['REMOTE_HOST'])) {
		$remote_host = $_SERVER['REMOTE_HOST'];
	} else {
		$remote_host = "";
	}
	$query = sprintf("INSERT INTO `sitehits` (`hit_datetime`, `hit_ip`, `hit_addr`, `hit_url`, `referrer`, `user_agent`, `sessionid`, `sesscount`) values ('%s','%s','%s','%s','%s','%s', '%s', %s);",
		mysqli_real_escape_string($conn, DatePHPtoSQL(time())),
		mysqli_real_escape_string($conn, $_SERVER['REMOTE_ADDR']),
		mysqli_real_escape_string($conn, $remote_host),
		mysqli_real_escape_string($conn, $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']),
		mysqli_real_escape_string($conn, $referrer),
		mysqli_real_escape_string($conn, $_SERVER['HTTP_USER_AGENT']),
		mysqli_real_escape_string($conn, session_id()),
		mysqli_real_escape_string($conn, $_SESSION['count'])
	);
	$result = mysqli_query($conn, $query);
}

function DateSQLtoPHP($mysqldate) {
	// mysql's datetime to PHP seconds-since-epoch date format
	return (strtotime($mysqldate));
}

function DatePHPtoSQL($phpdate) {
	// php's seconds-since-epoch timestamp to MySQL
	return (date('Y-m-d H:i:s', $phpdate));
}

function DebugShow() {
	echo "<div class='Debug'>";
	echo "You wanted to look at: ";
	if (isset($_REQUEST['url'])) {
		printf ("Page: %s<br>\nSpecifically: %s<br>\nCount: %s<br>\nLast Activity:%s seconds<br>\n",
			$_REQUEST['page'],
			$_REQUEST['url'],
			$_SESSION['count'],
			(time() - $_SESSION['last_move'])
		);
	} else {
		printf ("Page: %s<br>\nCount: %s<br>\nLast Activity:%s seconds<br>\n",
			$_REQUEST['page'],
			$_SESSION['count'],
			(time() - $_SESSION['last_move'])
		);
	}
	echo "</div>";
}

function isAdmin() {
	if ($_SESSION['is_admin'] === TRUE) {
		return TRUE;
	}
}

function AskForAdmin() {
	require_once("templates/admin.php");
	//$_SESSION['is_admin'] = TRUE;
	htmlAdminLogin(NULL);
}

function ConfirmLogin() {
	global $conn;
	require_once("templates/admin.php");
	$username = preg_replace("/[^A-Za-z]/", '', strtolower(trim(htmlspecialchars(strip_tags($_REQUEST['username'])))));
	$query = sprintf(
		"SELECT `username`,`password` FROM `admins` WHERE `username` = '%s'",
		mysqli_real_escape_string($conn,$username)
	);
	$result = mysqli_query($conn,$query);
	if (mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		$passwordGuess = trim(htmlspecialchars(strip_tags($_REQUEST['password'])));
		if (ValidatePassword($passwordGuess,$row['password'])) {
			$_SESSION['is_admin'] = TRUE;
			// XXX: half-ass way to log admin logins
			error_log("Admin Page Login Success: $username" , 0); 
			header("Location: https://". $_SERVER['HTTP_HOST'] ."/admin/", TRUE, 302);
		} else {
			error_log("Admin Page Login Password Failure: $username" , 0); 
			htmlAdminLogin("Invalid Password");
		}
	} else {
		// XXX: I know, but it's my boss, I gotta help him a little when he typos his username
		error_log("Admin Page Login Username Failure: $username" , 0); 
		htmlAdminLogin("Invalid Username");
	}
}

function SamplePage() {
	global $conn;
	require_once("templates/header.php");
	require_once("templates/samplepage.php");
	require_once("templates/FWDconstructors.php"); // shit to make grid and carousel go
	require_once("templates/Parsedown/Parsedown.php");
	if (isEmpty($_REQUEST['url'])) {
		// Whoops, no sample name in the URL, show the categories
		header("Location: https://". $_SERVER['HTTP_HOST'] ."/categories", TRUE, 302);
	} else {
		$url = MakeURL(strtolower(trim($_REQUEST['url'])));
		$sampleinfo = getSampleInfoFromURL($url);
		if (!is_countable($sampleinfo)) {
			// no exact match
			$query = "SELECT `url`,`alt_url`,`name` FROM `samples` WHERE `is_active` = 1";
			$result = mysqli_query($conn,$query);
			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
				$urls[] = $row['url'];
				//$urls[] = $row['alt_url'];
			}
			$closestsample = AltClosestWord($_REQUEST['url'],$urls);
			$sampleinfo = getSampleInfoFromURL($closestsample);
			// show multiple matching sample chooser
			// XXX: Not Done
		}
		if (count($sampleinfo) === 1) {
			$sampleinfo = obfuscatesampleInfo($sampleinfo);
			$sampleinfo = insertBreadCrumb($sampleinfo);
			$meta = getSampleMetaTags($sampleinfo);
			$meta['css'][] = "Rb-ui.css";
			$meta['js'][] = "jwplayer/jwplayer.js";
			$meta['js'][] = "facebook.js";
			$meta['js'][] = "more.js";
			$meta['js'][] = "justifiedGallery.js";
			//$meta['js'][] = "FWDGrid.js";
			htmlHeader($meta);
			htmlMasthead($meta);
			htmlDropDownNavigationSingle(gatherNavData());
			//htmlDropDownNavigationFull(gatherNavData());
			htmlWavesFullStart();
			htmlBreadcrumb($meta);
			htmlSamplePageTop($sampleinfo);
			htmlBodyStart();
			htmlStylesTags($sampleinfo);
			//htmlSamplePageBottom($sampleinfo);
			htmlSamplePageBottomGallery($sampleinfo);
			htmlSamplePageGalleryJS();
			htmlFooter($meta);
			//fwdConsGrid(); // dump this stuff in at the bottom of html
		} elseif (count($sampleinfo) === 0) {
			//header("Location: https://". $_SERVER['HTTP_HOST'] ."/categories", TRUE, 302);
			$closestsample = AltClosestWord($_REQUEST['url'],$urls);
			$sampleinfo = getSampleInfoFromURL($closestsample);
		}
	}
}

function getSampleMetaTags($sampleinfo) {
	$meta = array();
	// meta keywords are name, categories, styles, locations
	$meta['keywords'] = "iRev.net, iRev, Inner Revolution Networks, ";
	foreach (array_keys($sampleinfo) as $key) {
		$meta['keywords'] .= $sampleinfo[$key]['name'] . ", ";
		if (is_countable($sampleinfo[$key]['categories'])) {
			foreach (array_keys($sampleinfo[$key]['categories']) as $subkey) {
				$meta['keywords'] .= $sampleinfo[$key]['categories'][$subkey] . ", ";
			}
		}
		foreach (array_keys($sampleinfo[$key]['styles']) as $subkey) {
			$meta['keywords'] .= $sampleinfo[$key]['styles'][$subkey] . ", ";
		}
		foreach (array_keys($sampleinfo[$key]['locations']) as $subkey) {
			$meta['keywords'] .= "'" . $sampleinfo[$key]['locations'][$subkey] . "', ";
		}
	}
	$meta['keywords'] = substr($meta['keywords'], 0, -2) . ".";
	// meta description is: name - slug (cat,egor,ies)
	$meta['description'] = "";
	foreach (array_keys($sampleinfo) as $key) {
		$meta['description'] .= $sampleinfo[$key]['name'];
		$meta['description'] .= " - ";
		$meta['description'] .= $sampleinfo[$key]['slug'];
		$meta['description'] .= " (";
		foreach (array_keys($sampleinfo[$key]['styles']) as $subkey) {
			$meta['description'] .= $sampleinfo[$key]['styles'][$subkey] . ", ";
		}
		$meta['description'] = substr($meta['description'], 0, -2) . ")";
		$meta['description'] .= " / ";
	}
	$meta['description'] = substr($meta['description'], 0, -3);
	// title is: Presence name - slug (sty,les)
	$meta['title'] = "iRev.net ";
	foreach (array_keys($sampleinfo) as $key) {
		$meta['title'] .= $sampleinfo[$key]['name'];
		$meta['title'] .= " - ";
		$meta['title'] .= $sampleinfo[$key]['slug'];
		if (is_countable($sampleinfo[$key]['categories'])) {
			$meta['title'] .= " (";
			foreach (array_keys($sampleinfo[$key]['categories']) as $subkey) {
				$meta['title'] .= $sampleinfo[$key]['categories'][$subkey] . ", ";
			}
			$meta['title'] = substr($meta['title'], 0, -2) . ")";
		}
		$meta['description'] .= " / ";
	}
	$meta['description'] = substr($meta['description'], 0, -3);
	$meta['url'] = CurPageURL();
	// breadcrumb
	// step 0 : categories
	// step 1 : category name
	// step 2 : sample name
	$oid = $sampleinfo[key($sampleinfo)]['oid'];
	if (preg_match("/sample[s]?/",$_REQUEST['page'])) {
		// this is an sample page, not an event media page
		$meta['breadcrumb'][0]['name'] = "Portfolio Samples";
		$meta['breadcrumb'][0]['url'] = CurServerURL() . "portfolio";
	}
	if (count($sampleinfo) === 1) {
		if ($sampleinfo[$oid]['category']) {
			$meta['breadcrumb'][1]['name'] = $sampleinfo[$oid]['category'];
			$meta['breadcrumb'][1]['url'] = curServerURL() . "category/" . $sampleinfo[$oid]['caturl'];
		}
		$meta['breadcrumb'][2]['name'] = $sampleinfo[$oid]['name'];
		$meta['breadcrumb'][2]['url'] = curPageURL();
	} else {
		$meta['breadcrumb'][1]['name'] = "Selection: ";
		$meta['breadcrumb'][1]['url'] = curPageURL();
		foreach (array_keys($sampleinfo) as $key) {
			$meta['breadcrumb'][1]['name'] .= $sampleinfo[$key]['name'];
			$meta['breadcrumb'][1]['name'] .= ", ";
		}
		$meta['breadcrumb'][1] = substr($meta['breadcrumb'][1], 0, -2);
	}	
	// image
	if (isset($sampleinfo[$oid]['media']['filename'])) {
		$meta['image']  = CurServerUrl() . "i/sample/";
		$meta['image'] .= $sampleinfo[$oid]['media']['filename'][key($sampleinfo[$oid]['media']['filename'])];
	}
	return ($meta);

}

function obfuscateSampleInfo($sampleinfo) {
	// determine if it is necessarty to obfuscate any sample projects in this array, munge if so, and send the array back
	global $conn;
	foreach ($sampleinfo as $key => $blah) {
		$obfuscateMe = 0;
		// sample's own entry forced to obfuscate?
		if ((int)$sampleinfo[$key]['use_display_name'] === 1) {
			$obfuscateMe++;
		}
		// sample's category selected to obfuscate?
		$query = sprintf(
			"SELECT `force_display_names` FROM `categories` WHERE `cid` = '%s'",
			mysqli_real_escape_string($conn,$sampleinfo[$key]['cid'])
		);
		$row = mysqli_fetch_array(mysqli_query($conn,$query), MYSQLI_ASSOC);
		if ($row['force_display_names'] === "Y") {
			$obfuscateMe++;
		}
		if ($row['force_display_names'] === "N") {
			// AH HA, no, this special category MUST show full real name!
			$obfuscateMe = -9;
		}
		// was the obfuscate session cookie previously set?
		if ($_SESSION['obfuscate'] == "1") {
			$obfuscateMe++;
		}
		// incoming URL used obfuscated sample name?
		$url = MakeURL(strtolower(trim($_REQUEST['url'])));
		$query = sprintf(
			"SELECT `alt_url` FROM `samples` WHERE `alt_url` = '%s'",
			mysqli_real_escape_string($conn,$url)
		);
		$result = mysqli_query($conn, $query);
		if (mysqli_num_rows($result) > 0) {
			$obfuscateMe++;
			// XXX: Oh hey, set a cookie on this to obfuscate further samples during this user's visit
			// XXX: Since this user was originally given an obfuscated URL
			$_SESSION['obfuscate'] = 1;
		} else {
			// XXX: nevermind, they found a legit url somehow, remove that cookie
			if ($_SESSION['obfuscate'] == "1") {
				$obfuscateMe--;
				$_SESSION['obfuscate'] = 0;
			}
		}
		if ($obfuscateMe > 0) {
			$sampleinfo[$key]['bio'] = str_ireplace($sampleinfo[$key]['name'], $sampleinfo[$key]['display_name'], $sampleinfo[$key]['bio']);
			$sampleinfo[$key]['slug'] = str_ireplace($sampleinfo[$key]['name'], $sampleinfo[$key]['display_name'], $sampleinfo[$key]['slug']);
			$sampleinfo[$key]['name'] = $sampleinfo[$key]['display_name'];
			$sampleinfo[$key]['url'] = $sampleinfo[$key]['alt_url'];
		}
	}
	return ($sampleinfo);
}

function getSampleInfoFromURL($url) {
	global $conn;
	require_once("templates/Parsedown/Parsedown.php");
	$samplenames = array(); // to find the nearest sample name
	// search to find exact full URL match first
	$query = sprintf(
		"SELECT * FROM `samples` WHERE (`url` = '%s' OR `alt_url` = '%s') AND `is_active` = 1",
		mysqli_real_escape_string($conn, $url),
		mysqli_real_escape_string($conn, $url)
	);
	$result = mysqli_query($conn, $query);
	if (mysqli_num_rows($result)) {
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$samplenames[$row['oid']] = $row;
		}
	}
	// check category and get the hell outta here if we can
	if (count($samplenames) === 1) {
		$oid = key($samplenames);
		// has the user come in via a category page listing?
		$samplenames[key($samplenames)]['cid'] = getSampleCategory($oid);
		// Get other categories, styles, and locations
		$query = sprintf(
			"SELECT `categories`.`cid`, `categories`.`category` FROM `categories`
			 LEFT OUTER JOIN `samplecategories` ON `samplecategories`.`cid` = `categories`.`cid`
			 WHERE `samplecategories`.`oid` = '%s' AND `categories`.`published` = 1",
			mysqli_real_escape_string($conn,$oid)
		);
		$result = mysqli_query($conn,$query);
		while ($row = mysqli_fetch_assoc($result)) {
			$samplenames[$oid]['categories'][$row['cid']] = $row['category'];
		}
		$query = sprintf(
			"SELECT `styles`.`sid`, `styles`.`name` FROM `styles`
			 LEFT OUTER JOIN `samplestyles` ON `samplestyles`.`sid` = `styles`.`sid`
			 WHERE `samplestyles`.`oid` = '%s'",
			mysqli_real_escape_string($conn,$oid)
		);
		$result = mysqli_query($conn,$query);
		while ($row = mysqli_fetch_assoc($result)) {
			$samplenames[$oid]['styles'][$row['sid']] = $row['name'];
		}
		$query = sprintf(
			"SELECT `locations`.`lid`, `locations`.`city`, `locations`.`state` FROM `locations`
			 LEFT OUTER JOIN `samplelocations` ON `samplelocations`.`lid` = `locations`.`lid`
			 WHERE `samplelocations`.`oid` = '%s' ORDER BY `locations`.`state`",
			mysqli_real_escape_string($conn,$oid)
		);
		$result = mysqli_query($conn,$query);
		while ($row = mysqli_fetch_assoc($result)) {
			$samplenames[$oid]['locations'][$row['lid']] = $row['city'] . ", " . StateCodeToName($row['state']);
		}
		$query = sprintf(
			"SELECT * FROM `media` WHERE `oid` = %s ORDER BY `is_highlighted` DESC, `vidlength` ASC", 
			mysqli_real_escape_string($conn,$oid)
		);
		$result = mysqli_query($conn,$query);
		while ($row = mysqli_fetch_assoc($result)) {
			$samplenames[$oid]['media']['mid'][$row['mid']] = $row['mid'];
			$samplenames[$oid]['media']['name'][$row['mid']] = $row['name'];
			$samplenames[$oid]['media']['filetype'][$row['mid']] = $row['filetype'];
			$samplenames[$oid]['media']['filename'][$row['mid']] = $row['filename'];
			$samplenames[$oid]['media']['thumbwidth'][$row['mid']] = $row['thumbwidth'];
			$samplenames[$oid]['media']['thumbheight'][$row['mid']] = $row['thumbheight'];
			$samplenames[$oid]['media']['height'][$row['mid']] = $row['height'];
			$samplenames[$oid]['media']['width'][$row['mid']] = $row['width'];
			$samplenames[$oid]['media']['vidlength'][$row['mid']] = $row['vidlength'];
			$samplenames[$oid]['media']['is_highlighted'][$row['mid']] = $row['is_highlighted'];
			$samplenames[$oid]['media']['viewable'][$row['mid']] = $row['viewable'];
			$samplenames[$oid]['media']['published'][$row['mid']] = DateSQLtoPHP($row['published']);
		}
		// prepare the sample bio for mobile and desktop view
		// XXX Hardcoded at 850 characters
		// Split the sample Bio into Nice Fitting Space and include "More" link to display more text
		// XXX FixMe:  If just one dangling sentence remains, just include it please. 

// XXX: Presence 20180403 TEMP RESTORE ME PLZ AFTER A WEEK
// XXX: Presence 20190508 or maybe not.  I dunno yet.
		//if (isMobileDev() > 0) {
		if (false) {
			$samplenames[$oid]['bio'] = Parsedown::instance()->parse(htmlspecialchars_decode($samplenames[$oid]['bio']));
		} else {
			$bio = $samplenames[$oid]['bio'];
			$sentences = preg_split('/((?<=[.?!])\s+(?=[a-z]))/i', $bio, NULL, PREG_SPLIT_DELIM_CAPTURE); // break only on complete sentences
			$length = 0;
			$firstpart = "";
			$secondpart = "";
			foreach ($sentences as $sentence) {
				$length = strlen($sentence) + $length;
				if ($length < 900) {
					$firstpart .= $sentence ." ";
				} else {
					$secondpart .= $sentence . " ";
				}
			}
			if (!isEmpty($secondpart)) {
				$firstpart = Parsedown::instance()->parse(htmlspecialchars_decode($firstpart));
				$firstpart .= "<a href=\"#\" id=\"continued-show\" class=\"showLink\" onclick=\"showHide('continued');return false;\">More &#9660;</a>";
				$secondpartparsed = "<div style=\"margin-top: 6px;\" id=\"continued\" class=\"more\">\n";
				$secondpartparsed .= Parsedown::instance()->parse($secondpart);
				$secondpartparsed .= "\n</div>";
				$samplenames[$oid]['bio'] = $firstpart . $secondpartparsed;
			} else {
				$samplenames[$oid]['bio'] = Parsedown::instance()->parse(htmlspecialchars_decode($firstpart));
			}

		}
		mysqli_free_result($result);
		return ($samplenames);
	}
	//$closestsampleFromRequest = ClosestWord($url,$samplenames);
}

function insertBreadCrumb($sampleinfo) {
	// slap in the breadcrumbs for each sample in the array
	foreach (array_keys($sampleinfo) as $key) {
		if (preg_match("/event[s]?/",$_REQUEST['page'])) {
			$sampleinfo[$key]['category'] = "Event";
			$sampleinfo[$key]['caturl'] = "events";
		} else {
			$catarray = getCategoryBreadcrumb($sampleinfo[$key]['cid']);
			$sampleinfo[$key]['category'] = $catarray['category'];
			$sampleinfo[$key]['caturl'] = $catarray['url'];
		}
	}
	return ($sampleinfo);
}

function getCategoryBreadcrumb($cid) {
	// collect an incoming CID, return the URL and Name plz.
	global $conn;
	$query = sprintf(
		"SELECT `category`,`url` FROM `categories` WHERE `cid` = '%s'",
		mysqli_real_escape_string($conn,$cid)
	);
	$result = mysqli_query($conn, $query);
	$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
	return ($row);
}

function getSampleCategory($oid) {
	global $conn;
	// from looking at the sample id, return the category ID or null
	$cid = NULL;
	if ( (isset($_SESSION['category'])) && (strlen(preg_replace("/[^0-9]/","",$_SESSION['category'])) >= 1 )) {
		// did the web visitor pass through a category listing page?
		$sess_cid = preg_replace("/[^0-9]/","",$_SESSION['category']);
		// get all categories sample listed under
		$query = sprintf(
			"SELECT `categories`.`cid` FROM `categories` 
			 LEFT OUTER JOIN `samplecategories` ON `categories`.`cid` = `samplecategories`.`cid` 
			 WHERE `samplecategories`.`oid` = '%s' AND `categories`.`published` = 1 
			 ORDER BY `categories`.`is_highlighted` DESC, `categories`.`category` ASC",
			mysqli_real_escape_string($conn,$oid)
		);
		$result = mysqli_query($conn,$query);
		$categories = array();
		$j = 0; // I want the first category set aside plz
		if (mysqli_num_rows($result)) {
			while ($row = mysqli_fetch_assoc($result)) {
				$categories[$j] = $row['cid'];
				if ($row['cid'] == $sess_cid)
					$cid = $sess_cid;
				}
				$j++;
			} 
		if (isEmpty($cid)) {
			// wrong CID in session cookie, so use first category from the database
			$cid = $categories[0];
			$_SESSION['category'] = $cid;
		}
	} else {
		// no category was in the session, prolly a direct link, so go pick a category for this one sample
		$query = sprintf(
			"SELECT `categories`.`cid` FROM `categories` 
			 LEFT OUTER JOIN `samplecategories` ON `categories`.`cid` = `samplecategories`.`cid` 
			 WHERE `samplecategories`.`oid` = '%s' AND `categories`.`published` = 1 ORDER BY `categories`.`is_highlighted` DESC, `categories`.`category` ASC
			 LIMIT 0,1",
			mysqli_real_escape_string($conn,$oid)
		);
		$result = mysqli_query($conn, $query);
		if (mysqli_num_rows($result)) {
			$cid = mysqli_fetch_array($result, MYSQLI_ASSOC)['cid'];
		}
	}
	return($cid);
}

function getSampleSubCategory ($oid) {
	global $conn;
	$subid = NULL;
	if ($strlen(preg_replace("/[^0-9]/","",$_SESSION['subcategory'])) >= 1) {
		$sess_subid = preg_replace("/[^0-9]/","",$_SESSION['subcategory']);
		$query = sprintf(
			"SELECT `subcategories`.`subid` FROM `subcategories`
			 LEFT OUTER JOIN `samplesubcategories` ON `subcategories`.`subid` = `samplesubcategories`.`subid`
			 WHERE `samplesubcategories`.`oid` = '%s' AND `subcategories`.`published` = 1 ORDER BY `subcategories`.`subcategory` ASC",
			mysqli_real_escape_string($conn,$oid)
		);
		// XXX: sure great, but what about if that subcat is in the parent's cat??
	}
}

function FaceBookLike($sampleinfo) {
	$url = CurPageURL();                                                                                                                                                         
	// if they don't mind tracking, and the sample is searchable, show a facebook like button
	if (getDntStatus() === FALSE && $sampleinfo['is_searchable'] == 1) {
		?>
			<div id="fb-root"></div>
			<div style="margin-bottom: 10px;" class="fb-like" data-colorscheme="dark" data-href="<?= $url; ?>" data-width="320" data-layout="standard" data-action="like" data-show-faces="false" data-share="true"></div>
		<?
	}
}

function CategoriesList() {
	global $conn;
	require_once("templates/header.php");
	require_once("templates/categories.php");
	require_once("templates/FWDconstructors.php"); // shit to make grid and carousel go
	$meta = array();
	if (isEmpty($_REQUEST['url'])) {
		// all public categories, highlighted first.
		$query = "SELECT * FROM `categories` WHERE `published` = 1 ORDER BY is_highlighted DESC, `category` ASC";
		$result = mysqli_query($conn, $query);
		if (mysqli_num_rows($result)) {
			$categoryList = array();
			$highlightedList = array();
			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
				if ($row['is_highlighted'] == 1) {
					$highlightedList[] = $row;
				}
				// all categories go in here, but only lightlighted go into highlighted for the carousel
				$categoryList[] = $row;
			}
			// meta keywords
			$meta['keywords'] = "iRev.net, Presence, Inner Revolution Networks, ";
			foreach ($categoryList as $category) {
				$meta['keywords'] .= $category['category'] . ", ";
			}
			$meta['keywords'] = substr($meta['keywords'], 0, -2) . ".";
			$meta['description'] = "Categories Listing including ";
			foreach($highlightedList as $category) {
				$meta['description'] .= $category['category'] . ", ";
			}
			$meta['description'] = substr($meta['description'], 0, -2) . ".";
			$meta['title'] = "Project Samples Categories Listing - Paul Gorman's Online Portfolio";
			$meta['url'] = CurPageURL();
			$meta['image'] = CurServerUrl() . "i/category/" . $highlightedList[0]['carousel_id'];
			$meta['css'][] = "skin_modern_silver.css";
			//$meta['js'][] = "FWDRoyal3DCarousel.js";
			$meta['js'][] = "FWDRoyal3DCarousel_uncompressed.js";
			$meta['breadcrumb'][0]['name'] = "Portfolio Samples";
			$meta['breadcrumb'][0]['url'] = curPageURL();
			// display all the categories
			htmlHeader($meta);
			htmlMasthead($meta);
			htmlDropDownNavigationSingle(gatherNavData());
			//htmlDropDownNavigationFull(gatherNavData());
			htmlWavesStart();
			htmlBreadcrumb($meta);
			ListCategoryCarousel($highlightedList);
			htmlBodyStart();
			ListAllCategories($categoryList);
			fwdConsCarousel(); // carousel constructor settings
			ListCategoriesTextLinks($categoryList);
			htmlFooter($meta);
		} else {
			ErrorDisplay("Categories Listing Unavailable!");
		}
	} else {
		// all published samples in a specific category
		$url = MakeURL(strtolower(trim($_REQUEST['url'])));
		$subcat = MakeURL(strtolower(trim($_REQUEST['subcat'])));
		// what are all the categories we can choose from?
		$categorynames = array();
		$categoryurls= array();
		$query = "SELECT `url`, `category` FROM `categories`";
		$result = mysqli_query($conn,$query);
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$categorynames[] = strtolower($row['category']);
			$categoryurls[] = strtolower($row['url']);
		}
		$closestCategoryFromRequest = ClosestWord($url,$categoryurls);
		// sample names default to not obfuscated using real names, not requiring display names.
		$obfuscatedsampleNames = 0;
		// dig up closest category that matches the request
		$query = sprintf(
			"SELECT * FROM `categories` WHERE `url` = '%s' ORDER BY is_highlighted DESC, `category` ASC",
			mysqli_real_escape_string($conn,$closestCategoryFromRequest)
		);
		$resultMatchingCategories = mysqli_query($conn, $query);
		if (mysqli_num_rows($resultMatchingCategories) == 0) {
			ErrorDisplay("No Categories Match Your Request");
		} else {
			$categoryInfo = mysqli_fetch_array($resultMatchingCategories, MYSQLI_ASSOC);
			// check obfuscated cookie status
			if ($_SESSION['obfuscate'] == "1") {
				$obfuscatedsampleNames = 1;
			}
			// check if any of the categories require obfuscated sample names
			// "Y" is to force display names. (N is force real names, I is individual sample mode)
			if ($categoryInfo['force_display_names'] == "I" && $obfuscatedsampleNames == 0) {
				$obfuscatedsampleNames = "I";
			} else if ($categoryInfo['force_display_names'] == "Y") {
				$obfuscatedsampleNames = 1;
			}
			// joined query to find samples IDs that match the category,
			// sort them by is_highlighted desc, name asc,
			// save data to $samples array
			$samples = array(); // the big array of good samples data
			$query = sprintf(
				"SELECT `samples`.`oid`, `samples`.`name`, `samples`.`display_name`, `samples`.`url`, 
				 `samples`.`alt_url`, `samples`.`slug`, `samples`.`use_display_name`, `samples`.`is_highlighted`
				 FROM `samples` LEFT OUTER JOIN `samplecategories` ON `samples`.`oid` = `samplecategories`.`oid` 
				 WHERE `samplecategories`.`cid` = '%s' AND `samples`.`is_searchable` = 1 AND `samples`.`is_active` = 1 
				 ORDER BY `samples`.`is_highlighted` DESC, `samples`.`name` ASC",
				mysqli_real_escape_string($conn,$categoryInfo['cid'])
			);
			$result = mysqli_query($conn,$query);
			while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
				$samples[$row['oid']] = array();
				if ($obfuscatedsampleNames == "I") {
					// we're allowd to display names depending on the individual sample 
					if ($row['use_display_name'] == "1") {
						// obfuscate this one sample
						$samples[$row['oid']]['name'] = $row['display_name'];
						$samples[$row['oid']]['url'] = $row['alt_url'];
					} else {
						// Real name for this one sample
						$samples[$row['oid']]['name'] = $row['name'];
						$samples[$row['oid']]['url'] = $row['url'];
					}
				} else if ($obfuscatedsampleNames == 1) {
					// obfuscate ALL names in this list
					$samples[$row['oid']]['name'] = $row['display_name'];
					$samples[$row['oid']]['url'] = $row['alt_url'];
				} else { // we can use normal real names!
					$samples[$row['oid']]['name'] = $row['name'];
					$samples[$row['oid']]['url'] = $row['url'];
				}
				$samples[$row['oid']]['oid'] = $row['oid'];
				$samples[$row['oid']]['slug'] = $row['slug'];
				$samples[$row['oid']]['is_highlighted'] = $row['is_highlighted'];
				$samples[$row['oid']]['use_display_name'] = $row['use_display_name'];

				$query = sprintf(
					"SELECT `filename`,`thumbwidth`,`thumbheight` 
					 FROM `media` WHERE `oid` = %s ORDER BY `is_highlighted` DESC, `width` DESC LIMIT 0,1",
					mysqli_real_escape_string($conn,$row['oid'])
				);
				$photoresult = mysqli_query($conn,$query);
				$rowphoto = mysqli_fetch_array($photoresult, MYSQLI_ASSOC);
				$samples[$row['oid']]['filename'] = $rowphoto['filename'];
				$samples[$row['oid']]['thumbwidth'] = $rowphoto['thumbwidth'];
				$samples[$row['oid']]['thumbheight'] = $rowphoto['thumbheight'];
			}
			$samplesHighlighted = array();	// array of highlighted good samples
			foreach ($samples as $oid => $garbage) {
				if ($samples[$oid]['is_highlighted'] == 1) {
					// highlighted carousel data
					$samplesHighlighted[$oid] = $samples[$oid];
				}
			}
			// So what was the best real category name that matches the random user request?
			$closestCategoryFromRequest = $categoryInfo['category'];
			//$closestCategoryFromRequest = ucWords(ClosestWord($url,$categorynames));

			// meta keywords
			$meta['keywords'] = "iRev.net, Presence, Inner Revolution Networks, ";
			$meta['keywords'] .= "$closestCategoryFromRequest, ";
			$meta['keywords'] .= $categoryInfo['description'] .", ";
			foreach ($samples as $sample) {
				$meta['keywords'] .= $sample['name'] . ", ";
			}
			$meta['keywords'] = substr($meta['keywords'], 0, -2) . ".";
			$meta['description'] = "Listing of $closestCategoryFromRequest - ". $categoryInfo['description'] ." including ";
			foreach($samplesHighlighted as $highlights) {
				$meta['description'] .= $highlights['name'] . ", ";
			}
			$meta['description'] = substr($meta['description'], 0, -2) . ".";
			$meta['title'] = "$closestCategoryFromRequest Entertainment Category Listing";
			$meta['url'] = CurPageURL();
			if (isEmpty($categoryInfo['carousel_id'])) {
				$meta['image'] = CurServerURL() . "i/category/" . $categoryInfo['image_id'];
			} else {
				$meta['image'] = CurServerURL() . "i/category/" . $categoryInfo['carousel_id'];
			}
			$meta['css'][] = "skin_modern_silver.css";
			$meta['css'][] = "skin_minimal_dark_global.css";
			//$meta['css'][] = "jquery-ui.css";
			//$meta['js'][] = "FWDGrid.js";
			//$meta['js'][] = "FWDRoyal3DCarousel.js";
			$meta['js'][] = "FWDRoyal3DCarousel_uncompressed.js";
			//$meta['js'][] = "jquery.js";
			//$meta['js'][] = "jquery-ui.js";
			//$meta['js'][] = "presgrid.js";
			$meta['breadcrumb'][0]['name'] = "Portfolio Samples";
			$meta['breadcrumb'][0]['url'] = curServerURL() . "portfolio/";
			$meta['breadcrumb'][1]['name'] = $closestCategoryFromRequest;
			$meta['breadcrumb'][1]['url'] = curPageURL();

			$_SESSION['category'] = $categoryInfo['cid'];
			htmlHeader($meta);
			htmlMasthead($meta);
			htmlDropDownNavigationSingle(gatherNavData());
			//htmlDropDownNavigationFull(gatherNavData());
			if (count($samplesHighlighted) > 0) {
				htmlWavesStart();
				htmlBreadcrumb($meta);
				ListsampleCarousel($closestCategoryFromRequest,$samplesHighlighted);
				htmlBodyStart();
				htmlCategoryImageBelow($categoryInfo['image_id'], $closestCategoryFromRequest);
				ListsamplesForCategory($closestCategoryFromRequest,$samples);
				ListsamplesTextLinks($categoryInfo,$samples); 
				fwdConsCarousel(); // dump this stuff in at the bottom of html
			} else {
				// snap, we don't like no ones in this category! Put up a simple category header image
				htmlWavesShortStart();
				htmlBreadcrumb($meta);
				htmlCategoryImage($categoryInfo['image_id'], $closestCategoryFromRequest);
				htmlBodyStart();
				ListsamplesForCategory($closestCategoryFromRequest,$samples);
				ListsamplesTextLinks($categoryInfo,$samples); 
			}
			htmlFooter($meta);
		}
	}
}

function GetPageContent($page) {
	global $conn;
	$query = "SELECT `html` FROM `pages` WHERE `pagename` = '$page'";
	$result = mysqli_query($conn,$query);
	return (mysqli_fetch_array($result)[0]);
}

function HomePage() {
	$content['body'] = GetPageContent("home");
	$content['now'] = GetPageContent("now");
	require_once("templates/header.php");
	require_once("templates/homepage.php");
	require_once("templates/FWDconstructors.php"); // shit to make grid and carousel go
	$meta['keywords'] = "iRev.net, Inner Revolution Networks, Presence, Gallery, Photos, Engineering, Programming, Design, Hosting, Shells, FreeBSD, Staging, Video, ";
	$meta['description'] = "Presence's Gallery of Projects, Photos, and Videos / iRev.net Web, Shell, and Email Hosting";
	$meta['title'] = "Paul Gorman's Gallery of Projects, Photos, and Videos / iRev.net Web, Shell, and Email Hosting";
	$meta['url'] = CurPageURL();
	$meta['image'] = CurServerUrl() . "templates/irev/irev-logo.png";
	$meta['css'][] = "skin_modern_silver.css";
	$meta['js'][] = "justifiedGallery.js";
	$meta['js'][] = "jwplayer/jwplayer.js";
	htmlHeader($meta);
	htmlMasthead($meta);
	htmlDropDownNavigationSingle(gatherNavData());
	//htmlDropDownNavigationFull(gatherNavData());
	htmlWavesFullStart();
	homePageCarousel(gatherHighlightedsamples());
	homePageHighlightedGallery(gatherHighlightedsamples());
	htmlBodyStart();
	htmlHomePageTop();
	/* Don't want the drawer pullout 
	htmlHomePageCategories(allPublicCategories());
	*/
	htmlHomePageContent($content);
	//htmlBreadcrumb($meta);
	fwdConsCarousel(); // dump this stuff in at the bottom of html
	homePageHighlightedGalleryJS();
	/* Don't want the drawer pullout 
	htmlHomePageCategoriesJS();
	*/
	htmlFooter($meta);
}

function AboutPage() {
	require_once("templates/header.php");
	$contentTop = GetPageContent("about-top");
	$contentBottom = GetPageContent("about-bottom");
	$meta['keywords'] = "Paul Gorman, Portfolio, iRev.net, Presence, Inner Revolution Networks, ";
	$meta['description'] = "Online Portfolio of Paul Gorman, iRev.net Internet Web Hosting";
	$meta['title'] = "About & Contact - Paul Gorman";
	$meta['url'] = CurPageURL();
	$meta['image'] = CurServerUrl() . "iRev.png";
	htmlHeader($meta);
	htmlMasthead($meta);
	htmlDropDownNavigationSingle(gatherNavData());
	//htmlDropDownNavigationFull(gatherNavData());
	htmlWavesFullStart();
	htmlContent($contentTop);
	htmlBodyStart();
	htmlContent($contentBottom);
	//htmlBreadcrumb($meta);
	htmlFooter($meta);
}

function iRevPage() {
	require_once("templates/header.php");
	$contentTop = GetPageContent("irev-top");
	$contentBottom = GetPageContent("irev-bottom");
	$meta['keywords'] = "Paul Gorman, Portfolio, iRev.net, Presence, Inner Revolution Networks, ";
	$meta['description'] = "Online Portfolio of Paul Gorman, iRev.net Internet Web Hosting";
	$meta['title'] = "About iRev.net";
	$meta['url'] = CurPageURL();
	$meta['image'] = CurServerUrl() . "iRev.png";
	htmlHeader($meta);
	htmlMasthead($meta);
	htmlDropDownNavigationSingle(gatherNavData());
	//htmlDropDownNavigationFull(gatherNavData());
	htmlWavesFullStart();
	htmlContent($contentTop);
	htmlBodyStart();
	htmlContent($contentBottom);
	//htmlBreadcrumb($meta);
	htmlFooter($meta);
}

function gatherHighlightedsamples() {
	// get some samples for the homepage carousel
	global $conn;
	/*
	$query = "
		SELECT `samples`.`name`, `samples`.`display_name`, `samples`.`slug`, 
		 `samples`.`url`, `samples`.`alt_url`, `samples`.`use_display_name`,
		 `media`.`filename`, `media`.`thumbwidth`, `media`.`thumbheight`, `samples`.`oid`
		FROM `samples`
		LEFT OUTER JOIN `media` ON `media`.`oid` = `samples`.`oid`
		WHERE `samples`.`is_active` = 1 AND `samples`.`is_highlighted` = 1 AND `samples`.`is_searchable` = 1
		AND `media`.`viewable` = 1 AND `media`.`is_highlighted` = 1 ORDER BY RAND()";
		*/
	$query = "
		SELECT `samples`.`name`, `samples`.`display_name`, `samples`.`slug`, 
		 `samples`.`url`, `samples`.`alt_url`, `samples`.`use_display_name`,
		 `media`.`filename`, `media`.`thumbwidth`, `media`.`thumbheight`, `samples`.`oid`
		FROM `samples`
		LEFT OUTER JOIN `media` ON `media`.`oid` = `samples`.`oid`
		WHERE `samples`.`is_active` = 1 AND `samples`.`is_highlighted` = 1 AND `samples`.`is_searchable` = 1
		AND `media`.`is_highlighted` = 1 ORDER BY RAND()";
	$result = mysqli_query($conn,$query);
	$samples = array();
	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$samples[$row['oid']] = $row;
		if (($_SESSION['obfuscate'] == 1) || ($row['use_display_name'] == 1) ) {
			$samples[$row['oid']]['name'] = $samples[$row['oid']]['display_name'];
			$samples[$row['oid']]['url'] = $samples[$row['oid']]['alt_url'];
		}
	}
	return ($samples);
}

function gatherNavData() {
	// Get all the categories, and subcategories or sample names & links for navigation bar drop down
	// XXX: If a there's a subcategory, I'm showing those, but not samples under that.  3 levels deep (sub2) is as deep as we go today
	// XXX: Does not obey category order preference yet
	global $conn;
	$categories = AllPublicCategories();
	$subcategories = array();
	$navdata = array();
	$i = 0;
	$query = "SELECT `parent_cid`,`subcategory`,`url`,`description` FROM `subcategories`";
	$result = mysqli_query($conn,$query);
	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$subcategories[$i]['cid'] = $row['parent_cid'];
		$subcategories[$i]['subcategory'] = $row['subcategory'];
		$subcategories[$i]['url'] = $row['url'];
		$subcategories[$i]['description'] = $row['description'];
		$i++;
	}
	// glue together a nice array
	$i = 0;
	foreach ($categories as $categoryData) {
		// sub1 categories
		$j = 0;
		$navdata[$i][$j]['name'] = $categoryData['category'];
		$navdata[$i][$j]['description'] = $categoryData['description'];
		$navdata[$i][$j]['url'] = "/category/" . $categoryData['url'];
		$samples = GetSamplesInCategory($categoryData['cid']);
		$j++;
		// sub2 subcategories
		foreach (array_keys($subcategories) as $key) {
			if ($subcategories[$key]['cid'] == $categoryData['cid']) {
				$navdata[$i][$j]['name'] = $subcategories[$key]['subcategory'];
				$navdata[$i][$j]['description'] = $subcategories[$key]['description'];
				$navdata[$i][$j]['url'] = "/category/" . $subcategories[$key]['url'];
				$j++;
			}
		}
		// sub2 samples
		$samples = GetSamplesInCategory($categoryData['cid']);
		foreach ($samples as $sampledata) {
			$navdata[$i][$j]['name'] = $sampledata['name'];
			$navdata[$i][$j]['url'] = "/sample/" . $sampledata['url'];
			$navdata[$i][$j]['description'] = $sampledata['slug'];
			$j++;
		}
		$i++;
	}
	return ($navdata);
}

function GetSamplesInCategory($cid) {
	global $conn;
	// Return array of all published samples in a specific Category ID
	// sample names default to not obfuscated using real names, not requiring display names.
	$obfuscatedsampleNames = 0;
	// dig up closest category that matches the request
	$query = sprintf(
		"SELECT * FROM `categories` WHERE `cid` = '%s' ORDER BY is_highlighted DESC, `category` ASC",
		mysqli_real_escape_string($conn,$cid)
	);
	$result = mysqli_query($conn, $query);
	$categoryInfo = mysqli_fetch_array($result, MYSQLI_ASSOC);
	// check obfuscated cookie status
	if ($_SESSION['obfuscate'] == "1") {
		$obfuscatedsampleNames = 1;
	}
	// check if any of the categories require obfuscated sample names
	// "Y" is to force display names. (N is force real names, I is individual sample mode)
	if ($categoryInfo['force_display_names'] == "I" && $obfuscatedsampleNames == 0) {
		$obfuscatedsampleNames = "I";
	} else if ($categoryInfo['force_display_names'] == "Y") {
		$obfuscatedsampleNames = 1;
	}
	// joined query to find samples IDs that match the category,
	// sort them by is_highlighted desc, name asc,
	// save data to $samples array
	$samples = array(); // the big array of good samples data
	$query = sprintf(
		"SELECT `samples`.`oid`, `samples`.`name`, `samples`.`display_name`, `samples`.`url`, 
		 `samples`.`alt_url`, `samples`.`slug`, `samples`.`use_display_name`, `samples`.`is_highlighted`
		 FROM `samples` LEFT OUTER JOIN `samplecategories` ON `samples`.`oid` = `samplecategories`.`oid` 
		 WHERE `samplecategories`.`cid` = '%s' AND `samples`.`is_searchable` = 1 AND `samples`.`is_active` = 1 
		 ORDER BY `samples`.`is_highlighted` DESC, `samples`.`name` ASC",
		mysqli_real_escape_string($conn,$cid)
	);
	$result = mysqli_query($conn,$query);
	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$samples[$row['oid']] = array();
		if ($obfuscatedsampleNames == "I") {
			// we're allowd to display names depending on the individual sample 
			if ($row['use_display_name'] == "1") {
				// obfuscate this one sample
				$samples[$row['oid']]['name'] = $row['display_name'];
				$samples[$row['oid']]['url'] = $row['alt_url'];
			} else {
				// Real name for this one sample
				$samples[$row['oid']]['name'] = $row['name'];
				$samples[$row['oid']]['url'] = $row['url'];
			}
		} else if ($obfuscatedsampleNames == 1) {
			// obfuscate ALL names in this list
			$samples[$row['oid']]['name'] = $row['display_name'];
			$samples[$row['oid']]['url'] = $row['alt_url'];
		} else { // we can use normal real names!
			$samples[$row['oid']]['name'] = $row['name'];
			$samples[$row['oid']]['url'] = $row['url'];
		}
		$samples[$row['oid']]['oid'] = $row['oid'];
		$samples[$row['oid']]['slug'] = $row['slug'];
		$samples[$row['oid']]['is_highlighted'] = $row['is_highlighted'];
		$samples[$row['oid']]['use_display_name'] = $row['use_display_name'];
		$query = sprintf(
			"SELECT `filename`,`thumbwidth`,`thumbheight` 
			 FROM `media` WHERE `oid` = %s ORDER BY `is_highlighted` DESC, `width` DESC LIMIT 0,1",
			mysqli_real_escape_string($conn,$row['oid'])
		);
		$photoresult = mysqli_query($conn,$query);
		$rowphoto = mysqli_fetch_array($photoresult, MYSQLI_ASSOC);
		$samples[$row['oid']]['filename'] = $rowphoto['filename'];
		$samples[$row['oid']]['thumbwidth'] = $rowphoto['thumbwidth'];
		$samples[$row['oid']]['thumbheight'] = $rowphoto['thumbheight'];
	}
	return($samples);
}

function DisplayVideoPlayer($sampleinfo) {
	$vidcount = $sampleinfo['media']['vidcount'];
	if ($vidcount == 1) {
		?>
			<div class="sampleVideoIndividual" style="text-align: center; max-width: 540px;"><div class="VideoPlayer" id="container<?= $sampleinfo['media']['mid']; ?>">Loading video for <?= ($sampleinfo['use_display_name'])? $sampleinfo['display_name'] : $sampleinfo['name']; ?></div></div>
		<?
	} elseif ($vidcount > 1) {
		?>
			<div class="col6 sampleVideoIndividual"><div class="VideoPlayer" style="text-align: center; position: absolute;" id="container<?= $sampleinfo['media']['mid']; ?>">Loading video for <?= ($sampleinfo['use_display_name'])? $sampleinfo['display_name'] : $sampleinfo['name']; ?></div></div>
		<?
	}
	?>
	<script type="text/javascript">
		jwplayer('container<?= $sampleinfo['media']['mid']; ?>').setup({
			'modes': [
				{type: 'html5'},
				{type: 'flash', src: '/templates/js/jwplayer/player.swf'},
				{type: 'download'}
			],
			'author': 'Paul Gorman',
			'description': '<?= ($sampleinfo['use_display_name'])? htmlspecialchars($sampleinfo['display_name'], ENT_QUOTES) : htmlspecialchars($sampleinfo['name'], ENT_QUOTES); ?>',
			'file': 'https://irev.net/m/<?= $sampleinfo['media']['filename']; ?>',
			'image': '/i/sample/<?= $sampleinfo['media']['previewimage']; ?>',
			'duration': '<?= $sampleinfo['media']['vidlength']; ?>',
			'controlbar': 'over',
			'shownavigation': 'true',
			'icons': false,
			'width': '<?= $sampleinfo['media']['widthdisplay']; ?>',
			'stretching': 'uniform',
			<?= (isset($sampleinfo['media']['heightdisplay']))? $sampleinfo['media']['heightdisplay'] : NULL ?>
			'aspectratio': '<?= $sampleinfo['media']['aspectratio']; ?>',
		});
		jwplayer('container<?= $sampleinfo['media']['mid']; ?>').onPlay(function() {
			var oRequest = new XMLHttpRequest();
			var sURL = "https://" + self.location.hostname + "/videoplay/<?= $sampleinfo['media']['mid']; ?>";
			oRequest.open("GET",sURL,true);
			oRequest.setRequestHeader("User-Agent",navigator.userAgent);
			oRequest.send(null)
		});
	</script>
	<?
	/*
			//'width': '<?= $sampleinfo['media']['width']; ?>',
			//'height': '<?= $sampleinfo['media']['height']; ?>'
	*/
}

function getCommonDivisor($a, $b) {
	if ($a == 0 || $b == 0) {
		return abs( max(abs($a), abs($b)) );
	}
	$r = $a % $b;
	return ($r != 0) ? getCommonDivisor($b, $r) : abs($b);
}

function AdminDisplaySiteStats() {
	global $conn;
	$hits = array();
	$sessions = array();
	$query = "SELECT * FROM sitehits where hit_datetime >= '".DatePHPtoSQL(strtotime('-7 days'))."' AND hit_datetime <= '".DatePHPtoSQL(time())."' AND hit_url LIKE '%irev.net%';";
	$result = mysqli_query($conn, $query);
	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$visitor = $row['hit_addr'];
		if (array_key_exists($visitor, $hits)) { 
			$hits[$visitor] = $hits[$visitor] + 1;
		} else {
			$hits[$visitor] = 1;
		}
		if (array_key_exists($visitor,$sessions)) {
			if ($sessions[$visitor] < $row['sesscount']) {
				$sessions[$visitor] = $row['sesscount'];
			}
		} else {
			$sessions[$visitor] = 0;
		}
		arsort($hits);
	}
	?>
		<div class="metricsOverviewBlock">
		<div class="metricsBlock">
			<div class="metricsHeader">Website Activity - 7 Days</div>
			<? foreach ($hits as $visit => $hit) { 
				if ($sessions[$visit] == 0) { continue; }
				?>
				<div class="metricsDomain"><?= $visit; ?></div>
				<!--<div class="metricsLabel">Hits:</div>-->
				<div class="metricsValue"><?= $hit + 0; ?>/<?= $sessions[$visit]; ?></div>
			<? } ?>
		</div> <!-- class="metricsBlock" -->
	<?
	$hits = array();
	$sessions = array();
	$query = "SELECT * FROM sitehits where hit_datetime >= '".DatePHPtoSQL(strtotime('-1 day'))."' AND hit_datetime <= '".DatePHPtoSQL(time())."' AND hit_url LIKE '%irev.net%';";
	$result = mysqli_query($conn,$query);
	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$visitor = $row['hit_addr'];
		if (array_key_exists($visitor, $hits)) { 
			$hits[$visitor] = $hits[$visitor] + 1;
		} else {
			$hits[$visitor] = 1;
		}
		if (array_key_exists($visitor, $sessions)) {
			if ($sessions[$visitor] < $row['sesscount']) {
				$sessions[$visitor] = $row['sesscount'];
			}
		} else {
			$sessions[$visitor] = 0;
		}
		arsort($hits);
	}
	?>
		<div class="metricsOverviewBlock">
		<div class="metricsBlock">
			<div class="metricsHeader">Website Activity - 24 Hours</div>
			<? foreach ($hits as $visit => $hit) { 
				if ($sessions[$visit] == 0) { continue; }
				?>
				<div class="metricsDomain"><?= $visit; ?></div>
				<!--<div class="metricsLabel">Hits:</div>-->
				<div class="metricsValue"><?= $hit + 0; ?>/<?= $sessions[$visit]; ?></div>
			<? } ?>
		</div> <!-- class="metricsBlock" -->
		<?

	$hits = array();
	$sessions = array();
	$query = "SELECT * FROM sitehits where hit_datetime >= '".DatePHPtoSQL(strtotime('-1 hour'))."' AND hit_datetime <= '".DatePHPtoSQL(time())."' AND hit_url LIKE '%irev.net%';";
	$result = mysqli_query($conn, $query);
	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$visitor = $row['hit_addr'];
		if (array_key_exists($visitor, $hits)) { 
			$hits[$visitor] = $hits[$visitor] + 1;
		} else {
			$hits[$visitor] = 1;
		}
		if (array_key_exists($visitor,$sessions)) {
			if ($sessions[$visitor] < $row['sesscount']) {
				$sessions[$visitor] = $row['sesscount'];
			}
		} else {
			$sessions[$visitor] = 0;
		}
		arsort($hits);
	}
	?>
		<div class="metricsOverviewBlock">
		<div class="metricsBlock">
			<div class="metricsHeader">Website Activity - 60 Minutes</div>
			<? foreach ($hits as $visit => $hit) { ?>
				<div class="metricsDomain"><?= $visit; ?></div>
				<!--<div class="metricsLabel">Hits:</div>-->
				<div class="metricsValue"><?= $hit + 0; ?>/<?= $sessions[$visit]; ?></div>
			<? } ?>
		</div> <!-- class="metricsBlock" -->
		</div> <!-- class="MetricsOverviewBlock" -->
	<?
}

function ShowAdminPage() {
	$adminfunctions = array(
		"Overview" => "",
		"Samples" => "samples",
		"Categories" => "categories_list",
		"Styles" => "styles_list",
		"Locations" => "locations_list",
		"Pages" => "pages",
		"Admins" => "admin_users",
		"Metrics" => "web_stats"
	);
	include("templates/admin.php");
	AdminHead($_REQUEST['url'],$adminfunctions);
	AdminNav($_REQUEST['url'],$adminfunctions);
	if (isEmpty($_REQUEST['url']) || ((string)$_REQUEST['url'] === "web_stats")) {
		AdminDisplaySiteStats();
	} else {
		echo "go go";
		if ($_REQUEST['url'] == "admin_users") {
			switch($_REQUEST['function']) {
				case "add_admin":
					AdminAddAdmin();
					break;
				case "save_new_admin":
					AdminSaveNewAdmin();
					break;
				default:
					AdminAdminsList();
			}
		}
		if ($_REQUEST['url'] == "categories_list") {
			switch($_REQUEST['function']) {
				case "add_category":
					AdminSaveNewCategory();
					AdminEditCategories();
					break;
				case "del_category":
					AdminDeleteCategory($_REQUEST['categoryurl']);
					break;
				case "del_subcategory":
					AdminDeleteSubCategory($_REQUEST['categoryurl']);
					break;
				case "del_category_for_reals":
					AdminDeleteCategoryGo($_REQUEST['targetcategoryurl']);
					AdminEditCategories();
					break;
				case "del_subcategory_for_reals":
					AdminDeleteSubCategoryGo($_REQUEST['targetcategoryurl']);
					AdminEditCategories();
					break;
				case "edit_category":
					AdminEditSingleCategory($_REQUEST['categoryurl']);
					break;
				case "edit_subcategory":
					AdminEditSingleSubCategory($_REQUEST['categoryurl']);
					break;
				case "save_category":
					AdminSaveSingleCategory($_REQUEST['form_cid']);
					AdminEditSingleCategory(GetCatUrlFromCID($_REQUEST['form_cid']));
					break;
				case "save_subcategory":
					AdminSaveSingleSubCategory($_REQUEST['form_subid']);
					AdminEditSingleCategory(GetCatUrlFromCID($_REQUEST['form_cid']));
					break;
				case "search_category":
					AdminListsamplesByCategory();
					break;
				case "add_sub_category":
					AdminSaveNewSubCategory();
					AdminEditSingleCategory(GetCatUrlFromCID($_REQUEST['form_cid']));
					break;
				default:
					AdminEditCategories();
			}
		}
		if ($_REQUEST['url'] == "styles_list") {
			switch($_REQUEST['function']) {
				case "add_style":
					AdminSaveNewStyle();
					AdminListStyles();
					break;
				case "del_style":
					AdminDeleteStyle($_REQUEST['sid']);
					break;
				case "del_style_for_reals":
					AdminDeleteStyleGo($_REQUEST['targetcategoryurl']);
					AdminListStyles();
					break;
				case "edit_style":
					AdminEditSingleStyle($_REQUEST['sid']);
					break;
				case "save_style":
					AdminSaveSingleStyle();
					AdminListStyles();
					break;
				case "search_style":
					AdminListsampleByStyle();
					break;
				default:
					AdminListStyles();
			}
		}
		if ($_REQUEST['url'] == "locations_list") {
			switch($_REQUEST['function']) {
				case "add_location":
					AdminSaveNewLocation();
					AdminListLocations();
					break;
				case "del_location":
					AdminDeleteLocation($_REQUEST['lid']);
					AdminListLocations();
					break;
				case "edit_location":
					AdminEditSingleLocation($_REQUEST['lid']);
					break;
				case "save_location":
					AdminSaveSingleLocation();
					AdminListLocations();
					break;
				default:
					AdminListLocations();
			}
		}
		if ($_REQUEST['url'] == "pages") {
			$pages = Pages();
			AdminPagesButtonBar($pages); // display that additional nav/button bar
			switch ($_REQUEST['function']) {
				case "edit":
					AdminPageEdit();
					break;
				case "update":
					AdminPageUpdate();
					AdminPagesListPage(AdminPagesListData($pages));
					break;
				case "revert":
					AdminPageRevert();
					AdminPageEdit();
					break;
				default:
					AdminPagesListPage(AdminPagesListData($pages));
			}
		}
		if ($_REQUEST['url'] == "samples") {
			AdminsamplesButtonBar(); // display that additional nav/button bar
			switch($_REQUEST['function']) {
				case "search":
					AdminsampleListSearchResults();
					break;
				case "list_all":
					AdminsampleList();
					break;
				case "list_new":
					AdminsampleListNew();
					break;
				case "list_feat":
					AdminsampleListFeat();
					break;
				case "list_secret":
					AdminsampleListSecret();
					break;
				case "add_new":
					AdminsampleAddNew();
					break;
				case "edit":
					if ($_REQUEST['listpage'] > 0) {
						$oid = (preg_replace("/[^0-9]/","",$_REQUEST['listpage'])); // hack for direct URL access
					} else {
						$oid = (preg_replace("/[^0-9]/","",$_REQUEST['oid']));
					}
					switch ($_REQUEST['executeButton']) {
						case "delete":
							AdminsampleDelete($oid);
							break;
						case "update":
							AdminSampleSaveSingle();
							AdminsampleEditSingle($oid);
							break;
						default:
							AdminsampleEditSingle($oid);
					}
					break;
				case "del_sample_for_reals":
					AdminsampleDeleteGo($_REQUEST['targetcategoryurl']); // yeah, it's really the oid
					AdminsampleList();
					break;
				default:
					AdminsampleList();
			}
		}
	}
}

function AdminPagesListData($pages) {
	global $conn;
	$pagedata = array();
	foreach ($pages as $url => $name) {
		$query = sprintf("SELECT `htmltime` FROM `pages` WHERE `pagename` = '%s'", mysqli_real_escape_string($conn,$url));
		$result = mysqli_query($conn,$query);
		$updated = mysqli_fetch_array($result, MYSQLI_NUM)[0];
		$pagedata[$url]['lastupdated'] = $updated;
		$pagedata[$url]['name'] = $name;
	}
	return ($pagedata);
}

function AdminPageEdit() {
	global $conn;
	$pageurl = preg_replace("/[^a-z\-]/","",$_REQUEST['listpage']);
	if (strlen($pageurl) < 2) {
		$pageurl = preg_replace("/[^a-z\-]/","",$_REQUEST['pageurl']);
	}
	$query = sprintf("SELECT `pagename`,`html`,`undo`,`htmltime`,`undotime` FROM `pages` WHERE `pagename` = '%s'", mysqli_real_escape_string($conn,$pageurl));
	$result = mysqli_query($conn,$query);
	$row = mysqli_fetch_assoc($result);
	AdminPagesEditPage($row);
}

function AdminPageImageUpload() {
	global $dirlocation;
	$fileid = uniqid();
	move_uploaded_file($_FILES['file']['tmp_name'], "$dirlocation/i/pages/$fileid");
	if (filesize("$dirlocation/i/pages/$fileid") < 1024) {
		// if the file is smaller than 1kb, I don't trust it.
		unlink("$dirlocation/i/pages/$fileid");
		echo stripslashes(json_encode(array("filelink" => NULL)));
	} else {
		// Yay, its a file!
		$finfo = finfo_open(FILEINFO_MIME);
		$type = finfo_file($finfo, "$dirlocation/i/pages/$fileid");
		if (preg_match("/jpeg/i",$type)) {
			$newfileid = "$fileid.jpg";	
			rename (
				$dirlocation . "/i/pages/$fileid",
				$dirlocation . "/i/pages/$newfileid"
			);
			echo stripslashes(json_encode(array("filelink" => "/i/pages/$newfileid")));
		} elseif (preg_match("/png/i",$type)) {
			$newfileid = "$fileid.png";
			rename (
				$dirlocation . "/i/pages/$fileid",
				$dirlocation . "/i/pages/$newfileid"
			);
			echo stripslashes(json_encode(array("filelink" => "/i/pages/$newfileid")));
		} else {
			// Bad file!
			unlink ("$dirlocation/i/pages/$fileid");
			echo stripslashes(json_encode(array("filelink" => NULL)));
		}
	}
}

function AdminPageImageClipboard() {
	global $dirlocation;
	$contentType = $_POST['contentType'];
	$data = base64_decode($_REQUEST['data']);
	$filename = uniqid();
	$fileloc = "$dirlocation/i/pages/$filename";
	file_put_contents($fileloc, $data);
	$finfo = finfo_open(FILEINFO_MIME);
	$type = finfo_file($finfo, $fileloc);
	if (preg_match("/jpeg/i",$type)) {
		$newfilename = "$filename.jpg";	
		rename (
			$dirlocation . "/i/pages/$filename",
			$dirlocation . "/i/pages/$newfilename"
		);
		echo stripslashes(json_encode(array("filelink" => "/i/pages/$newfilename")));
	} elseif (preg_match("/png/i",$type)) {
		$newfilename = "$filename.png";
		rename (
			$dirlocation . "/i/pages/$filename",
			$dirlocation . "/i/pages/$newfilename"
		);
		echo stripslashes(json_encode(array("filelink" => "/i/pages/$newfilename")));
	} else {
		// Bad file!
		unlink ("$fileloc");
		echo stripslashes(json_encode(array("filelink" => NULL)));
	}
}

function AdminPageRevert() {
	global $conn;
	$pages = Pages();
	$pageurl = preg_replace("/[^a-z\-]/","",$_REQUEST['pageurl']);
	$query = sprintf(
		"SELECT `undo`,`undotime` FROM `pages` WHERE `pagename` = '%s'",
		mysqli_real_escape_string($conn, $pageurl)
	);
	$result = mysqli_query($conn,$query);
	$row = mysqli_fetch_assoc($result);
	$update = sprintf(
		"UPDATE `pages` SET `html` = '%s', `htmltime` = '%s' WHERE `pagename` = '%s'",
		mysqli_real_escape_string($conn, $row['undo']),
		mysqli_real_escape_string($conn, $row['undotime']),
		mysqli_real_escape_string($conn, $pageurl)
	);
	if (mysqli_query($conn, $update) === TRUE) {
		echo "<div class='AdminSuccess'><B>". makeCase($pages['pageurl']) ."</B> Reverted to Previous Version!</div>";
	} else {
		echo "<div class='AdminError'><B>\"$pageurl\" failed to roll back!</B> ". mysqli_error($conn) ."</div>";
	}
}

function AdminPageUpdate() {
	global $conn;
	$pages = Pages();
	$pageurl = preg_replace("/[^a-z\-]/","",$_REQUEST['pageurl']);
	$content = preg_replace(array("/\s{2,}/", "/[\t\n\r]/"), " ", $_REQUEST['content']);
	$query = sprintf("SELECT `html`,`htmltime` FROM `pages` WHERE `pagename` = '%s'", mysqli_real_escape_string($conn,$pageurl));
	$result = mysqli_query($conn,$query);
	$undo = mysqli_fetch_assoc($result);
	// put in new data
	$update = sprintf("
		UPDATE `pages` SET
		`html` = '%s',
		`htmltime` = '%s',
		`undo` = '%s',
		`undotime` = '%s'
		WHERE `pagename` = '%s'",
		mysqli_real_escape_string($conn, $content), // html
		mysqli_real_escape_string($conn, DatePHPtoSQL(time())), // htmltime
		mysqli_real_escape_string($conn, $undo['html']), //undo
		mysqli_real_escape_string($conn, $undo['htmltime']), //undo time
		mysqli_real_escape_string($conn, $pageurl)
	);
	if (mysqli_query($conn,$update) === TRUE) {
		echo "<div class='AdminSuccess'><B>". makeCase($pages[$pageurl]) ."</B> Successfully Updated!</div>";
	} else {
		echo "<div class='AdminError'><B>\"$pageurl\" page Failed to Update!</B> ". mysqli_error($conn) ."</div>";
	}
}

function AdminAdminsList() {
	global $conn;
	$query = "SELECT `username` FROM `admins` ORDER BY `username` ASC";
	$result = mysqli_query($conn,$query);
	while ($row = mysqli_fetch_assoc($result)) {                                                                                                                                           
		$admins[] = $row['username'];
	}   
	AdminShowAdminsList($admins);
}

function AdminAddAdmin() {
	AdminShowNewAdminForm();
}

function AdminSaveNewAdmin() {
	global $conn;
	$password = HashPassword(htmlspecialchars(strip_tags(trim($_REQUEST['password']))));
	$username = strtolower(htmlspecialchars(strip_tags(trim($_REQUEST['username']))));
	$query = sprintf(
		"INSERT INTO `admins` (`username`,`password`) VALUES ('%s','%s')",
		mysqli_real_escape_string($conn,$username),
		mysqli_real_escape_string($conn,$password)
	);
	$result = mysqli_query($conn,$query);
	AdminAdminsList();
}

function AdminsampleDelete($oid) {
	global $conn;
	$nextfunction = "del_sample_for_reals";
	$urlDo = "samples";
	$urlCancel = "samples/edit/$oid";
	$query = sprintf("SELECT `name` FROM `samples` WHERE `oid` = %s", preg_replace("/[^0-9]/","",$oid));
	$result = mysqli_query($conn,$query);
	$row = mysqli_fetch_assoc($result);
	$desc = $row['name'];
	AdminShowDeleteConfirmation($oid,$desc,$urlDo,$urlCancel,$nextfunction);
}

function AdminsampleAddNew() {
	if (!isset($_REQUEST['formpage'])) {
		// brand new sample
		AdminsampleFormNew();
	} elseif ((string)$_REQUEST['formpage'] === "1") {
		// attempt to save the sample info and media
		$oid = AdminsampleSaveNew();
		if (isEmpty($oid)) {
			// Error in first page, redisplay first page.
			AdminsampleFormNew();
		} else {
			// there's an $oid from saving basic data, so check that media in
			$filecount = AdminsampleSaveMedia($oid);
			if ($filecount >= 1) {
				echo "<div class='AdminSuccess'>New sample added with $filecount media files!</div>";
			} else {
				echo "<div class='AdminError'>No media was saved. Please add a photo and/or video now.</div>";
			}
			// regardless of media, we did save SOMETHING, so sets see it.
			AdminsampleEditSingle($oid);
		}
	}
}

function allPublicCategories() {
	// XXX: Redo for custom cat sorting
	// Used by cats drawer, navigation dropdown, and categories listing page
	global $conn;
	$query = "SELECT `cid`, `category`, `url`, `description` FROM `categories` WHERE `published` = 1 ORDER BY `is_highlighted` DESC, `category` ASC";
	$result = mysqli_query($conn,$query);
	$categories = array();
	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		$categories[] = $row;
	}
	return ($categories);
}

function GathersampleInfo($oid) {
	global $conn;
	$oid = preg_replace("/[^0-9]/",'',$oid);
	// XXX: this could be some massive joined query
	$sampleinfo = array();
	if (isEmpty($oid)) {
		echo "<div class='AdminError'>Bad Request for sample Record!</div>";
		return($sampleinfo);
	}
	$query = sprintf("SELECT * FROM `samples` WHERE `oid` = %s", mysqli_real_escape_string($conn,$oid));
	$result = mysqli_query($conn,$query);
	$row = mysqli_fetch_assoc($result);
	$sampleinfo['oid'] = $oid;
	if (isEmpty($row['oid'])) {
		echo "<div class='AdminError'>No record available for ID Number $oid.</div>";
	} else {
		foreach ($row as $fieldname => $value) {
			$sampleinfo[$fieldname] = $value;
		}
	}
	mysqli_free_result($result);
	// AdminSelectCategories($oid) AdminSelectStyles($oid) AdminSelectLocations($oid)
	// lemme have hash of media
	$query = sprintf("SELECT `cid` FROM `samplecategories` WHERE `oid` = %s", mysqli_real_escape_string($conn,$oid));
	$result = mysqli_query($conn,$query);
	while ($row = mysqli_fetch_assoc($result)) {
		$sampleinfo['categories'][$row['cid']] = $row['cid'];
	}
	//subcats
	$sampleinfo['subcategories'] = array();
	$query = sprintf("SELECT `subid` FROM `samplesubcategories` WHERE `oid` = %s", mysqli_real_escape_string($conn,$oid));
	$result = mysqli_query($conn,$query);
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$sampleinfo['subcategories'][$row['subid']] = $row['subid'];
		}
	}
	mysqli_free_result($result);
	$query = sprintf("SELECT `lid` FROM `samplelocations` WHERE `oid` = %s", mysqli_real_escape_string($conn,$oid));
	$result = mysqli_query($conn,$query);
	while ($row = mysqli_fetch_assoc($result)) {
		$sampleinfo['locations'][$row['lid']] = $row['lid'];
	}
	mysqli_free_result($result);
	$query = sprintf("SELECT `sid` FROM `samplestyles` WHERE `oid` = %s", mysqli_real_escape_string($conn,$oid));

	$result = mysqli_query($conn,$query);
	while ($row = mysqli_fetch_assoc($result)) {
		$sampleinfo['styles'][$row['sid']] = $row['sid'];
	}
	mysqli_free_result($result);
	$query = sprintf(
		"SELECT * FROM `media` WHERE `oid` = %s ORDER BY `is_highlighted` DESC, `vidlength` ASC", 
		mysqli_real_escape_string($conn,$oid)
	);
	$result = mysqli_query($conn,$query);
	while ($row = mysqli_fetch_assoc($result)) {
		$sampleinfo['media']['mid'][$row['mid']] = $row['mid'];
		$sampleinfo['media']['name'][$row['mid']] = $row['name'];
		$sampleinfo['media']['filetype'][$row['mid']] = $row['filetype'];
		$sampleinfo['media']['filename'][$row['mid']] = $row['filename'];
		$sampleinfo['media']['thumbwidth'][$row['mid']] = $row['thumbwidth'];
		$sampleinfo['media']['thumbheight'][$row['mid']] = $row['thumbheight'];
		$sampleinfo['media']['height'][$row['mid']] = $row['height'];
		$sampleinfo['media']['width'][$row['mid']] = $row['width'];
		$sampleinfo['media']['vidlength'][$row['mid']] = $row['vidlength'];
		$sampleinfo['media']['is_highlighted'][$row['mid']] = $row['is_highlighted'];
		$sampleinfo['media']['viewable'][$row['mid']] = $row['viewable'];
		$sampleinfo['media']['published'][$row['mid']] = DateSQLtoPHP($row['published']);
	}
	mysqli_free_result($result);
	return $sampleinfo;
}

function AdminsampleEditSingle($oid) {
	$sampleinfo = GathersampleInfo($oid);
	if (!isEmpty($sampleinfo['name'])) {
		AdminsampleFormSingle($sampleinfo);
	}
}

function ObfuscatesampleNameAutomatically($name) {
	// crappy way of guessing a band's obfuscated "display" name: first whole word, then first letter of each additional word
	// unless only two words, then initials all the way
	// logic subject to be totally changed on a whim
	$name = preg_replace("/[^a-zA-Z0-9\/\\_|+ ]/", '', $name);  // do not want "'" in names like "'N Demand"
	$words = explode(" ", $name);
	$display_name = "";
	$counter = 0;
	if (count($words) > 0) {
		if (count($words) == 1) {
			$display_name = substr($name,0,3);	// one word sample name is now first 3 letters
		} elseif (count($words) == 2) {
			foreach ($words as $word) {
				$display_name .= strtoupper(substr($word,0,1));
				$display_name .= ". ";
			}
		} elseif (count($words) >= 3) {
			foreach ($words as $word) {
				if (preg_match("/\b(the|group|band|of|a|an|and)\b/i",$word)) {
					$display_name .= "$word ";
				} else {
					if ($counter == 0) {
						$display_name .= strtoupper(substr($word,0,1));
						$display_name .= ". ";
						//$display_name = "$word ";
					} else {
						$display_name .= strtoupper(substr($word,0,1));
						$display_name .= ". ";
					}
				}
				$counter++;
			}
		}
	}
	return (makeCase(trim($display_name)));
}

function UniqueObfuscatedSampleName($name) {
	// roll through teh database and choose a unique obfsd name for this sample
	// prevents the "AR" =~ "Ashley Red | Al Robinson" iss.
	global $conn;
	$query = sprintf("SELECT `display_name` FROM `samples` WHERE `display_name` = '%s'", mysqli_real_escape_string($conn,$name));
	$result = mysqli_query($conn,$query);
	if (mysqli_num_rows($result) == 0) {
		return ($name);
	} else {
		// go loop for a new alturl
		$number = 1;
		$basename = $name; // increment the number, not append another digit
		while (!$done) {
			$name = "$basename $number"; // HEY THERES A SPACE HERE 
			$query = sprintf("SELECT `display_name` FROM `samples` WHERE `display_name` = '%s'", mysqli_real_escape_string($conn,$name));
			$result = mysqli_query($conn,$query);
			(mysqli_num_rows($result) == 0)? $done++ : $number++; 
		}
		return ($name);
	}
}

function AdminSampleSaveSingle() {
	// Updated a sample's data from within the sample editor
	global $conn;
	global $dirlocation;
	$oid = preg_replace("/[^0-9]/",'',$_REQUEST['oid']);
	$sampleinfo = GathersampleInfo($oid);
	$samplesave = array("oid" => $oid);
	// compare any $_REQUEST stuff with existing database, update.
	// check for new file uploads, deal with.
	// return the (adjusted) $sampleinfo hash
	// name
	if (isEmpty($_REQUEST['name'])) { 
		$errors[] = "Please enter the project sample's name.";
	} else {
		$name = makeCase(htmlspecialchars(convert_smart_quotes(trim($_REQUEST['name']))));
		if ($name !== $sampleinfo['name']) {
			$samplesave['name'] = $name;
			$url = MakeURL(strtolower($name));
			if ($url !== $sampleinfo['url']) {
				$samplesave['url'] = $url;
			}
			// check if this is a duplicate // being a real jerk by including the "cleaned" URL text
			$query = sprintf("SELECT `name` FROM `samples` WHERE `oid` <> '%s' AND (`name` = '%s' OR `url` = '%s')",
				mysqli_real_escape_string($conn, $samplesave['oid']),
				mysqli_real_escape_string($conn, $samplesave['name']),
				mysqli_real_escape_string($conn, $samplesave['url'])
			);
			$result = mysqli_query($conn,$query);
			if (mysqli_num_rows($result) > 0) {
				$errors[] = "This sample's name is already used by another project! Please check the sample's name carefully.";
			}
		}
	}
	// slug
	if (isEmpty($_REQUEST['slug'])) {
		$errors[] = "Please provide a descriptive phrase about project sample.";
	} else {
		$slug = htmlspecialchars(convert_smart_quotes(trim($_REQUEST['slug'])));
		if ($slug !== $sampleinfo['slug']) {
			$samplesave['slug'] = $slug;
		}
	}
	// bio
	if (isEmpty($_REQUEST['bio'])) {
		 echo "<div class='AdminError'>No sample's bio? Please try to have a paragraph describing the sample.</div>";
	}
	$bio = htmlspecialchars(convert_smart_quotes(trim($_REQUEST['bio'])));
	if ($bio !== $sampleinfo['bio']) {
		$samplesave['bio'] = $bio;
	}
	// display name
	if (isEmpty($_REQUEST['display_name'])) {
		$display_name = MakeCase(ObfuscatesampleNameAutomatically(htmlspecialchars(convert_smart_quotes(trim($_REQUEST['name'])))));
	} else {
		$display_name = htmlspecialchars(convert_smart_quotes(trim($_REQUEST['display_name'])));
	}
	if ($display_name !== $sampleinfo['display_name']) {
			$samplesave['alt_url'] = GetAltUrl(MakeURL(preg_replace("/\.\s/","",strtolower($display_name))));
			$samplesave['display_name'] = UniqueObfuscatedsampleName($display_name);
	}
	// alt-url standalone change
	if (isEmpty($samplesave['alt_url'])) {
		if (!isEmpty($_REQUEST['alt_url'])) {
			$samplesave['alt_url'] = MakeURL(htmlspecialchars(strtolower(trim($_REQUEST['alt_url']))));
		} else {
			$samplesave['alt_url'] = GetAltUrl(MakeURL(strtolower($display_name)));
		}
	}
	// use display
	isset($_REQUEST['use_display_name']) ? $use_display_name = 1 : $use_display_name = 0;
	if ($use_display_name != $sampleinfo['use_display_name']) {
		$samplesave['use_display_name'] = $use_display_name;
	}
	// active?
	isset($_REQUEST['is_active']) ? $is_active = 1 : $is_active = 0;
	if ($is_active != $sampleinfo['is_active']) {
		$samplesave['is_active'] = $is_active;
	}
	// searchable?
	isset($_REQUEST['is_searchable']) ? $is_searchable = 1 : $is_searchable = 0;
	if ($is_searchable != $sampleinfo['is_searchable']) {
		$samplesave['is_searchable'] = $is_searchable;
	}
	// highlighted?
	isset($_REQUEST['is_highlighted']) ? $is_highlighted = 1 : $is_highlighted = 0;
	if ($is_highlighted != $sampleinfo['is_highlighted']) {
		$samplesave['is_highlighted'] = $is_highlighted;
	}
	// categories update
	if (isset($_REQUEST['categories'])) {
		$categories = array();
		foreach ($_REQUEST['categories'] as $key => $value) {
			$categories[$key] = preg_replace("/[^0-9]/","",$value);
		}
		if (array_diff($categories, $sampleinfo['categories']) || array_diff($sampleinfo['categories'], $categories)) {
			$samplesave['samplecategories'] = $categories;	// categories have been updated
		}
	} else {
		$errors[] = "Please select one or more categories for this sample.";
	}
	// subcategories
	$subcategories = array();
	if (is_countable($_REQUEST['subcategories'])) {
		foreach ($_REQUEST['subcategories'] as $key => $value) {
			$subcategories[$key] = preg_replace("/[^0-9]/","",$value);
		}
	}
	$samplesave['samplesubcategories'] = $subcategories;
	// styles
	if (isset($_REQUEST['styles'])) {
		$styles = array();
		foreach ($_REQUEST['styles'] as $key => $value) {
			$styles[$key] = preg_replace("/[^0-9]/","",$value);
		}
		if (isEmpty($sampleinfo['styles'])) {
			$samplesave['samplestyles'] = $styles;	// styles have been updated
		} else if (array_diff($styles, $sampleinfo['styles']) || array_diff($sampleinfo['styles'], $styles)) {
			$samplesave['samplestyles'] = $styles;	// styles have been updated
		}
	} else {
		$errors[] = "Please select one or more styles of entertainment this sample performs.";
	}
	// locations
	if (isset($_REQUEST['locations'])) {
		$locations = array();
		foreach ($_REQUEST['locations'] as $key => $value) {
			$locations[$key] = preg_replace("/[^0-9]/","",$value);
		}
		if (array_diff($locations, $sampleinfo['locations']) || array_diff($sampleinfo['locations'], $locations)) {
			$samplesave['samplelocations'] = $locations;	// styles have been updated
		}
	} else {
		$errors[] = "Please select one or more cities that this sample is local to.";
	}
	if (!isset($errors)) {
		if (count($samplesave) > 1) { // more than just the oid...
			foreach ($samplesave as $field => $value) {
				if (preg_match("/(oid|samplecategories|samplesubcategories|samplestyles|samplelocations)/",$field)) {
					continue;
				} else {
					$query = sprintf("UPDATE `samples` SET `%s` = '%s' WHERE `oid` = %s",
						mysqli_real_escape_string($conn, $field),
						mysqli_real_escape_string($conn, $value),
						mysqli_real_escape_string($conn, $samplesave['oid'])
					);
					if (mysqli_query($conn,$query) === FALSE) {
						$errors[] = "<B>Did not update sample!</B> Database Failure: ".mysqli_error($conn);
					}
				}
			}
			// update timestamp
			$query = sprintf("UPDATE `samples` SET `last_updated` = '%s' WHERE `oid` = %s",
				mysqli_real_escape_string($conn, DatePHPtoSQL(time())),
				mysqli_real_escape_string($conn, $samplesave['oid'])
			);
			if (mysqli_query($conn,$query) === FALSE) {
				$errors[] = "<B>Did not update sample information!</B> Database Failure: ".mysqli_error($conn);
			}
		}
		foreach (array("samplecategories" => "cid","samplestyles" => "sid","samplelocations" => "lid", "samplesubcategories" => "subid") as $table => $column) {
			if ( is_countable($samplesave[$table]) || ($table == "samplesubcategories") ){
				// wipe out old data
				$query = sprintf("DELETE FROM `%s` WHERE `oid` =  '%s'", 
					mysqli_real_escape_string($conn, $table),
					mysqli_real_escape_string($conn, $samplesave['oid'])
				);
				mysqli_query($conn,$query);
				// pump in new data
				foreach($samplesave[$table] as $id) { // individual cid, sid, or lid items that we're saving
					$query = sprintf("INSERT INTO `%s` (`%s`,`oid`) VALUES (%s,%s)",
						mysqli_real_escape_string($conn, $table),
						mysqli_real_escape_string($conn, $column),
						mysqli_real_escape_string($conn, $id),
						mysqli_real_escape_string($conn, $samplesave['oid'])
					);
					if (mysqli_query($conn,$query) === FALSE) {
						$errors[] = "Error saving category $cid for $oid!" .mysqli_error($conn);
					}
				}
			}// else no category/style/locaiton changes
		}
		// Save Uploaded Files
		$filecount = AdminsampleSaveMedia($oid);
		if ($filecount >= 1) {
			echo "<div class='AdminSuccess'>Added $filecount additional media files!</div>";
		}
		// Modify existing photos
		if (isset($_REQUEST['ImageFeatures'])) {
			foreach ($_REQUEST['ImageFeatures'] as $mid => $change) {
				$mid = preg_replace("/[^0-9]/","",$mid);
				if ($sampleinfo['media']['mid'][$mid] !== $mid) {
					echo "<div class='AdminError'>Media request is not valid.</div>";
				} else {
					// legit media id, do the request
					foreach (array("ToggleHighlight" => "is_highlighted", "ToggleHidden" => "viewable", "Remove" => "") as $action => $column) {
						if ($_REQUEST['ImageFeatures'][$mid] === $action) {
							if ($action === "Remove") {
								unlink($dirlocation . "/i/sample/original-" . $sampleinfo['media']['filename'][$mid]);
								unlink($dirlocation . "/i/sample/" . $sampleinfo['media']['filename'][$mid]);
								$query = sprintf("DELETE FROM `media` WHERE `mid` = '%s'", mysqli_real_escape_string($conn,$mid));
							} else {
								($sampleinfo['media'][$column][$mid] == 1)? $switcheroo = 0 : $switcheroo = 1;
								$query = sprintf("UPDATE `media` SET `%s` = '%s' WHERE `mid` = '%s'",
									mysqli_real_escape_string($conn, $column),
									mysqli_real_escape_string($conn, $switcheroo),
									mysqli_real_escape_string($conn, $mid)
								);
							}
							if (mysqli_query($conn,$query) === FALSE) {
								$errors[] = "Error updating image status $mid!" .mysqli_error($conn);
							}
						}
					}
				}
			}
		}
		// Modify video screen shot
		if (isset($_REQUEST['radio'])) {
			foreach ($_REQUEST['radio'] as $mid => $change) {
				$mid = preg_replace("/[^0-9]/","",$mid);
				if ($sampleinfo['media']['mid'][$mid] !== $mid) {
					echo "<div class='AdminError'>Media request is not valid.</div>";
				} else {
					$change = preg_replace("/[^0-9]/","",$change);
					$fileid = substr($sampleinfo['media']['filename'][$mid], 0, -4);
					// preview images are in jpg format from ResizeImage() and ffmpeg thumbnailer
					if (!copy("$dirlocation/i/sample/$fileid-$change.jpg","$dirlocation/i/sample/$fileid.jpg")) {
						$errors[] = "Failed to replace video thumbnail.";
					}
				}
			}
		}
		// process video actions
		if (isset($_REQUEST['videoaction'])) {
			foreach ($_REQUEST['videoaction'] as $mid => $change) {
				$mid = preg_replace("/[^0-9]/","",$mid);
				if ($sampleinfo['media']['mid'][$mid] !== $mid) {
					echo "<div class='AdminError'>Media request is not valid.</div>";
				} else {
					if ($_REQUEST['videoaction'][$mid] === "delete") {
						$fileid = substr($sampleinfo['media']['filename'][$mid], 0, -4);
						unlink ("$dirlocation/m/". $sampleinfo['media']['filename'][$mid]);
						unlink ("$dirlocation/i/sample/$fileid.jpg");	// video screenies are always jpeg.
						unlink ("$dirlocation/i/sample/$fileid-1.jpg");
						unlink ("$dirlocation/i/sample/$fileid-2.jpg");
						unlink ("$dirlocation/i/sample/$fileid-3.jpg");
						unlink ("$dirlocation/i/sample/$fileid-4.jpg");
						$query = sprintf("DELETE FROM `media` WHERE `mid` = %s", mysqli_real_escape_string($conn,$mid));
					} else {
						$value = preg_replace("/[^01]/","",$_REQUEST['videoaction'][$mid]);
						$query = sprintf("UPDATE `media` SET `viewable` = %s WHERE `mid` = %s",
							mysqli_real_escape_string($conn,$value),
							mysqli_real_escape_string($conn,$mid)
						);
					}
					if (mysqli_query($conn,$query) === FALSE) {
							$errors[] = "Error updating video status $mid!" .mysqli_error($conn);
					}
				}
			}
		} // no video changes
	} // else there are errors!
	if (isset($errors)) { // not included above since new errors could have been introduced
		echo "<div class='AdminError'><B>There are some missing details, please check the form and re-submit.</B><ul>";
		foreach ($errors as $error) {
			echo "<li>$error</li>";
		}
		echo "</ul></div>\n";
	}
	return($oid); // or null if bad
}

function PrepareVideoPlayer($input) {
	// Put video(s) into jwplayer
	global $conn;
	global $videowidth;
	if (is_array($input)) {
		$sampleinfo = $input;
		$videocount = 0;
		// I am the sampleinfo's media keyed array
		// If this is used, SHOW ALL (viewable) VIDEOS
		//if (is_array($sampleinfo['media'])) { // are there videos here (fixes error on the foreach line below)
		if (is_countable($sampleinfo['media']['vidlength'])) { 
			// run the loop once so I have an accurate video count plz
			foreach ($sampleinfo['media']['mid'] as $mid) {
				if (((string)$_REQUEST['page'] === 'admin' OR (string)$sampleinfo['media']['viewable'][$mid] == '1') AND ($sampleinfo['media']['vidlength'][$mid] > 0)) {
					$videocount++;
				}
			}
			foreach ($sampleinfo['media']['mid'] as $mid) {
				// if in the admin page, or is viewable, and media is a video, ...
				if (((string)$_REQUEST['page'] === 'admin' OR (string)$sampleinfo['media']['viewable'][$mid] == '1') AND ($sampleinfo['media']['vidlength'][$mid] > 0)) {
					// single out the one media ID for the Video Player
					$tempsampleinfo = $sampleinfo;
					unset ($tempsampleinfo['media']);	// dump all the media info on this sample, replacing with the one video to display
					$gcd=getCommonDivisor($sampleinfo['media']['width'][$mid],$sampleinfo['media']['height'][$mid]);
					if ((string)$_REQUEST['page'] === "admin") {
						// make video players a reasonable size
						if ($sampleinfo['media']['width'][$mid] > $videowidth) {
							$width = $sampleinfo['media']['width'][$mid];
							$height = $sampleinfo['media']['height'][$mid];
							$scale = $width / $videowidth;
							$tempsampleinfo['media']['width'] = ceil($width / $scale);
							$tempsampleinfo['media']['height'] = ceil($height / $scale);
							$tempsampleinfo['media']['widthdisplay'] = $tempsampleinfo['media']['width'];
							$tempsampleinfo['media']['heightdisplay'] = "'height': '". $tempsampleinfo['media']['height'] ."',";
						}
					} else {
						$tempsampleinfo['media']['width'] = $sampleinfo['media']['width'][$mid];
						$tempsampleinfo['media']['height'] = $sampleinfo['media']['height'][$mid];
						$tempsampleinfo['media']['widthdisplay'] = "100%";
					}
					$tempsampleinfo['media']['realdimensions'] = $sampleinfo['media']['width'][$mid] . "x" . $sampleinfo['media']['height'][$mid];
					$tempsampleinfo['media']['aspectratio'] = ($sampleinfo['media']['width'][$mid]/$gcd) . ":" . ($sampleinfo['media']['height'][$mid]/$gcd);
					$tempsampleinfo['media']['mid'] = $sampleinfo['media']['mid'][$mid];
					$tempsampleinfo['media']['previewimage'] = substr($sampleinfo['media']['filename'][$mid],0,-4) . ".jpg";
					$tempsampleinfo['media']['vidlength'] = $sampleinfo['media']['vidlength'][$mid];
					$tempsampleinfo['media']['name'] = $sampleinfo['media']['name'][$mid];
					$tempsampleinfo['media']['filename'] = $sampleinfo['media']['filename'][$mid];
					$tempsampleinfo['media']['fileid'] = substr($sampleinfo['media']['filename'][$mid], 0, -4);
					$tempsampleinfo['media']['is_highlighted'] = $sampleinfo['media']['is_highlighted'][$mid];
					$tempsampleinfo['media']['viewable'] = $sampleinfo['media']['viewable'][$mid];
					if ((string)$_REQUEST['page'] === 'admin') {
						$tempsampleinfo['media']['published'] = $sampleinfo['media']['published'][$mid];
						if ((string)$sampleinfo['media']['viewable'][$mid] === '1') {
							$tempsampleinfo['classname'] = "VideoPlayer";
						} else {
							$tempsampleinfo['classname'] = "VideoPlayerNOVIEW";
						}
						echo sprintf(
							"<div class='AdminVideoTitle'>%s (%s) %s</div>",
							$sampleinfo['media']['name'][$mid],
							date("i:s",($sampleinfo['media']['vidlength'][$mid])),
							date("F d, Y",$sampleinfo['media']['published'][$mid])
						);
					}
					$tempsampleinfo['media']['vidcount'] = $videocount; // how many videos are there for this sample?
					DisplayVideoPlayer($tempsampleinfo);
					if ((string)$_REQUEST['page'] === 'admin') {
						AdminVideoPreviewChooser($tempsampleinfo);
					}
				}
			}
		}
		if (($_REQUEST['page'] === 'admin') && ((string)$videocount === '0')) {
			echo "<div class='AdminError'>No Videos Available for this sample!</div>";
		}
	} elseif (is_string($input) || is_int($input)) {
		// I just got a media ID only, lemme populate with all info
		// If this is used, show THIS ONE video
		$mid = preg_replace("/[^0-9]/","",$input);
		$sampleinfo = array();
		$query = sprintf("SELECT * FROM `media` WHERE `mid` = %s",
			mysqli_real_escape_string($conn,$mid)
		);
		$result = mysqli_query($conn,$query);
		$row = mysqli_fetch_assoc($result);
		foreach ($row as $fieldname => $value) {
			$sampleinfo['media'][$fieldname] = $value;
		}
		mysqli_free_result($result);
		if ($sampleinfo['media']['viewable'] == 0) {
			echo "<div class='AdminError'>Video not available.</div>";
		} else {
			// now lemme get some meta data of the sample for the player
			// XXX: duplicated from AdminsampleEditSingle
			$query = sprintf("SELECT * FROM `samples` WHERE `oid` = %s", mysqli_real_escape_string($conn,$sampleinfo['media']['oid']));
			$result = mysqli_query($conn,$query);
			$row = mysqli_fetch_assoc($result);
			$sampleinfo['oid'] = $oid;
			foreach ($row as $fieldname => $value) {
				$sampleinfo[$fieldname] = $value;
			}
			mysqli_free_result($result);
			// scale the video if necessary
			if ($sampleinfo['media']['height'][$mid] > $videoheight) {
				$width = $sampleinfo['media']['width'][$mid];
				$height = $sampleinfo['media']['height'][$mid];
				$scale = $height / $videoheight;
				$sampleinfo['media']['width'] = ceil($width / $scale);
				$sampleinfo['media']['height'] = ceil($height / $scale);
			}
			DisplayVideoPlayer($sampleinfo);
		}
	}
}

function FigurePageNav($type,$page=1) {
	global $conn;
	global $pagination;
	if ($type === "list_all" || $type === "list_new") {
		$query = "SELECT COUNT(*) FROM `samples`";
	} else if ($type === "list_feat") {
		$query = "SELECT COUNT(*) FROM `samples` WHERE `is_highlighted` = 1";
	} else if ($type === "list_secret") {
		$query = "SELECT COUNT(*) FROM `samples` WHERE `is_searchable` = 0 OR `is_active` = 0";
	}
	$result = mysqli_query($conn,$query);
	list($count) = mysqli_fetch_array($result);
	mysqli_free_result($result);
	// get maximum number of pages
	$maximum = ceil($count/$pagination); // round up to a whole page number
	// get previous page
	if ($page ==  1) {
		$previous = 1;
	} else {
		$previous = abs($page - 1);
	}
	// get next page
	if ($maximum > $page) {
		$next = ($page + 1);
	} else {
		$next = $page;
	}
	return(
		array(
			"type"=>$type,
			"first"=>1,
			"previous"=>$previous,
			"page"=>$page,
			"next"=>$next,
			"maximum"=>$maximum
		)
	);
}

function AdminsampleSaveNew() {
	// save sample info, locations, styles, categories.
	// then go to AdminsampleSaveMedia() for the media processing
	global $conn;
	(isEmpty($_REQUEST['name']))? $errors[] = "Please enter the sample or act name." : $name = htmlspecialchars(makeCase(convert_smart_quotes(trim($_REQUEST['name']))));
	(isEmpty($_REQUEST['slug']))? $errors[] = "Please provide a short descriptive phrase about sample." : $slug = htmlspecialchars(makeCase(convert_smart_quotes(trim($_REQUEST['slug']))));
	// XXX: we don't deal with CR/newlines or html/markup at all in bio field yet!
	if (isEmpty($_REQUEST['bio'])) {
		echo "<div class='AdminError'>Missing the sample's bio. Please have at least a paragraph describing the sample.</div>"; 
	} else {
		$bio = htmlspecialchars(convert_smart_quotes(trim($_REQUEST['bio'])));
	}
	if (isEmpty($_REQUEST['display_name'])) {
		$display_name = UniqueObfuscatedsampleName(ObfuscatesampleNameAutomatically(htmlspecialchars(convert_smart_quotes(trim($_REQUEST['name'])))));
	} else {
		$display_name = UniqueObfuscatedsampleName(htmlspecialchars(makeCase(convert_smart_quotes(trim($_REQUEST['display_name'])))));
	}
	isset($_REQUEST['use_display_name']) ? $use_display_name = 1 : $use_display_name = 0;
	$is_active = isset($_REQUEST['is_active']);
	isset($_REQUEST['is_searchable']) ? $is_searchable = 1 : $is_searchable = 0;
	isset($_REQUEST['is_highlighted']) ? $is_highlighted = 1 : $is_highlighted = 0;
	if (isset($_REQUEST['categories'])) {
		$categories = array();
		foreach ($_REQUEST['categories'] as $key => $value) {
			$categories[$key] = preg_replace("/[^0-9]/","",$value);
		}
	} else {
		$errors[] = "Please select one or more categories for this sample.";
	}
	if (isset($_REQUEST['styles'])) {
		$styles = array();
		foreach ($_REQUEST['styles'] as $key => $value) {
			$styles[$key] = preg_replace("/[^0-9]/","",$value);
		}
	} else {
		$errors[] = "Please select one or more styles of entertainment this sample performs.";
	}
	if (isset($_REQUEST['locations'])) {
		$locations = array();
		foreach ($_REQUEST['locations'] as $key => $value) {
			$locations[$key] = preg_replace("/[^0-9]/","",$value);
		}
	} else {
		$errors[] = "Please select one or more cities that this sample is local to.";
	}
	// check if this is a duplicate // being a real jerk by including the "cleaned" URL text
	$query = sprintf("SELECT `name` FROM `samples` WHERE `name` = '%s' OR `url` = '%s'",
		mysqli_real_escape_string($conn, $name),
		mysqli_real_escape_string($conn, $url)
	);
	$result = mysqli_query($conn,$query);
	if (mysqli_num_rows($result) > 0) {
		$errors[] = "This sample may already exist in the database! Please check the sample's name carefully.";
	}
	// XXX: This guess at an URL is pretty weaksauce
	$url = MakeURL(strtolower($name));
	$alt_url = GetAltUrl(MakeURL(strtolower($display_name)));	// what's the URL if we're in use_display_name mode?  XXX: This is pretty retarded. I want full names in URL for SEO.  even specifiying an URL at all is unnecessary since going to just search on name anyways.
	// insert into sample table and get the auto_incremented oid
	if (!isset($errors)) {
		$query = sprintf("INSERT INTO `samples` (`name`,`display_name`,`url`,`alt_url`,`slug`,`bio`,`use_display_name`,`is_active`,`is_highlighted`,`is_searchable`,`last_updated`) VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
			mysqli_real_escape_string($conn, $name),
			mysqli_real_escape_string($conn, $display_name),
			mysqli_real_escape_string($conn, $url),
			mysqli_real_escape_string($conn, $alt_url),
			mysqli_real_escape_string($conn, $slug),
			mysqli_real_escape_string($conn, $bio),
			mysqli_real_escape_string($conn, $use_display_name),
			mysqli_real_escape_string($conn, $is_active),
			mysqli_real_escape_string($conn, $is_highlighted),
			mysqli_real_escape_string($conn, $is_searchable),
			mysqli_real_escape_string($conn, DatePHPtoSQL(time()))
		);
		if (mysqli_query($conn,$query) === TRUE) {
			$oid = mysqli_insert_id($conn);
			foreach($categories as $cid) {
				$query = sprintf("INSERT INTO `samplecategories` (`cid`,`oid`) VALUES (%s,%s)",
					mysqli_real_escape_string($conn, $cid),
					mysqli_real_escape_string($conn, $oid)
				);
				if (mysqli_query($conn,$query) === FALSE) {
					$errors[] = "Error saving category $cid for $oid!" .mysqli_error($conn);
				}
			}
			foreach($styles as $sid) {
				$query = sprintf("INSERT INTO `samplestyles` (`sid`,`oid`) VALUES (%s,%s)",
					mysqli_real_escape_string($conn, $sid),
					mysqli_real_escape_string($conn, $oid)
				);
				if (mysqli_query($conn,$query) === FALSE) {
					$errors[] = "Error saving style $sid for $oid!" .mysqli_error($conn);
				}
			}
			foreach($locations as $lid) {
				$query = sprintf("INSERT INTO `samplelocations` (`lid`,`oid`) VALUES (%s,%s)",
					mysqli_real_escape_string($conn, $lid),
					mysqli_real_escape_string($conn, $oid)
				);
				if (mysqli_query($conn,$query) === FALSE) {
					$errors[] = "Error saving location $lid for $oid!" .mysqli_error($conn);
				}
			}
			echo "<div class='AdminSuccess'>sample information for <B>$name</B> updated!</div>";
		} else {
			$errors[] = "<B>Did not update sample!</B> Database Failure: ".mysqli_error($conn);
		}
	}
	if (isset($errors)) {
		echo "<div class='AdminError'><B>There are some missing details preventing us from updating this sample.</B><ul>";
		foreach ($errors as $error) {
			echo "<li>$error</li>";
		}
		echo "</ul></div>\n";
	}
	return($oid); // or null if bad
}

function GetAltUrl($alturl) {
	global $conn;
	$query = sprintf("SELECT `alt_url` FROM `samples` WHERE `alt_url` = '%s'", mysqli_real_escape_string($conn,$alturl));
	$result = mysqli_query($conn,$query);
	if (mysqli_num_rows($result) == 0) {
		return ($alturl);
	} else {
		// go loop for a new alturl
		$number = 1;
		$basename = $alturl; // increment the number, not append another digit
		while (!$done) {
			$alturl = "$basename$number";
			$query = sprintf("SELECT `alt_url` FROM `samples` WHERE `alt_url` = '%s'", mysqli_real_escape_string($conn,$alturl));
			$result = mysqli_query($conn,$query);
			(mysqli_num_rows($result) == 0)? $done++ : $number++; 
		}
		return ($alturl);
	}
}

function AdminsampleSaveMedia($oid) {
	global $conn;
	$savedfilecount = 0;
	// page 1's save sample's media
	if (CheckForFiles()) {
		$newfiles = array();
		// put all uploaded files into the filesystem
		$newfiles = SaveFile("sample"); // should return an array of [fileid, orig name]
		// step thru each uploaded file and process media
		foreach($newfiles as $key => $newfileinfo) {
			if (strlen($newfiles[$key][0]) < 1) {  // [0] is fileid
				continue; // XXX: This isn't a file, this is just SaveFile() noise
			}
			// make thumbnail
			$newfileid = ResizeImage($newfiles[$key][0],"sample");
			// XXX: watermark function here?
			$filename = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $_FILES['filesToUpload']['name'][$key]);
			$mediainfo = MediaInfo($newfileid,"sample");
			$query = sprintf("INSERT INTO `media` (`filename`, `filetype`,
				`oid`, `name`, `thumbwidth`, `thumbheight`, `width`, `height`,
				`vidlength`, `is_highlighted`, `viewable`, `published`
				) VALUES ( '%s','%s',%s,'%s',%s,%s,%s,%s,%s,%s,%s,'%s')",
				mysqli_real_escape_string($conn, $newfileid),
				mysqli_real_escape_string($conn, $mediainfo['filetype']),
				mysqli_real_escape_string($conn, preg_replace("/[^0-9]/",'',$oid)),
				mysqli_real_escape_string($conn, $filename),
				preg_replace("/[^0-9]/",'',$mediainfo['thumbwidth']),
				preg_replace("/[^0-9]/",'',$mediainfo['thumbheight']),
				preg_replace("/[^0-9]/",'',$mediainfo['width']),
				preg_replace("/[^0-9]/",'',$mediainfo['height']),
				preg_replace("/[^0-9]/",'',$mediainfo['vidlength']),
				"0",	// XXX: we don't highlight on upload, user gotta select one manually.
				"1",	// Assume yes, media file is viewable for this initial upload.
				mysqli_real_escape_string($conn, DatePHPtoSQL(time()))
			);
			if (mysqli_query($conn,$query) === TRUE) {
				$savedfilecount++;
			} else {
				$errors[] = "Media file '<i>$filename</i>' not saved in database!<br>". mysqli_error($conn);
			}
		}
	}
	if (is_countable($errors)) {
		foreach ($errors as $error) {
			echo "<div class='AdminError'><B>$error</B></div>";
		}
		return (0);
	} else {
		return ($savedfilecount);
	}
}

function MediaInfo($fileid,$purpose) {
	global $dirlocation;
	//fileid includes confirmed file extension
	$mediainfo = array();
	if (preg_match("/\.jpg/",$fileid)) {
		list($mediainfo['width'], $mediainfo['height'], $filetype, $attr) = getimagesize("$dirlocation/i/$purpose/original-$fileid");
		list($mediainfo['thumbwidth'], $mediainfo['thumbheight'], $thumbtype, $thumbattr) = getimagesize("$dirlocation/i/$purpose/$fileid");
		$mediainfo['filetype'] = "jpg";
		$mediainfo['vidlength'] = 0;
	} else if (preg_match("/\.png/",$fileid)) {
		list($mediainfo['width'], $mediainfo['height'], $filetype, $attr) = getimagesize("$dirlocation/i/$purpose/original-$fileid");
		list($mediainfo['thumbwidth'], $mediainfo['thumbheight'], $thumbtype, $thumbattr) = getimagesize("$dirlocation/i/$purpose/$fileid");
		$mediainfo['filetype'] = "png";
		$mediainfo['vidlength'] = 0;
	} else if (preg_match("/mp4/",$fileid)) {
		/* no ffmpeg PHP library dependancies anymore, since php-ffmpeg is locked to php5 */
		exec("/usr/local/bin/ffprobe -i m/$fileid -v quiet -print_format json -show_format -show_streams -hide_banner", $ffresult);
		$ffresult = implode("",$ffresult);
		$ffinfo = json_decode($ffresult, TRUE);
		$mediainfo['vidlength'] = ceil($ffinfo['format']['duration']);
		$mediainfo['width'] = $ffinfo['streams'][0]['width'];
		$mediainfo['height'] = $ffinfo['streams'][0]['height'];
		$mediainfo['thumbwidth'] = 0;
		$mediainfo['thumbheight'] = 0;
		$mediainfo['filetype'] = "mp4";
	}
	return ($mediainfo);
}

function AdminsampleList() {
	global $conn;
	global $pagination;
	if ($_REQUEST['listpage'] > 0) {
		$page = preg_replace("/[^0-9]/","",$_REQUEST['listpage']);
	} else {
		$page = 1;
	}
	$limit_start = (abs($page - 1) * $pagination);
	$limit_end = $pagination;
	//echo "$limit_start / $limit_end";
	// XXX: Presence -- Figure out what to select, plz
	$query = sprintf("SELECT * FROM `samples` ORDER BY `name` LIMIT %s,%s",
		mysqli_real_escape_string($conn,$limit_start),
		mysqli_real_escape_string($conn,$limit_end)
	);
	$result = mysqli_query($conn,$query);
	$row = mysqli_fetch_assoc($result);
	AdminsampleListPage($result,$page);
	mysqli_free_result($result);
}

function AdminsampleListNew() {
	global $conn;
	global $pagination;
	if ($_REQUEST['listpage'] > 0) {
		$page = preg_replace("/[^0-9]/","",$_REQUEST['listpage']);
	} else {
		$page = 1;
	}
	$limit_start = (abs($page - 1) * $pagination);
	$limit_end = $pagination;
	$query = sprintf("SELECT * FROM `samples` ORDER BY `last_updated` DESC LIMIT %s,%s",
		mysqli_real_escape_string($conn,$limit_start),
		mysqli_real_escape_string($conn,$limit_end)
	);
	$result = mysqli_query($conn,$query);
	$row = mysqli_fetch_assoc($result);
	AdminsampleListPageNew($result,$page);
	mysqli_free_result($result);
}

function AdminsampleListFeat() {
	global $conn;
	global $pagination;
	if ($_REQUEST['listpage'] > 0) {
		$page = preg_replace("/[^0-9]/","",$_REQUEST['listpage']);
	} else {
		$page = 1;
	}
	$limit_start = (abs($page - 1) * $pagination);
	$limit_end = $pagination;
	$query = sprintf("SELECT * FROM `samples` WHERE `is_highlighted` = 1 ORDER BY `name` LIMIT %s,%s",
		mysqli_real_escape_string($conn,$limit_start),
		mysqli_real_escape_string($conn,$limit_end)
	);
	$result = mysqli_query($conn,$query);
	$row = mysqli_fetch_assoc($result);
	AdminsampleListPageFeat($result,$page);
	mysqli_free_result($result);
}

function AdminsampleListSecret() {
	global $conn;
	global $pagination;
	if ($_REQUEST['listpage'] > 0) {
		$page = preg_replace("/[^0-9]/","",$_REQUEST['listpage']);
	} else {
		$page = 1;
	}
	$limit_start = (abs($page - 1) * $pagination);
	$limit_end = $pagination;
	$query = sprintf("SELECT * FROM `samples` WHERE `is_active` = 0 OR `is_searchable` = 0 ORDER BY `is_active` DESC, `name` LIMIT %s,%s",
		mysqli_real_escape_string($conn,$limit_start),
		mysqli_real_escape_string($conn,$limit_end)
	);
	$result = mysqli_query($conn,$query);
	$row = mysqli_fetch_assoc($result);
	AdminsampleListPageSecret($result,$page);
	mysqli_free_result($result);
}

function AdminListsamplesByCategory() {
	global $conn;
	// wtf is the cid for categoryurl
	$query = sprintf(
		"SELECT `cid` FROM `categories` WHERE `url` = '%s'",
		mysqli_real_escape_string($conn,$_REQUEST['categoryurl'])
	);
	$result = mysqli_query($conn,$query);
	$row = mysqli_fetch_assoc($result);
	// wtf are all the oid's associated with the one cid?
	$query = sprintf(
		"SELECT * FROM `samples` LEFT OUTER JOIN `samplecategories` ON `samples`.`oid` = `samplecategories`.`oid` WHERE `samplecategories`.`cid` = %s ORDER BY `samples`.`name`",
		mysqli_real_escape_string($conn,$row['cid'])
	);
	$result = mysqli_query($conn,$query);
	// XXX: no pagination, just a long listing
	AdminsampleListPageByCategory($result,$page);
	mysqli_free_result($result);
}

function AdminListsampleByStyle() {
	global $conn;
	// wtf are all the oid's associated with the one sid?
	$query = sprintf(
		"SELECT * FROM `samples` LEFT OUTER JOIN `samplestyles` ON `samples`.`oid` = `samplestyles`.`oid` WHERE `samplestyles`.`sid` = %s ORDER BY `samples`.`name`",
		mysqli_real_escape_string($conn,preg_replace("/[^0-9]/","",$_REQUEST['sid']))
	);
	$result = mysqli_query($conn,$query);
	$row = mysqli_fetch_assoc($result);
	// XXX: no pagination, just a long listing
	AdminsampleListPageByStyle($result,$page);
	mysqli_free_result($result);
}

function AdminsampleListSearchResults() {
	global $conn;
	if (isEmpty($_REQUEST['q'])) {
		$search = htmlspecialchars(strip_tags(strtolower(trim($_REQUEST['listpage']))));
	} else {
		$search = htmlspecialchars(strip_tags(strtolower(trim($_REQUEST['q']))));
	}
	$search = preg_replace('/[\W]+/', '', $search);	// remove punctuation and dashes
	$query = sprintf(
		"
			SELECT * FROM `samples` WHERE 
			REPLACE(REPLACE(`name`,' ',''),'-','') LIKE '%%%s%%' OR 
			REPLACE(REPLACE(`alt_url`,'-',''),'_','') LIKE '%%%s%%' OR 
			REPLACE(REPLACE(`display_name`,'.',''),'-','') LIKE '%%%s%%' 
			ORDER BY `name`
		",
		mysqli_real_escape_string($conn,$search),
		mysqli_real_escape_string($conn,$search),
		mysqli_real_escape_string($conn,$search)
	);
	$result = mysqli_query($conn,$query);
	$row = mysqli_fetch_assoc($result);
	// XXX: no pagination, just a long listing
	AdminsampleListPageBySearchResult($result,$page);
	mysqli_free_result($result);
}

function AdminEditSingleLocation($lid) {
	global $conn;
	$query = sprintf("SELECT `lid`, `city`,`state` FROM `locations` WHERE `lid` = '%s'",
		mysqli_real_escape_string($conn,$lid)
	);
	$result = mysqli_query($conn,$query);
	$row = mysqli_fetch_assoc($result);
	AdminEditLocation($row);
	mysqli_free_result($result);
}

function AdminListLocations() {
	global $conn;
	$query = "SELECT `lid`, `city`, `state` FROM `locations` ORDER BY `state`, `city`";
	$result = mysqli_query($conn,$query);
	$locationslist = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$locations[] = array(
			"lid" => $row['lid'],
			"city" => $row['city'],
			"state" => StateCodeToName($row['state'])
		);
	}
	mysqli_free_result($result);
	AdminShowLocations($locations);
}

function AdminSaveNewLocation() {
	global $conn;
	if ( (isEmpty($_REQUEST['city'])) || (strlen($_REQUEST['state']) != 2) ) {
		echo "<div class='AdminError'>Please input both a city and state.</div>";
	} else {
		$city = htmlspecialchars(ucwords(trim($_REQUEST['city'])));
		$state = htmlspecialchars(strtoupper(trim($_REQUEST['state'])));
		$query = sprintf("INSERT INTO `locations` (`city`,`state`) VALUES ('%s','%s')",
			mysqli_real_escape_string($conn,$city),
			mysqli_real_escape_string($conn,$state)
		); // XXX: there may be two legitimate cities named the same, but in different states.  Not allowed currently.
		$statename = StateCodeToName($state);
		if (mysqli_query($conn,$query) === TRUE) {
			echo "<div class='AdminSuccess'><B>$city, $statename</B> Successfully Added.</div>";
		} else {
			echo "<div class='AdminError'><B>$city, $statename</B> Failed to Save!<br>". mysqli_error($conn) ."</div>";
		}
	}
}

function AdminDeleteLocation($lid) {
	global $conn;
	$sid = preg_replace("/[^0-9]/","",$lid); // input sanitization -- only numbers
	// find all samples using this location in `samplelocations` and clean 'em up
	$query = sprintf("DELETE FROM `samplelocations` WHERE `lid` = '%s'",
		mysqli_real_escape_string($conn,$lid)
	);
	if (mysqli_query($conn,$query) === FALSE) {
		echo "<div class='AdminError'>Whoa, couldn't delete '$lid' from samplelocations. ". mysqli_error($conn) ."</div>";
	}
	$query = sprintf("DELETE FROM `locations` WHERE `lid` = '%s'",
		mysqli_real_escape_string($conn,$lid)
	);
	if (mysqli_query($conn,$query) === TRUE) {
		echo "<div class='AdminSuccess'>Location removed.</div>";
	} else {
		echo "<div class='AdminError'>Hmm, couldn't delete '$lid' from locations.". mysqli_error($conn) ."</div>";
	}
}

function AdminListStyles() {
	global $conn;
	$query = "SELECT `sid`, `name` FROM `styles`";
	$result = mysqli_query($conn,$query);
	$categorieslist = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$styles[] = array(
			"sid" => $row['sid'],
			"name" => $row['name']
		);
	}
	mysqli_free_result($result);
	aasort($styles,"name");
	$quantity = count($styles);
	AdminShowStyles($styles,$quantity);
}

function AdminSaveSingleLocation() {
	// save an existing location that was just edited
	global $conn;
	if ( (isEmpty($_REQUEST['city'])) || (strlen($_REQUEST['state']) != 2) ) {
		echo "<div class='AdminError'>Please input both a city and state.</div>";
	} else {
		$lid = preg_replace("/[^0-9]/","",$_REQUEST['lid']); // input sanitization -- only numbers
		$city = htmlspecialchars(ucwords(trim($_REQUEST['city'])));
		$state = htmlspecialchars(strtoupper(trim($_REQUEST['state'])));
		$statename = StateCodeToName($state);
		$query = sprintf("UPDATE `locations` SET `city` = '%s', `state` = '%s' WHERE `lid` = '%s'",
			mysqli_real_escape_string($conn,$city),
			mysqli_real_escape_string($conn,$state),
			mysqli_real_escape_string($conn,$lid)
		);
		if (mysqli_query($conn,$query) === TRUE) {
			echo "<div class='AdminSuccess'>Location <B>$city</B>, <B>$statename</B> [$lid] Successfully Updated.</div>";
		} else {
			echo "<div class='AdminError'>Location <B>$city</B>, <B>$statename</B> [$lid] Failed to Update!<br>". mysqli_error($conn) ."</div>";
		}
	}
}

function AdminSaveSingleStyle() {
	// save an existing style that was just edited
	global $conn;
	$sid = preg_replace("/[^0-9]/","",$_REQUEST['sid']); // input sanitization -- only numbers
	$name = htmlspecialchars(ucwords(trim($_REQUEST['name'])));
	if (isEmpty($name)) {
		echo "<div class='AdminError'>Please fill in the style's Name.</div>";
	} else {
		$sid = preg_replace("/\[^0-9]/","",trim($_REQUEST['sid']));
		$query = sprintf("UPDATE `styles` SET `name` = '%s' WHERE `sid` = '%s'",
			mysqli_real_escape_string($conn,$name),
			mysqli_real_escape_string($conn,$sid)
		);
		if (mysqli_query($conn,$query) === TRUE) {
			echo "<div class='AdminSuccess'>Style Entry <B>$name</B> [$sid] Successfully Updated.</div>";
		} else {
			echo "<div class='AdminError'>Style Entry <B>$name</B> [$sid] Failed to Update!<br>". mysqli_error($conn) ."</div>";
		}
	}
}

function AdminDeleteStyle($targetcategoryurl) {
	global $conn;
	$query = sprintf(
		"SELECT `name` FROM `styles` WHERE `sid` = %s",
		mysqli_real_escape_string($conn,preg_replace("/[^0-9]/","",$_REQUEST['sid']))
	);
	$result = mysqli_query($conn,$query);
	$row = mysqli_fetch_assoc($result);
	$nextfunction = "del_style_for_reals";
	$url = "styles_list";
	AdminShowDeleteConfirmation($targetcategoryurl,$row['name'],$url,$url,$nextfunction);  
}

function AdminDeleteStyleGo($sid) {
	global $conn;
	$sid = preg_replace("/[^0-9]/","",$sid); // input sanitization -- only numbers
	// find all samples using this category in `samplecategories` and clean 'em up
	$query = sprintf("DELETE FROM `samplestyles` WHERE `sid` = '%s'",
		mysqli_real_escape_string($conn,$sid)
	);
	if (mysqli_query($conn,$query) === FALSE) {
		echo "<div class='AdminError'>Whoa, couldn't delete '$sid' from samplestyles. ". mysqli_error($conn) ."</div>";
	}
	// delete the style from `styles`
	$query = sprintf("DELETE FROM `styles` WHERE `sid` = '%s'",
		mysqli_real_escape_string($conn,$sid)
	);
	if (mysqli_query($conn,$query) === TRUE) {
		echo "<div class='AdminSuccess'>Style removed.</div>";
	} else {
		echo "<div class='AdminError'>Hmm, couldn't delete '$sid' from categories.". mysqli_error($conn) ."</div>";
	}
}

function AdminEditSingleStyle($sid) {
	global $conn;
	$query = sprintf("SELECT `sid`,`name` FROM `styles` WHERE `sid` = '%s'",
		mysqli_real_escape_string($conn,$sid)
	);
	$result = mysqli_query($conn,$query);
	$row = mysqli_fetch_assoc($result);
	AdminEditStyle($row);
	mysqli_free_result($result);
}

function AdminSaveNewStyle() {
	// save a NEW style
	global $conn;
	if (isEmpty($_REQUEST['name'])) {
		echo "<div class='AdminError'>Please fill in the style's name.</div>";
	} else {
		$name = htmlspecialchars(ucwords(trim($_REQUEST['name'])));
		$query = sprintf("INSERT INTO `styles` (`name`) VALUES ('%s')",
			mysqli_real_escape_string($conn,$name)
		); // I'm trusting that MySQL's UNIQUE will prevent duplicates
		if (mysqli_query($conn,$query) === TRUE) {
			echo "<div class='AdminSuccess'>Style Entry <B>$name</B> Successfully Added.</div>";
		} else {
			echo "<div class='AdminError'>Style Entry <B>$name</B> Failed to Save!<br>". mysqli_error($conn) ."</div>";
		}
	}
}

function AdminEditSingleCategory($targetcategoryurl) {
	global $conn;
	$query = sprintf("SELECT * FROM `categories` WHERE `url` = '%s'",
		mysqli_real_escape_string($conn,$targetcategoryurl)
	);
	$result = mysqli_query($conn,$query);
	$row = mysqli_fetch_assoc($result);
	AdminEditCategory($row);
	mysqli_free_result($result);
}

function AdminEditSingleSubCategory($targetcategoryurl) {
	global $conn;
	$query = sprintf("SELECT * FROM `subcategories` WHERE `url` = '%s'",
		mysqli_real_escape_string($conn,$targetcategoryurl)
	);
	$result = mysqli_query($conn,$query);
	$row = mysqli_fetch_assoc($result);
	$query = sprintf("SELECT `category` FROM `categories` WHERE `cid` = '%s'",
		mysqli_real_escape_string($conn,$row['parent_cid'])
	);
	$result = mysqli_query($conn,$query);
	list($category) = mysqli_fetch_array($result);
	$row['category'] = $category;
	AdminEditSubCategory($row);
}

function AdminSaveSingleCategory($cid) {
	// save an existing category that was just edited
	global $conn;
	global $dirlocation;
	if (CheckForFiles()) {
		list ($fileid, $filename) = SaveFile("category")[0]; // for Categories, only one image uploaded.
		$newfileid = ResizeImage($fileid,"category"); // 728x90
	}
	if ( (isEmpty($_REQUEST['form_url'])) || (isEmpty($_REQUEST['form_category'])) || (isEmpty($_REQUEST['form_description'])) ) {
		echo "<div class='AdminError'>Please fill in all three Category fields</div>";
	} else {
		$cid = preg_replace("/\[^0-9]/","",trim($cid));
		$url = MakeURL(strip_tags(trim($_REQUEST['form_url'])));
		$category = htmlspecialchars(trim($_REQUEST['form_category']));
		if (strlen($_REQUEST['published'])) {
			$published = TRUE;
		} else {
			$published = FALSE;
		}
		// do highlighted carousel image
		$highlighted = FALSE;
		if (strlen($_REQUEST['is_highlighted'])) {
			list ($highlighted_fileid, $highlighted_filename) = SaveCategoryHighlight(); // for Highghlighted Carousel image, only one file uploaded.
			if (!isEmpty($highlighted_fileid)) {
				$query = sprintf("UPDATE `categories` SET `carousel_filename` = '%s', `carousel_id` = '%s' WHERE `cid` = '%s'",
					mysqli_real_escape_string($conn,$highlighted_filename),
					mysqli_real_escape_string($conn,$highlighted_fileid),
					mysqli_real_escape_string($conn,$cid)
				);
				if (mysqli_query($conn,$query) === TRUE) {
					echo "<div class='AdminSuccess'>Category Entry <B>$category</B> [$url] Carousel Image Successfully Updated.</div>";
				} else {
					echo "<div class='AdminError'>Category Entry <B>$category</B> [$url] Failed to Update Carousel Image!<br>". mysqli_error($conn) ."</div>";
				}
			}
			// XXX: I'm leaving behind the old category carousel image.
			$highlighted = TRUE;
		} else {
			$highlighted = 0;
		}
		if ($filename) {
			// delete the old category image file from the system
			$query = sprintf("SELECT `image_id` FROM `categories` WHERE `cid` = '%s'", mysqli_real_escape_string($conn,$cid));
			$result = mysqli_query($conn,$query);
			list($old_fileid) = mysqli_fetch_array($result);
			unlink("$dirlocation/i/category/$old_fileid");
			unlink("$dirlocation/i/category/original-$old_fileid"); // XXX: we're not deleting jpegs, only png.
			$query = sprintf("UPDATE `categories` SET `url` = '%s', `category` = '%s', `description` = '%s', `force_display_names` = '%s', `published` = '%s', `image_filename` = '%s', `image_id` = '%s', `is_highlighted` = '%s', `last_updated` = '%s' WHERE `cid` = '%s'",
				mysqli_real_escape_string($conn,$url),
				mysqli_real_escape_string($conn,$category),
				mysqli_real_escape_string($conn,htmlspecialchars(trim($_REQUEST['form_description']))),
				mysqli_real_escape_string($conn,preg_replace("/[^YNI]/","",$_REQUEST['force_display_names'])),
				mysqli_real_escape_string($conn,$published),
				mysqli_real_escape_string($conn,$filename),
				mysqli_real_escape_string($conn,$newfileid),
				mysqli_real_escape_string($conn,$highlighted),
				mysqli_real_escape_string($conn,DatePHPtoSQL(time())),
				mysqli_real_escape_string($conn,$cid)
			);
		} else {
			$query = sprintf("UPDATE `categories` SET `url` = '%s', `category` = '%s', `description` = '%s', `force_display_names` = '%s', `published` = '%s', `is_highlighted` = '%s', `last_updated` = '%s' WHERE `cid` = '%s'",
				mysqli_real_escape_string($conn,$url),
				mysqli_real_escape_string($conn,$category),
				mysqli_real_escape_string($conn,htmlspecialchars(trim($_REQUEST['form_description']))),
				mysqli_real_escape_string($conn,preg_replace("/[^YNI]/","",$_REQUEST['force_display_names'])),
				mysqli_real_escape_string($conn,$published),
				mysqli_real_escape_string($conn,$highlighted),
				mysqli_real_escape_string($conn,DatePHPtoSQL(time())),
				mysqli_real_escape_string($conn,$cid)
			);
		}
		if (mysqli_query($conn,$query) === TRUE) {
			echo "<div class='AdminSuccess'>Category Entry <B>$category</B> [$url] Successfully Updated.</div>";
		} else {
			echo "<div class='AdminError'>Category Entry <B>$category</B> [$url] Failed to Update!<br>". mysqli_error($conn) ."</div>";
		}
	}
}

function AdminSaveSingleSubCategory($subid) {
	global $conn;
	global $dirlocation;
	if (CheckForFiles()) {
		list ($fileid, $filename) = SaveFile("sample")[0]; // for Categories, only one image uploaded.  // XXX: I suck, just calling this an sample image
		$newfileid = ResizeImage($fileid,"sample"); // 600x400
	}
	if ( (isEmpty($_REQUEST['form_url'])) || (isEmpty($_REQUEST['form_subcategory'])) || (isEmpty($_REQUEST['form_description'])) ) {
		echo "<div class='AdminError'>Please fill in all three Category fields</div>";
	} else {
		$subid = preg_replace("/\[^0-9]/","",trim($subid));
		$url = MakeURL(strtolower(strip_tags(trim($_REQUEST['form_url']))));
		$subcategory = htmlspecialchars(trim($_REQUEST['form_subcategory']));
		if ($filename) {
			// delete the old category image file from the system
			$query = sprintf("SELECT `image_id` FROM `subcategories` WHERE `subid` = '%s'", mysqli_real_escape_string($conn,$subid));
			$result = mysqli_query($conn,$query);
			list($old_fileid) = mysqli_fetch_array($result);
			unlink("$dirlocation/i/sample/$old_fileid");	// XXX: It's an sample image, not a category image. I suck.
			unlink("$dirlocation/i/sample/original-$old_fileid"); // XXX: It's an sample image, not a category image. I suck.
			$query = sprintf("UPDATE `subcategories` SET `url` = '%s', `subcategory` = '%s', `description` = '%s', `image_filename` = '%s', `image_id` = '%s' WHERE `subid` = '%s'",
				mysqli_real_escape_string($conn,$url),
				mysqli_real_escape_string($conn,$subcategory),
				mysqli_real_escape_string($conn,htmlspecialchars(trim($_REQUEST['form_description']))),
				mysqli_real_escape_string($conn,$filename),
				mysqli_real_escape_string($conn,$newfileid),
				mysqli_real_escape_string($conn,$subid)
			);
		} else {
			$query = sprintf("UPDATE `subcategories` SET `url` = '%s', `subcategory` = '%s', `description` = '%s' WHERE `subid` = '%s'",
				mysqli_real_escape_string($conn,$url),
				mysqli_real_escape_string($conn,$subcategory),
				mysqli_real_escape_string($conn,htmlspecialchars(trim($_REQUEST['form_description']))),
				mysqli_real_escape_string($conn,$subid)
			);
		}
		if (mysqli_query($conn,$query) === TRUE) {
			echo "<div class='AdminSuccess'>Sub-category Entry <B>$subcategory</B> [$url] Successfully Updated.</div>";
		} else {
			echo "<div class='AdminError'>Sub-category Entry <B>$subcategory</B> [$url] Failed to Update!<br>". mysqli_error($conn) ."</div>";
		}
	}
}

function AdminDeleteCategory($targetcategoryurl) {
	$nextfunction = "del_category_for_reals";
	$url = "categories_list";
	AdminShowDeleteConfirmation($targetcategoryurl,$targetcategoryurl,$url,$url,$nextfunction);  
}

function AdminDeleteSubCategory($targetcategoryurl) {
	$nextfunction = "del_subcategory_for_reals";
	$url = "categories_list";
	AdminShowDeleteConfirmation($targetcategoryurl,$targetcategoryurl,$url,$url,$nextfunction);  
}

function AdminsampleDeleteGo($oid) {
	global $conn;
	global $dirlocation;
	$oid = preg_replace("/[^0-9]/","",$oid);
	// Delete media files from filesystem
	$query = "SELECT `filename`,`filetype` FROM `media` WHERE `oid` = $oid";
	$result = mysqli_query($conn, $query);	
	while ($row = mysqli_fetch_assoc($result)) {
		$filename = $row['filename'];
		if ($row['filetype'] == "mp4") {
			unlink ("$dirlocation/m/$filename");
			echo "XXX: unlink $dirlocation m $filename\n";
		} else {
			unlink ("$dirlocation/i/sample/$filename");
			unlink ("$dirlocation/i/sample/original-$filename");
		}
	}
	// delete from database tables
	$tables = array("samplestyles","samplecategories","samplelocations","samplesubcategories","media","samples");
	$error = 0;
	foreach ($tables as $table) {
		$query = "DELETE FROM `$table` WHERE `oid` = $oid";
		if (mysqli_query($conn,$query) === FALSE) {
			echo "<div class='AdminError'>Error deleting $oid from $table: ". mysqli_error($conn) ."</div>";
			$error++;
		}
	}
	if ($error == 0) {
		echo "<div class='AdminSuccess'>Who? What? Them be gone, Man.</div>";
	}
}

function AdminDeleteCategoryGo($targetcategoryurl) {
	global $conn;
	global $dirlocation;
	// fetch us the category id for tihs url
	// XXX: could this be some sort of joined thing?  Yeah.  Am I doing it?  Dunno how.  Does it matter?  Not this time.
	$query = sprintf("SELECT `cid`,`image_id`,`carousel_id` FROM `categories` WHERE `url` = '%s'",
		mysqli_real_escape_string($conn,$targetcategoryurl)
	);
	$result = mysqli_query($conn,$query);
	list($cid,$fileid,$carousel_id) = mysqli_fetch_array($result);
	if (isEmpty($cid)) {
		echo "<div class='AdminError'>Huh, I couldn't retrieve the cid from $targetcategoryurl.". mysqli_error($conn) ."</div>";
	}
	// find all samples using this category in `samplecategories` and clean 'em up
	$query = sprintf("DELETE FROM `samplecategories` WHERE `cid` = '%s'",
		mysqli_real_escape_string($conn,$cid)
	);
	if (mysqli_query($conn,$query) === FALSE) {
		echo "<div class='AdminError'>Whoa, couldn't delete '$cid' from samplecategories.". mysqli_error($conn) ."</div>";
	}
	// delete the category from `categories`
	$query = sprintf("DELETE FROM `categories` WHERE `cid` = '%s'",
		mysqli_real_escape_string($conn,$cid)
	);
	// delete the category image file from the system
	@unlink("$dirlocation/i/category/$fileid");
	@unlink("$dirlocation/i/category/original-$fileid"); // XXX: we're not deleting jpegs, only png.
	if (!isEmpty($carousel_id)) {
		unlink("$dirlocation/i/category/$carousel_id");
		unlink("$dirlocation/i/category/original-$carousel_id");
	}
	if (mysqli_query($conn,$query) === TRUE) {
		echo "<div class='AdminSuccess'>The light is green, the trap is clean.</div>";
	} else {
		echo "<div class='AdminError'>Hmm, couldn't delete '$cid' from categories.". mysqli_error($conn) ."</div>";
	}
}

function AdminDeleteSubCategoryGo($targetcategoryurl) {
	global $conn;
	global $dirlocation;
	$query = sprintf("SELECT `subid`,`image_id` FROM `subcategories` WHERE `url` = '%s'",
		mysqli_real_escape_string($conn,$targetcategoryurl)
	);
	$result = mysqli_query($conn,$query);
	list($subid,$fileid) = mysqli_fetch_array($result);
	if (isEmpty($subid)) {
		echo "<div class='AdminError'>Derp, there's no subid for $targetcategoryurl.". mysqli_error($conn) ."</div>";
	} else {
		$query = sprintf("DELETE FROM `samplesubcategories` WHERE `subid` = %s",
			mysqli_real_escape_string($conn,$subid)
		);
		if (mysqli_query($conn,$query) === FALSE) {
			echo "<div class='AdminError'>Snap, couldn't delete '$subid' from samplesubcategories! ". mysqli_error($conn) ."</div>";
		} else {
			$query = sprintf("DELETE FROM `subcategories` WHERE `subid` = '%s'",
				mysqli_real_escape_string($conn,$subid)
			);
			@unlink("$dirlocation/i/category/$fileid");
			@unlink("$dirlocation/i/category/original-$fileid");
			if (mysqli_query($conn,$query) === TRUE) {
				echo "<div class='AdminSuccess'>Silly Subcategory, Say Sayonara!</div>";
			} else {
				echo "<div class='AdminError'>O NOES! '$subid' is too hardcore. ". mysqli_error($conn) ."</div>";
			}
		}
	}
}

function AdminEditCategories() {
	global $conn;
	$query = "SELECT * FROM `categories`";
	$result = mysqli_query($conn,$query);
	$categorieslist = array();
	while ($row = mysqli_fetch_assoc($result)) {
		// does this category have subcategories?
		$subs = 0;
		$query = sprintf("SELECT `subid` FROM `subcategories` WHERE `parent_cid` = '%s'",
			mysqli_real_escape_string($conn,$row['cid'])
		);
		$subresult = mysqli_query($conn,$query);
		if (mysqli_num_rows($subresult) > 0) {
			$subs++;
		}
		$categorieslist[] = array(
			"url" => $row['url'],
			"category" => $row['category'],
			"description" => $row['description'],
			"published" => $row['published'],
			"highlighted" => $row['is_highlighted'],
			"subs" => $subs
		);
	}
	mysqli_free_result($result);
	aasort($categorieslist,"category");
	AdminShowCategories($categorieslist);
}

function AdminEditSubCategories($parent) {
	global $conn;
	if (preg_match("/^[0-9]+$/",$parent)) {
		$parent_cid = $parent;
	} else {
		$parent_cid = CategoryIDFromURL($parent);
	}
	$query = sprintf("SELECT * FROM `subcategories` WHERE `parent_cid` = '%s'",
		mysqli_real_escape_string($conn,$parent_cid)
	);
	$result = mysqli_query($conn,$query);
	$subcategorieslist = array();
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$subcategorieslist[] = array(
				"url" => $row['url'],
				"category" => $row['subcategory'],
				"description" => $row['description'],
				"parent_cid" => $row['parent_cid']
			);
		}
		aasort($subcategorieslist,"category");
		AdminShowSubCategories($subcategorieslist);
	}
}

function AdminSelectCategories($oid = NULL) {
	// for adding or editing an sample
	// XXX: This does not respect user-defined priority/sequence order from webform or database
	global $conn;
	$categorieslist = array();
	$samplecategories = array();
	$query = "SELECT * FROM `categories` ORDER BY `category`";
	$result = mysqli_query($conn,$query);
	while ($row = mysqli_fetch_assoc($result)) {
		$categorieslist[$row['cid']] = $row['category'];
	}
	mysqli_free_result($result);
	if ($oid) {
		$query = sprintf("SELECT `cid` FROM `samplecategories` WHERE `oid` = %s",
			mysqli_real_escape_string($conn,$oid)
		);
		$result = mysqli_query($conn,$query);
		while ($row = mysqli_fetch_assoc($result)) {
			$samplecategories[$row['cid']] = TRUE;
		}
	}
	foreach($categorieslist as $cid => $category) {
		$s = 0; // selected flag
		if ($samplecategories[$cid]) {
			// if sample is assigned to category is in the database
			$s++;
		}
		if (($_REQUEST['formpage'] == 1) && is_countable($_REQUEST['categories'])) {
			// if this is from the new sample webform...
			if (strlen(array_search($cid,$_REQUEST['categories'])) > 0) { // LAME: array_search returns null for a key that's "0"
				// if sample selected to category from form page 1
				$s++;
			}
		}
		$string .= sprintf("<option value='%s'%s>%s</option>",
			$cid,
			($s > 0)? ' selected' : '',
			$category
		);
	}
	return($string);
}

function AdminSelectSubCategories($sampleinfo) {
	// $sampleinfo['oid'] and array in $sampleinfo['categories'] of cid=>cid
	// XXX: This does not respect user-defined priority/sequence order from webform or database
	global $conn;
	$subcategorieslist = array();
	$samplesubcategories = array();
	$parentcatslist = array();
	foreach ($sampleinfo['categories'] as $cid) {
		$parent_cids .= "$cid,";
	}
	$parent_cids = substr($parent_cids, 0, -1);
	$query = sprintf("SELECT * FROM `subcategories` WHERE `parent_cid` IN (%s) ORDER BY `subcategory`",
		mysqli_real_escape_string($conn,$parent_cids)
	);
	$result = mysqli_query($conn,$query);
	if (mysqli_num_rows($result) > 0) {
		while ($row = mysqli_fetch_assoc($result)) {
			$subcategorieslist[$row['subid']] = $row['subcategory'];
			$parentquery = "SELECT `category` FROM `categories` WHERE `cid` = '". $row['parent_cid'] ."'";
			$parentresult = mysqli_query($conn,$parentquery);
			$parentrow = mysqli_fetch_assoc($parentresult);
			$parentcatslist[$row['subid']] = $parentrow['category'];
			mysqli_free_result($parentresult);
		}
		mysqli_free_result($result);
		if ($sampleinfo['oid']) {
			$query = sprintf("SELECT `subid` FROM `samplesubcategories` WHERE `oid` = %s",
				mysqli_real_escape_string($conn,$sampleinfo['oid'])
			);
			$result = mysqli_query($conn,$query);
			while ($row = mysqli_fetch_assoc($result)) {
				$samplesubcategories[$row['subid']] = TRUE;
			}
		}
		?>
				<div class="AdminCategoryListingAddItem" style="margin-left: 2em;">
					<label for="SubCategories">SubCategories</label>
				</div>
				<div class="AdminCategoryListingAddDropDown">
					<select id="SubCategories" multiple="multiple" name="subcategories[]" title="SubCategories" class="sminit">
		<?
		foreach($subcategorieslist as $subid => $subcategory) {
			$s = 0; // selected flag
			if ($samplesubcategories[$subid]) {
				// if sample is assigned to category is in the database
				$s++;
			}
			$string .= sprintf("<option value='%s'%s>%s &#8594; %s</option>",
				$subid,
				($s > 0)? ' selected' : '',
				$parentcatslist[$subid],
				$subcategory
			);
		}
		echo $string;
		?>
					</select>
				</div>
			<div class="clear"></div>
		<?
	}
}

function CategoryNameFromURL($caturl) {
	// simple turn categoryurl to nice category name
	global $conn;
	$query = sprintf("SELECT `category` FROM `categories` WHERE `url` = '%s'", mysqli_real_escape_string($conn,$caturl));
	$result = mysqli_query($conn,$query);
	$row = mysqli_fetch_assoc($result);
	mysqli_free_result($result);
	return ($row['category']);
}

function CategoryIDFromURL($caturl) {
	// simple turn categoryurl to cid
	global $conn;
	$query = sprintf("SELECT `cid` FROM `categories` WHERE `url` = '%s'", mysqli_real_escape_string($conn,$caturl));
	$result = mysqli_query($conn,$query);
	$row = mysqli_fetch_assoc($result);
	mysqli_free_result($result);
	return ($row['cid']);
}

function GetCatUrlFromCID($cid) {
	// simple turn categoryid to url, since categories was first thing I wrote and did it wrong
	global $conn;
	$query = sprintf("SELECT `url` FROM `categories` WHERE `cid` = %s", mysqli_real_escape_string($conn,preg_replace("/[^0-9]/","",$cid)));
	$result = mysqli_query($conn,$query);
	$row = mysqli_fetch_assoc($result);
	mysqli_free_result($result);
	return ($row['url']);
}

function StyleNameFromSID($sid) {
	// simple turn styleID into name
	global $conn;
	$query = sprintf(
		"SELECT `name` FROM `styles` WHERE `sid` = %s",
		mysqli_real_escape_string($conn,preg_replace("/[^0-9]/","",$sid))
	);
	$result = mysqli_query($conn,$query);
	$row = mysqli_fetch_assoc($result);
	mysqli_free_result($result);
	return($row['name']);
}

function AdminSelectStyles($oid = NULL) {
	// for adding or editing an sample // I'm totally duplicating code // please review AdminSelectCategories() above for notes
	global $conn;
	$styleslist = array();
	$samplestyles = array();
	$query = "SELECT * FROM `styles` ORDER BY `name`";
	$result = mysqli_query($conn,$query);
	while ($row = mysqli_fetch_assoc($result)) {
		$styleslist[$row['sid']] = $row['name'];
	}
	mysqli_free_result($result);
	if ($oid) {
		$query = sprintf("SELECT `sid` FROM `samplestyles` WHERE `oid` = %s",
			mysqli_real_escape_string($conn,$oid)
		);
		$result = mysqli_query($conn,$query);
		while ($row = mysqli_fetch_assoc($result)) {
			$samplestyles[$row['sid']] = TRUE;
		}
	}
	foreach($styleslist as $sid => $name) {
		$s = 0;
		if ($samplestyles[$sid]) {
			$s++;
		}
		if (($_REQUEST['formpage'] == 1) && (is_countable($_REQUEST['styles']))) {
			if (strlen(array_search($sid,$_REQUEST['styles'])) > 0) {
				$s++;
			}
		}
		$string .= sprintf("<option value='%s'%s>%s</option>",
			$sid,
			($s > 0)? ' selected' : '',
			$name
		);
	}
	return($string);
}

function AdminSelectLocations($oid = NULL) {
	// for adding or editing an sample // I'm totally duplicating code // please review AdminSelectCategories() above for notes
	global $conn;
	$locationslist = array();
	$samplelocations = array();
	$query = "SELECT * FROM `locations` ORDER BY `state`, `city`";
	$result = mysqli_query($conn,$query);
	while ($row = mysqli_fetch_assoc($result)) {
		$locationslist[$row['lid']] = array($row['city'],$row['state']);
	}
	mysqli_free_result($result);
	if ($oid) {
		$query = sprintf("SELECT `lid` FROM `samplelocations` WHERE `oid` = %s",
			mysqli_real_escape_string($conn,$oid)
		);
		$result = mysqli_query($conn,$query);
		while ($row = mysqli_fetch_assoc($result)) {
			$samplelocations[$row['lid']] = TRUE;
		}
	}
	// insanity to make the dropdown list nice grouped by state
	$oldstate = "";
	foreach($locationslist as $lid => $citystate) {
		if ($citystate[1] != $oldstate) {
			($firstpost)? $string .= "</optgroup>" : $firstpost++;
			$string .= "\n<optgroup label='". StateCodeToName($citystate[1]) ."'>";
			$oldstate = $citystate[1];
		}
		$s = 0;
		if ($samplelocations[$lid]) {
			$s++;
		}
		if (($_REQUEST['formpage'] == 1) && (count($_REQUEST['locations']) > 0)) {
			if (strlen(array_search($lid,$_REQUEST['locations'])) > 0) {
				$s++;
			}
		}
		$string .= sprintf("<option value='%s'%s>%s, %s</option>",
			$lid,
			($s > 0)? ' selected' : '',
			$citystate[0],
			StateCodeToName($citystate[1])
		);
	}
	$string .= "</optgroup>";
	return($string);
}

function AdminSaveNewCategory() {
	// save a NEW category
	global $conn;
	if (isEmpty($_REQUEST['form_category']) || isEmpty($_REQUEST['form_description'])) {
		echo "<div class='AdminError'>Please fill in Category Name, Description and the Category Graphic</div>";
	} else {
		$category = htmlspecialchars(ucwords(trim($_REQUEST['form_category'])));
		$url = MakeURL(strip_tags(trim($_REQUEST['form_url'])));
		if (isEmpty($url)) {
			$url = MakeURL(strtolower($category));
		}

		if (CheckForFiles()) {
			list ($fileid, $filename) = SaveFile("category")[0]; // for Categories, only one image uploaded.
			$newfileid = ResizeImage($fileid,"category"); // 728x90
		}

		// do highlighted carousel image
		$highlighted_fileid = NULL;
		$highlighted_filename = NULL;
		$highlighted = FALSE;
		if (strlen($_REQUEST['highlighted'])) {
			list ($highlighted_fileid, $highlighted_filename) = SaveCategoryHighlight(); // for Highghlighted Carousel image, only one file uploaded.
			$highlighted = TRUE;
		} else {
			$highlighted = 0;
		}

		if (strlen($_REQUEST['published'])) {
			$published = 1;
		} else {
			$published = 0;
		}
		$query = sprintf("INSERT INTO `categories` (`url`,`category`,`description`,`force_display_names`,`published`,`is_highlighted`,`carousel_id`,`carousel_filename`,`image_filename`,`image_id`, `last_updated`) VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
			mysqli_real_escape_string($conn,$url),
			mysqli_real_escape_string($conn,$category),
			mysqli_real_escape_string($conn,htmlspecialchars(ucwords(trim($_REQUEST['form_description'])))),
			mysqli_real_escape_string($conn,preg_replace("/[^YNI]/","",$_REQUEST['force_display_names'])),
			mysqli_real_escape_string($conn,$published),
			mysqli_real_escape_string($conn,$highlighted),
			mysqli_real_escape_string($conn,$highlighted_fileid),
			mysqli_real_escape_string($conn,$highlighted_filename),
			mysqli_real_escape_string($conn,$filename),
			mysqli_real_escape_string($conn,$newfileid),
			mysqli_real_escape_string($conn,DatePHPtoSQL(time()))
		);
		if (mysqli_query($conn,$query) === TRUE) {
			echo "<div class='AdminSuccess'>Category Entry <B>$category</B> [$url] Successfully Added.</div>";
		} else {
			echo "<div class='AdminError'>Category Entry <B>$category</B> [$url] Failed to Save!<br>". mysqli_error($conn) ."</div>";
		}
	}
}

function AdminSaveNewSubCategory() {
	// save a NEW Sub-category
	global $conn;
	if (isEmpty($_REQUEST['form_category']) || isEmpty($_REQUEST['form_description'])) {
		echo "<div class='AdminError'>Please fill in Category Name, Description and the Category Graphic</div>";
	} else {
		$subcategory = htmlspecialchars(ucwords(trim($_REQUEST['form_category'])));
		$url = MakeURL(strtolower(strip_tags(trim($_REQUEST['form_url']))));
		if (isEmpty($url)) {
			$url = MakeURL(strtolower($subcategory));
		}
		if (CheckForFiles()) {
			list ($fileid, $filename) = SaveFile("sample")[0]; // for Categories, only one image uploaded.  // XXX: I suck, just calling this an sample image
			$newfileid = ResizeImage($fileid,"sample"); // 600x400
		} else {
			$error = "<div class='AdminError'>No Sub-category image uploaded!</div>";
		}
		$parent_cid = preg_replace("/[^0-9]/","",$_REQUEST['form_cid']);
		$query = sprintf("INSERT INTO `subcategories` (`url`,`subcategory`,`description`,`parent_cid`,`image_filename`,`image_id`) VALUES ('%s','%s','%s','%s','%s','%s')",
			mysqli_real_escape_string($conn,$url),
			mysqli_real_escape_string($conn,$subcategory),
			mysqli_real_escape_string($conn,htmlspecialchars(ucwords(trim($_REQUEST['form_description'])))),
			mysqli_real_escape_string($conn,$parent_cid),
			mysqli_real_escape_string($conn,$filename),
			mysqli_real_escape_string($conn,$newfileid)
		);
		if (mysqli_query($conn,$query) === TRUE) {
			echo "<div class='AdminSuccess'>Sub-Category <B>$subcategory</B> [$url] Successfully Added.</div>";
			unset ($_REQUEST['form_category']);	// XXX: Uncool but sufficient way to empty the web form
			unset ($_REQUEST['form_description']);
			unset ($_REQUEST['form_url']);
		} else {
			echo "<div class='AdminError'>Sub-Category <B>$category</B> [$url] Failed to Save!<br>". mysqli_error($conn) ."</div>";
		}
		echo $error;
	}
}

function ResizeImage($fileid,$purpose) {
	// Resize image according to its purpose
	global $dirlocation;
	if (preg_match("/\.jpg/",$fileid)) {
		$origimage = imagecreatefromjpeg("$dirlocation/i/$purpose/original-$fileid");
	} elseif (preg_match("/\.png/",$fileid)) {
		$origimage = imagecreatefrompng("$dirlocation/i/$purpose/original-$fileid");
		imagealphablending($origimage, true);
		imagesavealpha($origimage, true);
	}
	if ($origimage) {
		if (preg_match("/category/",$purpose)) {
			$width = 728;
			$height = 90;
		}
		if (preg_match("/sample/",$purpose)) {
			$height = 450;
			$width = abs(round( (imagesX($origimage) / imagesY($origimage)) * $height ));
		}
		if (preg_match("/carousel/",$purpose)) {
			$height = 400;
			$width = 266;
		}
		$newimage = imagecreatetruecolor($width,$height);
		imagesavealpha($newimage, true);
		$color = imagecolorallocatealpha($newimage,0x00,0x00,0x00,127);
		imagefill($newimage, 0, 0, $color);
		// dest , src , x dest, y dest , x src , y src , dest w, dest h, src w, src h
		if (!imagecopyresampled($newimage,$origimage,0, 0, 0, 0, $width, $height, imagesX($origimage), imagesY($origimage))) {
			echo "<div class='AdminError'>Image No Web Resize/Compress WTF $fileid</div>";
		}
		// if its a category, only do a transparent png.  sample, whatever came in.
		if (preg_match("/\.jpg/",$fileid) && (!preg_match("/category/",$purpose))) {
			$newfilename = substr($fileid,0,-4) . ".jpg";
			imagejpeg($newimage, "$dirlocation/i/$purpose/$newfilename", 80); // http://www.ebrueggeman.com/blog/php_image_optimization
		} else if (preg_match("/\.png/",$fileid)) {
			$newfilename = substr($fileid,0,-4) . ".png";
			imagepng($newimage, "$dirlocation/i/$purpose/$newfilename",9);
		}
		imagedestroy($origimage);
		imagedestroy($newimage);
	}
	if (preg_match("/\.mp4/",$fileid)) {
		// create a thumbnail
		// XXXXXX
		exec("/usr/local/bin/ffprobe -i m/$fileid -v quiet -print_format json -show_format -show_streams -hide_banner", $ffresult);
		$ffresult = implode("",$ffresult);
		$ffinfo = json_decode($ffresult, TRUE);
		$vidlength = ceil($ffinfo['format']['duration']);
		$totalframes = $vidlength * 30; // assuming 30fps...
		// XXX: This takes some time to render
		for ($i = 1; $i < 5; $i++) {
			$thumbnailname = substr($fileid,0,-4) . "-$i.jpg";
			$frame = framesToTC(ceil($totalframes*($i * "0.2")), 30); // make four thumbnails every 200~ish frames, assuming 30 fps
			echo "rendering preview $i using frame ". ceil($totalframes*($i * "0.1")) ." at ". $frame ." of $totalframes\n<br>";
			exec ("/usr/local/bin/ffmpeg -v quiet -hide_banner -ss $frame -i $dirlocation/m/$fileid -frames 1 -qscale:v 2 $dirlocation/i/$purpose/$thumbnailname");
		}
		// whatever image is fileid.jpg is the visible thumbnail, others just there for alternates 
		copy ("$dirlocation/i/$purpose/".substr($fileid,0,-4) . "-1.jpg", "$dirlocation/i/$purpose/". substr($fileid,0,-4) .".jpg");
		$newfilename = $fileid;
	}
	return($newfilename);
}

function CheckForFiles() {
	$count = 0;
	foreach ($_FILES['filesToUpload']['error'] as $status){
		if ($status === UPLOAD_ERR_OK) {
			$count++;
		}
	}
	return ($count);
}

function SaveFile($purpose) {
	// save a form's files into their purpose's directory as a unique ID, returning back the id and "file name"
	global $dirlocation;
	$happyuploads = array(); // the array of ids & names of the numerous uploaded files
	$error_types = array(
		1=>"Your file is too large for the server",
		2=>"Your file is larger than expected",
		3=>"Your file upload incomplete, only partially uploaded",
		4=>"You did not select a file, so no file uploaded",
		6=>"Server problem with the temp directory",
		7=>"Server failed to write file to disk",
		8=>"Server PHP extension prevented upload"
	);
	foreach ($_FILES['filesToUpload']['tmp_name'] as $ref => $tmp_name) {
		$gotafile = FALSE;
		//make the filename safe
		$filename = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $_FILES['filesToUpload']['name'][$ref]);
		$errorIndex = $_FILES['filesToUpload']['error'][$ref];
		if ($errorIndex > 0) {
			if ( ($errorIndex != 4) && (!$gotafile) ) {
				// listen, we got at least one file, so I no longer care about "no file uploaded" errors.
				$error_message = $error_types[$_FILES['filesToUpload']['error'][$ref]];
				echo "<div class='AdminError'>File Upload Error: $error_message.</div>";
   			$happyuploads[] = array(NULL,NULL);
			}
		} else {
			$fileid = uniqid();
			// XXX: I am a race condition, where my unconfirmed file name is exposed on the webs
			move_uploaded_file($tmp_name, $dirlocation . "/i/" . $purpose  . "/original-" . $fileid );
			if (filesize($dirlocation . "/i/" . $purpose . "/original-" . $fileid) < 1024) {
				// if the file is smaller than 1kb, I don't trust it.
				unlink($dirlocation . "/i/" . $purpose . "/original-" . $fileid);
				echo "<div class='AdminError'>File Upload Error: File is invalid due to small size.</div>";
				$happyuploads[] = array(NULL,NULL);
			} else {
				// Yay, its a file!  Lets totally blow off the given file name and replace with my own.
				$finfo = finfo_open(FILEINFO_MIME);
				$type = finfo_file($finfo, $dirlocation . "/i/" . $purpose . "/original-" . $fileid);
				if (preg_match("/jpeg/i",$type)) {
					$newfileid = "$fileid.jpg";	
					rename (
						$dirlocation . "/i/" . $purpose . "/original-". $fileid,
						$dirlocation . "/i/" . $purpose . "/original-". $newfileid
					);
				} elseif (preg_match("/png/i",$type)) {
					$newfileid = "$fileid.png";
					rename (
						$dirlocation . "/i/" . $purpose . "/original-". $fileid,
						$dirlocation . "/i/" . $purpose . "/original-". $newfileid
					);
				} elseif (preg_match("/(mp4|m4v)/i",$type)) {
					$newfileid = "$fileid.mp4";
					rename (
						$dirlocation . "/i/" . $purpose . "/original-". $fileid,
						$dirlocation . "/m/$newfileid"
					);
				} elseif (preg_match("/word/i",$type)) {
					$newfileid = "$fileid.doc";
					rename (
						$dirlocation . "/i/" . $purpose . "/original-". $fileid,
						$dirlocation . "/i/" . $purpose . "/original-". $newfileid
					);
				} elseif (preg_match("/excel/i",$type)) {
					$newfileid = "$fileid.xls";
					rename (
						$dirlocation . "/i/" . $purpose . "/original-". $fileid,
						$dirlocation . "/i/" . $purpose . "/original-". $newfileid
					);
				} elseif (preg_match("/pdf/i",$type)) {
					$newfileid = "$fileid.pdf";
					rename (
						$dirlocation . "/i/" . $purpose . "/original-". $fileid,
						$dirlocation . "/i/" . $purpose . "/original-". $newfileid
					);
				} else {
					$newfileid = $fileid;
					rename (
						$dirlocation . "/i/" . $purpose . "/original-". $fileid,
						$dirlocation . "/i/" . $purpose . "/original-". $newfileid
					);
				}
				$happyuploads[] = array($newfileid,$filename);
				$gotafile = TRUE;
			}
		}
	}
	// We accepted a positive number of files !
	return $happyuploads;
}

function SaveCategoryHighlight() {
	// save categorty highlight image as a unique ID, returning back the id and "file name"
	// I'm repeating code because I suck sometimes
	global $dirlocation;
	$happyuploads = array(); // the array of ids & names of the numerous uploaded files
	$error_types = array(
		1=>"Your file is too large for the server",
		2=>"Your file is larger than expected",
		3=>"Your file upload incomplete, only partially uploaded",
		4=>"You did not select a file, so no file uploaded",
		6=>"Server problem with the temp directory",
		7=>"Server failed to write file to disk",
		8=>"Server PHP extension prevented upload"
	);
	$gotafile = FALSE;
	//make the filename safe
	$filename = preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $_FILES['carousel_image']['name']);
	$errorIndex = $_FILES['carousel_image']['error'];
	if ($errorIndex > 0) {
		if ( ($errorIndex != 4) && (!$gotafile) ) {
			// listen, we got at least one file, so I no longer care about "no file uploaded" errors.
			$error_message = $error_types[$_FILES['carousel_image']['error']];
			echo "<div class='AdminError'>File Upload Error: $error_message.</div>";
 			$happyuploads[] = array(NULL,NULL);
		}
	} else {
		$fileid = uniqid();
		// XXX: I am a race condition, where my unconfirmed file name is exposed on the webs
		move_uploaded_file($_FILES['carousel_image']['tmp_name'], $dirlocation . "/i/category/original-" . $fileid );
		if (filesize($dirlocation . "/i/category/original-" . $fileid) < 1024) {
			// if the file is smaller than 1kb, I don't trust it.
			unlink($dirlocation . "/i/category/original-" . $fileid);
			echo "<div class='AdminError'>File Upload Error: File is invalid due to small size.</div>";
			$happyuploads[] = array(NULL,NULL);
		} else {
			// Yay, its a file!  Lets totally blow off the given file name and replace with my own.
			$finfo = finfo_open(FILEINFO_MIME);
			$type = finfo_file($finfo, $dirlocation . "/i/category/original-" . $fileid);
			if (preg_match("/jpeg/i",$type)) {
				$newfileid = "$fileid.jpg";	
				rename (
					$dirlocation . "/i/category/original-". $fileid,
					$dirlocation . "/i/category/original-". $newfileid
				);
			} elseif (preg_match("/png/i",$type)) {
				$newfileid = "$fileid.png";
				rename (
					$dirlocation . "/i/category/original-". $fileid,
					$dirlocation . "/i/category/original-". $newfileid
				);
			} 
			$happyuploads[] = array($newfileid,$filename);
			$gotafile = TRUE;
		}
	}
	// XXX: Yes, this whole routine is retarded. I just want it over with.
	// get the damn category highlight carousel image, do the thing, move the hell on.
	list ($fileid, $filename) = $happyuploads[0]; // for Highghlighted Carousel image, only one file uploaded.
	if (preg_match("/\.jpg/",$fileid)) {
		$origimage = imagecreatefromjpeg("$dirlocation/i/category/original-$fileid");
	} elseif (preg_match("/\.png/",$fileid)) {
		$origimage = imagecreatefrompng("$dirlocation/i/category/original-$fileid");
		imagealphablending($origimage, true);
		imagesavealpha($origimage, true);
	}
	// XXX: hardcoded carousel highlighted size!
	if ($origimage) {
		$height = 266;
		$width = 400;
		$newimage = imagecreatetruecolor($width,$height);
		imagesavealpha($newimage, true);
		$color = imagecolorallocatealpha($newimage,0x00,0x00,0x00,127);
		imagefill($newimage, 0, 0, $color);
		// dest , src , x dest, y dest , x src , y src , dest w, dest h, src w, src h
		if (!imagecopyresampled($newimage,$origimage,0, 0, 0, 0, $width, $height, imagesX($origimage), imagesY($origimage))) {
			echo "<div class='AdminError'>Highlight Image No Web Resize/Compress WTF $fileid</div>";
		}
		// if its a category, only do a transparent png.  sample, whatever came in.
		if (preg_match("/\.jpg/",$fileid)) {
			$newfilename = substr($fileid,0,-4) . ".jpg";
			imagejpeg($newimage, "$dirlocation/i/category/$newfilename", 80); // http://www.ebrueggeman.com/blog/php_image_optimization
		} else if (preg_match("/\.png/",$fileid)) {
			$newfilename = substr($fileid,0,-4) . ".png";
			imagepng($newimage, "$dirlocation/i/category/$newfilename",9);
		}
		imagedestroy($origimage);
		imagedestroy($newimage);
	}
	$alldone = array($fileid,$filename);
	return($alldone);
}

function ShowPhotoArray($mediadata) {
	// show a bunch of photos
	// argument is just $sampleinfo['media']
	global $conn;
	$photosorder = array();
	$videos = array();
	if (is_array($mediadata)) {
		foreach ($mediadata['mid'] as $arraykey => $mid) {
			if (preg_match("/png|jpg/",$mediadata['filetype'][$mid])) {
				// check if highlighted is viewable, then put highlighted first
				if (($mediadata['is_highlighted'][$mid] == 1) && ($mediadata['viewable'][$mid] == 1)) {
					$location = $arraykey;
					$photosorder[$location] = $mediadata['mid'][$mid];
				}
				// viewable items next, sorted by recent published first
				if (($mediadata['viewable'][$mid] == 1) && ($mediadata['is_highlighted'][$mid] == 0)) {
					$location = $arraykey * 100;	// put this media ID later in the sort index
					$photosorder[$location] = $mediadata['mid'][$mid];
				}
				// non-viewable crap, with marker
				if ($mediadata['viewable'][$mid] == 0) {
					$location = $arraykey * 1000;	// put this media ID later in the sort index
					$photosorder[$location] = $mediadata['mid'][$mid];
				}
			} else {
				$videos[] = $mid;
			}
		}
	} else {
		if ($_REQUEST['page'] === 'admin') {
			echo "<div class='AdminError'>No Photos Available for this sample!</div>";
		}
	}
	ksort($photosorder, SORT_NUMERIC);
	foreach ($photosorder as $key => $mid) {
		//echo "$key = $mid / real mid: ".$mediadata['mid'][$mid] ."<br>";
		if ($mediadata['is_highlighted'][$mid]) {
			$highlightclass = "AdminImagesPreviewHighlighted";
		} elseif ($mediadata['viewable'][$mid] == 0) {
			$highlightclass = "AdminImagesPreviewNotVisible";
		} else {
			$highlightclass = "AdminImagesPreviewNormal";
		}
		$megapixels = $mediadata['width'][$mid] * $mediadata['height'][$mid];
		// if image is over 1 megapixel, call it "high-res"
		// XXX: what about DPI? This is a bad way and a bad place to do this.
		if ($megapixels > 1000000) {
			$adminimageicon = "AdminImageIconHighRes";
		} else {
			$adminimageicon = "AdminImageIconLowRes";
		}
		if ($mediadata['is_highlighted'][$mid] == 1) {
			$highlighted = "Remove Highlight";
			$highlightstatus = "Highlighted";
		} else {
			$highlighted = "Highlight This";
			$highlightstatus = "Not Highlighted";
		}
		if ($mediadata['viewable'][$mid] == 1) {
			$viewable = "Make Hidden";
			$viewablestatus = "Visible";
		} else {
			$viewable = "Make Visible";
			$viewablestatus = "Hidden";
		}
		if (strlen($mediadata['name'][$mid]) > 18) {
			$filename = htmlspecialchars(substr($mediadata['name'][$mid],0,18) . "...");
		} else {
			$filename = htmlspecialchars($mediadata['name'][$mid]);
		}
		// attempt to provide video preview image options
		$videothumboptions = "";
		foreach ($videos as $vidmid) {
			$videothumboptions .= sprintf(
				"<option value='VidThumb%s'>Video %s</option>",
				$mid,
				htmlspecialchars(substr($mediadata['name'][$vidmid],0,16))
			);
		}
		$string = sprintf(
			// fields
			"<div class='CheckBoxImageContainer'>".
			"<a href='/i/sample/%s' target='_new' border='0'>".
			"<img class='%s' src='/i/sample/%s' data-width='%s' data-height='%s' alt='%s' title='%s'>".
			"</a>".
			"<div class='%s'></div>".
			"<select name='ImageFeatures[%s]' class='DropDownImage' %s>". // final %s colors list pink if not viewable
			"<option value='' disabled='disabled' selected='selected'>Image Features</option>".
			"<option value='ToggleHighlight'>%s</option>".
			"<option value='ToggleHidden'>%s</option>".
			"$videothumboptions". 
			"<option value='Remove'>Delete Image</option>".
			"<optgroup disabled='disabled' label='Image Info'>".
			"<option value='' disabled='disabled'>%s</option>".	// filename
			"<option value='' disabled='disabled'>%s</option>".	// highlightstatus
			"<option value='' disabled='disabled'>%s</option>".	// viewablestatus
			"<option value='' disabled='disabled'>Size: %sx%s</option>".
			"<option value='' disabled='disabled'>Uploaded: %s</option>".
			"</select></div>\n",
			// values
			"original-".$mediadata['filename'][$mid],
			$highlightclass,
			$mediadata['filename'][$mid],
			$mediadata['thumbwidth'][$mid],
			$mediadata['thumbheight'][$mid],
			$mediadata['name'][$mid],
			$mediadata['name'][$mid],
			$adminimageicon,
			$mediadata['mid'][$mid],
			((int)$mediadata['viewable'][$mid] === 0)? 'style="background-color: #FFBABA" ' : '',
			$highlighted,
			$viewable,
			$filename,
			$highlightstatus,
			$viewablestatus,
			$mediadata['width'][$mid],
			$mediadata['height'][$mid],
			date("M d, Y",$mediadata['published'][$mid])
		);
		echo "$string\n";
	}
}

function UploadProgress() {
	// ajax file upload progress percentage for URL /uploadprogress
	$key = ini_get("session.upload_progress.prefix") . "irevform";
	if (!empty($_SESSION[$key])) {
		$current = $_SESSION[$key]["bytes_processed"];
		$total = $_SESSION[$key]["content_length"];
		echo $current < $total ? ceil($current / $total * 100) : 100;
	} else {
		echo "100";
	}
}

function aasort (&$array, $key) {
	// sort an array's array by the sub-array's key name
	$sorter=array();
	$ret=array();
	if (isset($array)) {
		reset($array);
		foreach ($array as $ii => $va) {
			$sorter[$ii]=$va[$key];
		}
		asort($sorter);
		foreach ($sorter as $ii => $va) {
			$ret[$ii]=$array[$ii];
		}
		$array=$ret;
	}
}

function HashPassword($password) {
		$hash = password_hash($password, PASSWORD_DEFAULT);
    return $hash;
}

function ValidatePassword($password, $correctHash) {
    $result = password_verify($password, $correctHash);
    return $result;
}

function nicetime($date) {
	if(empty($date)) {
		return "ERROR: No date provided";
	}
	$periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
	$lengths = array("60","60","24","7","4.35","12","10");
	$now = time();
	$unix_date = strtotime($date);
	// check validity of date
	if(empty($unix_date)) {
		return "ERROR: Invalid date";
	}
	// is it future date or past date
	if($now > $unix_date) {
		$difference = $now - $unix_date;
		$tense = "ago";
	} else if ($now == $unix_date) {
		$tense = "Just now";
	} else {
		$difference = $unix_date - $now;
		$tense = "from now";
	} 
	for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
		$difference /= $lengths[$j];
	}
	$difference = round($difference);
	if($difference != 1) {
	//  $periods[$j] .= "s"; // plural for English language
		$periods = array("seconds", "minutes", "hours", "days", "weeks", "months", "years", "decades"); // plural for international words
	}
	if ($tense == "Just now") {
		return ($tense);
	} else {
		return "$difference $periods[$j] {$tense}";
	}
}

function StatesArray() {
	return(array('AL'=>"Alabama",'AK'=>"Alaska",'AZ'=>"Arizona",'AR'=>"Arkansas",'CA'=>"California",'CO'=>"Colorado",'CT'=>"Connecticut",'DE'=>"Delaware",'DC'=>"District Of Columbia",'FL'=>"Florida",'GA'=>"Georgia",'HI'=>"Hawaii",'ID'=>"Idaho",'IL'=>"Illinois", 'IN'=>"Indiana", 'IA'=>"Iowa",  'KS'=>"Kansas",'KY'=>"Kentucky",'LA'=>"Louisiana",'ME'=>"Maine",'MD'=>"Maryland", 'MA'=>"Massachusetts",'MI'=>"Michigan",'MN'=>"Minnesota",'MS'=>"Mississippi",'MO'=>"Missouri",'MT'=>"Montana",'NE'=>"Nebraska",'NV'=>"Nevada",'NH'=>"New Hampshire",'NJ'=>"New Jersey",'NM'=>"New Mexico",'NY'=>"New York",'NC'=>"North Carolina",'ND'=>"North Dakota",'OH'=>"Ohio",'OK'=>"Oklahoma", 'OR'=>"Oregon",'PA'=>"Pennsylvania",'RI'=>"Rhode Island",'SC'=>"South Carolina",'SD'=>"South Dakota",'TN'=>"Tennessee",'TX'=>"Texas",'UT'=>"Utah",'VT'=>"Vermont",'VA'=>"Virginia",'WA'=>"Washington",'WV'=>"West Virginia",'WI'=>"Wisconsin",'WY'=>"Wyoming",'UK'=>'United Kingdom','AU'=>"Australia",'MX'=>"Mexico",'CN'=>"China",'CD'=>"Canada"));
}

function StateCodeToName($code) {
	$states = StatesArray();
	return($states[$code]);
}

function StateNameToCode($state) {
	$states = StatesArray();
	return(array_search($state, $states));
}

function DisplayNamesOptionsDropDown($cid) {
	// Show a dropdown of category's "use real or obfuscated display name" per category
	// N force real names only, I individual sample mode, Y force display names only
	global $conn;
	if ($cid) {
		$query = sprintf("SELECT `force_display_names` FROM `categories` WHERE `cid` = %s",
			preg_replace("/[^0-9]/","",$cid)
		);
		$result = mysqli_query($conn,$query);
		$row = mysqli_fetch_assoc($result);
	}
	$display_mode = $row['force_display_names'];
	$options = array(
		"I" => "Use the Individual Sample's Setting",
		"N" => "Real Names to be used for All Samples in this Category",
		"Y" => "Obfuscated Display Names to be used for All Samples in this Category"
	);
	$string = "";
	foreach ($options as $key => $value) {
		$string .= sprintf("<option value='%s'%s>%s</option>",
			$key,
			($key == $display_mode)? ' selected="SELECTED"' : '',
			$value
		);
	}
	return ($string);
}

function StateOptionsDropDown($active) {
	// show a dropdown of states with the active state highlighted, or just zend a zero for nuffin
	$states = StatesArray();
	$string = "";
	foreach($states as $code => $state) {
		$string .= sprintf("<option value='%s'%s>%s</option>",
			$code,
			($active == $code)? ' selected="SELECTED"' : '', // yeah bitches
			$state
		);
	}
	return($string);
}

function convert_smart_quotes($string) {
	// utf-8
 $search = array(
	 	"\xe2\x80\x98",
		"\xe2\x80\x99",
		"\xe2\x80\x9c",
		"\xe2\x80\x9d",
		"\xe2\x80\x93",
		"\xe2\x80\x94",
		"\xe2\x80\xa6"
	);
	$replace = array(
		"'",
		"'",
		'"',
		'"',
		'-',
		'--',
		'...'
	);
	$string = str_replace($search, $replace, $string);
	// windows
	$search = array(
		chr(133),
		chr(145),
		chr(146),
		chr(147),
		chr(148),
		chr(150),
		chr(151)
	);
	$replace = array(
		'...',
		"'",
		"'",
		'"',
		'"',
		'-',
		'--'
	);
	return str_replace($search, $replace, $string);
}

function MakeURL($str, $replace=array(), $delimiter='-') {
	// XXX: Using underscore for spaces per LeviV
	// XXX: Back to using dashes, since Google SEO prefers it.
	setlocale(LC_ALL, 'en_US.UTF8');
	if( !empty($replace) ) {
		$str = str_replace((array)$replace, ' ', $str);
	}
	$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
	$clean = preg_replace("/&quot;/", '', $clean);
	$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
	$clean = strtolower(trim($clean, '-'));
	$clean = trim(preg_replace("/[\/_|+ -]+/", $delimiter, $clean));
	return $clean;
}

function isEmpty($var, $allow_false = false, $allow_ws = false) {
	// freaking sick of trim, strlen, empty, and isset weirdness
	// XXX: we don't trim anymore, we don't check if $var is an actual array.
	if (!isset($var) || is_null($var) || is_array($var) || ($allow_false === false && is_bool($var) && $var === false) ) {
		return true;
	} else {
		return false;
	}
}

//Converts a string to Title Case based on one set of title case rules
// put <no_parse></no_parse> around content that you don't want to be parsed by the title case rules
function makeCase($string) {
	//remove no_parse content
	$string_array = preg_split("/(<no_parse>|<\/no_parse>)+/i",$string);
	$newString = "";
	for ($k=0; $k<count($string_array); $k=$k+2) {
		$string = $string_array[$k];
		//if the entire string is upper case dont perform any title case on it
		if ($string != strtoupper($string)){
			//TITLE CASE RULES:
			//1.) uppercase the first char in every word
			// php5 // $new = preg_replace("/(^|\s|\'|'|\"|-){1}([a-z]){1}/ie","''.stripslashes('\\1').''.stripslashes(strtoupper('\\2')).''", $string);
			$new = preg_replace_callback(
				"/(^|\s|\'|'|\"|-){1}([a-z]){1}/i",
				function($m) {
					return stripslashes($m[1]) . stripslashes(strtoupper($m[2]));
				},
				$string
			);
			//2.) lower case words exempt from title case
			// Lowercase all articles, coordinate conjunctions ("and", "or", "nor"), and prepositions regardless of length, when they are other than the first or last word.
			// Lowercase the "to" in an infinitive." - this rule is of course aproximated since it is contex sensitive
			$matches = array();
			// perform recusive matching on the following words
			preg_match_all("/(\sof|\sis|\sa|\san|\sthe|\sbut|\sor|\snot|\syet|\sat|\son|\sin|\sover|\sabove|\sunder|\sbelow|\sbehind|\snext\sto|\sbeside|\sby|\samoung|\sbetween|\sby|\still|\ssince|\sdurring|\sfor|\sthroughout|\sto|\sand){2}/i",$new ,$matches);
			for ($i=0; $i<count($matches); $i++) {
				for ($j=0; $j<count($matches[$i]); $j++){
					//$new = preg_replace("/(".$matches[$i][$j]."\s)/ise","''.strtolower('\\1').''",$new);
					$new = preg_replace_callback("/(".$matches[$i][$j]."\s)/is", function ($m) { return strtolower($m[1]); }, $new);
				}
			}
			//3.) do not allow upper case appostraphies
			//$new = preg_replace("/(\w'S)/ie","''.strtolower('\\1').''",$new);
			$new = preg_replace_callback("/(\w'S)/i", function($m) { return strtolower($m[1]); }, $new);
			//$new = preg_replace("/(\w'\w)/ie","''.strtolower('\\1').''",$new);
			$new = preg_replace_callback("/(\w'\w)/i", function($m) { return strtolower($m[1]); }, $new);
			//$new = preg_replace("/(\W)(of|a|an|the|but|or|not|yet|at|on|in|over|above|under|below|behind|next to| beside|by|amoung|between|by|till|since|durring|for|throughout|to|and)(\W)/ise","'\\1'.strtolower('\\2').'\\3'",$new);
			$new = preg_replace_callback(
				"/(\W)(of|a|an|the|but|or|not|yet|at|on|in|over|above|under|below|behind|next to| beside|by|amoung|between|by|till|since|durring|for|throughout|to|and)(\W)/is",
				function ($m) { 
					return $m[1] . strtolower($m[2]) . $m[3];
				},
				$new
			);
			//4.) capitalize first letter in the string always
			//$new = preg_replace("/(^[a-z]){1}/ie","''.strtoupper('\\1').''", $new);
			$new = preg_replace_callback("/(^[a-z]){1}/i", function($m) { return strtoupper($m[1]); }, $new);
			$new = stripslashes($new);
			$string_array[$k] = $new;
		}
	}
	for ($k=0; $k<count($string_array); $k++){
		$newString .= $string_array[$k];
	}
	return($newString);
};

function ScriptTime($starttime) {
	$mtime = microtime(); $mtime = explode(" ",$mtime); $mtime = $mtime[1] + $mtime[0]; $endtime = $mtime; $totaltime = ($endtime - $starttime) * 1000;
	$totaltime = sprintf("%.2f", $totaltime);
	echo "<!-- This page was created in ".$totaltime." milliseconds -->"; 
}

function CurPageURL() {
	$pageURL = 'http';
	if (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"]) == "on") {
		$pageURL .= "s";
	}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	$pageURL = preg_replace('/\?.*/', '', $pageURL); // drop any query string crap that comes in from Facebook Like/Share links
	return $pageURL;
}

function CurServerURL() {
	$serverURL = "http";
	if (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"]) == "on") {
		$serverURL .= "s";
	}
	$serverURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$serverURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
	} else {
		$serverURL .= $_SERVER["SERVER_NAME"];
	}
	$serverURL .= "/";
	return $serverURL;
}

function ClosestWord($input,$possibles) {
	// returns closest or matching word to $input, as selected from the $possibles array
	// no shortest distance found, yet
	$shortest = -1;
	// loop through words to find the closest
	foreach ($possibles as $word) {
		// calculate the distance between the input word,
		// and the current word
		//$lev = similar_text($input, $word);
		$lev = levenshtein($input, $word);
		// check for an exact match
		if ($lev == 0) {
			// closest word is this one (exact match)
			$closest = $word;
			$shortest = 0;
			// break out of the loop; we've found an exact match
			break;
		}
		// if this distance is less than the next found shortest
		// distance, OR if a next shortest word has not yet been found
		if ($lev <= $shortest || $shortest < 0) {
			// set the closest match, and shortest distance
			$closest  = $word;
			$shortest = $lev;
		}
	}
	//if ($shortest == 0)
	return ($closest);
}

function btlfsa($word1,$word2) {
	$score = 0;
	$remainder  = preg_replace("/[".preg_replace("/[^A-Za-z0-9\']/",' ',$word1)."]/i",'',$word2);
	$remainder .= preg_replace("/[".preg_replace("/[^A-Za-z0-9\']/",' ',$word2)."]/i",'',$word1);
	$score      = strlen($remainder)*2;
	// Take the difference in string length and add it to the score
	$w1_len  = strlen($word1);
	$w2_len  = strlen($word2);
	$score  += $w1_len > $w2_len ? $w1_len - $w2_len : $w2_len - $w1_len;

	$w1 = $w1_len > $w2_len ? $word1 : $word2;
	$w2 = $w1_len > $w2_len ? $word2 : $word1;

	for($i=0; $i < strlen($w1); $i++) {
		if ( !isset($w2[$i]) || $w1[$i] != $w2[$i] ) {
			$score++;
		}
	}
	return $score;
}

function AltClosestWord($misspelled,$suggestions) {
	// Firstly order an array based on levenshtein
	$levenshtein_ordered = array();
	foreach ( $suggestions as $suggestion ) {
		$levenshtein_ordered[$suggestion] = levenshtein($misspelled,$suggestion);
	}
	asort($levenshtein_ordered, SORT_NUMERIC );
	// Secondly order an array based on btlfsa
	$btlfsa_ordered = array();
	foreach ( $suggestions as $suggestion ) {
		$btlfsa_ordered[$suggestion] = btlfsa($misspelled,$suggestion);
	}
	asort($btlfsa_ordered, SORT_NUMERIC );
	$insertMeOnTop = array();
	foreach ($btlfsa_ordered as $name => $cost) {
		if (preg_match("/$misspelled/i",$name)) {
			// if the sample name was in the URL, it wins regardless
			$insertMeOnTop = array($name => 0);
		}
	}
	if (count($insertMeOnTop) > 0) {
		$btlfsa_ordered = array_reverse($btlfsa_ordered, true);
		//list($key, $value) = each($insertMeOnTop); //deprec php7.4
		foreach($insertMeOnTop as $key => $value) {
			unset($btlfsa_ordered[$key]);
			$btlfsa_ordered[$key] = $value;
			$btlfsa_ordered = array_reverse($btlfsa_ordered, true);
		}
	}
	reset($btlfsa_ordered);
	//echo "<!--"; print_r ($btlfsa_ordered); echo "-->";
	$closestword = key($btlfsa_ordered);
	//echo $closestword;
	//exit;
	return($closestword);
}

function getDntStatus() {
	// returns TRUE if Do-Not-Track is on and is equal to 1,
	// returns FALSE if DNT is unset or not equal to 1.
	// return (FALSE); // force my facebook like button
	return (isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] == 1);
}

function isMobileDev(){
	if(isset($_SERVER['HTTP_USER_AGENT']) and !empty($_SERVER['HTTP_USER_AGENT'])){
		$user_ag = $_SERVER['HTTP_USER_AGENT'];
		if(preg_match('/(Mobile|Android|Tablet|GoBrowser|[0-9]x[0-9]*|uZardWeb\/|Mini|Doris\/|Skyfire\/|iPhone|Fennec\/|Maemo|Iris\/|CLDC\-|Mobi\/)/uis',$user_ag)) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;    
	}
}

function framesToTC($frames, $framerate) {
	$hours = floor( $frames / ( $framerate * 60 * 60 ) );
	$framesleft = $frames - ($hours * $framerate * 60 * 60);
	$minutes = floor( $framesleft / ( $framerate * 60 ) );
	$framesleft -= ( $minutes * $framerate * 60 );
	$seconds = floor( $framesleft / ( $framerate ) );
	$framesleft -= ( $seconds * $framerate ); // unused by ffmpegthumbnailer
	$tc = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
	return $tc;
}

function getIP() {
	$headers = apache_request_headers(); //  wasn't getting all headers in _SERVER
	foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
		if (array_key_exists($key, $headers) === true) {
			foreach (explode(',', $headers[$key]) as $ip) {
				if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
					return $ip;
				}
			}
		}
	}
}

if( !function_exists('apache_request_headers') ) {
	function apache_request_headers() {
		$arh = array();
		$rx_http = '/\AHTTP_/';
		foreach($_SERVER as $key => $val) {
			if( preg_match($rx_http, $key) ) {
				$arh_key = preg_replace($rx_http, '', $key);
				$rx_matches = array();
				$rx_matches = explode('_', $arh_key);
				if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
					foreach($rx_matches as $ak_key => $ak_val) {
						$rx_matches[$ak_key] = ucfirst($ak_val);
					}
					$arh_key = implode('-', $rx_matches);
				}
				$arh[$arh_key] = $val;
			}
		}
	return( $arh );
	}
}

function getHost() {
	$headers = apache_request_headers(); //  wasn't getting all headers in _SERVER
	foreach (explode(',', $headers['HTTP_CLIENT_HOST']) as $client_host) {
		if (strlen($client_host) > 1) {
			return $client_host;
		}
	}
}
