<?
/* admin html templates */

function htmlAdminLogin($message) {
	?>
	<html>
		<head>
			<title>iRev.net Admin Login</title>
			<link rel="stylesheet" type="text/css" href="/templates/css/responsiveboilerplate.css">
			<link rel="stylesheet" type="text/css" href="/templates/css/irev.css">
		</head>
		<body>
		<form method="POST" action="/login">
			<div class="content" style="max-width: 960px; margin: 0px auto 0px auto;">
			<br>
				<div class="box dark carousel col12" style="border: 1px solid #fff;">
					<? if (!isEmpty($message)) { 
						?>
							<div class="col12" style="width: 95%;margin: 10px;padding: 10px;border: 1px solid #fff; background-color: orange"><?= $message; ?></div>
						  <div class="clearfix">
						<?
					}
					?>
					<div class="col1">&nbsp;</div>
					<div class="col2" style="text-align:left;">Username:</div>
					<div class="col2"><input type="text" name="username"></div>
					<div class="clearfix"></div>
					<div class="col12">&nbsp;</div>
					<div class="clearfix">
					<div class="col1">&nbsp;</div>
					<div class="col2" style="text-align:left;">Password:</div>
					<div class="col2"><input type="password" name="password"></div>
					<div class="col12">&nbsp;</div>
					<div class="clearfix">
					<div class="col1">&nbsp;</div>
					<div class="col2">&nbsp;</div><div class="col1"><input type="submit" value="Log In"></div>
					<div class="col12">&nbsp;</div>
				</div>
			</div>
		</form>
		</body>
	</html>
	<?
}


function AdminHead($curfunction,$adminfunctions) {
	$tempadminfunctionsflipped = array_flip ($adminfunctions);
	?><!DOCTYPE html>
		<html lang="en">
			<head>
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<meta charset="utf-8">
				<title>iRev.net Admin - <?= $tempadminfunctionsflipped[$curfunction]; ?></title>
				<link rel="stylesheet" type="text/css" href="/templates/css/admin.css" />
	  		<link rel="stylesheet" type="text/css" href="/templates/css/jquery.bsmselect.css" />
	  		<link href="/templates/css/jquery-ui.css" rel="stylesheet" type="text/css" />
	  		<script type="text/javascript" src="/templates/js/jquery.js"></script>
	  		<script type="text/javascript" src="/templates/js/jquery-ui.js"></script>
	  		<script type="text/javascript" src="/templates/js/jquery.bsmselect.js"></script>
	  		<script type="text/javascript" src="/templates/js/jquery.bsmselect.sortable.js"></script>
	  		<script type="text/javascript" src="/templates/js/jquery.bsmselect.compatibility.js"></script>
			</head>
			<body>
				<div class='adminTitle'>iRev Website Administration - <?= $tempadminfunctionsflipped[$curfunction]; ?></div>
	<?
}

function AdminNav($curfunction,$adminfunctions) {
	echo "<nav class='adminNavHolder'>";
	foreach ($adminfunctions as $nicename => $url) {
		if ($curfunction == $url) {
			?>
					<div class='adminNavItemSelected'>
						<a href='/admin/<?= $url ?>'><B><?= strtoupper($nicename) ?></B></a>
					</div>
			<?
		} else {
			?>
					<div class='adminNavItem'>
						<a href='/admin/<?= $url ?>'><?= strtoupper($nicename) ?></a>
					</div>
			<?
		}
	}
	echo "</nav>";
}

function AdminsamplesButtonBar() {
	?>
		<div  class="adminNavSubHolder">
			<div class="adminNavSubItem"><a href='/admin/samples/add_new'>Add New Sample</a></div>
			<div class="adminNavSubItem"><a href='/admin/samples/list_new'>List Newest Samples</a></div>
			<div class="adminNavSubItem"><a href='/admin/samples/list_feat'>List Featured Samples</a></div>
			<div class="adminNavSubItem"><a href='/admin/samples/list_secret'>List Hidden Samples</a></div>
			<div class="adminNavSubItem"><a href='/admin/samples/list_all'>List All Samples</a></div>
			<div class="adminNavSubItem"><form method="POST" action="/admin/samples/search/" id="search"><input type="hidden" name="function" value="search"><input type="text" name="q" placeholder="Search..."></form></div>
		</div>
	<?
}

function AdminShowNewAdminForm() {
	?>
		<form method="POST" action="/admin/admin_users" enctype="multipart/form-data">
			<input type="hidden" name="function" value="save_new_admin">
			<div class="AdminCategoryListingAddContainer">
				<div class="AdminCategoryListingAddHeader">ADD NEW ADMINISTRATIVE USER</div>
				<div class="AdminCategoryListingAddItem">Username</div>
				<div class="AdminCategoryListingAddValue"><input type="text" name="username" size="40"></div>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddItem">Password</div>
				<div class="AdminCategoryListingAddValue"><input type="password" name="password" size="40"></div>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddSubmit"><input type="submit" value="Add User"></div>
				<div class="clear"></div>
			</div> <!-- class="AdminCategoryListingAddContainer" -->
		</form>
	<?
}

function AdminShowAdminsList($admins) {
	?>
		<div class="AdminCategoryListContainer">
			<div class="AdminCategoryListHeader">
				<div class="AdminCategoryListItemDescription">Administrative Users</div>
				<div class="AdminCategoryListItemURL">[<a href="/admin/admin_users/add_admin">New Administrator</a>]</div>
			</div>
			<div class="clear"></div>
			<? foreach ($admins as $key => $username) { ?>
				<form method="POST" action="/admin/admins/edit/<?= $username; ?>" name="edit<?= $username; ?>">
					<input type="hidden" name="function" value="edit">
					<input type="hidden" name="username" value="<?= $username; ?>">
					<div class="AdminCategoryListRow" onclick="document.forms['edit<?= $username; ?>'].submit(); return false;">
						<div class="AdminCategoryListItemCategory"></div>
						<div class="AdminCategoryListItemDescription" onclick="document.forms['edit<?= $username; ?>'].submit(); return false;"><?= $username; ?></div>
					</div>
				</form>
			<? } ?>
			<div class="AdminCategoryListHeader">
			</div>
		</div>
	<?	
}

function AdminPagesButtonBar($pages) {
	?>
		<div  class="adminNavSubHolder">
			<?
				foreach ($pages as $url => $name) {
					echo "<div class=\"adminNavSubPagesItem\"><a href=\"/admin/pages/edit/$url\">$name</a></div>\n";
				}
			?>
		</div>
	<?
}

function AdminPagesListPage($pagedata) {
	?>
		<div class="AdminCategoryListContainer">
			<div class="AdminCategoryListHeader">
				<div class="AdminCategoryListItemDescription">Pages</div>
			</div>
			<div class="clear"></div>
			<? foreach (array_keys($pagedata) as $url) { ?>
				<form method="POST" action="/admin/pages/edit/<?= $url; ?>" name="edit<?= $url; ?>">
					<input type="hidden" name="function" value="edit">
					<input type="hidden" name="pageurl" value="<?= $url; ?>">
					<div class="AdminCategoryListRow" onclick="document.forms['edit<?= $url; ?>'].submit(); return false;">
						<div class="AdminCategoryListItemCategory"><?= $pagedata[$url]['name']; ?></div>
						<div class="AdminCategoryListItemDescription" onclick="document.forms['edit<?= $url; ?>'].submit(); return false;"><?= nicetime($pagedata[$url]['lastupdated']); ?></div>
					</div>
				</form>
			<? } ?>
			<div class="AdminCategoryListHeader">
			</div>
		</div>
	<?
}

function AdminPagesEditPage($pagedata) {
	?>
		<div class="AdminsampleContainer">
			<div class="AdminsampleEditHeader"><?= MakeCase($pagedata['pagename']); ?> Page Editor</div>
			<div class="AdminsampleLastUpdateHeader">Last updated <?= nicetime($pagedata['htmltime']) ?></div>
			<link rel="stylesheet" type="text/css" href="/templates/redactor/redactor.css">
			<script src="/templates/redactor/redactor.min.js"></script>
			<script src="/templates/redactor/fontcolor.js"></script>
			<script src="/templates/redactor/fontfamily.js"></script>
			<script src="/templates/redactor/fontsize.js"></script>
			<script type="text/javascript">
				$(function() {
					$('#redactor_content').redactor(
						{
							minHeight: 500,
							focus: true,
						  iframe: true,
							plugins: ['fontfamily','fontsize','fontcolor'],
					    css: ['/templates/css/responsiveboilerplate.css', '/templates/css/irev.css'],
							imageUpload: '/adminpageimageupload',
							clipboardUploadUrl: '/adminpageimageclipboard'
						}
					);
				});
			</script>
			<form method="POST" action="/admin/pages/update">
				<input type="hidden" name="pageurl" value="<?= $pagedata['pagename']; ?>">
				<textarea id="redactor_content" name="content">
					<?= stripslashes($pagedata['html']); ?>
				</textarea>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddSubmit">
				<input type="submit" value="&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;" name="send">
				</div>
			</form>
			<form method="POST" action="/admin/pages/revert">
				<div class="AdminCategoryListingAddSubmit">
				<input type="hidden" name="pageurl" value="<?= $pagedata['pagename']; ?>">
				<input type="submit" value="Revert to content from <?= niceTime($pagedata['undotime']); ?>" name="Revert">
				</div>
				<div class="clear"></div>
			</form>
		</div>
	<?
}

