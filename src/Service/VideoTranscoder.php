<?php declare(strict_types = 1);

namespace MediaServer\Service;

use Aws\MediaConvert\MediaConvertClient;
use Closure;

class VideoTranscoder
{
	/**
	 * @var MediaConvertClient Aws MediaConverter client handle.
	 */
	private $mediaConvertClient;

	/**
	 * @var string Where the original files are stored.
	 */
	private $source;

	/**
	 * @var string Where the transcoded files are stored.
	 */
	private $destination;

	/**
	 * @var Closure Closure that generates the MediaConvert job spec.
	 */
	private $mediaConvertJobGenerator;

	/**
	 * VideoTranscoder constructor.
	 *
	 * @param array  $config  App configuration.
	 */
	public function __construct(array $config)
	{
		$this->source = $config["aws"]["mediaConvert"]["source"];
		$this->destination = $config["aws"]["mediaConvert"]["destination"];
		$this->mediaConvertJobGenerator = $config["aws"]["mediaConvert"]["job"];
		$this->mediaConvertClient = $this->getMediaConvertClient($config["aws"]["clientOptions"]);
	}

	private function getMediaConvertClient(array $baseOptions) : MediaConvertClient
	{
		$tmpClient = new MediaConvertClient($baseOptions);
		$customEndpoints = $tmpClient->describeEndpoints();
		$baseOptions["endpoint"] = $customEndpoints['Endpoints'][0]['Url'];
		unset($tmpClient);
		return new MediaConvertClient($baseOptions);
	}

	/**
	 * @param array  $video  Some info about the video to identify it and be able to start the transcoding process.
	 */
	public function transcode(array $video)
	{
		$source = sprintf("%s%s.%s", $this->source, $video["uuid"], explode("/", $video["type"])[1]);
		$jobSpec = ($this->mediaConvertJobGenerator)($source, $this->destination);

		// Transcode original video to SD & HD HLS format
		$this->mediaConvertClient->createJob($jobSpec);
	}
}