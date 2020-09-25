<?php

	// if uninstall.php is not called by WordPress, die
	if (!defined('WP_UNINSTALL_PLUGIN')) {
	die;
	}

	// Clearing option created by the EDSP
	delete_option('EDSP_separator_format');
	delete_option('EDSP_attributes');
	delete_option('EDSP_classes');


