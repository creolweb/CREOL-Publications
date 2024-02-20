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
						<select name="yr" id="yr" class="form-control" onchange="this.form.submit()" style="width: 100%;">
							<option value=0>Year</option>
							<?php for ( $i = 0; $i < count( $year_arr ); $i++ ) : ?>
								<option value="<?= $year_arr[ $i ]->PublicationTxt ?>">
									<?= $year_arr[ $i ]->PublicationTxt ?>
								</option>
							<?php endfor; ?>
						</select>
					</div>
					<div class="col-xs-12 col-sm-6 col-md-3 form-group">
						<select name="type" id="type" class="form-control" onchange="this.form.submit()" style="width: 100%;">
							<option value=0>Type</option>
							<?php for ( $i = 0; $i < count( $type_arr ); $i++ ) : ?>
								<option value="<?= $type_arr[ $i ]->PublicationType ?>">
									<?= pub_type($type_arr[ $i ]->PublicationType) ?>
								</option>
							<?php endfor; ?>
						</select>
					</div>
					<div class="col-xs-12 col-sm-6 col-md-3 form-group">
						<select name="author" id="author" class="form-control" onchange="this.form.submit()" style="width: 100%;">
							<option value=0>Author</option>
							<?php for ( $i = 0; $i < count( $author_arr ); $i++ ) : ?>
								<option value="<?= $author_arr[ $i ]->PeopleID ?>">
									<?= $author_arr[ $i ]->LastFirstName ?>
								</option>
							<?php endfor; ?>
						</select>
					</div>
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

			<div class="col mt-lg-0 mt-5">
				<?php
				if ( isset( $_GET['year'] ) && isset( $_GET['type'] ) && isset( $_GET['author'] ) ) {
					if ( $_GET['year'] == ALL_YEARS && $_GET['type'] == ALL_TYPES && $_GET['author'] == ALL_AUTHORS ) {
						publications_display(ALL_YEARS, ALL_TYPES, ALL_AUTHORS, 1);
					} else {
						publications_display($_GET['year'], $_GET['type'], $_GET['author'], 1);
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
	$url = 'https://api.creol.ucf.edu/PublicationsJson.asmx/PublicationInfo?yr=' . $year . '&Type=' . $type . '&Author=' . $author . '&pg=' . 1;
	$publication_info_arr = get_json_nocache( $url );
	error_log(json_encode($publication_info_arr));

	$resultLength = count($publication_info_arr);
	$totalPages = ceil($totalItems / 20);
	?>

	<div class="row float-right">
		Found <?= $resultLength ?> publications.
	</div>
	<br>

	<?php if ($page > 1): ?>
		<a href="?yr=<?= $year ?>&type=<?= $type ?>&author=<?= $author ?>&pg=<?= $pg - 1 ?>">Previous</a>
	<?php endif; ?>

	<a href="?yr=<?= $year ?>&type=<?= $type ?>&author=<?= $author ?>&page=<?= $page + 1 ?>">Next</a>

	<script>
		var publications = <?= json_encode($publication_info_arr); ?>;
		var count = publications.length;
		document.getElementById('publicationCount').textContent = count;
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