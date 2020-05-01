<?php declare(strict_types=1);

namespace MediaServer\Action;

use DateTimeImmutable;
use mef\Sql\Driver\SqlDriver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetVideo extends AbstractAction
{
	public function run(Request $request): Response
	{
		/*******************************************************************************
		 * We are gonna prepare a list of available videos to return to the client.
		 ******************************************************************************/
		$videos = [];

		/*******************************************************************************
		 * Get the videos from the DB, and point the client to the CloudFormation
		 * distribution.
		 ******************************************************************************/
		/**
		 * @var $database SqlDriver
		 */
		$database = $this->config["database"]();

		$rows = $database->select()->from("video")->query();

		foreach ($rows as $row)
		{
			$row["created_dt"] = (new DateTimeImmutable($row["created_dt"]))->format(DATE_W3C);
			$row["uri"] = $this->config["aws"]["cloudFormation"]["url"] . $row["uuid"] . ".m3u8";
			$videos[] = $row;
		}

		return new Response(json_encode($videos), 200, ["Content-Type" => "application/json"]);
	}
}