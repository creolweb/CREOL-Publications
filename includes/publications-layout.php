<?php
/**
 * Handles the form and the output.
 **/

 // Handles the dropdown on the left.
 function publications_form_display( $atts = [], $content = null, $tag = '' ) {

	$atts = array_change_key_case( (array) $atts, CASE_LOWER );

    $wporg_atts = shortcode_atts(
        array(
            'auth'  => '',
        ), $atts, $tag
    );

	$year_arr = get_json_nocache( 'https://api.creol.ucf.edu/PublicationsJson.asmx/YearList' );
	$type_arr = get_json_nocache( 'https://api.creol.ucf.edu/PublicationsJson.asmx/TypeList' );
	$author_arr = get_json_nocache( 'https://api.creol.ucf.edu/PublicationsJson.asmx/AuthorList' );

	ob_start();
	?>

	<div class="container">
		<div class="row">
			<!-- Form -->
				<form method="get" name="form" class="form-inline">
					<div class="col-xs-12 col-sm-6 col-md-2 form-group">
						<select name="pubyr" id="pubyr" class="form-control" onchange="handleSelectorChange()" style="width: 100%;">
							<option value=0>Year</option>
							<?php for ( $i = 0; $i < count( $year_arr ); $i++ ) : ?>
								<option value="<?= $year_arr[ $i ]->PublicationTxt ?>">
									<?= $year_arr[ $i ]->PublicationTxt ?>
								</option>
							<?php endfor; ?>
						</select>
					</div>
					<div class="col-xs-12 col-sm-6 col-md-2 form-group">
						<select name="type" id="type" class="form-control" onchange="handleSelectorChange()" style="width: 100%;">
							<option value=0>Type</option>
							<?php for ( $i = 0; $i < count( $type_arr ); $i++ ) : ?>
								<option value="<?= $type_arr[ $i ]->PublicationType ?>">
									<?= pub_type($type_arr[ $i ]->PublicationType) ?>
								</option>
							<?php endfor; ?>
						</select>
					</div>
					<div class="col-xs-12 col-sm-6 col-md-2 form-group">
						<select name="author" id="author" class="form-control" onchange="handleSelectorChange()" style="width: 100%;">
							<option value="0">Author</option>
							<?php for ( $i = 0; $i < count( $author_arr ); $i++ ) : ?>
								<option value="<?= $author_arr[ $i ]->PeopleID ?>">
									<?= $author_arr[ $i ]->LastFirstName ?>
								</option>
							<?php endfor; ?>
						</select>
					</div>

					<input type="hidden" name="pg" id="pg" value="<?php echo isset($_GET['pg']) ? $_GET['pg'] : 1; ?>">
					
					<div class="col-xs-12 col-sm-6 col-md-6 form-group">
						<div class="input-group" style="width: 100%;">
							<input type="search" id="search" name="search" class="form-control" placeholder="Search" aria-label="Search">
							<span class="input-group-btn">
								<button class="btn btn-primary" type="button"><i class="fa fa-search" aria-hidden="true"></i></button>
							</span>
						</div>
					</div>
					<br>
				</form>

				<script>
					let form = document.getElementsByName("form")[0];
					let elements = form.elements;
					function handleSelectorChange() {
						document.getElementById('pg').value = 1;

						for (let i = 0, len = elements.length; i < len; ++i) {
							elements[i].style.pointerEvents = "none";
							elements[i].onclick = () => false;
							elements[i].onkeydown = () => false;
							elements[i].style.backgroundColor = "#f0f0f0";
			            	elements[i].style.color = "#6c757d";
			            	elements[i].style.border = "1px solid #ced4da";
						}
						form.submit();
					}
				</script>

			<div class="col mt-lg-0 mt-5">
				<?php
				if ( isset( $_GET['pubyr'] ) && isset( $_GET['type'] ) && isset( $_GET['author'] ) ) {
					if ( $_GET['pubyr'] == ALL_YEARS && $_GET['type'] == ALL_TYPES && $_GET['author'] == ALL_AUTHORS ) {
						publications_display(ALL_YEARS, ALL_TYPES, ALL_AUTHORS, $_GET['pg'], $_GET['search']);
					} else {

						publications_display($_GET['pubyr'], $_GET['type'], $_GET['author'], $_GET['pg'], $_GET['search']);
						?>
						<script>
							const urlParams = new URLSearchParams(window.location.search);
							document.getElementById("pubyr").value = urlParams.get("pubyr");
							document.getElementById("type").value = urlParams.get("type");
							document.getElementById("author").value = urlParams.get("author");
							document.getElementById("search").value = urlParams.get("search");
						</script>
						<?php
					}
				} else {
					publications_display(ALL_YEARS, ALL_TYPES, ALL_AUTHORS, 1, "");
				}
				?>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

function publications_display( $year, $type, $author, $page, $search ) {
	$url = 'https://api.creol.ucf.edu/PublicationsJson.asmx/PublicationInfo?pubyr=' . $year . '&pubType=' . $type . '&pubAuth=' . $author . '&pg=' . $page . '&pubsearch=' . $search;
	$publication_info_arr = get_json_nocache($url);

	$countUrl = 'https://api.creol.ucf.edu/PublicationsJson.asmx/PublicationInfoCount?pubyr=' . $year . '&Type=' . $type . '&Author=' . $author;
	$total_publications = get_plain_text($countUrl);

	error_log(json_encode($publication_info_arr));

	$pageSize = 20;
    $totalPages = ceil($total_publications / $pageSize);
	?>

	<br>
	<div class="row float-right">
		Found <?= $total_publications ?> publications.
	</div>
	<br>

	<?php
	$range = 3;
	echo '<div class="text-right">';
    if ($page > 1) {		
        echo '<a href="?pubyr=' . $year . '&type=' . $type . '&pubAuth=' . $author . '&pg=1">First</a> ';
        echo '<a href="?pubyr=' . $year . '&type=' . $type . '&pubAuth=' . $author . '&pg=' . ($page - 1) . '"><i class="fa fa-caret-left" aria-hidden="true"></i></a> ';
    }
	else {
		echo '<span href="?pubyr=' . $year . '&type=' . $type . '&pubAuth=' . $author . '&pg=1">First</span> ';
        echo '<span href="?pubyr=' . $year . '&type=' . $type . '&pubAuth=' . $author . '&pg=' . ($page - 1) . '"><i class="fa fa-caret-left" aria-hidden="true"></i></span> ';
	}

    for ($x = ($page - $range); $x < (($page + $range) + 1); $x++) {
        if (($x > 0) && ($x <= $totalPages)) {
            if ($x == $page) {
                echo '<strong>' . $x . '</strong> ';
            } else {
                echo '<a href="?pubyr=' . $year . '&type=' . $type . '&pubAuth=' . $author . '&pg=' . $x . '">' . $x .'</a> '; 
            }
        }
    }

    if ($page < $totalPages) {
        echo '<a href="?pubyr=' . $year . '&type=' . $type . '&pubAuth=' . $author . '&pg=' . ($page + 1) . '"><i class="fa fa-caret-right" aria-hidden="true"></i></a> ';
        echo '<a href="?pubyr=' . $year . '&type=' . $type . '&pubAuth=' . $author . '&pg=' . $totalPages . '">Last</a>';
    }
	else {
		echo '<span href="?pubyr=' . $year . '&type=' . $type . '&pubAuth=' . $author . '&pg=' . ($page + 1) . '"><i class="fa fa-caret-right" aria-hidden="true"></i></span> ';
        echo '<span href="?pubyr=' . $year . '&type=' . $type . '&pubAuth=' . $author . '&pg=' . $totalPages . '">Last</span>';
	}

    echo '</div>';
	?>
	<script>
		var publications = <?= json_encode($publication_info_arr); ?>;
		var count = publications.length;
		// document.getElementById('publicationCount').textContent = count;
	</script>
	<?php
	$currentType = -1;
	foreach ( $publication_info_arr as $curr ) {
		?>
		<div class="px-2 pb-3 container">
			<?php if ( $curr->PublicationType != $currentType ) {
				?>
				<div class="row font-weight-bold">
					<?= pub_type($curr->PublicationType) ?>
				</div>
				<?php
				$currentType = $curr->PublicationType;
			}?>
			<div class="row">
				<div class="col-xs">
					<span class="h-5 font-weight-bold letter-spacing-1">
						<?= $curr->PublicationYear ?>
					</span>
				</div>
				<div class="col-sm">
					<?= $curr->Authors ?>.
					<span class="fw-italic">
					"<?= $curr->Title ?>".
					</span>
					<?= $curr->Reference ?>
					<?php if (isset($curr->PDFLink) && $curr->PDFLink != '') : ?>
						<a href="<?= $curr->PDFLink ?>" target="_blank"><i class="fa fa-file-pdf-o"></i></a>
					<?php endif; ?>
					<?php if (isset($curr->Link) && $curr->Link != '') : ?>
						<a href="<?= $curr->Link ?>" target="_blank"><i class="fa fa-external-link"></i></a>
					<?php endif; ?>
					<?php if (isset($curr->DOI) && $curr->DOI != '' && isset($curr->DOIVisble)) : ?>
						<a href="<?= $curr->DOI ?>" target="_blank"><i class="fa fa-external-link"></i></a>
					<?php endif; ?>
				</div>
			</div>
		</div>
			
		<?php
	}
	$range = 3;
	echo '<div class="text-right">';
	if ($page > 1) {		
		echo '<a href="?pubyr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=1">First</a> ';
		echo '<a href="?pubyr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . ($page - 1) . '"><i class="fa fa-caret-left" aria-hidden="true"></i></a> ';
	}
	else {
		echo '<span href="?pubyr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=1">First</span> ';
		echo '<span href="?pubyr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . ($page - 1) . '"><i class="fa fa-caret-left" aria-hidden="true"></i></span> ';
	}

	for ($x = ($page - $range); $x < (($page + $range) + 1); $x++) {
		if (($x > 0) && ($x <= $totalPages)) {
			if ($x == $page) {
				echo '<strong>' . $x . '</strong> ';
			} else {
				echo '<a href="?pubyr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . $x . '">' . $x .'</a> '; 
			}
		}
	}

	if ($page < $totalPages) {
		echo '<a href="?pubyr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . ($page + 1) . '"><i class="fa fa-caret-right" aria-hidden="true"></i></a> ';
		echo '<a href="?pubyr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . $totalPages . '">Last</a>';
	}
	else {
		echo '<span href="?pubyr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . ($page + 1) . '"><i class="fa fa-caret-right" aria-hidden="true"></i></span> ';
		echo '<span href="?pubyr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . $totalPages . '">Last</span>';
	}

	echo '</div>';
	echo '<br>';
	echo '<br>';
}