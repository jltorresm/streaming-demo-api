<?php declare(strict_types=1);

/**
 * Builder for the MediaConvert transcoding job.
 *
 * For the full option specification see:
 * https://docs.aws.amazon.com/aws-sdk-php/v3/api//api-mediaconvert-2017-08-29.html#createjob
 *
 * @param string $sourceFile  The original file to be transcoded.
 * @param string $destination The folder where the transcoded files will be created.
 *
 * @return array The full job options to be executed in AWS MediaConvert.
 */
return function (string $sourceFile, string $destination): array
{
	$settings = json_decode(file_get_contents(__DIR__ . "/../etc/lambda/job.json"), true);
	$settings["Inputs"][0]["FileInput"] = $sourceFile;

	if ($settings["OutputGroups"][0]["OutputGroupSettings"]["Type"] != "HLS_GROUP_SETTINGS")
	{
		throw new RuntimeException("Unexpected OutputGroup type.");
	}

	$settings["OutputGroups"][0]["OutputGroupSettings"]["HlsGroupSettings"]["Destination"] = $destination;

	return [
		'AccelerationSettings' => ['Mode' => 'DISABLED'],
		'Priority'             => 0,
		'Queue'                => "arn:aws:mediaconvert:us-east-2:956850163503:queues/Default",
		'Role'                 => "arn:aws:iam::956850163503:role/SimpleMediaServerRole",
		'Settings'             => $settings,
		'StatusUpdateInterval' => 'SECONDS_60',
		'UserMetadata'         => [],
	];
};