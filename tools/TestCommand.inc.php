<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
	// the name of the command (the part after "bin/console")
	protected static $defaultName = 'api';

	protected function configure()
	{
		$this
			->setDescription('Use this command to call any API endpoint.')
			->setHelp("To call the /publicknowledge/api/v1/contexts API endpoint run:\n\n<comment>$</comment> php tools/cli.php api publicknowledge contexts\n");

		$this
			->addArgument('contextPath', InputArgument::REQUIRED, 'The urlPath for the context you wish to interact with. Example: publicknowledge')
			->addArgument('endpoint', InputArgument::REQUIRED, 'The endpoint for the API call you wish to make. Example: contexts');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{

		require(dirname(__FILE__) . '/bootstrap.inc.php');

		$urlPath = '/' . $input->getArgument('contextPath') . '/api/v1/' . $input->getArgument('endpoint');

		// OJS routing depends on PATH_INFO
		$_SERVER['PATH_INFO'] = $urlPath;
		// Slim depends on REQUEST_URI
		// but this is handled by https://github.com/asmecher/pkp-lib/commit/6bbcb58e8af061e4e4eee838afacd9cf4b751dc8
		// $_SERVER['REQUEST_URI'] = $urlPath;
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
		$endpointParts = explode('/', $input->getArgument('endpoint'));
		$sourceFile = sprintf('api/v1/%s/index.php', reset($endpointParts));
		if (!file_exists($sourceFile)) {
			$output->writeln('<error>The route you specified is not supported.</error>');
			exit;
		}
		$handler = require ('./' . $sourceFile);
		// import('api.v1.contexts.ContextHandler');
		// $handler = new ContextHandler();

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
		$uri = \Slim\Http\Uri::createFromString($urlPath);
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
		$contexts = json_decode($result);

		$output->writeln(json_encode($contexts, JSON_PRETTY_PRINT));
		$output->writeln('<info>Hey there. Your request was passed to the following URL path: ' . $urlPath . '</info>');
	}
}
