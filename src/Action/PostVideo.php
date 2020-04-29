<?php declare(strict_types=1);

namespace MediaServer\Action;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostVideo implements ActionInterface
{
	public function run(Request $request): Response
	{
		/*******************************************************************************
		 * NOTE: For the sake of this demo I allowed a higher memory consumption for
		 *       post and upload sizes.
		 *
		 *             post_max_size --> 100M
		 *       upload_max_filesize --> 100M
		 ******************************************************************************/

		/*******************************************************************************
		 * Get the uploaded VIDEO and validate a few things.
		 ******************************************************************************/
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

		/*******************************************************************************
		 * Move the accepted media to a new, more permanent location.
		 ******************************************************************************/
		$generatedName = sprintf(
			"%s.%s.%s",
			gmdate("YmdHis"),
			$video->getClientOriginalName(),
			explode("/", $video->getMimeType())[1]
		);

		$video->move(__DIR__ . "/../../uploads", $generatedName);

		return new Response("OK");
	}
}