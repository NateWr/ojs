<?php
/**
 * @file classes/security/authorization/OjsPublishedSubmissionRequiredPolicy.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class OjsPublishedSubmissionRequiredPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Policy that ensures that the request contains a valid submission
 */

import('lib.pkp.classes.security.authorization.DataObjectRequiredPolicy');

class OjsPublishedSubmissionRequiredPolicy extends DataObjectRequiredPolicy {

	/** @var Context The request context */
	public $context;

	/** @var string|int The requested submission ID or URL path */
	public $urlPath = '';

	/** @var int The requested publication ID */
	public $publicationId = null;

	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $urlPath string|int The requested submission ID or URL path
	 * @param $context Context The context for this submission
	 * @param $publicationId int The requested publication ID
	 */
	function __construct($request, $urlPath, $context, $publicationId = null) {
		parent::__construct($request, $args = [], '', '');
		$this->context = $context;
		$this->urlPath = $urlPath;
		$this->publicationId = (int) $publicationId;
	}

	//
	// Implement template methods from AuthorizationPolicy
	//
	/**
	 * @see DataObjectRequiredPolicy::dataObjectEffect()
	 */
	function dataObjectEffect() {

		if (!$this->urlPath || !$this->context) {
			return AUTHORIZATION_DENY;
		}

		$submission = Services::get('submission')->getByUrlPath($this->urlPath, $this->context->getId());
		if (!$submission && ctype_digit((string) $this->urlPath)) {
			$submission = Services::get('submission')->get($this->urlPath);
		}

		if (!$submission
				|| $submission->getData('contextId') !== $this->context->getId()
				|| $submission->getData('status') !== STATUS_PUBLISHED) {
			return AUTHORIZATION_DENY;
		}

		if ($this->publicationId) {
			$publication = null;
			foreach ($submission->getData('publications') as $iPublication) {
				if ($iPublication->getId() === $this->publicationId) {
					$publication = $iPublication;
					break;
				}
			}

			if (empty($publication) || $publication->getData('status') !== STATUS_PUBLISHED) {
				return AUTHORIZATION_DENY;
			}
		}

		$this->addAuthorizedContextObject(ASSOC_TYPE_SUBMISSION, $submission);

		return AUTHORIZATION_PERMIT;
	}
}