function AdminsampleListPage($samples,$page) {
	?>
		<div class="AdminCategoryListContainer">
			<div class="AdminCategoryListHeader">
				<div class="AdminCategoryListItemURL">All samples</div>
				<div class="ListPage"><?= ShowPageNav(FigurePageNav("list_all",$page)); ?></div>
			</div>
			<div class="clear"></div>
			<? foreach ($samples as $key => $values) { ?>
				<form method="POST" action="/admin/samples/edit/<?= $values['oid']; ?>" name="edit<?= $values['oid']; ?>">
					<input type="hidden" name="function" value="edit">
					<input type="hidden" name="oid" value="<?= $values['oid']; ?>">
					<div class="AdminCategoryListRow" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;">
						<div class="AdminCategoryListItemCategory"><a href="/samples/<?= $values['url']; ?>"><?= $values['name'] ?></a></div>
						<div class="AdminCategoryListItemDescription" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;"><?= $values['slug'] ?></div>
						<div class="AdminCategoryListItemCategory" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;"><?= nicetime($values['last_updated']); ?></div>
						<? AdminsampleListIconColumn($values); ?>
					</div>
				</form>
			<? } ?>
			<div class="AdminCategoryListHeader">
				<div class="ListPage"><?= ShowPageNav(FigurePageNav("list_all",$page)); ?></div>
			</div>
		</div>
	<?
}

function AdminsampleListIconColumn($values) {
	if((int)$values['is_highlighted'] === 1) { 
		?><a href="javascript:;" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;" class="AdminCategoryPublishedIcon" title="Featured Highlighted sample"></a><?
	}
	if((int)$values['is_active'] === 0) { 
		?><a href="javascript:;" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;" class="AdminCategoryInactiveIcon" title="Inactive sample"></a><?
	} elseif ((int)$values['is_active'] === 1 && (int)$values['is_searchable'] === 0) { 
		?><a href="javascript:;" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;" class="AdminCategorySecretIcon" title="Secret sample"></a><?
	}
}

function AdminsampleListPageNew($samples,$page) {
	?>
		<div class="AdminCategoryListContainer">
			<div class="AdminCategoryListHeader">
				<div class="AdminCategoryListItemDescription">Recently Added/Updated samples</div>
				<div class="ListPage"><?= ShowPageNav(FigurePageNav("list_new",$page)); ?></div>
			</div>
			<div class="clear"></div>
			<? foreach ($samples as $key => $values) { ?>
				<form method="POST" action="/admin/samples/edit/<?= $values['oid']; ?>" name="edit<?= $values['oid']; ?>">
					<input type="hidden" name="function" value="edit">
					<input type="hidden" name="oid" value="<?= $values['oid']; ?>">
					<div class="AdminCategoryListRow" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;">
						<div class="AdminCategoryListItemCategory"><a href="/samples/<?= $values['url']; ?>"><?= $values['name'] ?></a></div>
						<div class="AdminCategoryListItemDescription" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;"><?= $values['slug'] ?></div>
						<div class="AdminCategoryListItemCategory" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;"><?= nicetime($values['last_updated']); ?></div>
						<? AdminsampleListIconColumn($values); ?>
					</div>
				</form>
			<? } ?>
			<div class="AdminCategoryListHeader">
				<div class="ListPage"><?= ShowPageNav(FigurePageNav("list_new",$page)); ?></div>
			</div>
		</div>
	<?
}

function AdminsampleListPageFeat($samples,$page) {
	?>
		<div class="AdminCategoryListContainer">
			<div class="AdminCategoryListHeader">
				<div class="AdminCategoryListItemDescription">Featured samples</div>
				<div class="ListPage"><?= ShowPageNav(FigurePageNav("list_feat",$page)); ?></div>
			</div>
			<div class="clear"></div>
			<? foreach ($samples as $key => $values) { ?>
				<form method="POST" action="/admin/samples/edit/<?= $values['oid']; ?>" name="edit<?= $values['oid']; ?>">
					<input type="hidden" name="function" value="edit">
					<input type="hidden" name="oid" value="<?= $values['oid']; ?>">
					<div class="AdminCategoryListRow" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;">
						<div class="AdminCategoryListItemCategory"><a href="/samples/<?= $values['url']; ?>"><?= $values['name'] ?></a></div>
						<div class="AdminCategoryListItemDescription" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;"><?= $values['slug'] ?></div>
						<div class="AdminCategoryListItemCategory" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;"><?= nicetime($values['last_updated']); ?></div>
						<? AdminsampleListIconColumn($values); ?>
					</div>
				</form>
			<? } ?>
			<div class="AdminCategoryListHeader">
				<div class="ListPage"><?= ShowPageNav(FigurePageNav("list_feat",$page)); ?></div>
			</div>
		</div>
	<?
}

function AdminsampleListPageSecret($samples,$page) {
	?>
		<div class="AdminCategoryListContainer">
			<div class="AdminCategoryListHeader">
				<div class="AdminCategoryListItemDescription">Unsearchable / Inactive samples</div>
				<div class="ListPage"><?= ShowPageNav(FigurePageNav("list_secret",$page)); ?></div>
			</div>
			<div class="clear"></div>
			<? foreach ($samples as $key => $values) { ?>
				<form method="POST" action="/admin/samples/edit/<?= $values['oid']; ?>" name="edit<?= $values['oid']; ?>">
					<input type="hidden" name="function" value="edit">
					<input type="hidden" name="oid" value="<?= $values['oid']; ?>">
					<div class="AdminCategoryListRow" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;">
						<div class="AdminCategoryListItemCategory"><a href="/samples/<?= $values['url']; ?>"><?= $values['name'] ?></a></div>
						<div class="AdminCategoryListItemDescription" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;"><?= $values['slug'] ?></div>
						<div class="AdminCategoryListItemCategory" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;"><?= nicetime($values['last_updated']); ?></div>
						<? AdminsampleListIconColumn($values); ?>
					</div>
				</form>
			<? } ?>
			<div class="AdminCategoryListHeader">
				<div class="ListPage"><?= ShowPageNav(FigurePageNav("list_secret",$page)); ?></div>
			</div>
		</div>
	<?
}

function AdminsampleListPageByCategory($samples,$page) {
	?>
		<div class="AdminCategoryListContainer">
			<div class="AdminCategoryListHeader">
				<div class="AdminCategoryListItemDescription">All <?= mysqli_num_rows($samples); ?> samples in <?= CategoryNameFromURL($_REQUEST['categoryurl']); ?></div>
			</div>
			<div class="clear"></div>
			<? foreach ($samples as $key => $values) { ?>
				<form method="POST" action="/admin/samples/edit/<?= $values['oid']; ?>" name="edit<?= $values['oid']; ?>">
					<input type="hidden" name="function" value="edit">
					<input type="hidden" name="oid" value="<?= $values['oid']; ?>">
					<div class="AdminCategoryListRow" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;">
						<div class="AdminCategoryListItemCategory"><a href="/samples/<?= $values['url']; ?>"><?= $values['name'] ?></a></div>
						<div class="AdminCategoryListItemDescription" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;"><?= $values['slug'] ?></div>
						<div class="AdminCategoryListItemCategory" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;"><?= nicetime($values['last_updated']); ?></div>
						<? AdminsampleListIconColumn($values); ?>
					</div>
				</form>
			<? } ?>
		</div>
	<?
}

function AdminsampleListPageByStyle($samples,$page) {
	?>
		<div class="AdminCategoryListContainer">
			<div class="AdminCategoryListHeader">
				<div class="AdminCategoryListItemDescription">All <?= mysqli_num_rows($samples); ?> samples in the <?= MakeCase(StyleNameFromSID($_REQUEST['sid'])); ?> Style</div>
			</div>
			<div class="clear"></div>
			<? foreach ($samples as $key => $values) { ?>
				<form method="POST" action="/admin/samples/edit/<?= $values['oid']; ?>" name="edit<?= $values['oid']; ?>">
					<input type="hidden" name="function" value="edit">
					<input type="hidden" name="oid" value="<?= $values['oid']; ?>">
					<div class="AdminCategoryListRow" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;">
						<div class="AdminCategoryListItemCategory"><a href="/samples/<?= $values['url']; ?>"><?= $values['name'] ?></a></div>
						<div class="AdminCategoryListItemDescription" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;"><?= $values['slug'] ?></div>
						<div class="AdminCategoryListItemCategory" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;"><?= nicetime($values['last_updated']); ?></div>
						<? AdminsampleListIconColumn($values); ?>
					</div>
				</form>
			<? } ?>
		</div>
	<?
}

