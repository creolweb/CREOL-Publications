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
							<?php for ( $i = 0; $i < count( $instructor_arr ); $i++ ) : ?>
								<option value="<?= $type_arr[ $i ]->{PublicationType} ?>">
									<?= $type_arr[ $i ]->PublicationType ?>
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
                        <input type="search" name="search" id="search">
					</div>
					<br>
				</form>
			</div>
			<!-- Course output from form selection -->
			<div class="col mt-lg-0 mt-5">
				<?php
				if ( isset( $_GET['semester'] ) && isset( $_GET['instructor'] ) && isset( $_GET['course'] ) && isset( $_GET['level'] ) ) {
					if ( $_GET['semester'] == ALL_SEMESTERS && $_GET['instructor'] == ALL_INSTRUCTORS && $_GET['course'] == ALL_COURSES ) {
						echo 'Choose a semester, instructor, or course';
					} else {
						courses_display( $_GET['year'], $_GET['type'], $_GET['author'] );
						?>
						<!-- Setting the drop downs to match the selection -->
						<script>
							const urlParams = new URLSearchParams(window.location.search);
							document.getElementById("year").value = urlParams.get("year");
							document.getElementById("type").value = urlParams.get("type");
							document.getElementById("author").value = urlParams.get("author");
						</script>
						<?php
					}
				} else {
					courses_display( semester_serial(), ALL_INSTRUCTORS, ALL_COURSES, UNDERGRAD_GRAD );
					?>
					<script>
						document.getElementById("semester").value = <?= semester_serial() ?>;
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
	$url = 'https://api.creol.ucf.edu/PublicationsJson.asmx/PublicationInfo?Year=' . $year . '&Type=' . $type . '&Author=' . $author;
	$publication_info_arr = get_json( $url );

	foreach ( $publication_info_arr as $curr ) {
		?>
		<div class="px-2 pb-3">
			<span class="h-5 font-weight-bold letter-spacing-1">
				<?= $curr->Publication . ' ' . $curr->Author ?>
			</span><br>
		</div>
		<?php
	}
}
