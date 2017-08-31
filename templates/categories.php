<?
	/* Category displays */

function ErrorDisplay($error) {
	echo "<div class='SiteError'>$error</div>";
}

function ListCategoryCarousel ($categoryList) {
	// $url, $category, description, is_highlighted
	?>
		<div id="goCarousel"></div>
			<ul id="carouselPlaylist" style="display: none;">
				<!-- skin -->
				<ul data-skin="">
					<li data-preloader-path="/templates/skin_modern_silver/preloader.png"></li>
					<li data-thumbnail-gradient-left-path="/templates/skin_modern_silver/gradientLeft.png"></li>
					<li data-thumbnail-gradient-right-path="/templates/skin_modern_silver/gradientRight.png"></li>
					<li data-thumbnail-title-gradient-path="/templates/skin_modern_silver/textGradient.png"></li>
					<li data-next-button-normal-path="/templates/skin_modern_silver/nextButtonNormalState.png"></li>
					<li data-next-button-selected-path="/templates/skin_modern_silver/nextButtonSelectedState.png"></li>
					<li data-prev-button-normal-path="/templates/skin_modern_silver/prevButtonNormalState.png"></li>
					<li data-prev-button-selected-path="/templates/skin_modern_silver/prevButtonSelectedState.png"></li>
					<li data-play-button-normal-path="/templates/skin_modern_silver/playButtonNormalState.png"></li>
					<li data-play-button-selected-path="/templates/skin_modern_silver/playButtonSelectedState.png"></li>
					<li data-pause-button-path="/templates/skin_modern_silver/pauseButtonSelectedState.png"></li>
					<li data-handler-left-normal-path="/templates/skin_modern_silver/handlerLeftNormal.png"></li>
					<li data-handler-left-selected-path="/templates/skin_modern_silver/handlerLeftSelected.png"></li>
					<li data-handler-center-normal-path="/templates/skin_modern_silver/handlerCenterNormal.png"></li>
					<li data-handler-center-selected-path="/templates/skin_modern_silver/handlerCenterSelected.png"></li>
					<li data-handler-right-normal-path="/templates/skin_modern_silver/handlerRightNormal.png"></li>
					<li data-handler-right-selected-path="/templates/skin_modern_silver/handlerRightSelected.png"></li>
					<li data-track-left-path="/templates/skin_modern_silver/trackLeft.png"></li>
					<li data-track-center-path="/templates/skin_modern_silver/trackCenter.png"></li>
					<li data-track-right-path="/templates/skin_modern_silver/trackRight.png"></li>
					<li data-slideshow-timer-path="/templates/skin_modern_silver/slideshowTimer.png"></li>
					<li data-lightbox-slideshow-preloader-path="/templates/skin_modern_silver/slideShowPreloader.png"></li>
					<li data-lightbox-close-button-normal-path="/templates/skin_modern_silver/closeButtonNormalState.png"></li>
					<li data-lightbox-close-button-selected-path="/templates/skin_modern_silver/closeButtonSelectedState.png"></li>
					<li data-lightbox-next-button-normal-path="/templates/skin_modern_silver/lightboxNextButtonNormalState.png"></li>
					<li data-lightbox-next-button-selected-path="/templates/skin_modern_silver/lightboxNextButtonSelectedState.png"></li>
					<li data-lightbox-prev-button-normal-path="/templates/skin_modern_silver/lightboxPrevButtonNormalState.png"></li>
					<li data-lightbox-prev-button-selected-path="/templates/skin_modern_silver/lightboxPrevButtonSelectedState.png"></li>
					<li data-lightbox-play-button-normal-path="/templates/skin_modern_silver/lightboxPlayButtonNormalState.png"></li>
					<li data-lightbox-play-button-selected-path="/templates/skin_modern_silver/lightboxPlayButtonSelectedState.png"></li>
					<li data-lightbox-pause-button-normal-path="/templates/skin_modern_silver/lightboxPauseButtonNormalState.png"></li>
					<li data-lightbox-pause-button-selected-path="/templates/skin_modern_silver/lightboxPauseButtonSelectedState.png"></li>
					<li data-lightbox-maximize-button-normal-path="/templates/skin_modern_silver/maximizeButtonNormalState.png"></li>
					<li data-lightbox-maximize-button-selected-path="/templates/skin_modern_silver/maximizeButtonSelectedState.png"></li>
					<li data-lightbox-minimize-button-normal-path="/templates/skin_modern_silver/minimizeButtonNormalState.png"></li>
					<li data-lightbox-minimize-button-selected-path="/templates/skin_modern_silver/minimizeButtonSelectedState.png"></li>
					<li data-lightbox-info-button-open-normal-path="/templates/skin_modern_silver/infoButtonOpenNormalState.png"></li>
					<li data-lightbox-info-button-open-selected-path="/templates/skin_modern_silver/infoButtonOpenSelectedState.png"></li>
					<li data-lightbox-info-button-close-normal-path="/templates/skin_modern_silver/infoButtonCloseNormalPath.png"></li>
					<li data-lightbox-info-button-close-selected-path="/templates/skin_modern_silver/infoButtonCloseSelectedPath.png"></li>
					<li data-combobox-arrow-icon-normal-path="/templates/skin_modern_silver/comboboxArrowNormal.png"></li>
					<li data-combobox-arrow-icon-selected-path="/templates/skin_modern_silver/comboboxArrowSelected.png"></li>
				</ul><!-- /data-skin -->
				<!-- /skin -->
				<!-- category  -->
				<ul data-cat="Category one">
				<?
					foreach ($categoryList as $key => $blah) {
						?>
						<ul>
							<li data-type="link" data-url="/categories/<?= $categoryList[$key]['url']; ?>" data-target="_self"></li>
							<li data-thumbnail-path="/i/category/<?= $categoryList[$key]['carousel_id']; ?>"></li>
							<li data-thumbnail-text="<?= $categoryList[$key]['category']; ?>" data-thumbnail-text-title-offset="35" data-thumbnail-text-offset-top="10" data-thumbnail-text-offset-bottom="7">
								<p class="largeLabel"><?= $categoryList[$key]['category']; ?></p>
								<p class="smallLabel"><?= $categoryList[$key]['description']; ?></p>
							</li>
							<li data-info="">
								<p class="mediaDescriptionHeader"><?= $categoryList[$key]['catagory']; ?></p>
								<p class="mediaDescriptionText"><?= $categoryList[$key]['description']; ?></p>
							</li>
						</ul>
						<?
					}
				?>
				</ul><!-- /category one -->
			<!-- /category -->
		</ul><!-- /CarouselPlaylist -->
	<?
}

