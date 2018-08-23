<?php
/**
 * @file controllers/form/context/AccessForm.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AccessForm
 * @ingroup classes_controllers_form
 *
 * @brief A preset form for configuring the terms under which a journal will
 *  allow access to its published content.
 */
import('lib.pkp.components.form.FormComponent');

class AccessForm extends FormComponent {

	/**
	 * Constructor
	 *
	 * @param $apiUrl string URL to submit the form to
	 * @param $localeKeys array Allowed locales
	 */
	public function __construct($apiUrl) {
		parent::__construct(
			FORM_ACCESS,
			'PUT',
			$apiUrl,
			__('manager.distribution.publishingMode.success')
		);
	}

	/**
   * @copydoc FormComponent::setFields()
	 */
	public function setFields($args) {
		$context = $args['context'];

		$this->addField(new FieldOptions('publishingMode', [
				'label' => __('manager.distribution.publishingMode'),
				'type' => 'radio',
				'options' => [
					['value' => PUBLISHING_MODE_OPEN, 'label' => __('manager.distribution.publishingMode.openAccess')],
					['value' => PUBLISHING_MODE_SUBSCRIPTION, 'label' => __('manager.distribution.publishingMode.subscription')],
					['value' => PUBLISHING_MODE_NONE, 'label' => __('manager.distribution.publishingMode.none')],
				],
				'value' => (bool) $context->getData('publishingMode'),
			]));

		return $this;
	}
}
