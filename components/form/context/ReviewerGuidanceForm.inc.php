<?php
/**
 * @file controllers/form/context/ReviewerGuidanceForm.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerGuidanceForm
 * @ingroup classes_controllers_form
 *
 * @brief A factory class for producing form configuration objects ready to be
 *  passed to the UI. It wraps individual form configurations into a single
 *  static function call.
 */
import('lib.pkp.components.form.context.PKPReviewGuidanceForm');

class ReviewerGuidanceForm extends PKPReviewGuidanceForm {}
