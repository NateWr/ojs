<?php

/**
 * @file classes/services/StatsService.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StatsService
 * @ingroup services
 *
 * @brief Helper class that encapsulates statistics business logic
 */

namespace OJS\Services;

use \PKP\Services\EntityProperties\PKPBaseEntityPropertyService;
use \DAORegistry;


class StatsService extends PKPBaseEntityPropertyService {

	/**
	 * Initialize hooks for extending PKPSubmissionService
	 */
	public function __construct() {
		parent::__construct($this);
	}

	/**
	 * @copydoc \PKP\Services\EntityProperties\EntityPropertyInterface::getProperties()
	 */
	public function getProperties($entity, $props, $args = null) {
		\AppLocale::requireComponents(LOCALE_COMPONENT_APP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION);
		$values = array();

		$entityService = $filter = null;
		if (is_a($entity, 'Submission')) {
			$props[] = 'submissionSummary';
			$entityService = \ServicesContainer::instance()->get('submission');
			$filter = array(STATISTICS_DIMENSION_SUBMISSION_ID => $entity->getId());
		} elseif (is_a($entity, 'Issue')) {
			$props[] = 'issueSummary';
			$entityService = \ServicesContainer::instance()->get('issue');
		}

		$request = \Application::getRequest();
		$context = $request->getContext();
		$dispatcher = $request->getDispatcher();

		/*  SELECT assoc_type, file_type, SUM(metric) AS metric FROM metrics WHERE submission_id = '3' AND assoc_type IN (1048585, 515) AND metric_type = 'ojs::counter' GROUP BY assoc_type, file_type */
		$metricsDao = DAORegistry::getDAO('MetricsDAO');
		$metricTypes = array($context->getDefaultMetricType());
		$columns = array(STATISTICS_DIMENSION_ASSOC_TYPE, STATISTICS_DIMENSION_FILE_TYPE);
		$filter[STATISTICS_DIMENSION_ASSOC_TYPE] = array(ASSOC_TYPE_SUBMISSION, ASSOC_TYPE_SUBMISSION_FILE);
		$records = $metricsDao->getMetrics($metricTypes, $columns, $filter);

		foreach ($props as $prop) {
			switch ($prop) {
				case 'total':
					$total = array_sum(array_map(
						function($record){
							return $record[STATISTICS_METRIC];
						},
						$records
					));
					$values[$prop] = $total;
					break;
				case 'views':
					$filterAssocTypeBy = ASSOC_TYPE_SUBMISSION;
					$views = array_filter($records, function ($records) use ($filterAssocTypeBy) {
						return ($records[STATISTICS_DIMENSION_ASSOC_TYPE] == $filterAssocTypeBy);
					});
					// there should only be one record
					$values[$prop] = (int) current($views)[STATISTICS_METRIC];
					break;
				case 'downloads':
					$filterAssocTypeBy = ASSOC_TYPE_SUBMISSION_FILE;
					$downloads = array_filter($records, function ($records) use ($filterAssocTypeBy) {
						return ($records[STATISTICS_DIMENSION_ASSOC_TYPE] == $filterAssocTypeBy);
					});
					$totalDownloads = array_sum(array_map(
						function($download){
							return $download[STATISTICS_METRIC];
						},
						$downloads
					));
					$values[$prop] = $totalDownloads;
					break;
				case 'pdf':
					$filterAssocTypeBy = ASSOC_TYPE_SUBMISSION_FILE;
					$filterFileTypeBy = STATISTICS_FILE_TYPE_PDF;
					$pdfs = array_filter($records, function ($record) use ($filterAssocTypeBy, $filterFileTypeBy) {
						return ($record[STATISTICS_DIMENSION_ASSOC_TYPE] == $filterAssocTypeBy && $record[STATISTICS_DIMENSION_FILE_TYPE] == $filterFileTypeBy);
					});
					$values[$prop] = (int) current($pdfs)[STATISTICS_METRIC];
					break;
				case 'html':
					$filterAssocTypeBy = ASSOC_TYPE_SUBMISSION_FILE;
					$filterFileTypeBy = STATISTICS_FILE_TYPE_HTML;
					$htmls = array_filter($records, function ($record) use ($filterAssocTypeBy, $filterFileTypeBy) {
						return ($record[STATISTICS_DIMENSION_ASSOC_TYPE] == $filterAssocTypeBy && $record[STATISTICS_DIMENSION_FILE_TYPE] == $filterFileTypeBy);
					});
					// there should only be one record
					$values[$prop] = (int) current($htmls)[STATISTICS_METRIC];
					break;
				case 'other':
					$filterAssocTypeBy = ASSOC_TYPE_SUBMISSION_FILE;
					$filterFileTypeBy = STATISTICS_FILE_TYPE_OTHER;
					$others = array_filter($records, function ($record) use ($filterAssocTypeBy, $filterFileTypeBy) {
						return ($record[STATISTICS_DIMENSION_ASSOC_TYPE] == $filterAssocTypeBy && $record[STATISTICS_DIMENSION_FILE_TYPE] == $filterFileTypeBy);
					});
					// there should only be one record
					$values[$prop] = (int) current($others)[STATISTICS_METRIC];
					break;
				case 'submissionSummary':
				case 'issueSummary':
					assert($entityService);
					$values['submission'][] = $entityService->getSummaryProperties($entity, $args);
					break;
			}
		}

		\HookRegistry::call('Stats::getProperties::values', array(&$values, $entity, $props, $args));

		return $values;
	}

	/**
	 * @copydoc \PKP\Services\EntityProperties\EntityPropertyInterface::getSummaryProperties()
	 */
	public function getSummaryProperties($entity, $args = null) {
		$request = $args['request'];
		$context = $request->getContext();
		$currentUser = $request->getUser();

		$props = array (
				'total', 'views', 'downloads', 'pdf', 'html', 'other',
		);

		\HookRegistry::call('Stats::getProperties::summaryProperties', array(&$props, $entity, $args));

		return $this->getProperties($entity, $props, $args);
	}

	/**
	 * @copydoc \PKP\Services\EntityProperties\EntityPropertyInterface::getFullProperties()
	 */
	public function getFullProperties($entity, $args = null) {
		$request = $args['request'];
		$context = $request->getContext();
		$currentUser = $request->getUser();

		$props = array (
				'total', 'views', 'downloads', 'pdf', 'html', 'other',
		);

		\HookRegistry::call('Stats::getProperties::fullProperties', array(&$props, $entity, $args));

		return $this->getProperties($entity, $props, $args);
	}


}
