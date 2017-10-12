<?
$dirlocation = "/home/presence/irev.net";	// no trailing slash. // default, overridden in db.php
$host  = "localhost";
$db    = "irevnet";
require_once($dirlocation."/db.php");
$conn = mysqli_connect($host, $user, $pass, $db) or die(mysqli_error());
$run_resolver = 1;
while ($run_resolver) {
	//$query = "SELECT `hit_ip`,`hit_addr` from `sitehits` WHERE CHAR_LENGTH(`hit_addr`) = 0 OR `hit_addr` = 'UNKNOWN' LIMIT 1";
	$query = "SELECT `hit_ip`,`hit_addr` from `sitehits` WHERE CHAR_LENGTH(`hit_addr`) = 0 LIMIT 1";
	$result = mysqli_query($conn,$query);
	if (mysqli_num_rows($result) == 0) {
		$run_resolver = 0;
	} else {
		while ($row = mysqli_fetch_assoc($result)) {
			$hostname = gethostbyaddr($row['hit_ip']);
			if ($hostname == $row['hit_ip']) {
				$hostname = "UNKNOWN";
			}
			$update = sprintf(
				"UPDATE `sitehits` SET `hit_addr` = '%s' WHERE `hit_ip` = '%s' AND CHAR_LENGTH(`hit_addr`) = 0",
				mysqli_real_escape_string($conn,$hostname),
				mysqli_real_escape_string($conn,$row['hit_ip'])
			);
			echo "$update\n";
			if (mysqli_query($conn,$update) === FALSE) {
				echo "\nFAILED:\n" . mysqli_error($conn) . "\n\n";
				exit();
			}
		}
	}
}