function AdminsampleListPageBySearchResult($samples,$page) {
	?>
		<div class="AdminCategoryListContainer">
			<div class="AdminCategoryListHeader">
				<div class="AdminCategoryListItemDescription">All <?= mysqli_num_rows($samples); ?> samples Matching Search "<?
					echo htmlspecialchars(strip_tags(MakeCase(trim($_REQUEST['q']))));
					echo htmlspecialchars(strip_tags(MakeCase(trim($_REQUEST['listpage']))));
					?>"</div>
			</div>
			<div class="clear"></div>
			<? foreach ($samples as $key => $values) { ?>
				<form method="POST" action="/admin/samples/edit/<?= $values['oid']; ?>" name="edit<?= $values['oid']; ?>">
					<input type="hidden" name="function" value="edit">
					<input type="hidden" name="oid" value="<?= $values['oid']; ?>">
					<div class="AdminCategoryListRow" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;">
						<div class="AdminCategoryListItemCategory"><a href="/samples/<?= $values['url']; ?>" title="<?= $values['url']; ?>"><?= $values['name'] ?></a></div>
						<div class="AdminCategoryListItemShort"><a href="/samples/<?= $values['alt_url']; ?>" title="<?= $values['alt_url']; ?>">(<?= $values['display_name'] ?>)</a></div>
						<div class="AdminCategoryListItemIcon"></div>
						<div class="AdminCategoryListItemDescription" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;"><?= $values['slug'] ?></div>
						<div class="AdminCategoryListItemLastUpdated" onclick="document.forms['edit<?= $values['oid']; ?>'].submit(); return false;"><?= nicetime($values['last_updated']); ?></div>
						<? AdminsampleListIconColumn($values); ?>
					</div>
				</form>
			<? } ?>
		</div>
	<?
}

function ShowPageNav($pageinfo) {
	$returnhtml = "";
	if ($pageinfo['page'] != $pageinfo['first']) {
		$returnhtml .= "<div class='ListPageItem'><a href='/admin/samples/". $pageinfo['type'] ."/". $pageinfo['first'] ."'>First</a></div>";
	}
	if ($pageinfo['previous'] != $pageinfo['page'] && $pageinfo['previous'] != $pageinfo['first']) {
		$returnhtml .= "<div class='ListPageItem'><a href='/admin/samples/". $pageinfo['type'] ."/". $pageinfo['previous'] ."'>Previous</a></div>";
	}
	$returnhtml .= "<div class='ListPageItem'> (Page ". $pageinfo['page'] ." of ". $pageinfo['maximum'] .") </div>";
	if ($pageinfo['next'] != $pageinfo['page'] && $pageinfo['next'] != $pageinfo['maximum']) {
		$returnhtml .= "<div class='ListPageItem'><a href='/admin/samples/". $pageinfo['type'] ."/". $pageinfo['next'] ."'>Next</a></div>";
	}
	if ($pageinfo['page'] != $pageinfo['maximum']){
		$returnhtml .= "<div class='ListPageItem'><a href='/admin/samples/". $pageinfo['type'] ."/". $pageinfo['maximum'] ."'>Last</a></div>";
	}
	return $returnhtml;
}

function AdminShowLocations($locations) {
	?>
		<div class="AdminCategoryListContainer">
			<div class="AdminCategoryListHeader">
				<div class="AdminCategoryListItemURL">Locations</div>
			</div>
			<div class="clear"></div>
			<? if (isset($locations)) { foreach ($locations as $key => $values) { ?>
				<div class="AdminCategoryListRow">
					<form method="POST" action="/admin/locations_list" name="edit<?= $values['lid']; ?>">
						<div class="AdminCategoryListItemCategory" onclick="document.forms['edit<?= $values['lid']; ?>'].submit(); return false;"><?=$values['city'] ?></div>
						<div class="AdminCategoryListItemURL" onclick="document.forms['edit<?= $values['lid']; ?>'].submit(); return false;"><?=$values['state'] ?></div>
						<div class="AdminCategoryListItemDescription" onclick="document.forms['edit<?= $values['lid']; ?>'].submit(); return false;">&nbsp;</div>
						<div class="AdminCategoryListItemIcon" onclick="document.forms['edit<?= $values['lid']; ?>'].submit(); return false;">
							<input type="hidden" name="lid" value="<?= $values['lid']; ?>">
							<input type="hidden" name="function" value="edit_location">
							<a href="javascript:;" onclick="document.forms['edit<?= $values['lid']; ?>'].submit(); return false;" class="AdminCategoryEditIcon" title="Rename <?= $values['city'] ?>, <?= $values['state'] ?>."></a>
						</div> <!-- class="AdminCategoryListItem" -->
					</form>
					<div class="AdminCategoryListItemIcon">
						<form method="POST" action="/admin/locations_list" name="del<?= $values['lid']; ?>">
							<input type="hidden" name="lid" value="<?= $values['lid']; ?>">
							<input type="hidden" name="function" value="del_location">
							<a href="javascript:;" onclick="document.forms['del<?= $values['lid']; ?>'].submit(); return false;" class="AdminCategoryRemoveIcon" title="Remove <?= $values['city']; ?>, <?= $values['state'] ?>."></a>
						</form>
					</div>
					<div class="AdminCategoryListItemIcon">
						<form method="POST" action="/admin/locations_list" name="search<?= $values['lid']; ?>">
							<input type="hidden" name="lid" value="<?= $values['lid']; ?>">
							<input type="hidden" name="function" value="search_location">
							<a href="javascript:;" onclick="document.forms['search<?= $values['lid']; ?>'].submit(); return false;" class="AdminCategorySearchIcon" title="Show all samples in '<?= $values['city'] ?>, <?= $values['state'] ?>."></a>
						</form>
					</div>
				</div>  <!-- class="AdminCategoryListRow" -->
			<? } } ?>
		</div> <!-- AdminCategoryListContainer -->
		<div class="clear"></div>
		<form method="POST" action="/admin/locations_list" enctype="multipart/form-data">
			<input type="hidden" name="function" value="add_location">
			<div class="AdminCategoryListingAddContainer">
				<div class="AdminCategoryListingAddHeader">ADD NEW LOCATION</div>
				<div class="AdminCategoryListingAddItem">City</div>
				<div class="AdminCategoryListingAddValue"><input type="text" name="city" size="30"></div>
				<div class="AdminCategoryListingAddItem">State</div>
				<div class="AdminCategoryListingAddValue"><select name="state"><option value="">Select a State</option><?= StateOptionsDropDown("none"); ?></select></div>
				<div class="AdminCategoryListingAddSubmit"><input type="submit" value="Add Location"></div>
				<div class="clear"></div>
			</div> <!-- class="AdminCategoryListingAddContainer" -->
		</form>
	<?
}

function AdminShowStyles($styles,$quantity) {
	?>
		<div class="AdminCategoryListContainer">
			<div class="AdminCategoryListHeader">
				<div class="AdminCategoryListItemURL">Styles (<?= $quantity ?>)</div>
			</div>
			<div class="clear"></div>
			<? if (isset($styles)) { foreach ($styles as $key => $values) { ?>
				<div class="AdminCategoryListRow">
					<form method="POST" action="/admin/styles_list" name="edit<?= $values['sid']; ?>">
						<div class="AdminCategoryListItemDescription" onclick="document.forms['edit<?= $values['sid']; ?>'].submit(); return false;"><?=$values['name'] ?></div>
						<div class="AdminCategoryListItemURL" onclick="document.forms['edit<?= $values['sid']; ?>'].submit(); return false;">&nbsp;</div>
						<div class="AdminCategoryListItemCategory" onclick="document.forms['edit<?= $values['sid']; ?>'].submit(); return false;">&nbsp;</div>
						<div class="AdminCategoryListItemIcon" onclick="document.forms['edit<?= $values['sid']; ?>'].submit(); return false;">
							<input type="hidden" name="sid" value="<?= $values['sid']; ?>">
							<input type="hidden" name="function" value="edit_style">
							<a href="javascript:;" onclick="document.forms['edit<?= $values['sid']; ?>'].submit(); return false;" class="AdminCategoryEditIcon" title="Edit '<?= $values['name'] ?>' Style"></a>
						</div> <!-- class="AdminCategoryListItem" -->
					</form>
					<div class="AdminCategoryListItemIcon">
						<form method="POST" action="/admin/styles_list" name="del<?= $values['sid']; ?>">
							<input type="hidden" name="sid" value="<?= $values['sid']; ?>">
							<input type="hidden" name="function" value="del_style">
							<a href="javascript:;" onclick="document.forms['del<?= $values['sid']; ?>'].submit(); return false;" class="AdminCategoryRemoveIcon" title="Remove '<?= $values['name']; ?>' Style"></a>
						</form>
					</div>
					<div class="AdminCategoryListItemIcon">
						<form method="POST" action="/admin/styles_list" name="search<?= $values['sid']; ?>">
							<input type="hidden" name="sid" value="<?= $values['sid']; ?>">
							<input type="hidden" name="function" value="search_style">
							<a href="javascript:;" onclick="document.forms['search<?= $values['sid']; ?>'].submit(); return false;" class="AdminCategorySearchIcon" title="Show all samples in the '<?= $values['name']; ?>' style"></a>
						</form>
					</div>
				</div>  <!-- class="AdminCategoryListRow" -->
			<? } } ?>
		</div> <!-- AdminCategoryListContainer -->
		<div class="clear"></div>
		<form method="POST" action="/admin/styles_list" enctype="multipart/form-data">
			<input type="hidden" name="function" value="add_style">
			<div class="AdminCategoryListingAddContainer">
				<div class="AdminCategoryListingAddHeader">ADD NEW STYLE</div>
				<div class="AdminCategoryListingAddItem">Name</div>
				<div class="AdminCategoryListingAddValue"><input type="text" name="name" size="40"></div>
				<div class="AdminCategoryListingAddSubmit"><input type="submit" value="Add Style"></div>
				<div class="clear"></div>
			</div> <!-- class="AdminCategoryListingAddContainer" -->
		</form>
	<?
}

