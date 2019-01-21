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
	 * Get statistics records of the most used submissions
	 *
	 * @param int $contextId
	 * @param array $args {
	 * 		@option string orderBy
	 * 		@option string orderDirection
	 * 		@option int count
	 * 		@option int offset
	 *		@option string dateRange
	 * }
	 *
	 * @return array
	 */
	public function getSubmissions($contextId, $args = array()) {
		$statsListQB = $this->_buildGetSubmissionsQueryObject($contextId, $args);
		$statsQO = $statsListQB->get();
		/* DEFAULT: SELECT submission_id, SUM(metric) AS metric FROM metrics WHERE context_id = ? AND assoc_type IN (1048585, 515) AND metric_type = 'ojs::counter' GROUP BY submission_id  ORDER BY metric DESC */
		/*
		$file = 'debug.txt';
		$current = file_get_contents($file);
		$current .= print_r("++++++++++++++++++++++++++++\n", true);
		$current .= print_r($statsQO->toSql(), true);
		$current .= print_r("#####\n", true);
		$current .= print_r($statsQO->getBindings(), true);
		$current .= print_r("++++++++++++++++++++++++++++\n", true);
		file_put_contents($file, $current);
		*/

		$range = $this->getRangeByArgs($args);

		$dao = \DAORegistry::getDAO('MetricsDAO');
		$result = $dao->retrieveRange($statsQO->toSql(), $statsQO->getBindings(), $range);
		$records = $result->GetAll();

		return $records;
	}

	/**
	 * @copydoc \PKP\Services\EntityProperties\EntityPropertyInterface::getProperties()
	 */
	public function getProperties($entity, $props, $args = null) {
		\AppLocale::requireComponents(LOCALE_COMPONENT_APP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION);
		$values = array();

		$entityService = null;
		if (is_a($entity, 'Submission')) {
			$props[] = 'submissionSummary';
			$entityService = \ServicesContainer::instance()->get('submission');
			$filterEntityAssocTypeBy = ASSOC_TYPE_SUBMISSION;
			$filterGalleyAssocTypeBy = ASSOC_TYPE_SUBMISSION_FILE;
			$statsListQB = $this->_buildGetSubmissionQueryObject($entity->getContextId(), $entity->getId(), $args['params']);
			$statsQO = $statsListQB->get();
			/*  SELECT month, assoc_type, file_type, SUM(metric) AS metric FROM metrics WHERE submission_id = ? AND assoc_type IN (1048585, 515) AND metric_type = 'ojs::counter' GROUP BY month, assoc_type, file_type, month */
			/*
			$file = 'debug.txt';
			$current = file_get_contents($file);
			$current .= print_r("++++++++++++++++++++++++++++\n", true);
			$current .= print_r($statsQO->toSql(), true);
			$current .= print_r("#####\n", true);
			$current .= print_r($statsQO->getBindings(), true);
			$current .= print_r("++++++++++++++++++++++++++++\n", true);
			file_put_contents($file, $current);
			*/
		} elseif (is_a($entity, 'Issue')) {
			$props[] = 'issueSummary';
			$entityService = \ServicesContainer::instance()->get('issue');
		}

		$request = \Application::getRequest();
		$context = $request->getContext();
		$dispatcher = $request->getDispatcher();

		$dao = \DAORegistry::getDAO('MetricsDAO');
		$result = $dao->retrieve($statsQO->toSql(), $statsQO->getBindings());
		$records = $result->GetAll();

		// Prepare stats by month
		$months = array();
		foreach ($records as $record) {
			if (!in_array($record[STATISTICS_DIMENSION_MONTH], $months)) $months[] = $record[STATISTICS_DIMENSION_MONTH];
		}
		$monthlyStats = array();
		foreach ($months as $month) {
			$total = $abstractViews = $galleyViews = $pdfs = $htmls = $others = 0;
			$monthlyRecords = array_filter($records, function ($record) use ($month) {
				return ($record[STATISTICS_DIMENSION_MONTH] == $month);
			});
			// total
			$total = array_sum(array_map(
				function($record){
					return $record[STATISTICS_METRIC];
				},
				$monthlyRecords
			));
			$monthlyStats[$month]['total'] = $total;
			// abstract views
			$abstractViewsMonthlyRecords = array_filter($monthlyRecords, function ($record) use ($filterEntityAssocTypeBy) {
				return ($record[STATISTICS_DIMENSION_ASSOC_TYPE] == $filterEntityAssocTypeBy);
			});
			$abstractViews = array_sum(array_map(
				function($record){
					return $record[STATISTICS_METRIC];
				},
				$abstractViewsMonthlyRecords
			));
			$monthlyStats[$month]['abstractViews'] = $abstractViews;
			// galley downloads
			$galleyViewsMonthlyRecords = array_filter($monthlyRecords, function ($record) use ($filterGalleyAssocTypeBy) {
				return ($record[STATISTICS_DIMENSION_ASSOC_TYPE] == $filterGalleyAssocTypeBy);
			});
			$galleyViews = array_sum(array_map(
				function($record){
					return $record[STATISTICS_METRIC];
				},
				$galleyViewsMonthlyRecords
			));
			$monthlyStats[$month]['totalGalleyViews'] = $galleyViews;
			// pdf downloads
			$pdfMonthlyRecords = array_filter($monthlyRecords, function ($record) use ($filterGalleyAssocTypeBy) {
				return ($record[STATISTICS_DIMENSION_ASSOC_TYPE] == $filterGalleyAssocTypeBy && $record[STATISTICS_DIMENSION_FILE_TYPE] == STATISTICS_FILE_TYPE_PDF);
			});
			$pdfs = array_sum(array_map(
				function($record){
					return $record[STATISTICS_METRIC];
				},
				$pdfMonthlyRecords
			));
			$monthlyStats[$month]['pdf'] = $pdfs;
			// html downloads
			$htmlMonthlyRecords = array_filter($monthlyRecords, function ($record) use ($filterGalleyAssocTypeBy) {
				return ($record[STATISTICS_DIMENSION_ASSOC_TYPE] == $filterGalleyAssocTypeBy && $record[STATISTICS_DIMENSION_FILE_TYPE] == STATISTICS_FILE_TYPE_HTML);
			});
			$htmls = array_sum(array_map(
				function($record){
					return $record[STATISTICS_METRIC];
				},
				$htmlMonthlyRecords
			));
			$monthlyStats[$month]['html'] = $htmls;
			// other file type downloads
			$otherMonthlyRecords = array_filter($monthlyRecords, function ($record) use ($filterGalleyAssocTypeBy) {
				return ($record[STATISTICS_DIMENSION_ASSOC_TYPE] == $filterGalleyAssocTypeBy && $record[STATISTICS_DIMENSION_FILE_TYPE] == STATISTICS_FILE_TYPE_OTHER);
			});
			$others = array_sum(array_map(
				function($record){
					return $record[STATISTICS_METRIC];
				},
				$otherMonthlyRecords
			));
			$monthlyStats[$month]['other'] = $others;
		}

		foreach ($props as $prop) {
			switch ($prop) {
				case 'total':
					$values[$prop] = array_sum(array_map(
						function($record){
							return $record['total'];
						},
						$monthlyStats
					));
					break;
				case 'abstractViews':
					$values[$prop] = array_sum(array_map(
						function($record){
							return $record['abstractViews'];
						},
						$monthlyStats
					));
					break;
				case 'totalGalleyViews':
					$values[$prop] = array_sum(array_map(
						function($record){
							return $record['totalGalleyViews'];
						},
						$monthlyStats
					));
					break;
				case 'pdf':
					$values[$prop] = array_sum(array_map(
						function($record){
							return $record['pdf'];
						},
						$monthlyStats
					));
					break;
				case 'html':
					$values[$prop] = array_sum(array_map(
						function($record){
							return $record['html'];
						},
						$monthlyStats
					));
					break;
				case 'other':
					$values[$prop] = array_sum(array_map(
						function($record){
							return $record['other'];
						},
						$monthlyStats
					));
					break;
				case 'monthly':
					$values[$prop] = $monthlyStats;
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
				'total', 'abstractViews', 'totalGalleyViews', 'pdf', 'html', 'other', 'monthly',
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
				'total', 'abstractViews', 'totalGalleyViews', 'pdf', 'html', 'other',
		);

		\HookRegistry::call('Stats::getProperties::fullProperties', array(&$props, $entity, $args));

		return $this->getProperties($entity, $props, $args);
	}

	/**
	 * Build the stats query object for getSubmissions requests
	 *
	 * @see self::getSubmissions()
	 * @return object Query object
	 */
	private function _buildGetSubmissionsQueryObject($contextId, $args = array()) {
		$defaultArgs = array(
			'orderBy' => STATISTICS_METRIC,
			'orderDirection' =>  STATISTICS_ORDER_DESC,
			'count' => 30,
			'offset' => 0,
		);
		$args = array_merge($defaultArgs, $args);

		$columns = array(
			STATISTICS_DIMENSION_SUBMISSION_ID,
		);
		$filters = array(
			STATISTICS_DIMENSION_CONTEXT_ID => $contextId,
			STATISTICS_DIMENSION_ASSOC_TYPE => array(ASSOC_TYPE_SUBMISSION, ASSOC_TYPE_SUBMISSION_FILE),
		);
		if (!empty($args['sectionIds'])) {
			$filters[STATISTICS_DIMENSION_PKP_SECTION_ID] = $args['sectionIds'];
		}
		if (!empty($args['submissionIds'])) {
			$filters[STATISTICS_DIMENSION_SUBMISSION_ID] = $args['submissionIds'];
		}
		if (array_key_exists('to', $args) || array_key_exists('from', $args)) {
			if ($args['to'] !== null) {
				$filters[STATISTICS_DIMENSION_DAY]['from'] = $args['from'];
				$filters[STATISTICS_DIMENSION_DAY]['to'] = $args['to'];
			} else {
				$filters[$args['dimension']] = $args['from'];
			}
		}
		$orderBy = array($args['orderBy'] => $args['orderDirection']);

		$statsListQB = new \OJS\Services\QueryBuilders\StatsListQueryBuilder($contextId);
		$statsListQB
			->columns($columns)
			->filters($filters)
			->orderBy($orderBy);

		\HookRegistry::call('Stats::getSubmissions::queryBuilder', array($statsListQB, $contextId, $args));

		return $statsListQB;
	}

	/**
	 * Build the stats query object for getProperties of a Submission requests
	 *
	 * @see self::getProperties()
	 * @return object Query object
	 */
	private function _buildGetSubmissionQueryObject($contextId, $submissionId, $args = array()) {

		$columns = array(STATISTICS_DIMENSION_MONTH, STATISTICS_DIMENSION_ASSOC_TYPE, STATISTICS_DIMENSION_FILE_TYPE);
		$filters = array(
			STATISTICS_DIMENSION_SUBMISSION_ID => $submissionId,
			STATISTICS_DIMENSION_ASSOC_TYPE => array(ASSOC_TYPE_SUBMISSION, ASSOC_TYPE_SUBMISSION_FILE)
		);
		if (array_key_exists('to', $args) || array_key_exists('from', $args)) {
			if ($args['to'] !== null) {
				$filters[STATISTICS_DIMENSION_DAY]['from'] = $args['from'];
				$filters[STATISTICS_DIMENSION_DAY]['to'] = $args['to'];
			} else {
				$filters[$args['dimension']] = $args['from'];
			}
		}
		$orderBy = array(STATISTICS_DIMENSION_MONTH => STATISTICS_ORDER_ASC);

		$statsListQB = new \OJS\Services\QueryBuilders\StatsListQueryBuilder($contextId);
		$statsListQB
			->columns($columns)
			->filters($filters);

		\HookRegistry::call('Stats::getSubmission::queryBuilder', array($statsListQB, $contextId, $submissionId, $args));

		return $statsListQB;
	}


}
