<?php

/**
 * @defgroup api_v1_announcements User API requests
 */

/**
 * @file api/v1/announcements/index.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup api_v1_announcements
 * @brief Handle requests for user API functions.
 *
 */

import('api.v1.announcements.AnnouncementHandler');
return new AnnouncementHandler();
