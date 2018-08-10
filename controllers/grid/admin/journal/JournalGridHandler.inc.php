<?php

/**
 * @file controllers/grid/admin/journal/JournalGridHandler.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JournalGridHandler
 * @ingroup controllers_grid_admin_journal
 *
 * @brief Handle journal grid requests.
 */

import('lib.pkp.controllers.grid.admin.context.ContextGridHandler');

class JournalGridHandler extends ContextGridHandler {

	//
	// Public grid actions.
	//
	/**
	 * Edit an existing journal.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function editContext($args, $request) {
		import('classes.core.ServicesContainer');
		$contextService = ServicesContainer::instance()->get('context');
		$context = null;

		if ($request->getUserVar('rowId')) {
			$context = $contextService->getContext((int) $request->getUserVar('rowId'));
			if (!$context) {
				return new JSONMessage(false);
			}
		}

		$supportedLocales = $request->getSite()->getSupportedLocales();

		$router = $request->getRouter();
		if ($context) {
			$action = $router->getApiUrl($request, $context->getPath(), 'v1', 'contexts', $context->getId());
		} else {
			$action = $router->getApiUrl($request, '*', 'v1', 'contexts');
		}

		import('lib.pkp.controllers.form.FormHandler');
		$contextForm = new FormHandler(
			'editContext',
			$context ? 'PUT' : 'POST',
			$action,
			__($context ? 'admin.contexts.form.edit.success' : 'admin.contexts.form.create.success'),
			$supportedLocales
		);
		$contextFormData = $contextForm
			->addField(new FieldText('name', [
					'label' => __('manager.setup.journalTitle'),
					'isMultilingual' => true,
					'value' => $context ? $context->getData('name') : null,
				]))
			->addField(new FieldRichTextarea('description', [
					'label' => __('admin.journals.journalDescription'),
					'isMultilingual' => true,
					'value' => $context ? $context->getData('description') : null,
				]))
			->addField(new FieldText('path', [
					'label' => __('context.path'),
					'isRequired' => true,
					'value' => $context ? $context->getData('path') : null,
					'prefix' => $request->getBaseUrl() . '/',
					'size' => 'large',
				]))
			->getConfig();

		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('contextFormData', json_encode($contextFormData));


		return new JSONMessage(true, $templateMgr->fetch('admin/editContext.tpl'));
	}

	/**
	 * Delete a journal.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function deleteContext($args, $request) {

		if (!$request->checkCSRF()) {
			return new JSONMessage(false);
		}

		import('classes.core.ServicesContainer');
		$contextService = ServicesContainer::instance()->get('context');

		$context = $contextService->getContext((int) $request->getUserVar('rowId'));

		if (!$context) {
			return new JSONMessage(false);
		}

		$contextService->deleteContext($context);

		return DAO::getDataChangedEvent($journalId);
	}
}

?>
