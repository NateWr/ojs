<?php
/**
 * @file classes/services/ContextService.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ContextService
 * @ingroup services
 *
 * @brief Extends the base context service class with app-specific
 *  requirements.
 */
namespace OJS\Services;

class ContextService extends \PKP\Services\PKPContextService {
	/** @copydoc PKPContextService::$installSettingsFile */
	var $installSettingsFile = 'registry/journalSettings.xml';

	/** @copydoc PKPContextService::$contextsFileDirName */
	var $contextsFileDirName = 'journals';

	/**
	 * Initialize hooks for extending PKPContextService
	 */
	public function __construct() {
		parent::__construct();

		$this->installFileDirs = array(
			\Config::getVar('files', 'files_dir') . '/%s/%d',
			\Config::getVar('files', 'files_dir'). '/%s/%d/articles',
			\Config::getVar('files', 'files_dir'). '/%s/%d/issues',
			\Config::getVar('files', 'public_files_dir') . '/%s/%d',
		);

		\HookRegistry::register('Context::add', array($this, 'afterAddContext'));
		\HookRegistry::register('Context::delete', array($this, 'afterDeleteContext'));
	}


	/**
	 * Helper function to return the app-specific context list query builder
	 *
	 * @return \OJS\Services\QueryBuilders\ContextListQueryBuilder
	 */
	public function getContextListQueryBuilder() {
		return new \OJS\Services\QueryBuilders\ContextListQueryBuilder();
	}

	/**
	 * Take additional actions after a new context has been added
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option Journal The new context
	 *		@option Request
 	 * ]
	 */
	public function afterAddContext($hookName, $args) {
		$context = $args[0];
		$request = $args[1];

		// Create a default section
		$sectionDao = \DAORegistry::getDAO('SectionDAO'); // constants
		$section = new \Section();
		$section->setTitle(__('section.default.title'), $context->getPrimaryLocale());
		$section->setAbbrev(__('section.default.abbrev'), $context->getPrimaryLocale());
		$section->setMetaIndexed(true);
		$section->setMetaReviewed(true);
		$section->setPolicy(__('section.default.policy'), $context->getPrimaryLocale());
		$section->setEditorRestricted(false);
		$section->setHideTitle(false);

		\ServicesContainer::instance()->get('section')->addSection($section, $context);
	}

	/**
	 * Take additional actions after a context has been deleted
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option Journal The new context
	 *		@option Request
 	 * ]
	 */
	public function afterDeleteContext($hookName, $args) {
		$context = $args[0];

		$sectionDao = \DAORegistry::getDAO('SectionDAO');
		$sectionDao->deleteByJournalId($context->getId());

		$issueDao = \DAORegistry::getDAO('IssueDAO');
		$issueDao->deleteByJournalId($context->getId());

		$subscriptionDao = \DAORegistry::getDAO('IndividualSubscriptionDAO');
		$subscriptionDao->deleteByJournalId($context->getId());
		$subscriptionDao = \DAORegistry::getDAO('InstitutionalSubscriptionDAO');
		$subscriptionDao->deleteByJournalId($context->getId());

		$subscriptionTypeDao = \DAORegistry::getDAO('SubscriptionTypeDAO');
		$subscriptionTypeDao->deleteByJournal($context->getId());

		$articleDao = \DAORegistry::getDAO('ArticleDAO');
		$articleDao->deleteByContextId($context->getId());

		import('classes.file.PublicFileManager');
		$publicFileManager = new \PublicFileManager();
		$publicFileManager->rmtree($publicFileManager->getJournalFilesPath($context->getId()));
	}
}