function AdminShowCategories ($categorieslist) {
	?>
		<div class="AdminCategoryListContainer">
			<div class="AdminCategoryListHeader">
				<div class="AdminCategoryListItemURL">URL</div>
				<div class="AdminCategoryListItemCategory">Category Name</div>
				<div class="AdminCategoryListItemDescription">Description</div>
			</div>
			<div class="clear"></div>
			<? foreach ($categorieslist as $catkey => $catvalues) { ?>
				<div class="AdminCategoryListRow">
					<form method="POST" action="/admin/categories_list" name="edit<?= $catvalues['url']; ?>">
						<div class="AdminCategoryListItemURL" onclick="document.forms['edit<?= $catvalues['url']; ?>'].submit(); return false;"><?=$catvalues['url'] ?></div>
						<div class="AdminCategoryListItemCategory" onclick="document.forms['edit<?= $catvalues['url']; ?>'].submit(); return false;"><?=$catvalues['category'] ?></div>
						<div class="AdminCategoryListItemDescription" onclick="document.forms['edit<?= $catvalues['url']; ?>'].submit(); return false;"><?=$catvalues['description'] ?></div>
						<?
							if ($catvalues['highlighted']) {
								echo "<div class='AdminCategoryHighlightedIcon' title='Highlighted Category'></div>";
							} else {
								echo "<div class='AdminCategoryEmptyIcon'></div>";
							}
						?>
						<?
							if ($catvalues['published']) {
								echo "<div class='AdminCategoryPublishedIcon' title='Public Listed Category'></div>";
							} else {
								echo "<div class='AdminCategoryHiddenIcon' title='Not Listed'></div>";
							}
						?>
						<div class="AdminCategoryListItemIcon" onclick="document.forms['edit<?= $catvalues['url']; ?>'].submit(); return false;">
							<input type="hidden" name="categoryurl" value="<?= $catvalues['url']; ?>">
							<input type="hidden" name="function" value="edit_category">
							<a href="javascript:;" onclick="document.forms['edit<?= $catvalues['url']; ?>'].submit(); return false;" class="AdminCategoryEditIcon" title="Edit '<?= $catvalues['category'] ?>' Category"></a>
						</div> <!-- class="AdminCategoryListItem" -->
					</form>
					<div class="AdminCategoryListItemIcon">
						<form method="POST" action="/admin/categories_list" name="del<?= $catvalues['url']; ?>">
							<input type="hidden" name="categoryurl" value="<?= $catvalues['url']; ?>">
							<input type="hidden" name="function" value="del_category">
							<a href="javascript:;" onclick="document.forms['del<?= $catvalues['url']; ?>'].submit(); return false;" class="AdminCategoryRemoveIcon" title="Remove '<?= $catvalues['category']; ?>' Category"></a>
						</form>
					</div>
					<div class="AdminCategoryListItemIcon">
						<form method="POST" action="/admin/categories_list" name="search<?= $catvalues['url']; ?>">
							<input type="hidden" name="categoryurl" value="<?= $catvalues['url']; ?>">
							<input type="hidden" name="function" value="search_category">
							<a href="javascript:;" onclick="document.forms['search<?= $catvalues['url']; ?>'].submit(); return false;" class="AdminCategorySearchIcon" title="Show all samples in the '<?= $catvalues['category']; ?>' Category"></a>
						</form>
					</div>
					<?
						if ($catvalues['subs'] > 0) {
							echo "<div class='AdminCategoryPlusIcon' title='Sub-Categories'></div>";
						} else {
							echo "<div class='AdminCategoryEmptyIcon'></div>";
						}
					?>
				</div>  <!-- class="AdminCategoryListRow" -->
			<? } ?>
		</div> <!-- AdminCategoryListContainer -->
		<script type="text/javascript">
			<!--
			function showHide(){
				var highlighted = document.getElementById("highlighted");
				var carousel_image = document.getElementById("carousel_image");
				if (highlighted.checked){
					carousel_image.style.visibility = "visible";
				} else {
					carousel_image.style.visibility = "hidden";
				}
			}
			-->
		</script>
		<div class="clear"></div>
		<form method="POST" action="/admin/categories_list" enctype="multipart/form-data">
			<input type="hidden" name="function" value="add_category">
			<div class="AdminCategoryListingAddContainer">
				<div class="AdminCategoryListingAddHeader">ADD NEW CATEGORY</div>
				<div class="AdminCategoryListingAddItem">Category Name:</div>
				<div class="AdminCategoryListingAddValue"><input type="text" name="form_category" value="<?= MakeCase(htmlspecialchars(trim($_REQUEST['form_category']))); ?>" size="20"></div>
				<div class="AdminCategoryListingAddItem">URL:</div>
				<div class="AdminCategoryListingAddValue"><input type="text" placeholder="Optional" name="form_url" value="<?= htmlspecialchars(trim($_REQUEST['form_url'])); ?>" size="15" style="text-transform: lowercase"></div>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddItem">Description:</div>
				<div class="AdminCategoryListingAddValue"><input type="text" name="form_description" value="<?= htmlspecialchars(trim($_REQUEST['form_description'])); ?>" size="40"></div>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddItem">sample Names:</div>
				<div class="AdminCategoryListingAddValue"><select name="force_display_names"><?= DisplayNamesOptionsDropDown($dataarray['cid']) ?></select></div>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddItem">Category Logo:</div>
				<div class="AdminCategoryListingAddValue"><input name="filesToUpload[]" class="filesToUpload" size="40" id="1" type="file" multiple="" accept="image/png"></div>
				<div class="AdminCategoryListItemURL">728x90 PNG</div>
				<div class="clear"></div>
    		<div class="AdminCategoryListingAddItem"><label for="published">Public</label></div>
    		<div class="AdminCategoryListingCheckBox">
					<input type="checkbox" name="published" id="published" class="regular-checkbox big-checkbox" CHECKED /><label title="Publicly Displayed in Categories Listing" for="published"></label>
				</div>
    		<div class="AdminCategoryListingAddItem"><label for="highlighted">Highlighted (1024x680)</label></div>
    		<div class="AdminCategoryListingCheckBox">
					<input type="checkbox" name="highlighted" id="highlighted" onclick="showHide();" class="regular-checkbox big-checkbox" /><label title="Highlighted in the Carousel" for="highlighted"></label>
					<input name="carousel_image" id="carousel_image" style="visibility: hidden;" class="filesToUpload" size="40" type="file" multiple="" accept="image/png">
				</div>
				<div class="AdminCategoryListingAddSubmit"><input type="submit" value="Add Category"></div>
				<div class="clear"></div>
			</div> <!-- class="AdminCategoryListingAddContainer" -->
		</form>
	<?
}

