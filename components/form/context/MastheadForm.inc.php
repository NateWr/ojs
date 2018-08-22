<?php
/**
 * @file controllers/form/context/MastheadForm.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MastheadForm
 * @ingroup classes_controllers_form
 *
 * @brief A factory class for producing form configuration objects ready to be
 *  passed to the UI. It wraps individual form configurations into a single
 *  static function call.
 */
import('lib.pkp.components.form.context.PKPMastheadForm');

class MastheadForm extends PKPMastheadForm {

	/**
   * @copydoc FormComponent::setFields()
	 */
	public function setFields($args) {
		parent::setFields($args);

		$context = $args['context'];

		$this->addField(new FieldText('abbreviation', [
					'label' => __('manager.setup.journalAbbreviation'),
					'isMultilingual' => true,
					'groupId' => 'identity',
					'value' => $context->getData('abbreviation'),
				]))
			->addGroup([
					'id' => 'publishing',
					'label' => __('manager.setup.publishing'),
					'description' => __('manager.setup.publishingDescription'),
				], ['after', 'identity'])
			->addField(new FieldText('publisherInstitution', [
					'label' => __('manager.setup.publisher'),
					'groupId' => 'publishing',
					'value' => $context->getData('publisherInstitution'),
				]))
			->addField(new FieldText('onlineIssn', [
					'label' => __('manager.setup.onlineIssn'),
					'size' => 'small',
					'groupId' => 'publishing',
					'value' => $context->getData('onlineIssn'),
				]))
			->addField(new FieldText('printIssn', [
					'label' => __('manager.setup.printIssn'),
					'size' => 'small',
					'groupId' => 'publishing',
					'value' => $context->getData('printIssn'),
				]));

		return $this;
	}
}
