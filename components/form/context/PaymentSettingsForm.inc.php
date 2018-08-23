<?php
/**
 * @file controllers/form/context/PaymentSettingsForm.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PaymentSettingsForm
 * @ingroup classes_controllers_form
 *
 * @brief A preset form for configuring the general payment settings.
 */
import('lib.pkp.components.form.FormComponent');

class PaymentSettingsForm extends FormComponent {

	/**
	 * Constructor
	 *
	 * @param $apiUrl string URL to submit the form to
	 * @param $localeKeys array Allowed locales
	 */
	public function __construct($apiUrl) {
		parent::__construct(
			FORM_PAYMENT_SETTINGS,
			'PUT',
			$apiUrl,
			__('manager.payment.success'),
			$localeKeys
		);
	}

	/**
   * @copydoc FormComponent::setFields()
	 */
	public function setFields($args) {
		$context = $args['context'];

		$currencyDao = DAORegistry::getDAO('CurrencyDAO');
		$currencies = [];
		foreach ($currencyDao->getCurrencies() as $currency) {
			$currencies[] = [
				'value' => $currency->getCodeAlpha(),
				'label' => $currency->getName(),
			];
		}

		// Ensure payment method plugins can hook in
		PluginRegistry::loadCategory('paymethod', true);

		$this->addGroup([
				'id' => 'setup',
				'label' => __('navigation.setup'),
			])
			->addField(new FieldOptions('paymentsEnabled', [
				'label' => __('common.enable'),
				'options' => [
					['value' => true, 'label' => __('manager.payment.options.enablePayments')]
				],
				'value' => (bool) $context->getData('paymentsEnabled'),
				'groupId' => 'setup',
			]))
			->addField(new FieldSelect('currency', [
				'label' => __('manager.payment.currency'),
				'options' => $currencies,
				'showWhen' => 'paymentsEnabled',
				'value' => $context->getData('currency'),
				'groupId' => 'setup',
			]));

		return $this;
	}
}