function AdminShowSubCategories ($categorieslist) {
	?>
		<div class="AdminCategoryListContainer">
			<div class="AdminCategoryListHeader">
				<div class="AdminCategoryListItemURL">URL</div>
				<div class="AdminCategoryListItemCategory">Sub-Category</div>
				<div class="AdminCategoryListItemDescription">Description</div>
			</div>
			<div class="clear"></div>
			<? foreach ($categorieslist as $catkey => $catvalues) { ?>
				<div class="AdminCategoryListRow">
					<form method="POST" action="/admin/categories_list" name="edit<?= $catvalues['url']; ?>">
						<div class="AdminCategoryListItemURL" onclick="document.forms['edit<?= $catvalues['url']; ?>'].submit(); return false;"><?=$catvalues['url'] ?></div>
						<div class="AdminCategoryListItemCategory" onclick="document.forms['edit<?= $catvalues['url']; ?>'].submit(); return false;"><?=$catvalues['category'] ?></div>
						<div class="AdminCategoryListItemDescription" onclick="document.forms['edit<?= $catvalues['url']; ?>'].submit(); return false;"><?=$catvalues['description'] ?></div>
						<div class="AdminCategoryListItemIcon" onclick="document.forms['edit<?= $catvalues['url']; ?>'].submit(); return false;">
							<input type="hidden" name="parent_cid" value="<?= $catvalues['parent_cid']; ?>">
							<input type="hidden" name="categoryurl" value="<?= $catvalues['url']; ?>">
							<input type="hidden" name="function" value="edit_subcategory">
							<a href="javascript:;" onclick="document.forms['edit<?= $catvalues['url']; ?>'].submit(); return false;" class="AdminCategoryEditIcon" title="Edit '<?= $catvalues['category'] ?>' Sub-Category"></a>
						</div> <!-- class="AdminCategoryListItem" -->
					</form>
					<div class="AdminCategoryListItemIcon">
						<form method="POST" action="/admin/categories_list" name="del<?= $catvalues['url']; ?>">
							<input type="hidden" name="parent_cid" value="<?= $catvalues['parent_cid']; ?>">
							<input type="hidden" name="categoryurl" value="<?= $catvalues['url']; ?>">
							<input type="hidden" name="function" value="del_subcategory">
							<a href="javascript:;" onclick="document.forms['del<?= $catvalues['url']; ?>'].submit(); return false;" class="AdminCategoryRemoveIcon" title="Remove '<?= $catvalues['category']; ?>' Sub-Category"></a>
						</form>
					</div>
					<div class="AdminCategoryListItemIcon">
						<form method="POST" action="/admin/categories_list" name="search<?= $catvalues['url']; ?>">
							<input type="hidden" name="parent_cid" value="<?= $catvalues['parent_cid']; ?>">
							<input type="hidden" name="categoryurl" value="<?= $catvalues['url']; ?>">
							<input type="hidden" name="function" value="search_subcategory">
							<a href="javascript:;" onclick="document.forms['search<?= $catvalues['url']; ?>'].submit(); return false;" class="AdminCategorySearchIcon" title="Show all samples in the '<?= $catvalues['category']; ?>' Sub-Category"></a>
						</form>
					</div>
				</div>  <!-- class="AdminCategoryListRow" -->
			<? } ?>
		</div> <!-- AdminCategoryListContainer -->
	<?
}

function AdminShowDeleteConfirmation($id,$desc,$urlDo,$urlCancel,$nextfunction) {
	?>
		<div class="AdminWarning">
			<form method="POST" action="/admin/<?= $urlDo ?>">
				<input type="hidden" name="targetcategoryurl" value="<?= $id ?>">
				<input type="hidden" name="function" value="<?= $nextfunction ?>">
				Are you sure you want to delete <B><?= $desc ?></B>?<br>
				<input type="submit" value="Delete"> <input type="button" name="Cancel" value="Oops, no, totally not! Go Back! Cancel!" onclick="window.location='/admin/<?= $urlCancel ?>'">
			</form>
		</div>
	<?
}

function AdminEditCategory($dataarray) {
	// cid, category, url, description, image_filename, image_id, last_updated
	?>
		<script type="text/javascript">
			<!--
			function showHide(){
				var is_highlighted = document.getElementById("is_highlighted");
				var carousel_image = document.getElementById("carousel_image");
				if (is_highlighted.checked){
					carousel_image.style.visibility = "visible";
				} else {
					carousel_image.style.visibility = "hidden";
				}
			}
			-->
		</script>
		<form method="POST" action="/admin/categories_list" enctype="multipart/form-data">
			<input method="hidden" name="function" value="save_category" style="display:none">
			<input method="hidden" name="form_cid" value="<?= $dataarray['cid'] ?>" style="display:none">
			<div class="AdminCategoryListContainer">
				<div class="AdminCategoryListingEditHeader">
					Edit Category Properties for "<?= $dataarray['category'] ?>" <font size="-2">Last edited <?= nicetime($dataarray['last_updated']) ?> (<?= $dataarray['last_updated'] ?>)</font>
				</div>
				<div class="clear"></div>
				<div class="AdminCategoryListingEditItem">Short Name:</div>
				<div class="AdminCategoryListingAddValue">
					<input type="text" name="form_category" size="20" value="<?= $dataarray['category']?>">
				</div>
				<div class="clear"></div>
				<div class="AdminCategoryListingEditItem">Nice Description:</div>
				<div class="AdminCategoryListingAddValue">
					<input type="text" name="form_description" size="50" value="<?= $dataarray['description'] ?>">
				</div>
				<div class="clear"></div>
				<div class="AdminCategoryListingEditItem">URL:</div>
				<div class="AdminCategoryListingAddValue">
					<input type="text" name="form_url" size="20" value="<?= $dataarray['url'] ?>" style="text-transform: lowercase">
				</div>
				<div class="clear"></div>
				<div class="AdminCategoryListingEditItem">sample Names:</div>
				<div class="AdminCategoryListingAddValue"><select name="force_display_names"><?= DisplayNamesOptionsDropDown($dataarray['cid']) ?></select></div>
				<div class="clear"></div>
				<div class="AdminCategoryListingEditItem">Stored Image:</div>
				<div class="AdminCategoryListingShowImage"><img src="/i/category/<?= $dataarray['image_id'] ?>"></div>
				<div class="clear"></div>
				<div class="AdminCategoryListingEditItem">Image Name:</div>
				<div class="AdminCategoryListItemURL"><?= $dataarray['image_filename'] ?></div>
				<div class="AdminCategoryListingAddValue"><input name="filesToUpload[]" class="filesToUpload" size="40" id="1" type="file" multiple="" accept="image/png"></div>
				<div class="AdminCategoryListItemURL">728x90 PNG</div>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddItem"></div>
				<div class="clear"></div>

    		<div class="AdminCategoryListingEditItem">Public</div>
    		<div class="AdminCategoryListingCheckBox">
					<input type="checkbox" id="published" name="published" class="regular-checkbox big-checkbox" <?= ($dataarray['published']?'CHECKED ':'') ?>/><label title="Publicly Displayed in Categories Listing" for="published"></label>
				</div>

    		<div class="AdminCategoryListingEditItem">Highlighted (1024x680)</div>
    		<div class="AdminCategoryListingCheckBox">
					<input type="checkbox" id="is_highlighted" onclick="showHide();" name="is_highlighted" class="regular-checkbox big-checkbox" <?= ($dataarray['is_highlighted']?'CHECKED ':'') ?>/><label title="Highlighted in the Featured Carousel" for="is_highlighted"></label>
					<input name="carousel_image" id="carousel_image" style="visibility: hidden;" class="filesToUpload" size="40" type="file" multiple="">
				</div>
				<div class="clear"></div>
		<div class="clear"></div>
				<? if (!isEmpty($dataarray['carousel_id'])) { ?>
					<div class="AdminCategoryListingEditItem"><img border="1" src="/i/category/<?= $dataarray['carousel_id']; ?>"></div>
				<? } ?>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddSubmit">
					<input type="submit" value="Update Category">
					<input type="button" name="Cancel" value="Cancel" onclick="window.location='/admin/categories_list'">
				</div>
				<div class="clear"></div>
			</div> <!-- AdminCategoryListContainer -->
		</form>

		<? AdminEditSubCategories($dataarray['cid']); ?>

		<form method="POST" action="/admin/categories_list" enctype="multipart/form-data">
			<input type="hidden" name="function" value="add_sub_category" style="display:none">
			<input method="hidden" name="form_cid" value="<?= $dataarray['cid']; ?>" style="display:none">
			<div class="AdminCategoryListingAddContainer">
				<div class="AdminCategoryListingAddHeader">ADD NEW SUB-CATEGORY</div>
				<div class="AdminCategoryListingAddItem">Sub-Category Name:</div>
				<div class="AdminCategoryListingAddValue"><input type="text" name="form_category" value="<?= MakeCase(htmlspecialchars(trim($_REQUEST['form_category']))); ?>" size="20"></div>
				<div class="AdminCategoryListingAddItem">URL:</div>
				<div class="AdminCategoryListingAddValue"><input type="text" placeholder="Optional" name="form_url" value="<?= htmlspecialchars(trim($_REQUEST['form_url'])); ?>" size="15" style="text-transform: lowercase"></div>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddItem">Description:</div>
				<div class="AdminCategoryListingAddValue"><input type="text" name="form_description" value="<?= htmlspecialchars(trim($_REQUEST['form_description'])); ?>" size="40"></div>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddItem">Sub-Category Logo:</div>
				<div class="AdminCategoryListingAddValue"><input name="filesToUpload[]" class="filesToUpload" size="40" id="1" type="file" multiple=""></div>
				<div class="AdminCategoryListItemURL">1024x680 PNG/JPG</div>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddSubmit"><input type="submit" value="Add Sub-Category"></div>
				<div class="clear"></div>
			</div> <!-- class="AdminCategoryListingAddContainer" -->
		</form>
	<?
}

