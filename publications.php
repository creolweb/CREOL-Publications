<?php
/*
Plugin Name: Publications
Description: {{Short project description here}}
Version: 0.0.0
Author: UCF Web Communications
License: GPL3
GitHub Plugin URI: https://github.com/UCF/CREOL-Publications
*/

if ( ! defined( 'WPINC' ) ) {
    die;
}



require_once 'includes/publications-feed.php';
require_once 'includes/publications-functions.php';
require_once 'includes/publications-layout.php';
require_once 'includes/publications-list.php';

add_shortcode( 'publications', 'publications_form_display');
