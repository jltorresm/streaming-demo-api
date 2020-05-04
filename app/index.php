<?php declare(strict_types=1);

date_default_timezone_set('UTC');
require __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/../config/config.php';

use MediaServer\Action\ActionInterface;
use MediaServer\Action\GetUpload;
use MediaServer\Action\GetVideo;
use MediaServer\Action\PostVideo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/*******************************************************************************
 * Define a few routes.
 ******************************************************************************/
$routeDefinition = [
	["getUpload", new Route('/upload', ['_controller' => GetUpload::class], [], [], null, ["http", "https"], "GET")],
	["postVideo", new Route('/video', ['_controller' => PostVideo::class], [], [], null, ["http", "https"], "POST")],
	["getVideo", new Route('/video', ['_controller' => GetVideo::class], [], [], null, ["http", "https"], "GET")],
];

$routes = new RouteCollection();
foreach ($routeDefinition as $rd)
{
	$routes->add(...$rd);
}

/*******************************************************************************
 * Create the whole request controller.
 ******************************************************************************/
$request = Request::createFromGlobals();
$context = new RequestContext();
$context->fromRequest($request);

/*******************************************************************************
 * Match the request to the proper "controller"
 ******************************************************************************/
$matcher = new UrlMatcher($routes, $context);
try
{
	$parameters = $matcher->matchRequest($request);
}
catch (MethodNotAllowedException $e)
{
	http_response_code(Response::HTTP_METHOD_NOT_ALLOWED);
	die("Method Not Allowed");
}
catch (ResourceNotFoundException $e)
{
	http_response_code(Response::HTTP_NOT_FOUND);
	die("Not Found");
}

/*******************************************************************************
 * Run the thing!
 ******************************************************************************/
/**
 * @var $handler ActionInterface
 */
$handler = new $parameters["_controller"]($config);
$response = $handler->run($request);
$response->prepare($request);
$response->send();
