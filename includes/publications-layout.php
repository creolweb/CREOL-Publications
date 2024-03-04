<?php
/**
 * Handles the form and the output.
 **/

 // Handles the dropdown on the left.
 function publications_form_display() {
    $year_arr = get_json_nocache('https://api.creol.ucf.edu/PublicationsJson.asmx/YearList');
    $type_arr = get_json_nocache('https://api.creol.ucf.edu/PublicationsJson.asmx/TypeList');
    $author_arr = get_json_nocache('https://api.creol.ucf.edu/PublicationsJson.asmx/AuthorList');

    ob_start();
?>
<div class="container">
    <div class="row">
        <!-- Form -->
        <form method="get" name="form" class="form-inline">
            <div class="col-xs-12 col-sm-6 col-md-2 form-group">
                <select name="pubyr" id="pubyr" class="form-control" onchange="handleSelectorChange()" style="width: 100%;">
                    <option value="0">Year</option>
                    <?php foreach ($year_arr as $year): ?>
                    <option value="<?= htmlspecialchars($year->PublicationTxt) ?>">
                        <?= htmlspecialchars($year->PublicationTxt) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-2 form-group">
                <select name="pubtype" id="pubtype" class="form-control" onchange="handleSelectorChange()" style="width: 100%;">
                    <option value="0">Type</option>
                    <?php foreach ($type_arr as $type): ?>
                    <option value="<?= htmlspecialchars($type->PublicationType) ?>">
                        <?= pub_type($type->PublicationType) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-12 col-sm-6 col-md-2 form-group">
                <select name="pubauth" id="pubauth" class="form-control" onchange="handleSelectorChange()" style="width: 100%;">
                    <option value="0">Author</option>
                    <?php foreach ($author_arr as $author): ?>
                    <option value="<?= htmlspecialchars($author->PeopleID) ?>">
                        <?= htmlspecialchars($author->LastFirstName) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="hidden" name="pubpg" id="pubpg" value="<?php echo isset($_GET['pubpg']) ? $_GET['pubpg'] : 1; ?>">
            <div class="col-xs-12 col-sm-6 col-md-6 form-group">
                <div class="input-group" style="width: 100%;">
                    <input type="search" id="pubsearch" name="pubsearch" class="form-control" placeholder="Search" aria-label="Search">
                    <span class="input-group-btn">
                        <button class="btn btn-primary" type="submit"><i class="fa fa-search" aria-hidden="true"></i></button>
                    </span>
                </div>
            </div>
            <br>
        </form>

        <script>
            function handleSelectorChange() {
                document.getElementById('pubpg').value = 1;
                document.forms['form'].submit();
            }
        </script>

        <div class="col mt-lg-0 mt-5">
            <?php
            if (isset($_GET['pubyr'], $_GET['pubtype'], $_GET['pubauth'])) {
                publications_display($_GET['pubyr'], $_GET['pubtype'], $_GET['pubauth'], $_GET['pubpg'], $_GET['pubsearch']);
            } else {
                publications_display('ALL_YEARS', 'ALL_TYPES', 'ALL_AUTHORS', 1, "");
            }
            ?>
        </div>
    </div>
</div>
<?php
    return ob_get_clean();
}

function publications_display( $year, $type, $author, $page, $search ) {
	$url = 'https://api.creol.ucf.edu/PublicationsJson.asmx/PublicationInfo?pubyr=' . $year . '&pubtype=' . $type . '&pubauth=' . $author . '&pubpg=' . $page . '&pubsearch=' . $search;
	$publication_info_arr = get_json_nocache($url);

	$countUrl = 'https://api.creol.ucf.edu/PublicationsJson.asmx/PublicationInfoCount?yr=' . $year . '&Type=' . $type . '&Author=' . $author;
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
        echo '<a href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=1">First</a> ';
        echo '<a href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . ($page - 1) . '"><i class="fa fa-caret-left" aria-hidden="true"></i></a> ';
    }
	else {
		echo '<span href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=1">First</span> ';
        echo '<span href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . ($page - 1) . '"><i class="fa fa-caret-left" aria-hidden="true"></i></span> ';
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
        echo '<a href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . ($page + 1) . '"><i class="fa fa-caret-right" aria-hidden="true"></i></a> ';
        echo '<a href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . $totalPages . '">Last</a>';
    }
	else {
		echo '<span href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . ($page + 1) . '"><i class="fa fa-caret-right" aria-hidden="true"></i></span> ';
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
		echo '<a href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=1">First</a> ';
		echo '<a href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . ($page - 1) . '"><i class="fa fa-caret-left" aria-hidden="true"></i></a> ';
	}
	else {
		echo '<span href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=1">First</span> ';
		echo '<span href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . ($page - 1) . '"><i class="fa fa-caret-left" aria-hidden="true"></i></span> ';
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
		echo '<a href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . ($page + 1) . '"><i class="fa fa-caret-right" aria-hidden="true"></i></a> ';
		echo '<a href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . $totalPages . '">Last</a>';
	}
	else {
		echo '<span href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . ($page + 1) . '"><i class="fa fa-caret-right" aria-hidden="true"></i></span> ';
		echo '<span href="?yr=' . $year . '&type=' . $type . '&author=' . $author . '&pg=' . $totalPages . '">Last</span>';
	}

	echo '</div>';
	echo '<br>';
	echo '<br>';
}