function ListAllCategories($categoryList) {
	// $url, $category, description, is_highlighted
	echo "<div class='content'>";
		foreach ($categoryList as $key => $blah) {
			$i++;
			($i % 2 == 0 )? $float="fr" : $float="fl";
			?>
				<div class="box catButton blue <?= $float; ?>"><a href="/category/<?= $categoryList[$key]['url']; ?>"><img src="/i/category/<?= $categoryList[$key]['image_id']; ?>" width="420" height="62" title="<?= $categoryList[$key]['category']; ?> - <?= $categoryList[$key]['description']; ?>" alt="<?= $categoryList[$key]['category']; ?>"></a></div>
			<?
		}
	echo "</div>";
}

function htmlCategoryImage($categoryImage,$category) {
	?>
		<div class='catHeader'><img src="/i/category/<?= $categoryImage; ?>" width="485" height="60" title="<?= $category; ?>" alt="<?= $category; ?>"></div>
	<?
}

function htmlCategoryImageBelow($categoryImage,$category) {
	?>
		<div class='catHeaderBelow'><img src="/i/category/<?= $categoryImage; ?>" width="485" height="60" title="<?= $category; ?>" alt="<?= $category; ?>"></div>
	<?
}

function ListSamplesForCategory($category,$samples) {
	// $samples array of name, url, filename, slug
	$width = 298;
	$height = 250;
	?>

		<div class="sampleArray"><!-- start of sampleArray -->
		<?
			foreach ($samples as $key => $blah) {
				$height = abs(round( ($samples[$key]['thumbwidth'] / $samples[$key]['thumbheight'])) * $width);
				//$width = abs(round( ($samples[$key]['thumbwidth'] / $samples[$key]['thumbheight'])) * $height );
				$text = sprintf(
					"<a href='/sample/%s' title=\"%s\"><p class='samplesListBoxText'><span class='samplesListBoxTextName'>%s</span><br>%s</p></a>",
					$samples[$key]['url'],
					$samples[$key]['name'] . " - " . $samples[$key]['slug'],
					$samples[$key]['name'],
					$samples[$key]['slug']
				);
				$photo = sprintf(
					// fields
					"<div class='box samplesListBox blue fl'>".
					"<a href='/sample/%s' border='0'>".
					"<img class='samplesListBoxImage' src='/i/sample/%s' width='%s' height='%s' alt=\"%s\" title=\"%s\">".
					"</a>".
					$text .
					"</div>\n",
					// values
					$samples[$key]['url'],
					$samples[$key]['filename'],
					$width,
					$height,
					$samples[$key]['name'],
					$samples[$key]['name'] . " - " . $samples[$key]['slug']
				);
				echo "$photo\n";
			}
		?>
		<div class="clearfix"></div>
		</div><!-- /sampleArray -->
	<?
}

