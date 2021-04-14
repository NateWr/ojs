<?php

/**
 * @file clases/facade/Command.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Command
 *
 * @brief This facade provides access to all the command classes for this application
 */

namespace APP\Facade;

use PKP\Announcement\Command as Announcement;

class Command
{
    public static function announcement(): Announcement
    {
        return new Announcement();
    }
}