function AdminEditSubCategory($dataarray) {
	?>
		<form method="POST" action="/admin/categories_list" enctype="multipart/form-data">
			<input method="hidden" name="function" value="save_subcategory" style="display:none">
			<input method="hidden" name="form_subid" value="<?= $dataarray['subid'] ?>" style="display:none">
			<input method="hidden" name="form_cid" value="<?= $dataarray['parent_cid'] ?>" style="display:none">
			<div class="AdminCategoryListContainer">
				<div class="AdminCategoryListingEditHeader">Edit Sub-Category Properties for "<?= $dataarray['subcategory'] ?>", child of "<?= $dataarray['category']; ?>"</div>
				<div class="clear"></div>
				<div class="AdminCategoryListingEditItem">Short Name:</div>
				<div class="AdminCategoryListingAddValue">
					<input type="text" name="form_subcategory" size="20" value="<?= $dataarray['subcategory']?>">
				</div>
				<div class="clear"></div>
				<div class="AdminCategoryListingEditItem">Nice Description:</div>
				<div class="AdminCategoryListingAddValue">
					<input type="text" name="form_description" size="50" value="<?= $dataarray['description'] ?>">
				</div>
				<div class="clear"></div>
				<div class="AdminCategoryListingEditItem">URL:</div>
				<div class="AdminCategoryListingAddValue">
					<input type="text" name="form_url" size="20" value="<?= $dataarray['url'] ?>" style="text-transform: lowercase">
				</div>
				<div class="clear"></div>
				<div class="AdminCategoryListingEditItem">Stored Image:</div>
				<div class="AdminCategoryListingShowImage"><img src="/i/sample/<?= $dataarray['image_id'] ?>"></div>
				<div class="clear"></div>
				<div class="AdminCategoryListingEditItem">Image Name:</div>
				<div class="AdminCategoryListItemURL"><?= $dataarray['image_filename'] ?></div>
				<div class="AdminCategoryListingAddValue"><input name="filesToUpload[]" class="filesToUpload" size="40" id="1" type="file" multiple="" accept="image/png"></div>
				<div class="AdminCategoryListItemURL">1024x680</div>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddItem"></div>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddSubmit">
					<input type="submit" value="Update Category">
					<input type="button" name="Cancel" value="Cancel" onclick="window.location='/admin/categories_list'">
				</div>
				<div class="clear"></div>
			</div> <!-- AdminCategoryListContainer -->
		</form>
	<?
}

function AdminEditLocation($dataarray) {
	?>
		<form method="POST" action="/admin/locations_list" enctype="multipart/form-data">
			<input method="hidden" name="function" value="save_location" style="display:none">
			<input method="hidden" name="lid" value="<?= $dataarray['lid'] ?>" style="display:none">
			<div class="AdminCategoryListContainer">
				<div class="AdminCategoryListingEditHeader">
					Rename Location "<?= $dataarray['city'] ?>, <?= StateCodeToName($dataarray['state']) ?>"
				</div>
				<div class="clear"></div>
				<div class="AdminCategoryListingEditItem">City:</div>
				<div class="AdminCategoryListingAddValue">
					<input type="text" name="city" size="40" value="<?= $dataarray['city'] ?>">
				</div>
				<div class="AdminCategoryListingAddValue"><select name="state"><?= StateOptionsDropDown($dataarray['state']) ?></select></div>
				<div class="AdminCategoryListingAddSubmit">
					<input type="submit" value="Update Location">
					<input type="button" name="Cancel" value="Cancel" onclick="window.location='/admin/locations_list'">
				</div>
				<div class="clear"></div>
			</div> <!-- AdminCategoryListContainer -->
		</form>
	<?
}

function AdminEditStyle($dataarray) {
	?>
		<form method="POST" action="/admin/styles_list" enctype="multipart/form-data">
			<input method="hidden" name="function" value="save_style" style="display:none">
			<input method="hidden" name="sid" value="<?= $dataarray['sid'] ?>" style="display:none">
			<div class="AdminCategoryListContainer">
				<div class="AdminCategoryListingEditHeader">
					Edit Style Name "<?= $dataarray['name'] ?>"
				</div>
				<div class="clear"></div>
				<div class="AdminCategoryListingEditItem">Style Name:</div>
				<div class="AdminCategoryListingAddValue">
					<input type="text" name="name" size="40" value="<?= $dataarray['name'] ?>">
				</div>
				<div class="AdminCategoryListingAddSubmit">
					<input type="submit" value="Update Style">
					<input type="button" name="Cancel" value="Cancel" onclick="window.location='/admin/styles_list'">
				</div>
				<div class="clear"></div>
			</div> <!-- AdminCategoryListContainer -->
		</form>
	<?
}

