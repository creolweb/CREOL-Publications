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
							<input type="search" class="form-control" placeholder="Search" aria-label="Search" style="width: 100%;">
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

			<div class="col mt-lg-0 mt-5">d
				<?php
				if ( isset( $_GET['yr'] ) && isset( $_GET['type'] ) && isset( $_GET['author'] ) ) {
					if ( $_GET['yr'] == ALL_YEARS && $_GET['type'] == ALL_TYPES && $_GET['author'] == ALL_AUTHORS ) {
						publications_display(ALL_YEARS, ALL_TYPES, ALL_AUTHORS, 1);
					} else {
						publications_display($_GET['yr'], $_GET['type'], $_GET['author'], 1);
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

function publications_display( $year, $type, $author, $pg ) {

    if ($page > 1) {
        echo '<a href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . ($page - 1) . '">Previous</a>';
    }
    if ($page < $totalPages) {
        echo '<a href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . ($page + 1) . '">Next</a>';
    }

	$url = 'https://api.creol.ucf.edu/PublicationsJson.asmx/PublicationInfo?yr=' . $year . '&Type=' . $type . '&Author=' . $author . '&pg=' . $pg;
	$publication_info_arr = get_json_nocache($url);

	$countUrl = 'https://api.creol.ucf.edu/PublicationsJson.asmx/PublicationInfoCount?yr=' . $year . '&Type=' . $type . '&Author=' . $author;
	$total_publications = get_json_nocache($countUrl);

	error_log(json_encode($publication_info_arr));

	$resultLength = count($publication_info_arr);
	$pageSize = 20;
    $totalPages = ceil($resultLength / $pageSize);
	?>

	<div class="row float-right">
		Found <?= $total_publications ?> publications.
	</div>
	<br>

	<?php if ($page > 1) {
        echo '<a href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . ($page - 1) . '">Previous</a>';
    }
    if ($page < $totalPages) {
        echo '<a href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . ($page + 1) . '">Next</a>';
    }
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
					<span class="fst-italic">
					"<?= $curr->Title ?>".
					<?= $curr->PDFLink ?>
					<a>
						<i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
					</a>
					</span>
				</div>
			</div>
		</div>
			
		<?php
	}
}