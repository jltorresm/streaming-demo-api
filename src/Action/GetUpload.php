<?php declare(strict_types=1);

namespace MediaServer\Action;

use Aws\S3\PostObjectV4;
use Aws\S3\S3Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetUpload extends AbstractAction
{
	public function run(Request $request): Response
	{
		/*******************************************************************************
		 * Construct the S3 client
		 ******************************************************************************/
		$client = new S3Client($this->config["aws"]["clientOptions"]);

		/*******************************************************************************
		 * Build the upload object.
		 ******************************************************************************/
		// Set some defaults for form input fields
		$formInputs = [
			'key' => 'source/${filename}',
		];

		// Construct an array of conditions for policy
		$options = [
			['bucket' => $this->config["aws"]["s3"]["bucket"]],
			["starts-with", '$key', "source/"]
		];

		// Set expiration time
		$expires = "+10 minutes";

		$postObject = new PostObjectV4($client, $this->config["aws"]["s3"]["bucket"], $formInputs, $options, $expires);

		/*******************************************************************************
		 * Get the attributes and fields for the post form, and send it to the caller.
		 ******************************************************************************/
		$data = json_encode([
			"formAttributes" => $postObject->getFormAttributes(),
			"formInputs"     => $postObject->getFormInputs(),
		]);

		return new Response($data, 200, ["Content-Type" => "application/json"]);
	}
}