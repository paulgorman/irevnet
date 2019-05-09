<?
function htmlsamplePageTop($sampleinfo) {
	$sample = $sampleinfo[key($sampleinfo)];
	if (isset($sample['media']['filename'])) {
		$hlighted_filename = $sample['media']['filename'][key($sample['media']['mid'])];
	}
	if (isset($sample['media']['thumbwidth'])) {
		$hlighted_width = $sample['media']['thumbwidth'][key($sample['media']['mid'])];
		$hlighted_height = $sample['media']['thumbheight'][key($sample['media']['mid'])];
	}
	$hlighted_alt = $sample['name'];
	?>
		<div class="sampleTop">
			<div class="col6 fl sampleHeadImageContainer"><!-- sample Highlighted Photo -->
			<?
				if (count($sample['media']['mid']) === 1) {
					// If there's only one photo for the sample, just link this here image to high-res, since there's no Gridfolio
					?>
						<a href="/i/sample/original-<?= $hlighted_filename; ?>" title="Click for High-Resolution Image"><img class="sampleHeadImage" src="/i/sample/<?= $hlighted_filename; ?>" width="<?= $hlighted_width; ?>" height="<?= $hlighted_height; ?>" alt="<?= $hlighted_alt; ?>" title="<?= $hlighted_alt; ?>"></a>
					<?
				} else {
					?>
						<img class="sampleHeadImage" src="/i/sample/<?= $hlighted_filename; ?>" width="<?= $hlighted_width; ?>" height="<?= $hlighted_height; ?>" alt="<?= $hlighted_alt; ?>" title="<?= $hlighted_alt; ?>">
					<?
				}
			?>
			</div>
			<div class="col6 fr sampleTitle">
				<h1><?= $sample['name']; ?></h1>
				<h3><?= $sample['slug']; ?></h3>
				<div class="sampleBio">
					<?= $sample['bio']; ?>
				</div>
			<? FaceBookLike($sample); ?>
			</div>
			<div class="clearfix"></div>
			<div class="sampleVideo">
			<table border="0" width="100%">
			 <tr align="center">
			  <td align="center">
				<? PrepareVideoPlayer($sample); ?>
				</td>
			</tr>
			</table>
			</div>
			<div class="clearfix"></div>
		</div>
	<?
}

function htmlsamplePageBottom($sampleinfo) {
	$sample = $sampleinfo[key($sampleinfo)];
	?>
		<div class="sampleTop">
			<div class="sampleGrid box">
				<div id="goGrid" style="width:100%;"></div>
			</div>
		</div>
		<!-- grid data list -->
		<ul id="gridPlaylist" style="display: none;">
			<!-- skin -->
			<ul data-skin="">
				<li data-preloader-path="/templates/skin_minimal_dark_round/rotite-30-29.png"></li>
				<li data-show-more-thumbnails-button-normal-path="/templates/skin_minimal_dark_round/showMoreThumbsNormalState.png"></li>
				<li data-show-more-thumbnails-button-selectsed-path="/templates/skin_minimal_dark_round/showMoreThumbsSelectedState.png"></li>
				<li data-image-icon-path="/templates/skin_minimal_dark_round/photoIcon.png"></li>
				<li data-video-icon-path="/templates/skin_minimal_dark_round/videoIcon.png"></li>
				<li data-link-icon-path="/templates/skin_minimal_dark_round/linkIcon.png"></li>
				<li data-iframe-icon-path="/templates/skin_minimal_dark_round/iframeIcon.png"></li>
				<li data-hand-move-icon-path="/templates/skin_minimal_dark_round/handnmove.cur"></li>
				<li data-hand-drag-icon-path="/templates/skin_minimal_dark_round/handgrab.cur"></li>
				<li data-combobox-down-arrow-icon-normal-path="/templates/skin_minimal_dark_round/combobox-down-arrow.png"></li>
				<li data-combobox-down-arrow-icon-selected-path="/templates/skin_minimal_dark_round/combobox-down-arrow-rollover.png"></li>
				<li data-lightbox-slideshow-preloader-path="/templates/skin_minimal_dark_round/slideShowPreloader.png"></li>
				<li data-lightbox-close-button-normal-path="/templates/skin_minimal_dark_round/galleryCloseButtonNormalState.png"></li>
				<li data-lightbox-close-button-selected-path="/templates/skin_minimal_dark_round/galleryCloseButtonSelectedState.png"></li>
				<li data-lightbox-next-button-normal-path="/templates/skin_minimal_dark_round/nextIconNormalState.png"></li>
				<li data-lightbox-next-button-selected-path="/templates/skin_minimal_dark_round/nextIconSelectedState.png"></li>
				<li data-lightbox-prev-button-normal-path="/templates/skin_minimal_dark_round/prevIconNormalState.png"></li>
				<li data-lightbox-prev-button-selected-path="/templates/skin_minimal_dark_round/prevIconSelectedState.png"></li>
				<li data-lightbox-play-button-normal-path="/templates/skin_minimal_dark_round/playButtonNormalState.png"></li>
				<li data-lightbox-play-button-selected-path="/templates/skin_minimal_dark_round/playButtonSelectedState.png"></li>
				<li data-lightbox-pause-button-normal-path="/templates/skin_minimal_dark_round/pauseButtonNormalState.png"></li>
				<li data-lightbox-pause-button-selected-path="/templates/skin_minimal_dark_round/pauseButtonSelectedState.png"></li>
				<li data-lightbox-maximize-button-normal-path="/templates/skin_minimal_dark_round/maximizeButtonNormalState.png"></li>
				<li data-lightbox-maximize-button-selected-path="/templates/skin_minimal_dark_round/maximizeButtonSelectedState.png"></li>
				<li data-lightbox-minimize-button-normal-path="/templates/skin_minimal_dark_round/minimizeButtonNormalState.png"></li>
				<li data-lightbox-minimize-button-selected-path="/templates/skin_minimal_dark_round/minimizeButtonSelectedState.png"></li>
				<li data-lightbox-info-button-open-normal-path="/templates/skin_minimal_dark_round/infoButtonOpenNormalState.png"></li>
				<li data-lightbox-info-button-open-selected-path="/templates/skin_minimal_dark_round/infoButtonOpenSelectedState.png"></li>
				<li data-lightbox-info-button-close-normal-path="/templates/skin_minimal_dark_round/infoButtonCloseNormalPath.png"></li>
				<li data-lightbox-info-button-close-selected-path="/templates/skin_minimal_dark_round/infoButtonCloseSelectedPath.png"></li>
			</ul> 
			<ul data-cat="Category one">
				<?
					if (count($sample['media']['mid']) > 1) {
						// if there's only the one photo, don't duplicate it down here...
						foreach (array_keys($sample['media']['mid']) as $key) {
							if ($sample['media']['vidlength'][$key] > 0) { continue; }
							if ($sample['media']['viewable'][$key] != 1) { continue; }
							?>
				<ul>
					<li data-type="media" data-url="/i/sample/original-<?= $sample['media']['filename'][$key]; ?>" data-target="_self" data-width="<?= $sample['media']['thumbwidth'][$key]; ?>" data-height="<?= $sample['media']['thumbheight'][$key]; ?>"></li>
					<li data-thumbnail-path="/i/sample/<?= $sample['media']['filename'][$key]; ?>"></li>
					<li data-thumbnail-text>
						<p class="largeLabel"><?= $sample['name']; ?></p>
						<p class="smallLabel"><?= $sample['slug']; ?><br><?= $sample['media']['width'][$key]; ?> x <?= $sample['media']['height'][$key]; ?></p>
					</li>
					<li data-info="">
						<p class="mediaDescriptionHeader"><?= $sample['name']; ?> High Resolution Image Download</p>
						<p class="mediaDescriptionText"><a href="/i/sample/original-<?= $sample['media']['filename'][$key]; ?>"><?= $sample['media']['filename'][$key]; ?></a> (<?= $sample['media']['width'][$key]; ?> x <?= $sample['media']['height'][$key]; ?>) Published: <?= nicetime(date("r",$sample['media']['published'][$key])); ?></p>
					</li>
				</ul>
							<?
						}
					}
				?>
			</ul>
		</ul>
	<?
}

