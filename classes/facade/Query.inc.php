<?php

/**
 * @file clases/facade/Query.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Query
 *
 * @brief This facade provides access to all of the query classes in this application
 */

namespace APP\Facade;

use APP\Context\Query as Context;
use PKP\Facade\Query as PKPQuery;

class Query extends PKPQuery
{
    public static function context(): Context
    {
        return new Context();
    }
}
