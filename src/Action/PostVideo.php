<?php declare(strict_types=1);

namespace MediaServer\Action;

use Aws\S3\S3Client;
use MediaServer\Service\VideoTranscoder;
use mef\Sql\Driver\SqlDriver;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostVideo extends AbstractAction
{
	public function run(Request $request): Response
	{
		/*************************************************************************
		 * NOTE: For the sake of this demo I allowed a higher memory consumption
		 *       for post and upload sizes.
		 *
		 *             post_max_size --> 100M
		 *       upload_max_filesize --> 100M
		 ************************************************************************/

		/*************************************************************************
		 * Get the uploaded VIDEO and validate a few things.
		 ************************************************************************/
		/**
		 * @var $video UploadedFile
		 */
		$video = $request->files->get("video");
		if ($video == null)
		{
			http_response_code(400);
			die("Bad Request :: expected video");
		}

		$acceptedMediaTypes = ["video/mp4"];

		if (!in_array($video->getMimeType(), $acceptedMediaTypes))
		{
			http_response_code(406);
			die("Not Acceptable :: media type");
		}

		if ($video->getClientOriginalName() == "")
		{
			http_response_code(400);
			die("Bad Request :: missing name");
		}

		/*************************************************************************
		 * Define all the video metadata we need.
		 ************************************************************************/
		$videoMeta = [
			"name"       => $video->getClientOriginalName(),
			"uuid"       => hash("sha256", $video->openFile("r")->fread($video->getSize())),
			"type"       => $video->getMimeType(),
			"created_dt" => gmdate(DATE_RFC3339),
		];

		/*************************************************************************
		 * Move the accepted media to a new, more permanent location.
		 ************************************************************************/
		$uploadDirectory = $this->config["uploads"]["local"];
		if ($this->config["uploads"]["type"] == "remote")
		{
			// Register the stream wrapper from an S3Client object
			$client = new S3Client($this->config["aws"]["clientOptions"]);
			$client->registerStreamWrapper();

			// Register the remote upload path
			$uploadDirectory = $this->config["uploads"]["remote"];
		}

		$generatedName = sprintf(
			"%s.%s",
			$videoMeta["uuid"],
			explode("/", $video->getMimeType())[1]
		);

		$video->move($uploadDirectory, $generatedName);

		/*************************************************************************
		 * Now we save the references to DB.
		 ************************************************************************/
		/**
		 * @var $database SqlDriver
		 */
		$database = $this->config["database"]();
		$database->insert()->into("video")->namedValues($videoMeta)->execute();

		/*************************************************************************
		 * And handle transcoding for remote uploads.
		 *
		 * TODO: This is a big candidate to be moved to an asynchronous handler
		 *       either via jobs, lambda functions, AWS pipelines/triggers, or any
		 *       other async solution.
		 ************************************************************************/
		if ($this->config["uploads"]["type"] == "remote")
		{
			$transcoder = new VideoTranscoder($this->config);
			$transcoder->transcode($videoMeta);
		}

		return new Response("OK");
	}
}