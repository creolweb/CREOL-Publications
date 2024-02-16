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
						publications_display(ALL_YEARS, ALL_TYPES, ALL_AUTHORS);
					} else {
						publications_display($_GET['year'], $_GET['type'], $_GET['author']);
					}
				} else {
					publications_display(ALL_YEARS, ALL_TYPES, ALL_AUTHORS);
				}
				?>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

function publications_display( $year, $type, $author ) {
	$url = 'https://api.creol.ucf.edu/PublicationsJson.asmx/PublicationInfo?yr=' . $_GET['yr'] . '&Type=' . $_GET['type'] . '&Author=' . $_GET['author'];
	$publication_info_arr = get_json_nocache( $url );
	error_log(json_encode($publication_info_arr));
	?>
	
	<script>
    	console.log(<?= json_encode($url); ?>);
    	console.log(<?= json_encode(get_json_nocache('$url')); ?>);
	</script>
	<?php
	$currentType = -1;
	foreach ( $publication_info_arr as $curr ) {
		?>
		<div class="px-2 pb-3 container">
			<?php if ( $curr->PublicationType != $currentType ) {
				?>
				<div class="font-weight-bold">
					<?= pub_type($curr->PublicationType) ?>
				</div>
				<?php
				$currentType = $curr->PublicationType;
			}?>
			<div class="row">
				<div class="col-1">
					<span class="h-5 font-weight-bold letter-spacing-1">
						<?= $curr->PublicationYear ?>
					</span>
				</div>
				<div class="col-mb-2">
					<?= $curr->Authors ?>.
					<span class="fst-italic">
					"<?= $curr->Title ?>".
					<?= $curr->PDFLink ?>
					<a title="PDF version" class="pr-1 text-decoration-none" href="<?= $curr->PDFLink ?>" target="_blank">
						<i class="fa-solid fa-file-pdf" aria-hidden="true"></i>
					</a>
					</span>
						<script>console.log(<?php json_encode($curr->PublicationID); ?>);</script>
						<script>console.log(<?php json_encode($curr->PDFLink); ?>);</script>
						<!-- <i class="fa-solid fa-file-pdf" aria-hidden="true" href="<?php $curr->PDFLink?>"></i> -->
				</div>
			</div>
		</div>
			
		<?php
	}
}