<?php

function get_json_nocache( $url ) {

	$expiration = 3600; // Seconds in an hour.
	$args = array(
		'timeout' => 60,
	);

    $request = wp_remote_get( $url, $args );

    $items = json_decode( wp_remote_retrieve_body( $request ) );

	if ( is_wp_error( $request ) ) {
		echo 'Please email UCFTeam-CREOL-IT@groups.ucf.edu with the url, error message, and screenshot.\n';
		echo $request->get_error_message() . '\n';
		return [];
	}

	// $items = json_decode( wp_remote_retrieve_body( $request ) );

	$items = array( $items->response )[0];

	return $items;
}