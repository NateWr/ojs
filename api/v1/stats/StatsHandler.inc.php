<?php

/**
 * @file api/v1/stats/StatsHandler.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StatsHandler
 * @ingroup api_v1_stats
 *
 * @brief Handle API requests for statistics operations.
 *
 */

import('lib.pkp.classes.handler.APIHandler');
import('classes.core.ServicesContainer');

class StatsHandler extends APIHandler {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->_handlerPath = 'stats';
		$roles = array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT, ROLE_ID_REVIEWER, ROLE_ID_AUTHOR);
		$this->_endpoints = array(
			'GET' => array (
				array(
					'pattern' => $this->getEndpointPattern() . '/articles',
					'handler' => array($this, 'getSubmissionList'),
					'roles' => $roles
				),
				array(
					'pattern' => $this->getEndpointPattern() . '/articles/{submissionId}',
					'handler' => array($this, 'getSubmission'),
					'roles' => $roles
				),
				array(
					'pattern' => $this->getEndpointPattern() . '/issues',
					'handler' => array($this, 'getSubmissionList'),
					'roles' => $roles
				),
				array(
					'pattern' => $this->getEndpointPattern() . '/issues/{issueId}',
					'handler' => array($this, 'getSubmission'),
					'roles' => $roles
				),
			),
		);
		parent::__construct();
	}

	//
	// Implement methods from PKPHandler
	//
	function authorize($request, &$args, $roleAssignments) {
		$routeName = null;
		$slimRequest = $this->getSlimRequest();

		import('lib.pkp.classes.security.authorization.ContextAccessPolicy');
		$this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));

		if (!is_null($slimRequest) && ($route = $slimRequest->getAttribute('route'))) {
			$routeName = $route->getName();
		}

		if ($routeName === 'getSubmission') {
			import('lib.pkp.classes.security.authorization.SubmissionAccessPolicy');
			$this->addPolicy(new SubmissionAccessPolicy($request, $args, $roleAssignments));
		}

		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Get total stats and a collection of submissions
	 * @param $slimRequest Request Slim request object
	 * @param $response Response object
	 * @param $args array
	 *
	 * @return Response
	 */
	public function getSubmissionList($slimRequest, $response, $args) {
		$request = Application::getRequest();
		$context = $request->getContext();

		if (!$context) {
			return $response->withStatus(404)->withJsonError('api.submissions.404.resourceNotFound');
		}

		$params = $this->_buildListRequestParams($slimRequest);

		if (array_key_exists('submissionIds', $params) && empty($params['submissionIds'])) {
			$submissionsRecords = array();
		} else {
			$statsService = ServicesContainer::instance()->get('stats');
			$submissionsRecords = $statsService->getOrderedSubmissions($context->getId(), $params);
		}

		$data = $items = array();
		if (!empty($submissionsRecords)) {
			$propertyArgs = array(
				'request' => $request,
				'slimRequest' => $slimRequest,
				'params' => $params
			);
			$totalStatsRecords = $statsService->getTotalStats($context->getId(), $params);
			/*
			$file = 'debug.txt';
			$current = file_get_contents($file);
			$current .= print_r("\n++++ totalStatsRecords ++++\n", true);
			$current .= print_r($totalStatsRecords, true);
			file_put_contents($file, $current);
			*/
			$data = $statsService->getTotalStatsProperties($totalStatsRecords, $propertyArgs);

			$slicedSubmissionsRecords = array_slice($submissionsRecords, isset($params['offset'])?$params['offset']:0, $params['count']);
			foreach ($slicedSubmissionsRecords as $submissionsRecord) {
				$publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
				$submission = $publishedArticleDao->getById($submissionsRecord['submission_id']);
				$items[] = $statsService->getSummaryProperties($submission, $propertyArgs);
			}
		} else {
			$data = array(
				'abstractViews' => 0,
				'totalGalleyViews' => 0,
				'timeSegments' => array()
			);
		}

		$data['itemsMax'] = count($submissionsRecords);
		$data['items'] = $items;

		return $response->withJson($data, 200);
	}

	/**
	 * Get a single submission usage statistics
	 * @param $slimRequest Request Slim request object
	 * @param $response Response object
	 * @param $args array
	 *
	 * @return Response
	 */
	public function getSubmission($slimRequest, $response, $args) {
		$request = Application::getRequest();

		$submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);

		$params = $this->_buildListRequestParams($slimRequest);

		$data = ServicesContainer::instance()
			->get('stats')
			->getFullProperties($submission, array(
				'request' => $request,
				'slimRequest' 	=> $slimRequest,
				'params' => $params
			));

		return $response->withJson($data, 200);
	}

	/**
	 * Convert params passed to the api end point. Coerce type and only return
	 * white-listed params.
	 *
	 * @param $slimRequest Request Slim request object
	 * @return array
	 */
	private function _buildListRequestParams($slimRequest) {
		$request = Application::getRequest();
		$context = $request->getContext();

		// Merge query params over default params
		$defaultParams = array(
			'count' => 30,
			'offset' => 0,
			'timeSegment' => 'monthly'
		);
		$requestParams = array_merge($defaultParams, $slimRequest->getQueryParams());

		$returnParams = array();
		// Process query params to format incoming data as needed
		foreach ($requestParams as $param => $val) {
			switch ($param) {
				case 'orderBy':
					if (in_array($val, array('total'))) {
						$returnParams[$param] = STATISTICS_METRIC;
					}
					break;

				case 'orderDirection':
					$returnParams[$param] = $val === STATISTICS_ORDER_ASC ? $val : STATISTICS_ORDER_DESC;
					break;
				// Enforce a maximum count to prevent the API from crippling the
				// server
				case 'count':
					$returnParams[$param] = min(100, (int) $val);
					break;
				case 'offset':
					$returnParams[$param] = (int) $val;
					break;
				case 'timeSegment':
					if ($val == 'daily') {
						$returnParams['dimension'] = STATISTICS_DIMENSION_DAY;
					} elseif ($val == 'monthly') {
						$returnParams['dimension'] = STATISTICS_DIMENSION_MONTH;
					}
					break;
				case 'dateStart':
					if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $val, $matches) === 1) {
						$returnParams['from'] = $matches[1] . $matches[2] . $matches[3];
					}
					break;
				case 'dateEnd':
					if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $val, $matches) === 1) {
						$returnParams['to'] = $matches[1] . $matches[2] . $matches[3];
					}
					break;
				case 'sectionIds':
					if (is_string($val) && strpos($val, ',') > -1) {
						$val = explode(',', $val);
					} elseif (!is_array($val)) {
						$val = array($val);
					}
					$returnParams['sectionIds'] = array_map('intval', $val);
					break;
				case 'searchPhrase':
					$submissionsParams[$param] = $val;
					$submissionService = ServicesContainer::instance()->get('submission');
					$submissions = $submissionService->getSubmissions($context->getId(), $submissionsParams);
					$returnParams['submissionIds'] = array_map(
						function($submission){
							return $submission->getId();
						},
						$submissions
					);
					break;
			}
		}

		// check date range and time segment parameters
		if (isset($returnParams['from']) && !isset($returnParams['to'])) {
			$returnParams['to'] = date('Ymd', time());
		} elseif (isset($returnParams['to']) && !isset($returnParams['from'])) {
			// TO-DO: chose the start date differently ?
			$returnParams['from'] = '20010101';
		}
		// check if dateEnd is bigger than dateStart
		$fromTimestamp = strtotime($returnParams['from']);
		$toTimestamp = strtotime($returnParams['to']);
		if ($toTimestamp < $fromTimestamp) {
			// error
		} else {
			// check the timeSegmet = daily and if the dateStart is withing the last 90 days
			$lastNinetyDaysTimestamp = strtotime('-90 days');
			if ($returnParams['dimension'] == STATISTICS_DIMENSION_DAY && $fromTimestamp < $lastNinetyDaysTimestamp) {
				// error
			}
		}

		\HookRegistry::call('API::statistics::params', array(&$returnParams, $slimRequest));

		return $returnParams;
	}
}