function AdminsampleFormNew() {
	?>
  <script type="text/javascript">//<![CDATA[
		var filecounter = 1;
		function makeFileList() {
			var input = document.getElementById(filecounter);
			var ul = document.getElementById("fileList");
			for (var i = 0; i < input.files.length; i++) {
				var li = document.createElement("li");
				li.innerHTML = "(" + filecounter + ") " + input.files[i].name;
				ul.appendChild(li);
			}
			if(!ul.hasChildNodes()) {
				var li = document.createElement("li");
				li.innerHTML = 'No Files Selected';
				ul.appendChild(li);
			}
			add_file_field();
		}
		function add_file_field(){
			var input = document.getElementById(filecounter);
			filecounter = filecounter + 1;
			var container=document.getElementById('file_container');
			var file_field=document.createElement('input');
			file_field.name='filesToUpload[]';
			file_field.type='file';
			file_field.onchange='makeFileList();';
			file_field.setAttribute("onchange", "makeFileList();");
			file_field.id=filecounter;
			file_field.setAttribute("id",filecounter);
			container.appendChild(file_field);
			//var br_field=document.createElement('br');
			//container.appendChild(br_field);
		}
    jQuery(function($) {
      $("#Categories").bsmSelect({
        addItemTarget: 'bottom',
        animate: true,
        highlight: true,
        plugins: [
          $.bsmSelect.plugins.sortable({ axis : 'y', opacity : 0.5 }, { listSortableClass : 'bsmListSortableCustom' }),
          $.bsmSelect.plugins.compatibility()
        ]
      });
      $("#SubCategories").bsmSelect({
        addItemTarget: 'bottom',
        animate: true,
        highlight: true,
        plugins: [
          $.bsmSelect.plugins.sortable({ axis : 'y', opacity : 0.5 }, { listSortableClass : 'bsmListSortableCustom' }),
          $.bsmSelect.plugins.compatibility()
        ]
			});
      $("#Styles").bsmSelect({
        addItemTarget: 'bottom',
        animate: true,
        highlight: true,
        plugins: [
          $.bsmSelect.plugins.sortable({ axis : 'y', opacity : 0.5 }, { listSortableClass : 'bsmListSortableCustom' }),
          $.bsmSelect.plugins.compatibility()
        ]
      });
      $("#Locations").bsmSelect({
        addItemTarget: 'bottom',
        animate: true,
        highlight: true,
        plugins: [
          $.bsmSelect.plugins.sortable({ axis : 'y', opacity : 0.5 }, { listSortableClass : 'bsmListSortableCustom' }),
          $.bsmSelect.plugins.compatibility()
        ]
			});
    });
  //]]></script>
			<form method="POST" action="/admin/samples/add_new" enctype="multipart/form-data" id="irevform">
			<input type="hidden" name="function" value="add_new">
			<input type="hidden" name="formpage" value="1">
			<input type="hidden" value="irevform" name="<?= ini_get("session.upload_progress.name"); ?>">
			<div class="AdminCategoryListingAddContainer">
				<div class="AdminCategoryListingAddHeader">ADD NEW SAMPLE</div>
				<div class="AdminCategoryListingAddItem">Sample Real Name</div>
				<div class="AdminCategoryListingAddValue"><input type="text" name="name" value="<?= MakeCase(htmlspecialchars(trim($_REQUEST['name']))); ?>" size="50"></div>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddItem">Sample's Obfuscated Display Name</div>
				<div class="AdminCategoryListingAddValue"><input type="text" name="display_name" placeholder="Optional (auto-generated)" value="<?= MakeCase(htmlspecialchars(trim($_REQUEST['display_name']))); ?>" size="50"></div>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddItem">One-Line Summary,<br> Header, Slug</div>
				<div class="AdminCategoryListingAddTextBox"><textarea rows="2" cols="50" name="slug" wrap="virtual"><?= MakeCase(htmlspecialchars(trim($_REQUEST['slug']))); ?></textarea></div>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddItem">&nbsp;</div>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddItem">&nbsp;</div>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddItem"><label for="is_active">Sample is Active</label></div>
    		<div class="AdminCategoryListingCheckBox">
					<input type="checkbox" name="is_active" id="is_active" class="regular-checkbox big-checkbox" <?= /* I want default first page to be checked */ ($_REQUEST['is_active'] || !($_REQUEST['formpage']))? 'CHECKED ' : ''; ?>/><label title="Active, available for gigs" for="is_active"></label>
				</div>
				<div class="AdminCategoryListingAddItem"><label for="is_searchable">Sample is Searchable</label></div>
    		<div class="AdminCategoryListingCheckBox">
					<input type="checkbox" name="is_searchable" id="is_searchable" class="regular-checkbox big-checkbox" <?= /* I want default first page to be checked */ ($_REQUEST['is_searchable'] || !($_REQUEST['formpage']))? 'CHECKED ' : ''; ?>/><label title="Sample listed in search results" for="is_searchable"></label>
				</div>
				<div class="AdminCategoryListingAddItem"><label for="is_highlighted">Highlighted on Home Page<br>and Category Listings</label></div>
    		<div class="AdminCategoryListingCheckBox">
					<input type="checkbox" name="is_highlighted" id="is_highlighted" class="regular-checkbox big-checkbox" <?= /* I want default first page to be unchecked */ ($_REQUEST['is_highlighted'])? 'CHECKED ' : ''; ?>/><label title="Sample is highlighted on Home Page and Category Listings" for="is_highlighted"></label>
				</div>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddItem">Bio</div>
				<div class="AdminCategoryListingAddTextBox"><textarea style="resize: both;" rows="8" cols="85" name="bio" wrap="virtual"><?= htmlspecialchars(trim($_REQUEST['bio'])); ?></textarea></div>
				<div class="clear">&nbsp;</div>

				<div class="AdminCategoryListingAddItem">
					<label for="Categories">Website Categories</label>
				</div>
				<div class="AdminCategoryListingAddDropDown">
					<select id="Categories" multiple="multiple" name="categories[]" title="Categories" class="sminit">
						<?= AdminSelectCategories(); ?>
					</select>
				</div>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddItem">
					<label for="Styles">Performance Styles</label>
				</div>
				<div class="AdminCategoryListingAddDropDown">
					<select id="Styles" multiple="multiple" name="styles[]" title="Styles" class="sminit">
						<?= AdminSelectStyles(); ?>
					</select>
				</div>
				<div class="AdminCategoryListingAddItem">
					<label for="Locations">Available Locations</label>
				</div>
				<div class="AdminCategoryListingAddDropDown">
					<select id="Locations" multiple="multiple" name="locations[]" title="Locations" class="sminit">
						<?= AdminSelectLocations(); ?>
					</select>
				</div>
				<div class="AdminCategoryListingAddItem">Select all photo and video files to upload for this project sample.</div>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddItem"><font size="-1">High resolution photos and HQ videos will be automatically processed for the web site.</font></div>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddFiles">
					<div id="file_container"><input name="filesToUpload[]" class="filesToUpload" id="1" type="file" multiple="" onchange="makeFileList();" /></div>
				</div>
				<div class="clear"></div>
				<div class="AdminCategoryListingValue"><ul id="fileList"></ul></div>
				<div class="clear"></div>
				<div class="clear"></div>
				<div class="AdminCategoryListingAddSubmit"><input type="submit" value="Save New Sample"></div>
				<div id="bar_blank">
					<div id="status"></div>
					<div id="bar_color"></div>
				</div>
				<div class="clear"></div>
			</div> <!-- class="AdminCategoryListingAddContainer" -->
		</form>
		<script type="text/javascript" src="/templates/js/upload.js"></script>
	<?
}

