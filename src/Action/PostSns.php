<?php declare(strict_types=1);

namespace MediaServer\Action;

use DateTimeImmutable;
use mef\Sql\Driver\SqlDriver;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PostSns. Receives notifications from AWS SNS and handles them accordingly.
 *
 * @package MediaServer\Action
 */
class PostSns extends AbstractAction
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
		 *
		 * The standard notification structure has more fields than the reflected
		 * below, but we only care about the ones below.
		 ************************************************************************/
		$sanitizer = [
			'Type'      => array('filter' => FILTER_SANITIZE_STRING),
			'MessageId' => array('filter' => FILTER_SANITIZE_STRING),
			'TopicArn'  => array('filter' => FILTER_SANITIZE_STRING),
			'Message'   => array('filter' => FILTER_UNSAFE_RAW),
			'Timestamp' => array('filter' => FILTER_SANITIZE_STRING),
		];
		$rawData = json_decode($request->getContent(), true);
		$data = filter_var_array($rawData, $sanitizer);

		/*************************************************************************
		 * Parse typed fields.
		 *
		 * Mesasge -> array
		 * Timestamp -> \DateTimeImmutable
		 ************************************************************************/
		try
		{
			$data["Message"] = json_decode($data["Message"], true);
			$data["Timestamp"] = new DateTimeImmutable($data["Timestamp"]);
		}
		catch (RuntimeException $e)
		{
			http_response_code(400);
			die("Bad Request :: " . $e->getMessage());
		}

		/*************************************************************************
		 * Handle only the messages we care about.
		 *
		 * Message.source == aws.mediaconvert
		 * Message.detail.userMetadata.application == "Simple Media Server"
		 ************************************************************************/
		if ($data["Message"]["source"] != "aws.mediaconvert" ||
			$data["Message"]["detail"]["userMetadata"]["application"] != "Simple Media Server")
		{
			return new Response("OK");
		}

		//
		// log just to keep track
		//
		$msg = sprintf(
			"[[DEBUG]\t%s\tType: %s\t Status: %s\nMessageId: %s\tTopic arn: %s",
			$data["Timestamp"]->format(DATE_W3C),
			$data["Type"],
			$data["Message"]["detail"]["status"],
			$data["MessageId"],
			$data["TopicArn"]
		);
		error_log($msg);

		/**
		 * @var $database SqlDriver
		 */
		$database = $this->config["database"]();

		switch ($data["Message"]["detail"]["status"])
		{
			case "COMPLETE":
				//
				// Set the processed flag in the DB to true.
				//
				$database->update()->table("video")
					->namedValues(["processed" => 1])
					->where("uuid", $data["Message"]["detail"]["userMetadata"]["assetID"])
					->execute();
				break;
			case "ERROR":
				//
				// Set the error flag in the DB to true.
				//
				$database->update()->table("video")
					->namedValues(["error" => 1])
					->where("uuid", $data["Message"]["detail"]["userMetadata"]["assetID"])
					->execute();
				break;
			default:
				http_response_code(400);
				die("Unknown transcoding job status:: " . $data["Message"]["detail"]["status"]);
				break;
		}


		return new Response("OK2");
	}
}