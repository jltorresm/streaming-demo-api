<?php declare(strict_types = 1);
/*******************************************************************************
 * NOTE: For the sake of this demo I allowed a higher memory consumption for
 *       post and upload sizes.
 *
 *             post_max_size --> 40M
 *       upload_max_filesize --> 40M
 ******************************************************************************/

date_default_timezone_set('UTC');
require __DIR__ . '/vendor/autoload.php';

use mef\Http\FileStream;
use mef\Http\ServerRequest;
use Psr\Http\Message\UploadedFileInterface;

/*******************************************************************************
 * Parse the http request.
 ******************************************************************************/
$request = ServerRequest::fromGlobals(
	new FileStream(fopen('php://input', 'r')),
	$_SERVER,
	$_GET,
	$_POST,
	$_COOKIE,
	$_FILES
);

/*******************************************************************************
 * Get the uploaded VIDEO and validate a few things.
 ******************************************************************************/
$files = $request->getUploadedFiles();
if (!isset($files["video"]))
{
	http_response_code(400);
	die("Bad Request :: expected video");
}

/**
 * @var $video UploadedFileInterface
 */
$video = $files["video"];
$acceptedMediaTypes = ["video/mp4"];

if (!in_array($video->getClientMediaType(), $acceptedMediaTypes))
{
	http_response_code(406);
	die("Not Acceptable :: media type");
}

if ($video->getClientFilename() == "")
{
	http_response_code(400);
	die("Bad Request :: missing name");
}

/*******************************************************************************
 * Move the accepted media to a new, more permanent location.
 ******************************************************************************/
$generatedName = sprintf(
	"%s/%s.%s.%s",
	__DIR__ . "/uploads",
	gmdate("YmdHis"),
	$video->getClientFilename(),
	explode("/", $video->getClientMediaType())[1]
);

$video->moveTo($generatedName);

http_response_code(200);
die("OK");