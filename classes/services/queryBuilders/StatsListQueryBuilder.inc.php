<?php

/**
 * @file classes/services/QueryBuilders/StatsListQueryBuilder.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StatsListQueryBuilder
 * @ingroup query_builders
 *
 * @brief Stats list Query builder
 */

namespace OJS\Services\QueryBuilders;

use PKP\Services\QueryBuilders\BaseQueryBuilder;
use Illuminate\Database\Capsule\Manager as Capsule;

class StatsListQueryBuilder extends BaseQueryBuilder {

	/** @var int Context ID */
	protected $contextId = null;

	/** @var string metric type ojs::counter or omp::counter */
	protected $metricType = null;

	/** @var array columns (aggregation level) selection */
	protected $columns = array();

	/** @var array report-level filter selection */
	protected $filters = array();

	/** @var array order criteria */
	protected $orderBy = array();


	/**
	 * Constructor
	 *
	 * @param $contextId int context ID
	 */
	public function __construct($contextId) {
		parent::__construct();
		$this->contextId = $contextId;
		$this->metricType = $this->getMetricType();
	}

	/**
	 * Set result columns (aggregation level)
	 *
	 * @param $columns array
	 *
	 * @return \OJS\Services\QueryBuilders\StatsListQueryBuilder
	 */
	public function columns($columns) {
		// If the metric column was defined, remove it. We will automatically add it.
		$metricKey = array_search(STATISTICS_METRIC, $columns);
		if ($metricKey !== false) unset($columns[$metricKey]);
		$this->columns = $columns;
		return $this;
	}

	/**
	 * Set result filters
	 *
	 * @param $filters array
	 *
	 * @return \OJS\Services\QueryBuilders\StatsListQueryBuilder
	 */
	public function filters($filters) {
		// Add the metric type as filter.
		$filters[STATISTICS_DIMENSION_METRIC_TYPE] = $this->metricType;
		$this->filters = $filters;
		return $this;
	}

	/**
	 * Set result orderBy array (order column => direction)
	 *
	 * @param $orderBy array
	 *
	 * @return \OJS\Services\QueryBuilders\StatsListQueryBuilder
	 */
	public function orderBy($orderBy) {
		$this->orderBy = $orderBy;
		return $this;
	}

	/**
	 * Execute query builder
	 *
	 * @return object Query object
	 */
	public function get() {
		$q = Capsule::table('metrics');
		if (empty($this->columns)) {
			$q->selectRaw('SUM(metric) AS metric');
		} else {
			$selectedColumns = implode(', ', $this->columns);
			$q->select($this->columns)
				->selectRaw('SUM(metric) AS metric')
				->groupBy($this->columns);
		}

		foreach ($this->filters as $column => $values) {
			// The filter array contains STATISTICS_* constants for the filtered
			// hierarchy aggregation level as keys.
			if ($column === STATISTICS_METRIC) {
				$havingClause = true;
				$whereClause = false;
			} else {
				$havingClause = false;
				$whereClause = true;
			}

			if (is_array($values) && isset($values['from'])) {
				// Range filter: The value is a hashed array with from/to entries.
				if ($whereClause) {
					$q->whereBetween($column, array($values['from'], $values['to']));
				} elseif ($havingClause) {
					$q->havingRaw($column . 'BETWEEN ? AND ?', [$values['from'], $values['to']]);
				}
			} else {
				// Element selection filter: The value is a scalar or an
				// unordered array of one or more hierarchy element IDs.
				if (is_array($values) && count($values) === 1) {
					$values = array_pop($values);
				}
				if (is_scalar($values)) {
					if ($whereClause) {
						$q->where($column, '=', $values);
					} elseif ($havingClause) {
						$q->having($column, '=', $values);
					}
				} else {
					if ($whereClause) {
						$q->whereIn($column, $values);
					} elseif ($havingClause) {
						$valuesString = implode(', ', $values);
						$q->havingRaw($column . ' IN (' . $valuesString .')');
					}
				}
			}
		}

		// Replace the current time constant by time values
		// inside the parameters array.
		$params = $q->getBindings();
		$currentTime = array(
			STATISTICS_YESTERDAY => date('Ymd', strtotime('-1 day', time())),
			STATISTICS_CURRENT_MONTH => date('Ym', time()));
		foreach ($currentTime as $constant => $time) {
			$currentTimeKeys = array_keys($params, $constant);
			foreach ($currentTimeKeys as $key) {
				$params[$key] = $time;
			}
		}
		$q->setBindings($params);

		// Build the order-by clause.
		foreach ($this->orderBy as $orderColumn => $direction) {
			$q->orderBy($orderColumn, $direction);
		}

		// Allow third-party query statements
		\HookRegistry::call('Stats::getStats::queryObject', array(&$q, $this));

		return $q;
	}

	/**
	 * Get the app specific metric type.
	 * @return string
	 */
	protected function getMetricType() {
		$application = \Application::getApplication();
		$applicationName = $application->getName();
		switch ($applicationName) {
			case 'ojs2':
				return OJS_METRIC_TYPE_COUNTER;
				break;
			case 'omp':
				return OMP_METRIC_TYPE_COUNTER;
				break;
			default:
				assert(false);
		}
	}
}
