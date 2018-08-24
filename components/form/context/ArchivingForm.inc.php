<?php
/**
 * @file controllers/form/context/ArchivingForm.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArchivingForm
 * @ingroup classes_controllers_form
 *
 * @brief A factory class for producing form configuration objects ready to be
 *  passed to the UI. It wraps individual form configurations into a single
 *  static function call.
 */
import('lib.pkp.components.form.FormComponent');

class ArchivingForm extends FormComponent {

	/**
	 * Constructor
	 *
	 * @param $url string URL to submit the form to
	 * @param $localeKeys array Allowed locales
	 */
	public function __construct($url, $localeKeys) {
		parent::__construct(
			FORM_ARCHIVING,
			'PUT',
			$url,
			__('manager.setup.archiving.success'),
			$localeKeys
		);
	}

	/**
   * @copydoc FormComponent::setFields()
	 */
	public function setFields($args) {
		$context = $args['context'];

		$versionDao = DAORegistry::getDAO('VersionDAO');
		$currentPLNVersion = $versionDao->getCurrentVersion('plugins.generic', 'pln', true);
		if (isset($currentPLNVersion)) {
			$isPLNPluginInstalled = true;
		}

		$this->addGroup([
					'id' => 'principal',
					'label' => __('manager.setup.principalContact'),
					'description' => __('manager.setup.principalContactDescription'),
				])
			->addField(new FieldText('contactName', [
					'label' => __('common.name'),
					'isRequired' => true,
					'groupId' => 'principal',
					'value' => $context->getData('contactName'),
				]))
			->addField(new FieldText('contactEmail', [
					'label' => __('user.email'),
					'isRequired' => true,
					'groupId' => 'principal',
					'value' => $context->getData('contactEmail'),
				]))
			->addField(new FieldText('contactPhone', [
					'label' => __('user.phone'),
					'groupId' => 'principal',
					'value' => $context->getData('contactPhone'),
				]))
			->addField(new FieldText('contactAffiliation', [
					'label' => __('user.affiliation'),
					'isMultilingual' => true,
					'groupId' => 'principal',
					'value' => $context->getData('contactAffiliation'),
				]))
			->addField(new FieldTextarea('mailingAddress', [
					'label' => __('common.mailingAddress'),
					'isRequired' => true,
					'size' => 'small',
					'groupId' => 'principal',
					'value' => $context->getData('mailingAddress'),
				]))
			->addGroup([
					'id' => 'technical',
					'label' => __('manager.setup.technicalSupportContact'),
					'description' => __('manager.setup.technicalSupportContactDescription'),
				])
			->addField(new FieldText('supportName', [
					'label' => __('common.name'),
					'isRequired' => true,
					'groupId' => 'technical',
					'value' => $context->getData('supportName'),
				]))
			->addField(new FieldText('supportEmail', [
					'label' => __('user.email'),
					'isRequired' => true,
					'groupId' => 'technical',
					'value' => $context->getData('supportEmail'),
				]))
			->addField(new FieldText('supportPhone', [
					'label' => __('user.phone'),
					'groupId' => 'technical',
					'value' => $context->getData('supportPhone'),
				]));

		return $this;
	}
}
