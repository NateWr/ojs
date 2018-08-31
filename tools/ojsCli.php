<?php

/**
 * @file tools/ojs.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ojscli
 * @ingroup tools
 *
 * @brief CLI tool for common functions in OJS.
 */
define('APP_ROOT', dirname(dirname(__FILE__)));
require(APP_ROOT . '/tools/bootstrap.inc.php');

class OJSCLI extends CommandLineTool {

	/** @param Request The Request object */
	var $request;

	/** @param array Arguments passed with the command */
	var $args = [];

	/**
	 * Constructor.
	 * @param $argv array command-line arguments (see usage)
	 */
	public function __construct($argv = []) {
		$this->request = Application::getRequest();
		$this->request->setDispatcher(Application::getDispatcher());
		$this->args = $argv;
		parent::__construct($argv);
	}

	/**
	 * Parse and execute the import/export task.
	 */
	public function execute() {
		$this->router();
	}

	/**
	 * Route $argss to their appropriate command
	 */
	public function router() {

		if (current($this->args) === 'ojsCli.php') {
			array_shift($this->args);
		}

		if (empty($this->args)) {
			$this->usage();
			exit;
		}

		switch ($command = $this->args[0]) {
			case 'context':
				if (count($this->args) < 2) {
					$this->usage();
					exit;
				}
				switch ($this->args[1]) {
					case 'add':
						$this->addContext();
						exit;
					default:
						$this->usage();
						exit;
				}
				exit;
			default:
				$this->usage();
				exit;
		}
	}

	/**
	 * Print command usage information.
	 */
	public function usage() {
		echo "Command-line tool to carry out actions in OJS\n"
			. "Usage:\n"
			. "\t{$this->scriptName} context add: Create a journal.\n"
			. "\t{$this->scriptName} usage: Display usage information this tool\n";
	}

	/**
	 * Retrieve the properties from a command
	 *
	 * Key/value properties in a command are passed as --key="value"
	 *
	 * @return array Key/value of found properties
	 */
	public function getProps() {
		$props = [];
		foreach ($this->args as $token) {
			$matches = [];
			preg_match('/^--([\._a-zA-Z0-9]*)="?([^"\n]*)"?$/', $token, $matches);
			if (!empty($matches[1]) && !empty($matches[2])) {
				// Properties that expect objects or arrays as values (like
				// multilingual fields) are passed as `name.en_US` and must be expanded
				// into objects/arrays.
				if (strpos($matches[1], '.')) {
					$propParts = explode('.', $matches[1]);
					if (!isset($props[$propParts[0]])) {
						$props[$propParts[0]] = [];
					}
					$props[$propParts[0]][$propParts[1]] = $matches[2];
				} else {
					$props[$matches[1]] = $matches[2];
				}
			}
		}
		return $props;
	}


	/**
	 * Add a context
	 */
	public function addContext() {
		$site = $this->request->getSite();
		$primaryLocale = $site->getPrimaryLocale();
		$allowedLocales = $site->getSupportedLocales();
		$props = $this->getProps();


		$contextService = ServicesContainer::instance()->get('context');
		$errors = $contextService->validate(VALIDATE_ACTION_ADD, $props, $allowedLocales, $primaryLocale);

		if (!empty($errors)) {
			echo "The following validation errors occurred and your request could not be completed:\n";
			print_r($errors);
			exit;
		}

		$context = Application::getContextDAO()->newDataObject();
		$context->_data = $props;
		$context = $contextService->addContext($context, $this->request);
		$contextProps = $contextService->getFullProperties($context, array(
			'request' => $this->request,
		));

		echo "The context, " . $context->getLocalizedName() . ", was successfully created.\n";
		print_r($contextProps);
		exit;
	}
}

$tool = new OJSCLI(isset($argv) ? $argv : []);
$tool->execute();
