<?php declare(strict_types=1);

namespace MediaServer\Action;

use MediaServer\Service\VideoTranscoder;
use mef\Sql\Driver\SqlDriver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PutUpload extends AbstractAction
{
	public function run(Request $request): Response
	{
		/*************************************************************************
		 * Get the data from the request and sanitize it.
		 ************************************************************************/
		$sanitizer = [
			'name' => array('filter' => FILTER_SANITIZE_STRING),
			'uuid' => array('filter' => FILTER_SANITIZE_STRING),
			'type' => array('filter' => FILTER_SANITIZE_STRING),
		];
		$rawData = json_decode($request->getContent(), true);
		$data = filter_var_array($rawData, $sanitizer);


		/*************************************************************************
		 * Define all the video metadata we need and save the reference in the DB.
		 ************************************************************************/
		$videoMeta = $data + ["created_dt" => gmdate(DATE_RFC3339)];

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