function ListSamplesTextLinks($category,$samples) {
	echo "<h3>". $category['category'] . " - " . $category['description'] . "</h3><hr>";
	echo "<div class='textlist'>\n";
	echo "<ul>\n";
	foreach ($samples as $key => $blah) {
		$line = sprintf(
			"<li><a href=\"/sample/%s\" title=\"%s\"><span class=\"textname\">%s</span> - %s</a></li>\n",
			$samples[$key]['url'],
			$samples[$key]['name'],
			$samples[$key]['name'],
			$samples[$key]['slug']
		);
		echo $line;
	}
	echo "<div class=\"clearfix\"></div></ul>\n";
	echo "</div>\n";
}

function ListCategoriesTextLinks($categories) {
	echo "<div class='textlist'>\n";
	echo "<ul>\n";
	foreach (array_keys($categories) as $key) {
		$line = sprintf(
			"<li><a href=\"/category/%s\" title=\"%s\"><span class=\"textname\">%s</span> - %s</a></li>\n",
			$categories[$key]['url'],
			$categories[$key]['category'],
			$categories[$key]['category'],
			$categories[$key]['description']
		);
		echo $line;
	}
	echo "<div class=\"clearfix\"></div></ul>\n";
	echo "</div>\n";
}

function ListSampleCarousel($category,$samples) {
	?>
		<div id="goCarousel"></div>
			<ul id="carouselPlaylist" style="display: none;">
			<!-- skin -->
			<ul data-skin="">
				<li data-preloader-path="/templates/skin_modern_silver/preloader.png"></li>
				<li data-thumbnail-gradient-left-path="/templates/skin_modern_silver/gradientLeft.png"></li>
				<li data-thumbnail-gradient-right-path="/templates/skin_modern_silver/gradientRight.png"></li>
				<li data-thumbnail-title-gradient-path="/templates/skin_modern_silver/textGradient.png"></li>
				<li data-next-button-normal-path="/templates/skin_modern_silver/nextButtonNormalState.png"></li>
				<li data-next-button-selected-path="/templates/skin_modern_silver/nextButtonSelectedState.png"></li>
				<li data-prev-button-normal-path="/templates/skin_modern_silver/prevButtonNormalState.png"></li>
				<li data-prev-button-selected-path="/templates/skin_modern_silver/prevButtonSelectedState.png"></li>
				<li data-play-button-normal-path="/templates/skin_modern_silver/playButtonNormalState.png"></li>
				<li data-play-button-selected-path="/templates/skin_modern_silver/playButtonSelectedState.png"></li>
				<li data-pause-button-path="/templates/skin_modern_silver/pauseButtonSelectedState.png"></li>
				<li data-handler-left-normal-path="/templates/skin_modern_silver/handlerLeftNormal.png"></li>
				<li data-handler-left-selected-path="/templates/skin_modern_silver/handlerLeftSelected.png"></li>
				<li data-handler-center-normal-path="/templates/skin_modern_silver/handlerCenterNormal.png"></li>
				<li data-handler-center-selected-path="/templates/skin_modern_silver/handlerCenterSelected.png"></li>
				<li data-handler-right-normal-path="/templates/skin_modern_silver/handlerRightNormal.png"></li>
				<li data-handler-right-selected-path="/templates/skin_modern_silver/handlerRightSelected.png"></li>
				<li data-track-left-path="/templates/skin_modern_silver/trackLeft.png"></li>
				<li data-track-center-path="/templates/skin_modern_silver/trackCenter.png"></li>
				<li data-track-right-path="/templates/skin_modern_silver/trackRight.png"></li>
				<li data-slideshow-timer-path="/templates/skin_modern_silver/slideshowTimer.png"></li>
				<li data-lightbox-slideshow-preloader-path="/templates/skin_modern_silver/slideShowPreloader.png"></li>
				<li data-lightbox-close-button-normal-path="/templates/skin_modern_silver/closeButtonNormalState.png"></li>
				<li data-lightbox-close-button-selected-path="/templates/skin_modern_silver/closeButtonSelectedState.png"></li>
				<li data-lightbox-next-button-normal-path="/templates/skin_modern_silver/lightboxNextButtonNormalState.png"></li>
				<li data-lightbox-next-button-selected-path="/templates/skin_modern_silver/lightboxNextButtonSelectedState.png"></li>
				<li data-lightbox-prev-button-normal-path="/templates/skin_modern_silver/lightboxPrevButtonNormalState.png"></li>
				<li data-lightbox-prev-button-selected-path="/templates/skin_modern_silver/lightboxPrevButtonSelectedState.png"></li>
				<li data-lightbox-play-button-normal-path="/templates/skin_modern_silver/lightboxPlayButtonNormalState.png"></li>
				<li data-lightbox-play-button-selected-path="/templates/skin_modern_silver/lightboxPlayButtonSelectedState.png"></li>
				<li data-lightbox-pause-button-normal-path="/templates/skin_modern_silver/lightboxPauseButtonNormalState.png"></li>
				<li data-lightbox-pause-button-selected-path="/templates/skin_modern_silver/lightboxPauseButtonSelectedState.png"></li>
				<li data-lightbox-maximize-button-normal-path="/templates/skin_modern_silver/maximizeButtonNormalState.png"></li>
				<li data-lightbox-maximize-button-selected-path="/templates/skin_modern_silver/maximizeButtonSelectedState.png"></li>
				<li data-lightbox-minimize-button-normal-path="/templates/skin_modern_silver/minimizeButtonNormalState.png"></li>
				<li data-lightbox-minimize-button-selected-path="/templates/skin_modern_silver/minimizeButtonSelectedState.png"></li>
				<li data-lightbox-info-button-open-normal-path="/templates/skin_modern_silver/infoButtonOpenNormalState.png"></li>
				<li data-lightbox-info-button-open-selected-path="/templates/skin_modern_silver/infoButtonOpenSelectedState.png"></li>
				<li data-lightbox-info-button-close-normal-path="/templates/skin_modern_silver/infoButtonCloseNormalPath.png"></li>
				<li data-lightbox-info-button-close-selected-path="/templates/skin_modern_silver/infoButtonCloseSelectedPath.png"></li>
				<li data-combobox-arrow-icon-normal-path="/templates/skin_modern_silver/comboboxArrowNormal.png"></li>
				<li data-combobox-arrow-icon-selected-path="/templates/skin_modern_silver/comboboxArrowSelected.png"></li>
			</ul>
			<!-- category  -->
			<ul data-cat="Category one">
			<?
				foreach ($samples as $key => $blah) {
					?>
				<ul>
					<li data-type="link" data-url="/sample/<?= $samples[$key]['url']; ?>" data-target="_self"></li>
					<li data-thumbnail-path="/i/sample/<?= $samples[$key]['filename']; ?>"></li>
					<li data-thumbnail-text="<?= $samples[$key]['name']; ?>" data-thumbnail-text-title-offset="35" data-thumbnail-text-offset-top="10" data-thumbnail-text-offset-bottom="7">
						<p class="largeLabel"><?= $samples[$key]['name']; ?></p>
						<p class="smallLabel"><?= $samples[$key]['slug']; ?></p>
					</li>
					<li data-info="">
						<p class="mediaDescriptionHeader"><?= $samples[$key]['name']; ?></p>
						<p class="mediaDescriptionText"><?= $samples[$key]['slug']; ?></p>
					</li>
				</ul>
					<?
				}
			?>
		</ul><!-- /category -->
	</ul><!-- /carouselPlaylist -->
	<?
}
