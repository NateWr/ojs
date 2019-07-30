<?php

/**
 * @defgroup plugins_blocks_exampleCheck Make a Submission block plugin
 */

/**
 * @file plugins/blocks/exampleCheck/index.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_blocks_exampleCheck
 * @brief Wrapper for "Make a Submission" block plugin.
 *
 */

require_once('ExampleCheckPlugin.inc.php');

return new ExampleCheckPlugin();

?>
