<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
	// the name of the command (the part after "bin/console")
	protected static $defaultName = 'app:test-command';

	protected function configure()
	{
		$this
			->setDescription('This is my test command. Pass a URL path like {contextPath}/api/v1/contexts/1')
			->setHelp('This command allows you to test the Symphony CLI app.');

		$this->addArgument('path', InputArgument::REQUIRED, 'API path');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{

		require(dirname(__FILE__) . '/bootstrap.inc.php');

		// OJS routing depends on PATH_INFO
		$_SERVER['PATH_INFO'] = $input->getArgument('path');
		// Slim depends on REQUEST_URI
		// but this is handled by https://github.com/asmecher/pkp-lib/commit/6bbcb58e8af061e4e4eee838afacd9cf4b751dc8
		// $_SERVER['REQUEST_URI'] = $input->getArgument('path');
		$request = Application::get()->getRequest();

		// Minimally need an interrelated router, dispatcher and request
		import('lib.pkp.classes.core.APIRouter');
		$router = new APIRouter();
		$router->setApplication(Application::get());
		$dispatcher = Application::get()->getDispatcher();
		$request->setDispatcher($dispatcher);
		$request->setRouter($router);
		$request->_protocol = 'https';

		// Initialize the locale and load generic plugins.
		AppLocale::initialize($request);
		PluginRegistry::loadCategory('generic');

		// Should get handler from command and route to
		// api/v1/{slug}/index.php
		import('api.v1.contexts.ContextHandler');
		$handler = new ContextHandler();

		$userId = 1; // should be admin. todo: get the actual admin
		// Set up the user
		$user = Services::get('user')->get($userId); // should get actual admin
		Registry::set('user', $user);

		// Set up the session
		$session = SessionManager::getManager()->sessionDao->newDataObject();
		$session->setId(1);
		$session->setUserId($userId);
		$session->setIpAddress(123);
		$session->setUserAgent('');
		$session->setSecondsCreated(time());
		$session->setSecondsLastUsed(time());
		$session->setDomain('');
		$session->setSessionData('');
		SessionManager::getManager()->userSession = $session;


		import('lib.pkp.classes.security.authorization.UserRolesRequiredPolicy');
		$handler->addPolicy(new UserRolesRequiredPolicy($request));

		// Set up the API handler
		$router->setHandler($handler);
		$request->setRouter($router);

		// Fake the request object
		$method = 'GET';
		$uri = \Slim\Http\Uri::createFromString($input->getArgument('path'));
		$handler->getApp()->add(function($request, $response, $next) use ($method, $uri) {
			$request = $request->withMethod($method);
			$request = $request->withUri($uri);
			// if there were to be a POST body
			// $request = $request->write(json_encode(['key' => 'val']));
			// $request->getBody()->rewind();

			return $next($request, $response);
		});

		// Run the route
		ob_start();
		$handler->getApp()->run();
		$result = ob_get_contents();
		ob_end_clean();
		$json = json_decode($result);

		$output->writeln(json_encode($json, JSON_PRETTY_PRINT));
		$output->writeln('<info>Hey there. You passed the following URL path: ' . $input->getArgument('path') . '</info>');
	}
}
