<?php

/**
 * @defgroup plugins_checks_requireKeywordsCheck Require keywords before publication plugin
 */

/**
 * @file plugins/checks/requireKeywordsCheck/index.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_checks_requireKeywordsCheck
 * @brief Wrapper for a pre-publication check on the keywords.
 *
 */

require_once('RequireKeywordsCheckPlugin.inc.php');

return new RequireKeywordsCheckPlugin();

?>
