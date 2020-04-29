<?php declare(strict_types=1);

namespace MediaServer\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetVideo implements ActionInterface
{
	public function run(Request $request): Response
	{
		/*******************************************************************************
		 * We are gonna prepare a list of available videos to return to the client.
		 ******************************************************************************/
		$videos = [];

		/*******************************************************************************
		 * Read the directory of uploads and add to the final list only the files that
		 * are actual videos.
		 ******************************************************************************/
		$baseDir = __DIR__ . "/../../uploads/";
		$videoDir = dir($baseDir);
		while (($entry = $videoDir->read()) !== false)
		{
			if (is_dir($baseDir . $entry))
			{
				continue;
			}
			[$id, $name, $type] = explode(".", $entry);
			$uri = "http://" . $_SERVER["HTTP_HOST"] . "/uploads/" . $entry;
			$videos[] = ["id" => $id, "name" => $name, "type" => $type, "uri" => $uri];
		}
		$videoDir->close();


		return new Response(json_encode($videos), 200, ["Content-Type" => "application/json"]);
	}
}