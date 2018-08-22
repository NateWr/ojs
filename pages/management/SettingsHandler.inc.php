<?php

/**
 * @file pages/management/SettingsHandler.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SettingsHandler
 * @ingroup pages_management
 *
 * @brief Handle requests for settings pages.
 */

// Import the base ManagementHandler.
import('lib.pkp.pages.management.ManagementHandler');

class SettingsHandler extends ManagementHandler {
	/**
	 * Constructor.
	 */
	function __construct() {
		parent::__construct();
		$this->addRoleAssignment(
			array(ROLE_ID_SITE_ADMIN),
			array(
				'access',
			)
		);
		$this->addRoleAssignment(
			ROLE_ID_MANAGER,
			array(
				'settings',
				'publication',
				'distribution',
			)
		);
	}


	//
	// Public handler methods
	//
	/**
	 * Route to other settings operations.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function settings($args, $request) {
		$path = array_shift($args);
		switch($path) {
			case 'index':
			case '':
			case 'context':
				$this->context($args, $request);
				break;
			case 'website':
				$this->website($args, $request);
				break;
			case 'workflow':
				$this->workflow($args, $request);
				break;
			case 'distribution':
				$this->distribution($args, $request);
				break;
			case 'access':
				$this->access($args, $request);
				break;
			default:
				$request->getDispatcher()->handle404();
				assert(false);
		}
	}

	/**
	 * Display publication process page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function publication($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$this->setupTemplate($request);
		$templateMgr->display('management/settings/workflow.tpl');
	}

	/**
	 * Display Access and Security page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function access($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$this->setupTemplate($request);
		$templateMgr->display('management/settings/access.tpl');
	}
}

?>
