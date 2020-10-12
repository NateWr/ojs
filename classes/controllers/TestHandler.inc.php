<?php
namespace APP\controllers;

// use Illuminate\Auth\Access\Gate;
// use Illuminate\Container\Container;
use PKP\security\GateFacade as Gate;
use Psr\Container\ContainerInterface;

class TestHandler {

	protected $container;

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
	}

	public function root($slimRequest, $response, $args) {

		if (Gate::allows('with-role', [ROLE_ID_SUB_EDITOR])) {
			$response->isAuthorized = true;
		}

		return $response->withJson('root', 200);
	}

	public function test($slimRequest, $response, $args) {
		return $response->withJson('test', 200);
	}

	public function submission($slimRequest, $response, $args) {
		$submission = \Services::get('submission')->get($args['id']);

		if (Gate::allows('delete-submission', [$submission])) {
			$response->isAuthorized = true;
		} else {
			return $response->withStatus(403)->withJsonError('api.submissions.403.unpublishedIssues');
		}

		return $response->withJson('submission', 200);
	}
}