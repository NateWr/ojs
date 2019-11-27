<?php
require __DIR__ . '/../lib/pkp/lib/vendor/autoload.php';

use Symfony\Component\Console\Application as ConsoleApplication;

$cli = new ConsoleApplication();

// ... register commands
require(__DIR__ . '/TestCommand.inc.php');
$cli->add(new TestCommand());

$cli->run();

die();
