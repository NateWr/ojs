<?php

/**
 * @file plugins/checks/exampleCheck/ExampleCheckPlugin.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ExampleCheckPlugin
 * @ingroup plugins_checks_exampleCheck
 *
 * @brief Example of a check plugin
 */



import('lib.pkp.classes.plugins.CheckPlugin');

class ExampleCheckPlugin extends CheckPlugin {
	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return __('plugins.check.exampleCheck.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.check.exampleCheck.description');
	}

	/**
	 *
	 */
	public function check($publication, $submission, $allowedLocales, $primaryLocale) {
		return ['test' => __('plugins.check.exampleCheck.errorMessage')];
	}
}
