<?php

/**
 * @file plugins/checks/requireAbstractCheck/RequireAbstractCheckPlugin.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RequireAbstractCheckPlugin
 * @ingroup plugins_checks_requireAbstractCheck
 *
 * @brief Example of a check plugin
 */



import('lib.pkp.classes.plugins.CheckPlugin');

class RequireAbstractCheckPlugin extends CheckPlugin {
	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return __('plugins.check.requireAbstractCheck.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.check.requireAbstractCheck.description');
	}

	/**
	 *
	 */
	public function check($publication, $submission, $allowedLocales, $primaryLocale) {
		$abstract = $publication->getData('abstract');

		if (count($allowedLocales) === 1 && empty($abstract[reset($allowedLocales)])) {
			return ['abstract' => __('plugins.check.requireAbstractCheck.errorMessage')];
		}

		foreach ($allowedLocales as $locale) {
			if (empty($abstract[$locale])) {
				return ['abstract' => __('plugins.check.requireAbstractCheck.errorMessageLocales')];
			}
		}

		return [];
	}
}
