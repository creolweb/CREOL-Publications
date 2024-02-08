<?php
/**
 * Handles the form and the output.
 **/

 // Handles the dropdown on the left.
function publications_form_display() {
	$year_arr = get_json( 'https://api.creol.ucf.edu/PublicationsJson.asmx/YearList' );
	
	$type_arr = get_json( 'https://api.creol.ucf.edu/PublicationsJson.asmx/TypeList' );
	$author_arr = get_json( 'https://api.creol.ucf.edu/PublicationsJson.asmx/AuthorList' );

	ob_start();
	?>

	<div class="container">
		<div class="row">
			<!-- Form -->
			<div class="col-lg-3 col-12">
				<form method="get" name="form">
					<div class="form-group">
						<label for="year">Year</label>
						<select name="year" id="year" class="form-control" onchange="this.form.submit()">
							<option value=0>All</option>
							<?php for ( $i = 0; $i < count( $year_arr ); $i++ ) : ?>
								<option value="<?= $year_arr[ $i ]->PublicationTxt ?>">
									<?= $year_arr[ $i ]->PublicationTxt ?>
								</option>
							<?php endfor; ?>
						</select>
					</div>
					<div class="form-group">
						<label for="type">Type</label>
						<select name="type" id="type" class="form-control" onchange="this.form.submit()">
							<option value=-1>All</option>
							<?php for ( $i = 0; $i < count( $type_arr ); $i++ ) : ?>
								<option value="<?= $type_arr[ $i ]->{PublicationType} ?>">
									<?= pub_type($type_arr[ $i ]->PublicationType) ?>
								</option>
							<?php endfor; ?>
						</select>
					</div>
					<div class="form-group">
						<label for="author">Author</label>
						<select name="author" id="author" class="form-control" onchange="this.form.submit()">
							<option value=0>All</option>
							<?php for ( $i = 0; $i < count( $author_arr ); $i++ ) : ?>
								<option value="<?= $author_arr[ $i ]->PeopleID ?>">
									<?= $author_arr[ $i ]->LastFirstName ?>
								</option>
							<?php endfor; ?>
						</select>
					</div>
					<div class="form-group">
							<div class="input-group">
							<input type="search" class="form-control" placeholder="Search" aria-label="Search">
							<span class="input-group-btn">
								<button class="btn btn-primary" type="button"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
							</span>
						</div>
					</div>
					<br>
				</form>
			</div>

			<div class="col mt-lg-0 mt-5">
				<?php
				if ( isset( $_GET['year'] ) && isset( $_GET['type'] ) && isset( $_GET['author'] ) ) {
					publications_display( $_GET['year'], $_GET['type'], $_GET['author'] );
					?>
					<!-- Setting the drop downs to match the selection -->
					<script>
						const urlParams = new URLSearchParams(window.location.search);
						document.getElementById("year").value = urlParams.get("year");
						document.getElementById("type").value = urlParams.get("type");
						document.getElementById("author").value = urlParams.get("author");
					</script>
					<?php
				} else {
					publications_display(ALL_YEARS, ALL_TYPES, ALL__AUTHORS);
					?>
					<script>
						console.log(<?php echo json_encode($publication_info_arr); ?>);
						//document.getElementById("semester").value = <?= semester_serial() ?>;
					</script>
					<?php
				}
				?>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

function publications_display( $year, $type, $author ) {
	// $url = 'https://api.creol.ucf.edu/PublicationsJson.asmx/PublicationInfo?Year=' . $year . '&Type=' . $type . '&Author=' . $author;
	$url = 'https://api.creol.ucf.edu/PublicationsJson.asmx/PublicationList';
	$publication_info_arr = get_json( $url );

	foreach ( $publication_info_arr as $curr ) {
		?>
		<div class="px-2 pb-3">
			<span class="h-5 font-weight-bold letter-spacing-1">
				<?= $curr->Column1 ?>
			</span><br>
		</div>
		<?php
	}
}
