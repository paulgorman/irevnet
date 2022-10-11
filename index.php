<?
	$mtime = microtime(); $mtime = explode(" ",$mtime); $mtime = $mtime[1] + $mtime[0]; $starttime = $mtime;
	require_once("shared.php");
	Init();
	switch($_REQUEST['page']) {
		case "admin":
			if (isAdmin()) {
				ShowAdminPage();
			} elseif ($_REQUEST['url'] == "login") {
				ConfirmLogin();
			} else {
				AskForAdmin();
			}
			ScriptTime($starttime);
			break;
		case "categories":
			RecordHit();
			CategoriesList();
			ScriptTime($starttime);
			break;
		case "sample":
			RecordHit();
			SamplePage();
			Scripttime($starttime);
			break;
		case "irev":
			RecordHit();
			iRevPage();
			ScriptTime($starttime);
			break;
		case "about":
			RecordHit();
			AboutPage();
			ScriptTime($starttime);
			break;
		case "uploadprogress":
			UploadProgress();
			break;
		case "videoplay":
			RecordHit();
			break;
		case "liked":
			RecordHit();
			break;
		case "adminpageimageupload":
			if (isAdmin()) {
				AdminPageImageUpload();
			}
			break;
		case "adminpageimageclipboard":
			if (isAdmin()) {
				AdminPageImageClipboard();
			}
			break;
		default:
			RecordHit();
			HomePage();
			ScriptTime($starttime);
	}
?>
