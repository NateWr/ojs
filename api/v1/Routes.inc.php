<?php
/**
 * @file api/v1/contexts/RoutesHandler.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class RoutesHandler
 *
 * @brief Base class to handle API requests.
 */

import('lib.pkp.classes.handler.APIHandler');

class RoutesHandler extends APIHandler {
	/**
	 * @copydoc APIHandler::__construct()
	 */
	public function __construct() {
		parent::__construct();

		$this->_app->group('/{contextPath}/api/{version}', function(\Slim\App $app) {
			$app->get('', '\APP\controllers\TestHandler:root');
			$app->get('/test', '\APP\controllers\TestHandler:test');
			$app->get('/submission/{id}', '\APP\controllers\TestHandler:submission');
			$app->get('/callback', function($request, $response, $args) {
				return $response->withJson('callback', 200);
			});
		});
	}
}
