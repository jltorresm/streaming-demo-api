<?php declare(strict_types=1);

namespace MediaServer\Action;

use mef\Sql\Driver\SqlDriver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PutUpload. Saves upload metadata to the DB.
 *
 * @package MediaServer\Action
 */
class PutUpload extends AbstractAction
{
	/**
	 * Execute the request handler.
	 *
	 * @param Request $request The request to handle.
	 *
	 * @return Response
	 */
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
		$videoMeta = $data + ["processed" => false, "created_dt" => gmdate(DATE_RFC3339)];

		/**
		 * @var $database SqlDriver
		 */
		$database = $this->config["database"]();
		$database->insert()->into("video")->namedValues($videoMeta)->execute();

		/*************************************************************************
		 * Note: We are just handling metadata at this point. The transcoding
		 *       process is handled asynchronously in the AWS infrastructure
		 *       via lambda functions and S3 triggers.
		 *
		 * If, for any reason, we want to do the transcode call manually,
		 * uncomment the lines below. Just make sure to de-activate the triggers
		 * in AWS first, otherwise you could be doing the process twice.
		 ************************************************************************/
		// if ($this->config["uploads"]["type"] == "remote")
		// {
		// 	$transcoder = new VideoTranscoder($this->config);
		// 	$transcoder->transcode($videoMeta);
		// }

		return new Response("OK");
	}
}