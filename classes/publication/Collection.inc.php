<?php
/**
 * @file classes/publication/Collection.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class publication
 *
 * @brief A class that represents a collection of publications
 */

namespace APP\Publication;

use APP\Publication;
use PKP\Context\Context;
use PKP\Publication\Collection as PKPPublicationCollection;
use Request;
use Services;

class Collection extends PKPPublicationCollection
{
    /**
     * Add OJS-specific properties when mapping a publication to the schema
     *
     * @param bool $isAnonymized Whether this publication should be anonymized,
     * 	for example if the user is assigned as a reviewer.
     */
    public function _getAppSchemaProperty(array $prop, Publication $publication, Submission $submission, bool $isAnonymized, Request $request, Context $submissionContext): mixed
    {
        switch ($prop) {
            case 'galleys':
                if ($isAnonymized) {
                    $item[$prop] = [];
                } else {
                    $props = Services::get('schema')->getSummaryProps(SCHEMA_GALLEY);
                    $item[$prop] = $publication->getData('galleys')->mapToSchema($props, $publication, $submission, $submissionContext);
                }
                break;
            default:
                return null;
        }
    }
}
