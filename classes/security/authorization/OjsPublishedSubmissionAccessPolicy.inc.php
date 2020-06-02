<?php
/**
 * @file classes/security/authorization/OjsPublishedSubmissionAccessPolicy.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class OjsPublishedSubmissionAccessPolicy
 * @ingroup security_authorization
 *
 * @brief Control access to a published submission
 */

import('lib.pkp.classes.security.authorization.internal.ContextPolicy');

class OjsPublishedSubmissionAccessPolicy extends ContextPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $urlPath int The requested submission ID or URL path
	 * @param $context Context The context for this submission
	 * @param $isPreview boolean Whether the user has requested a preview of an unpublished submission
	 */
	function __construct($request, $urlPath, $context, $isPreview = false) {
		parent::__construct($request);

		if (!$isPreview) {
			import('classes.security.authorization.OjsPublishedSubmissionRequiredPolicy');
			$this->addPolicy(new OjsPublishedSubmissionRequiredPolicy($request, $urlPath, $context));
		} else {

			import('lib.pkp.classes.security.authorization.internal.SubmissionRequiredPolicy');
			$this->addPolicy(new SubmissionRequiredPolicy($request, $args, $submissionParameterName));


			$submissionAccessPolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);

			import('lib.pkp.classes.security.authorization.internal.UserAccessibleWorkflowStageRequiredPolicy');
			$subEditorSubmissionAccessPolicy->addPolicy(new UserAccessibleWorkflowStageRequiredPolicy($request));

			// OR is manager and not assigned

		}

	}
}