function AdminsampleFormSingle($sampleinfo) {
	?>
	<script type="text/javascript" src="/templates/js/jwplayer/jwplayer.js"></script>
  <script type="text/javascript">//<![CDATA[
		var filecounter = 1;
		function makeFileList() {
			var input = document.getElementById(filecounter);
			var ul = document.getElementById("fileList");
			for (var i = 0; i < input.files.length; i++) {
				var li = document.createElement("li");
				li.innerHTML = "(" + filecounter + ") " + input.files[i].name;
				ul.appendChild(li);
			}
			if(!ul.hasChildNodes()) {
				var li = document.createElement("li");
				li.innerHTML = 'Upload Additional Media';
				ul.appendChild(li);
			}
			add_file_field();
		}
		function add_file_field(){
			var input = document.getElementById(filecounter);
			filecounter = filecounter + 1;
			var container=document.getElementById('file_container');
			var file_field=document.createElement('input');
			file_field.name='filesToUpload[]';
			file_field.type='file';
			file_field.onchange='makeFileList();';
			file_field.setAttribute("onchange", "makeFileList();");
			file_field.id=filecounter;
			file_field.setAttribute("id",filecounter);
			container.appendChild(file_field);
			//var br_field=document.createElement('br');
			//container.appendChild(br_field);
		}
    jQuery(function($) {
      $("#Categories").bsmSelect({
        addItemTarget: 'bottom',
        animate: true,
        highlight: true,
        plugins: [
          $.bsmSelect.plugins.sortable({ axis : 'y', opacity : 0.5 }, { listSortableClass : 'bsmListSortableCustom' }),
          $.bsmSelect.plugins.compatibility()
        ]
      });
      $("#SubCategories").bsmSelect({
        addItemTarget: 'bottom',
        animate: true,
        highlight: true,
        plugins: [
          $.bsmSelect.plugins.sortable({ axis : 'y', opacity : 0.5 }, { listSortableClass : 'bsmListSortableCustom' }),
          $.bsmSelect.plugins.compatibility()
        ]
      });
      $("#Styles").bsmSelect({
        addItemTarget: 'bottom',
        animate: true,
        highlight: true,
        plugins: [
          $.bsmSelect.plugins.sortable({ axis : 'y', opacity : 0.5 }, { listSortableClass : 'bsmListSortableCustom' }),
          $.bsmSelect.plugins.compatibility()
        ]
      });
      $("#Locations").bsmSelect({
        addItemTarget: 'bottom',
        animate: true,
        highlight: true,
        plugins: [
          $.bsmSelect.plugins.sortable({ axis : 'y', opacity : 0.5 }, { listSortableClass : 'bsmListSortableCustom' }),
          $.bsmSelect.plugins.compatibility()
        ]
			});
    });
		HEIGHTS = [];
		function getheight(images, width) {
			width -= images.length * 5;
			var h = 0;
			for (var i = 0; i < images.length; ++i) {
				h += $(images[i]).data('width') / $(images[i]).data('height');
			}
			return width / h;
		}
		function setheight(images, height) {
			HEIGHTS.push(height);
			for (var i = 0; i < images.length; ++i) {
				$(images[i]).css({
					width: height * $(images[i]).data('width') / $(images[i]).data('height'),
					height: height
				});
				$(images[i]).attr('src', $(images[i]).attr('src').replace(/w[0-9]+-h[0-9]+/, 'w' + $(images[i]).width() + '-h' + $(images[i]).height()));
			}
		}
		function resize(images, width) {
		  setheight(images, getheight(images, width));
		}
		function run(max_height) {
			var size = window.innerWidth - 50;
			var n = 0;
			var images = $('img');
			w: while (images.length > 0) {
				for (var i = 1; i < images.length + 1; ++i) {
					var slice = images.slice(0, i);
					var h = getheight(slice, size);
					if (h < max_height) {
						setheight(slice, h);
						n++;
						images = images.slice(i);
						continue w;
					}
				}
				setheight(slice, Math.min(max_height, h));
				n++;
				break;
			}
			console.log(n);
		}
		window.addEventListener('resize', function () { run(205); });
		$(function () { run(205); });

		//]]></script>
		<form method="POST" action="/admin/samples/edit/<?= $sampleinfo['oid']; ?>" enctype="multipart/form-data" id="irevform" name="edit<?= $sampleinfo['oid']; ?>">
			<input type="hidden" name="oid" value="<?= $sampleinfo['oid']; ?>">
			<input type="hidden" name="function" value="edit">
			<input type="hidden" value="irevform" name="<?= ini_get("session.upload_progress.name"); ?>">
			<div class="AdminsampleContainer">
				<div class="AdminSaveButtonContainer">
					<div class="AdminSaveLabel"><label for="save_me">Save Updates</label></div>
    			<div class="AdminCategoryListingCheckBox">
						<input type="submit" name="executeButton" value="update" class="AdminSaveButton" alt="Update <?= $sampleinfo['name']; ?>" id="save_me" />
					</div>
				</div>
				<div class="AdminsampleEditHeader">
					<B><a href="<?= CurServerUrl(); ?>sample/<?= $sampleinfo['url']; ?>"</a><?= $sampleinfo['name'] ?></a></B>
				</div>
				<div class="AdminsampleLastUpdateHeader">
					Last updated <?= nicetime($sampleinfo['last_updated']) ?>
				</div>
				<div class="clear"></div>
				<div class="AdminsampleCheckboxLabel"><label for="is_active">Active</label></div>
    		<div class="AdminCategoryListingCheckBox">
					<input type="checkbox" id="is_active" name="is_active" class="regular-checkbox big-checkbox" <?= ($sampleinfo['is_active']?'CHECKED ':'') ?>/><label title="sample is Active" for="is_active"></label>
				</div>
				<div class="AdminsampleCheckboxLabel"><label for="is_searchable">Searchable</label></div>
    		<div class="AdminCategoryListingCheckBox">
					<input type="checkbox" id="is_searchable" name="is_searchable" class="regular-checkbox big-checkbox" <?= ($sampleinfo['is_searchable']?'CHECKED ':'') ?>/><label title="Can be found in Search" for="is_searchable"></label>
				</div>
				<div class="AdminsampleCheckboxLabel"><label for="is_highlighted">Featured</label></div>
    		<div class="AdminCategoryListingCheckBox">
					<input type="checkbox" id="is_highlighted" name="is_highlighted" class="regular-checkbox big-checkbox" <?= ($sampleinfo['is_highlighted']?'CHECKED ':'') ?>/><label title="Highlighted as a Featured sample" for="is_highlighted"></label>
				</div>
				<div class="AdminsampleCheckboxLabel"><label for="use_display_name">Obfuscate Name</label></div>
    		<div class="AdminCategoryListingCheckBox">
					<input type="checkbox" id="use_display_name" name="use_display_name" class="regular-checkbox big-checkbox" <?= ($sampleinfo['use_display_name']?'CHECKED ':'') ?>/><label title="Enforce hiding the sample's Full Name" for="use_display_name"></label>
				</div>
				<div class="AdminSaveButtonContainer">
					<div class="AdminsampleCheckboxLabel"><label for="delete_me">Delete sample</label></div>
    			<div class="AdminCategoryListingCheckBox">
						<input type="submit" class="AdminDeleteButton" name="executeButton" value="delete" alt="Delete <?= $sampleinfo['name']; ?>" id="delete_me" style="width: 30px; height: 30px;" /><label title="Delete <?= $sampleinfo['name']; ?>" for="delete_me"></label>
					</div>
				</div>
				<div class="clear"></div>
				<div class="AdminsampleLabel">Project Sample Real Name:</div>
				<div class="AdminCategoryListingAddValue"><input type="text" name="name" value="<?= $sampleinfo['name']; ?>" size="50"></div>
				<div class="clear"></div>
				<div class="AdminsampleLabel">Obfuscated Display Name:</div>
				<div class="AdminCategoryListingAddValue"><input type="text" name="display_name" value="<?= $sampleinfo['display_name']; ?>" size="50"></div>
				<div class="clear"></div>
				<div class="AdminsampleLabel">Summary/Header/Slug:</div>
				<div class="AdminCategoryListingAddTextBox"><textarea rows="2" cols="50" name="slug" wrap="virtual"><?= $sampleinfo['slug']; ?></textarea></div>
				<div class="clear">&nbsp;</div>
				<div class="AdminsampleLabel">Bio:</div>
				<div class="AdminCategoryListingAddTextBox"><textarea style="resize: both;" rows="8" cols="65" name="bio" wrap="virtual"><?= $sampleinfo['bio']; ?></textarea></div>
				<div class="clear">&nbsp;</div>

				<div class="AdminsampleLabel">URL:</div>
				<div class="AdminCategoryListingAddValue"><input type="text" name="form_url" value="<?= $sampleinfo['url']; ?>" size="30"></div>
				<div class="AdminsampleLabel">Alt-URL:</div>
				<div class="AdminCategoryListingAddValue"><input type="text" name="alt_url" value="<?= $sampleinfo['alt_url']; ?>" size="30"></div>
				<div class="clear">&nbsp;</div>

				<div class="AdminCategoryListingAddItem">
					<label for="Categories">Website Categories</label>
				</div>
				<div class="AdminCategoryListingAddDropDown">
					<select id="Categories" multiple="multiple" name="categories[]" title="Categories" class="sminit">
						<?= AdminSelectCategories($sampleinfo['oid']); ?>
					</select>
				</div>
				<div class="clear"></div>

				<? AdminSelectSubCategories($sampleinfo); ?>

				<div class="clear"></div>
				<div class="AdminCategoryListingAddItem">
					<label for="Styles">Performance Styles</label>
				</div>
				<div class="AdminCategoryListingAddDropDown">
					<select id="Styles" multiple="multiple" name="styles[]" title="Styles" class="sminit">
						<?= AdminSelectStyles($sampleinfo['oid']); ?>
					</select>
				</div>
				<div class="AdminCategoryListingAddItem">
					<label for="Locations">Available Locations</label>
				</div>
				<div class="AdminCategoryListingAddDropDown">
					<select id="Locations" multiple="multiple" name="locations[]" title="Locations" class="sminit">
						<?= AdminSelectLocations($sampleinfo['oid']); ?>
					</select>
				</div>
				<div class="clear"></div>
				<div class="AdminSaveButtonContainer">
					<div class="AdminSaveLabel"><label for="save_me">Save Updates</label></div>
    			<div class="AdminCategoryListingCheckBox">
						<input type="submit" name="executeButton" value="update" class="AdminSaveButton" alt="Update <?= $sampleinfo['name']; ?>" id="save_me" />
					</div>
				</div>
				<div class="clear"></div>
				<div class="AdminsampleEditSubHeader">Media for <?= $sampleinfo['name']; ?>
				<div class="clear"></div>
				</div>
					<div class="AdminCategoryListingAddFilesSingle">
						<div id="file_container"><input name="filesToUpload[]" value="Upload Category Graphic" class="filesToUpload" id="1" type="file" multiple="" onchange="makeFileList();" /></div>
						<div class="AdminCategoryListingValue"><ul id="fileList"></ul></div>
					</div>
				<div class="clear"></div>
				<div id="bar_blank">
					<div id="status"></div>
					<div id="bar_color"></div>
				</div>
				<script type="text/javascript" src="/templates/js/upload.js"></script>
				<div class="clear"></div>
				<div class="AdminImagesPreviewContainer">
					<?= ShowPhotoArray($sampleinfo['media']); ?>
				</div>
				<div class="clear"></div>
				<div class="AdminImagesPreviewContainer">
					<?= PrepareVideoPlayer($sampleinfo); ?>
				</div>
				<div class="clear"></div>
				<div class="AdminSaveButtonContainer">
					<div class="AdminSaveLabel"><label for="save_me">Save Updates</label></div>
    			<div class="AdminCategoryListingCheckBox">
						<input type="submit" name="executeButton" value="update" class="AdminSaveButton" alt="Update <?= $sampleinfo['name']; ?>" id="save_me" />
					</div>
				</div>
				<div class="clear"></div>
			</div>
		</form>

	<?
}

function AdminVideoPreviewChooser($sampleinfo) {
	// using div in-line style background to show the images because img tag is taken by ShowPhotoArray()'s css image array resizer
	?>
		<div class="AdminVideoPreviewChooserContainer">
		<? for ($i = 1; $i < 5; $i++) { ?>
			<div class="CheckBoxImageContainer">
				<label for="<?= $sampleinfo['media']['mid'] . "-$i"; ?>"><div class="AdminVideoPreviewChooserImage" style="background: url('/i/sample/<?= $sampleinfo['media']['fileid']. "-$i.jpg"; ?>'); background-size:contain; background-repeat: no-repeat;"></div></label>
				<div class="CheckBoxImage"><input type="radio" name="radio[<?= $sampleinfo['media']['mid']; ?>]" id="<?= $sampleinfo['media']['mid']."-$i"; ?>" value="<?= $i; ?>"></div>
			</div>
		<? } ?>
		</div>
		<div class="VideoSize">
			<strong><?= $sampleinfo['media']['realdimensions']; ?></strong>
			(<?= $sampleinfo['media']['aspectratio']; ?>)
			<select name="videoaction[<?= $sampleinfo['media']['mid']; ?>]" <?= ((int)$sampleinfo['media']['viewable'] === 0)? "style='background-color: #FFBABA;' " : ""; ?>>
				<option value='' selected="SELECTED" disabled="disabled">Actions</option>
				<?= ((int)$sampleinfo['media']['viewable'] === 1)? '<option value="0">Make Hidden</option>' : '<option value="1">Make Visible</option>'; ?>
				<option value="delete">Remove Video</option>
			</select>
		</div>
		<div class="clear"></div>
	<?
}
