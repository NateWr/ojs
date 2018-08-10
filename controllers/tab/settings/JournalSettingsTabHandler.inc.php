<?php

/**
 * @file controllers/tab/settings/JournalSettingsTabHandler.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JournalSettingsTabHandler
 * @ingroup controllers_tab_settings
 *
 * @brief Handle AJAX operations for tabs on Journal page.
 */

import('lib.pkp.controllers.tab.settings.ManagerSettingsTabHandler');

class JournalSettingsTabHandler extends ManagerSettingsTabHandler {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
		$this->setPageTabs(array(
			'masthead' => 'controllers/tab/settings/mastheadForm.tpl',
			'contact' => 'controllers/tab/settings/contactForm.tpl',
			'sections' => 'controllers/tab/settings/journal/sections.tpl',
		));
	}

	/**
	 * @copydoc SettingsTabHandler::showTab()
	 */
	function showTab($args, $request) {

		if ($this->getCurrentTab() === 'masthead') {
			$context = $request->getContext();
			$router = $request->getRouter();
			$locales = $context->getSupportedFormLocales();
			import('lib.pkp.controllers.form.FormHandler');

			$mastheadForm = new FormHandler(
				'contextMasthead',
				'PUT',
				$router->getApiUrl($request, $context->getPath(), 'v1', 'contexts', $context->getId()),
				__('manager.setup.masthead.success'),
				$locales
			);
			$mastheadFormData = $mastheadForm
				->addGroup([
						'id' => 'identity',
						'label' => __('manager.setup.identity'),
					])
				->addField(new FieldText('name', [
						'label' => __('manager.setup.contextName'),
						'size' => 'large',
						'isRequired' => true,
						'isMultilingual' => true,
						'groupId' => 'identity',
						'value' => $context->getData('name'),
					]))
				->addField(new FieldText('acronym', [
						'label' => __('manager.setup.journalInitials'),
						'size' => 'small',
						'isRequired' => true,
						'isMultilingual' => true,
						'groupId' => 'identity',
						'value' => $context->getData('acronym'),
					]))
				->addField(new FieldText('abbreviation', [
						'label' => __('manager.setup.journalAbbreviation'),
						'isMultilingual' => true,
						'groupId' => 'identity',
						'value' => $context->getData('abbreviation'),
					]))
				->addGroup([
						'id' => 'publishing',
						'label' => __('manager.setup.publishing'),
						'description' => __('manager.setup.publishingDescription'),
					])
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
					]))
				->addGroup([
						'id' => 'keyInfo',
						'label' => __('manager.setup.keyInfo'),
						'description' => __('manager.setup.keyInfo.description'),
					])
				->addField(new FieldRichTextarea('description', [
						'label' => __('manager.setup.journalSummary'),
						'isMultilingual' => true,
						'groupId' => 'keyInfo',
						'value' => $context->getData('description'),
					]))
				->addField(new FieldRichTextarea('editorialTeam', [
						'label' => __('manager.setup.editorialTeam'),
						'isMultilingual' => true,
						'groupId' => 'keyInfo',
						'value' => $context->getData('editorialTeam'),
					]))
				->addGroup([
						'id' => 'about',
						'label' => __('common.description'),
						'description' => __('manager.setup.journalAbout.description'),
					])
				->addField(new FieldRichTextarea('about', [
						'label' => __('manager.setup.journalAbout'),
						'isMultilingual' => true,
						'size' => 'large',
						'groupId' => 'about',
						'value' => $context->getData('about'),
					]))
				->getConfig();

			$templateMgr = TemplateManager::getManager($request);
			$templateMgr->assign('mastheadFormData', json_encode($mastheadFormData));
		}

		return parent::showTab($args, $request);
	}

	//
	// Overridden methods from Handler
	//
	/**
	 * @copydoc PKPHandler::initialize()
	 */
	function initialize($request, $args = null) {
		parent::initialize($request, $args);

		// Load grid-specific translations
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_USER);
	}
}

?>
