<?php

/**
 * @defgroup plugins_checks_requireAbstractCheck Require abstract before publication plugin
 */

/**
 * @file plugins/checks/requireAbstractCheck/index.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_checks_requireAbstractCheck
 * @brief Wrapper for a pre-publication check on the abstract.
 *
 */

require_once('RequireAbstractCheckPlugin.inc.php');

return new RequireAbstractCheckPlugin();

?>
