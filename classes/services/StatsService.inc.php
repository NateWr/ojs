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
	 * Constructor
	 */
	public function __construct() {
		parent::__construct($this);
	}

	/**
	 * Get statistics records of the submissions (filtered by request parameters),
	 * ordered DESC by total stats numbers of their abstract and galley views.
	 *
	 * @param int $contextId
	 * @param array $args {
	 * 		@option string orderBy
	 * 		@option string orderDirection
	 * 		@option int count
	 * 		@option int offset
	 *		@option string dimension
	 *		@option string from
	 *		@option string to
	 *		@option array sectionIds
	 *		@option array submissionIds
	 * }
	 *
	 * @return array
	 */
	public function getOrderedSubmissions($contextId, $args = array()) {
		$statsListQB = $this->_buildGetOrderedSubmissionsQueryObject($contextId, $args);
		$statsQO = $statsListQB->get();
		/* default: SELECT submission_id, SUM(metric) AS metric FROM metrics WHERE context_id = ? AND assoc_type IN (1048585, 515) AND metric_type = 'ojs::counter' GROUP BY submission_id  ORDER BY metric DESC */
		/* with all filters: SELECT submission_id, SUM(metric) AS metric FROM metrics WHERE context_id = ? AND assoc_type IN (1048585, 515) AND metric_type = 'ojs::counter' AND pkp_section_id = ? AND day BETWEEN ? AND ? AND submission_id = ? GROUP BY submission_id  ORDER BY metric DESC */

		$dao = \DAORegistry::getDAO('MetricsDAO');
		$result = $dao->retrieve($statsQO->toSql(), $statsQO->getBindings());
		$records = $result->GetAll();

		return $records;
	}

	/**
	 * Get statistics records of the submissions (filtered by request parameters),
	 * containing given time segments, abstractViews and totalGalleyViews,
	 * ordered DESC by the time segments.
	 *
	 * @param int $contextId
	 * @param array $args {
	 * 		@option string orderBy
	 * 		@option string orderDirection
	 * 		@option int count
	 * 		@option int offset
	 *		@option string dimension
	 *		@option string from
	 *		@option string to
	 *		@option array sectionIds
	 *		@option array submissionIds
	 * }
	 *
	 * @return array
	 */
	public function getTotalStats($contextId, $args = array()) {
		$statsListQB = $this->_buildGetTotalStatsQueryObject($contextId, $args);
		$statsQO = $statsListQB->get();
		/* default monthly: SELECT month, assoc_type, SUM(metric) AS metric FROM metrics WHERE context_id = ? AND assoc_type IN (1048585, 515) AND metric_type = 'ojs::counter' GROUP BY month, assoc_type ORDER BY month DESC */
		/* with filters monthly: SELECT month, assoc_type, SUM(metric) AS metric FROM metrics WHERE context_id = ? AND assoc_type IN (1048585, 515) AND metric_type = 'ojs::counter' AND pkp_section_id = ? AND day BETWEEN ? AND ? AND submission_id = ? GROUP BY month, assoc_type ORDER BY month DESC */

		$dao = \DAORegistry::getDAO('MetricsDAO');
		$result = $dao->retrieve($statsQO->toSql(), $statsQO->getBindings());
		$records = $result->GetAll();

		return $records;
	}

	/**
	 * @see \PKP\Services\EntityProperties\EntityPropertyInterface::getProperties()
	 *
	 * @param $entity Submission
	 * @param $props array
	 * @param $args array
	 *		$args['request'] PKPRequest Required
	 *		$args['slimRequest'] SlimRequest
	 *		$args['params] array of validated request parameters
	 *
	 * @return array
	 */
	public function getProperties($entity, $props, $args = null) {
		$entityService = null;
		if (is_a($entity, 'Submission')) {
			$entityService = \ServicesContainer::instance()->get('submission');
			$entityProperty = 'submission';
			$params = array(
				'entityAssocType' => ASSOC_TYPE_SUBMISSION,
				'galleyAssocType' => ASSOC_TYPE_SUBMISSION_FILE,
			);
			$statsListQB = $this->_buildGetSubmissionQueryObject($entity->getContextId(), $entity->getId(), $args['params']);
			$statsQO = $statsListQB->get();
			/* default monthly: SELECT month, assoc_type, file_type, SUM(metric) AS metric FROM metrics WHERE submission_id = ? AND assoc_type IN (1048585, 515) AND metric_type = 'ojs::counter' GROUP BY month, assoc_type, file_type, month */
			/* with filters monthly: SELECT month, assoc_type, file_type, SUM(metric) AS metric FROM metrics WHERE submission_id = ? AND assoc_type IN (1048585, 515) AND metric_type = 'ojs::counter' AND day BETWEEN ? AND ? GROUP BY month, assoc_type, file_type, month */
		}

		$dao = \DAORegistry::getDAO('MetricsDAO');
		$result = $dao->retrieve($statsQO->toSql(), $statsQO->getBindings());
		$records = $result->GetAll();

		$values = $this->_getValues($records, $params, $props, $args);

		if ($entityService) {
			$values[$entityProperty][] = $entityService->getSummaryProperties($entity, $args);
		}

		\HookRegistry::call('Stats::getProperties::values', array(&$values, $entity, $props, $args));

		return $values;
	}

	/**
	 * @see \PKP\Services\EntityProperties\EntityPropertyInterface::getSummaryProperties()
	 *
	 * @param $entity Submission
	 * @param $args array
	 *		$args['request'] PKPRequest Required
	 *		$args['slimRequest'] SlimRequest
	 *		$args['params] array of validated request parameters
	 *
	 * @return array
	 */
	public function getSummaryProperties($entity, $args = null) {
		$props = array (
			'total', 'abstractViews', 'totalGalleyViews', 'pdf', 'html', 'other',
		);

		\HookRegistry::call('Stats::getProperties::summaryProperties', array(&$props, $entity, $args));

		return $this->getProperties($entity, $props, $args);
	}

	/**
	 * @see \PKP\Services\EntityProperties\EntityPropertyInterface::getFullProperties()
	 * @param $entity Submission
	 * @param $args array
	 *		$args['request'] PKPRequest Required
	 *		$args['slimRequest'] SlimRequest
	 *		$args['params] array of validated request parameters
	 * @return array
	 */
	public function getFullProperties($entity, $args = null) {
		$props = array (
			'total', 'abstractViews', 'totalGalleyViews', 'pdf', 'html', 'other',
		);

		\HookRegistry::call('Stats::getProperties::fullProperties', array(&$props, $entity, $args));

		return $this->getProperties($entity, $props, $args);
	}

	/**
	 * Get properties for the total stats
	 *
	 * @param $records array
	 * @param $args array
	 *		$args['request'] PKPRequest Required
	 *		$args['slimRequest'] SlimRequest
	 *		$args['params] array of validated request parameters
	 *
	 * @return array
	 */
	public function getTotalStatsProperties($records, $args = null) {
		$props = array (
			'total', 'abstractViews', 'totalGalleyViews',
		);

		\HookRegistry::call('Stats::getProperties::totalStatsProperties', array(&$props, $records, $args));

		$params = array(
			'entityAssocType' => ASSOC_TYPE_SUBMISSION,
			'galleyAssocType' => ASSOC_TYPE_SUBMISSION_FILE,
		);
		return $this->_getValues($records, $params, $props, $args);
	}

	/**
	 * Build the stats query object for getOrderedSubmissions requests
	 *
	 * @see self::getOrderedSubmissions()
	 *
	 * @return object Query object
	 */
	private function _buildGetOrderedSubmissionsQueryObject($contextId, $args = array()) {
		$defaultArgs = array(
			'orderBy' => STATISTICS_METRIC,
			'orderDirection' =>  STATISTICS_ORDER_DESC,
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
		if (isset($args['from']) && isset($args['to'])) {
			$filters[STATISTICS_DIMENSION_DAY]['from'] = $args['from'];
			$filters[STATISTICS_DIMENSION_DAY]['to'] = $args['to'];
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
	 * Build the stats query object for getTotalStats requests
	 *
	 * @see self::getTotalStats()
	 *
	 * @return object Query object
	 */
	private function _buildGetTotalStatsQueryObject($contextId, $args = array()) {
		$columns = array($args['dimension'], STATISTICS_DIMENSION_ASSOC_TYPE);
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
		if (isset($args['from']) && isset($args['to'])) {
			$filters[STATISTICS_DIMENSION_DAY]['from'] = $args['from'];
			$filters[STATISTICS_DIMENSION_DAY]['to'] = $args['to'];
		}

		$orderBy = array($args['dimension'] => STATISTICS_ORDER_DESC);

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
	 *
	 * @return object Query object
	 */
	private function _buildGetSubmissionQueryObject($contextId, $submissionId, $args = array()) {
		$columns = array($args['dimension'], STATISTICS_DIMENSION_ASSOC_TYPE, STATISTICS_DIMENSION_FILE_TYPE);
		$filters = array(
			STATISTICS_DIMENSION_SUBMISSION_ID => $submissionId,
			STATISTICS_DIMENSION_ASSOC_TYPE => array(ASSOC_TYPE_SUBMISSION, ASSOC_TYPE_SUBMISSION_FILE)
		);
		if (isset($args['from']) && isset($args['to'])) {
			$filters[STATISTICS_DIMENSION_DAY]['from'] = $args['from'];
			$filters[STATISTICS_DIMENSION_DAY]['to'] = $args['to'];
		}

		$orderBy = array($args['dimension'] => STATISTICS_ORDER_ASC);

		$statsListQB = new \OJS\Services\QueryBuilders\StatsListQueryBuilder($contextId);
		$statsListQB
			->columns($columns)
			->filters($filters);

		\HookRegistry::call('Stats::getSubmission::queryBuilder', array($statsListQB, $contextId, $submissionId, $args));

		return $statsListQB;
	}

	/**
	 * Returns values given a list of properties of the stats record
	 *
	 * @param $records array
	 * @param $params array
	 * 		@option int entityAssocType
	 * 		@otion int galleyAssocType
	 * @param $props array
	 * @param $args array
	 *		$args['request'] PKPRequest Required
	 *		$args['slimRequest'] SlimRequest
	 *		$args['params] array of validated request parameters
	 *
	 * @return array
	 */
	private function _getValues($records, $params, $props, $args = null) {
		$values = array();
		$entityAssocType = $params['entityAssocType'];
		$galleyAssocType = $params['galleyAssocType'];
		$dimension = $args['params']['dimension']; // 'month' or 'day'

		// Prepare stats by month
		$dimensionValues = array(); // existing months or days
		foreach ($records as $record) {
			if (!in_array($record[$dimension], $dimensionValues)) $dimensionValues[] = $record[$dimension];
		}

		// Get stats for all existing months or days, and
		// prepare the timeSegments return values (date, dateLabel and dimensionStats)
		$dimensionStats = $timeSegments = array();
		foreach ($dimensionValues as $dimensionValue) {
			$total = $abstractViews = $galleyViews = $pdfs = $htmls = $others = 0;
			$monthlyRecords = array_filter($records, function ($record) use ($dimensionValue, $dimension) {
				return ($record[$dimension] == $dimensionValue);
			});
			// total
			if (in_array('total', $props)) {
				$total = array_sum(array_map(
					function($record){
						return $record[STATISTICS_METRIC];
					},
					$monthlyRecords
				));
				$dimensionStats[$dimensionValue]['total'] = $total;
			}
			// abstract views
			if (in_array('abstractViews', $props)) {
				$abstractViewsMonthlyRecords = array_filter($monthlyRecords, function ($record) use ($entityAssocType) {
					return ($record[STATISTICS_DIMENSION_ASSOC_TYPE] == $entityAssocType);
				});
				$abstractViews = array_sum(array_map(
					function($record){
						return $record[STATISTICS_METRIC];
					},
					$abstractViewsMonthlyRecords
				));
				$dimensionStats[$dimensionValue]['abstractViews'] = $abstractViews;
			}
			// galley downloads
			if (in_array('totalGalleyViews', $props)) {
				$galleyViewsMonthlyRecords = array_filter($monthlyRecords, function ($record) use ($galleyAssocType) {
					return ($record[STATISTICS_DIMENSION_ASSOC_TYPE] == $galleyAssocType);
				});
				$galleyViews = array_sum(array_map(
					function($record){
						return $record[STATISTICS_METRIC];
					},
					$galleyViewsMonthlyRecords
				));
				$dimensionStats[$dimensionValue]['totalGalleyViews'] = $galleyViews;
			}
			// pdf downloads
			if (in_array('pdf', $props)) {
				assert(array_key_exists(STATISTICS_DIMENSION_FILE_TYPE, $record));
				$pdfMonthlyRecords = array_filter($monthlyRecords, function ($record) use ($galleyAssocType) {
					return ($record[STATISTICS_DIMENSION_ASSOC_TYPE] == $galleyAssocType && $record[STATISTICS_DIMENSION_FILE_TYPE] == STATISTICS_FILE_TYPE_PDF);
				});
				$pdfs = array_sum(array_map(
					function($record){
						return $record[STATISTICS_METRIC];
					},
					$pdfMonthlyRecords
				));
				$dimensionStats[$dimensionValue]['pdf'] = $pdfs;
			}
			// html downloads
			if (in_array('html', $props)) {
				assert(array_key_exists(STATISTICS_DIMENSION_FILE_TYPE, $record));
				$htmlMonthlyRecords = array_filter($monthlyRecords, function ($record) use ($galleyAssocType) {
					return ($record[STATISTICS_DIMENSION_ASSOC_TYPE] == $galleyAssocType && $record[STATISTICS_DIMENSION_FILE_TYPE] == STATISTICS_FILE_TYPE_HTML);
				});
				$htmls = array_sum(array_map(
					function($record){
						return $record[STATISTICS_METRIC];
					},
					$htmlMonthlyRecords
				));
				$dimensionStats[$dimensionValue]['html'] = $htmls;
			}
			// other file type downloads
			if (in_array('other', $props)) {
				assert(array_key_exists(STATISTICS_DIMENSION_FILE_TYPE, $record));
				$otherMonthlyRecords = array_filter($monthlyRecords, function ($record) use ($galleyAssocType) {
					return ($record[STATISTICS_DIMENSION_ASSOC_TYPE] == $galleyAssocType && $record[STATISTICS_DIMENSION_FILE_TYPE] == STATISTICS_FILE_TYPE_OTHER);
				});
				$others = array_sum(array_map(
					function($record){
						return $record[STATISTICS_METRIC];
					},
					$otherMonthlyRecords
				));
				$dimensionStats[$dimensionValue]['other'] = $others;
			}
			// date label
			if ($dimension == STATISTICS_DIMENSION_MONTH) {
				$dateLabel = strftime('%B %Y', strtotime($dimensionValue.'01'));
			} elseif ($dimension == STATISTICS_DIMENSION_DAY) {
				$dateLabel = strftime(\Config::getVar('general', 'date_format_long'), strtotime($dimensionValue));
			}
			// time segments values to be returned
			$timeSegments[] = array_merge(array(
					'date' => $dimensionValue,
					'dateLabel' => $dateLabel
				),
				$dimensionStats[$dimensionValue]
			);
		}

		foreach ($props as $prop) {
			switch ($prop) {
				case 'total':
					$values[$prop] = array_sum(array_map(
						function($record){
							return $record['total'];
						},
						$dimensionStats
					));
					break;
				case 'abstractViews':
					$values[$prop] = array_sum(array_map(
						function($record){
							return $record['abstractViews'];
						},
						$dimensionStats
					));
					break;
				case 'totalGalleyViews':
					$values[$prop] = array_sum(array_map(
						function($record){
							return $record['totalGalleyViews'];
						},
						$dimensionStats
					));
					break;
				case 'pdf':
					$values[$prop] = array_sum(array_map(
						function($record){
							return $record['pdf'];
						},
						$dimensionStats
					));
					break;
				case 'html':
					$values[$prop] = array_sum(array_map(
						function($record){
							return $record['html'];
						},
						$dimensionStats
					));
					break;
				case 'other':
					$values[$prop] = array_sum(array_map(
						function($record){
							return $record['other'];
						},
						$dimensionStats
					));
					break;
			}
		}

		$values['timeSegments'] = $timeSegments;

		return $values;
	}

}
