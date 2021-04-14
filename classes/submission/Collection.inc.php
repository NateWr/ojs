<?php
/**
 * @file classes/submission/Collection.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class submission
 *
 * @brief A class that represents a collection of submissions
 */

namespace APP\Submission;

use APP\Facade\Query;
use PKP\Context\Context;
use PKP\Submission\Collection as PKPSubmissionCollection;
use Request;

class Collection extends PKPSubmissionCollection
{
    /**
     * Add OJS-specific properties when mapping a submission to the schema
     */
    protected function _getAppSchemaProperty(array $prop, Submission $submission, Request $request, Context $context): mixed
    {
        switch ($prop) {
            case 'urlPublished':
                return Query::submission()->getUrlPublished($request, $context->getData('urlPath'), $submission->getBestId());
                break;
            default:
                return null;
        }
    }
}
