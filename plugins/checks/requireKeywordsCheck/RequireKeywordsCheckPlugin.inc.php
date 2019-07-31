<?php

/**
 * @file plugins/checks/requireKeywordsCheck/RequireKeywordsCheckPlugin.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RequireKeywordsCheckPlugin
 * @ingroup plugins_checks_requireKeywordsCheck
 *
 * @brief Example of a check plugin
 */



import('lib.pkp.classes.plugins.CheckPlugin');

class RequireKeywordsCheckPlugin extends CheckPlugin {
	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return __('plugins.check.requireKeywordsCheck.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.check.requireKeywordsCheck.description');
	}

	/**
	 *
	 */
	public function check($publication, $submission, $allowedLocales, $primaryLocale) {

		$submissionKeywordDao = DAORegistry::getDAO('SubmissionKeywordDAO');
		$keywords = $submissionKeywordDao->getKeywords($publication->getId());

		$keyword = $keywords[reset($allowedLocales)];
		if (count($allowedLocales) === 1 && empty($keyword)) {
			return ['keywords' => __('plugins.check.requireKeywordsCheck.errorMessage')];
		}

		foreach ($allowedLocales as $locale) {
			if (empty($keywords[$locale])) {
				return ['keywords' => __('plugins.check.requireKeywordsCheck.errorMessageLocales')];
			}
		}

		return [];
	}
}
