<?php
/**
 * Handles the form and the output.
 **/

 // Handles the dropdown on the left.
 function publications_form_display() {
	$year_arr = get_json_nocache( 'https://api.creol.ucf.edu/PublicationsJson.asmx/YearList' );
	$type_arr = get_json_nocache( 'https://api.creol.ucf.edu/PublicationsJson.asmx/TypeList' );
	$author_arr = get_json_nocache( 'https://api.creol.ucf.edu/PublicationsJson.asmx/AuthorList' );

	ob_start();
	?>

	<div class="container">
		<div class="row">
			<!-- Form -->
				<form method="get" name="form" class="form-inline">
					<div class="col-xs-12 col-sm-6 col-md-3 form-group">
						<select name="yr" id="yr" class="form-control" onchange="handleSelectorChange()" style="width: 100%;">
							<option value=0>Year</option>
							<?php for ( $i = 0; $i < count( $year_arr ); $i++ ) : ?>
								<option value="<?= $year_arr[ $i ]->PublicationTxt ?>">
									<?= $year_arr[ $i ]->PublicationTxt ?>
								</option>
							<?php endfor; ?>
						</select>
					</div>
					<div class="col-xs-12 col-sm-6 col-md-3 form-group">
						<select name="type" id="type" class="form-control" onchange="handleSelectorChange()" style="width: 100%;">
							<option value=0>Type</option>
							<?php for ( $i = 0; $i < count( $type_arr ); $i++ ) : ?>
								<option value="<?= $type_arr[ $i ]->PublicationType ?>">
									<?= pub_type($type_arr[ $i ]->PublicationType) ?>
								</option>
							<?php endfor; ?>
						</select>
					</div>
					<div class="col-xs-12 col-sm-6 col-md-3 form-group">
						<select name="author" id="author" class="form-control" onchange="handleSelectorChange()" style="width: 100%;">
							<option value=0>Author</option>
							<?php for ( $i = 0; $i < count( $author_arr ); $i++ ) : ?>
								<option value="<?= $author_arr[ $i ]->PeopleID ?>">
									<?= $author_arr[ $i ]->LastFirstName ?>
								</option>
							<?php endfor; ?>
						</select>
					</div>

					<input type="hidden" name="pg" id="pg" value="<?php echo isset($_GET['pg']) ? $_GET['pg'] : 1; ?>">
					
					<div class="col-xs-12 col-sm-6 col-md-3 form-group">
							<div class="input-group">
							<input type="search" name="search" class="form-control" placeholder="Search" aria-label="Search" style="width: 100%;">
							<span class="input-group-btn">
								<button class="btn btn-primary" type="button"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
							</span>
						</div>
					</div>
					<br>
				</form>

				<script>
					let form = document.getElementsByName("form")[0];
					let elements = form.elements;
					function handleSelectorChange() {
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
				if ( isset( $_GET['yr'] ) && isset( $_GET['type'] ) && isset( $_GET['author'] ) ) {
					if ( $_GET['yr'] == ALL_YEARS && $_GET['type'] == ALL_TYPES && $_GET['author'] == ALL_AUTHORS ) {
						publications_display(ALL_YEARS, ALL_TYPES, ALL_AUTHORS, 1);
					} else {
						publications_display($_GET['yr'], $_GET['type'], $_GET['author'], $_GET['pg']);
						?>
						<script>
							const urlParams = new URLSearchParams(window.location.search);
							document.getElementById("yr").value = urlParams.get("yr");
							document.getElementById("type").value = urlParams.get("type");
							document.getElementById("author").value = urlParams.get("author");

							
						</script>
						<?php
					}
				} else {
					publications_display(ALL_YEARS, ALL_TYPES, ALL_AUTHORS, 1);
				}
				?>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

function publications_display($year, $type, $author, $page, $search) {
    $url = 'https://api.creol.ucf.edu/PublicationsJson.asmx/PublicationInfo?yr=' . $year . '&Type=' . $type . '&Author=' . $author . '&pg=' . $page . '&search=' . urlencode($search);
	$publication_info_arr = get_json_nocache($url);

	$countUrl = 'https://api.creol.ucf.edu/PublicationsJson.asmx/PublicationInfoCount?yr=' . $year . '&Type=' . $type . '&Author=' . $author;
	$total_publications = get_plain_text($countUrl);

	error_log(json_encode($publication_info_arr));

	$pageSize = 20;
    $totalPages = ceil($total_publications / $pageSize);
	?>

	<div class="row float-right">
		Found <?= $total_publications ?> publications.
	</div>
	<br>

	<?php
	$range = 3;
	echo '<div class="text-right">';
    if ($page > 1) {		
        echo '<a href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=1">First</a> ';
        echo '<a href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . ($page - 1) . '">«</a> ';
    }
	else {
		echo '<span href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=1">First</span> ';
        echo '<span href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . ($page - 1) . '">«</span> ';
	}

    for ($x = ($page - $range); $x < (($page + $range) + 1); $x++) {
        if (($x > 0) && ($x <= $totalPages)) {
            if ($x == $page) {
                echo '<strong>' . $x . '</strong> ';
            } else {
                echo '<a href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . $x . '">' . $x .'</a> '; 
            }
        }
    }

    if ($page < $totalPages) {
        echo '<a href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . ($page + 1) . '">»</a> ';
        echo '<a href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . $totalPages . '">Last</a>';
    }
	else {
		echo '<span href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . ($page + 1) . '">»</span> ';
        echo '<span href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . $totalPages . '">Last</span>';
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
					<?php if (isset($curr->DOI) && $curr->DOI != '') : ?>
						<a href="<?= $curr->DOI ?>" target="_blank"><i class="fa fa-external-link"></i></a>
					<?php endif; ?>




				</div>
			</div>
		</div>
			
		<?php
	}
}