function htmlsamplePageBottomGallery($sampleinfo) {
	$sample = $sampleinfo[key($sampleinfo)];
	?>
		<div class="sampleTop">
			<div class="sampleGrid box">
				<div id="photoGrid" class="alt1">
				<?
					if (count($sample['media']['mid']) > 1) {
						// if there's only the one photo, don't duplicate it down here...
						foreach (array_keys($sample['media']['mid']) as $key) {
							if ($sample['media']['vidlength'][$key] > 0) { continue; }	// no videos allowed
							if ($sample['media']['viewable'][$key] != 1) { continue; }
							?>
									<a class="photo" href="/i/sample/original-<?= $sample['media']['filename'][$key]; ?>" alt="<?= $sample['name']; ?> Photo #<?= sprintf('%02d',$key); ?>">
										<img src="/i/sample/<?= $sample['media']['filename'][$key]; ?>" alt="<?= $sample['name']; ?> #<?= sprintf('%02d', $key); ?><br><?= number_format($sample['media']['width'][$key]); ?> x <?= number_format($sample['media']['height'][$key]); ?><br>Published <?= nicetime(date("r",$sample['media']['published'][$key])); ?>" />
									</a>
							<?
						}
					}
				?>
				</div>
			</div>
		</div>
	<?
}

function htmlsamplePageGalleryJS() {
	?>
		<script>
		$(document).ready(function(){
			$("#photoGrid").justifiedGallery({
				lastRow: 'justify', // justify / nojustify / hide
				maxRowHeight : -1, //negative value = no limits, 0 = 1.5 * rowHeight
				sizeRangeSuffixes: {'lt100':'', 'lt240':'', 'lt320':'', 'lt500':'', 'lt640':'', 'lt1024':''},
				rowHeight: 300,
				captions: true,
				margins: 15
			});
		});
		</script>
	<?
}

function htmlStylesTags($samples) {
	$colors = array("green","blue","turkese","orange","");
	$i = 0;
	?>
		<div class="sampleStyles">
	<?
	foreach (array_keys($samples) as $oid) {
		foreach (array_keys($samples[$oid]['styles']) as $key) {
			if ($i > 8) { continue; }
			$color = $colors[$i];
			$i++;
			if ($i == count($colors)) {
				$i = 0;
			}
			?>
				<p class="btn big <?= $color; ?>"><?= $samples[$oid]['styles'][$key]; ?></p>
			<?
		}
	}
	?>
		</div>
	<?
}
