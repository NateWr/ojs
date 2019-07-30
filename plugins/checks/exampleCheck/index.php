<?php

/**
 * @defgroup plugins_checks_exampleCheck Example pre-publication check plugin
 */

/**
 * @file plugins/checks/exampleCheck/index.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_checks_exampleCheck
 * @brief Wrapper for an example pre-publication check
 *
 */

require_once('ExampleCheckPlugin.inc.php');

return new ExampleCheckPlugin();

?>
