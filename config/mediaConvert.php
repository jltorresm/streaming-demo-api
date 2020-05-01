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
	$codecSettings = [
		'Codec'        => 'H_264',
		'H264Settings' => [
			'AdaptiveQuantization'                => 'HIGH',
			'CodecLevel'                          => 'AUTO',
			'CodecProfile'                        => 'MAIN',
			'DynamicSubGop'                       => 'STATIC',
			'EntropyEncoding'                     => 'CABAC',
			'FieldEncoding'                       => 'PAFF',
			'FlickerAdaptiveQuantization'         => 'DISABLED',
			'FramerateControl'                    => 'INITIALIZE_FROM_SOURCE',
			'FramerateConversionAlgorithm'        => 'DUPLICATE_DROP',
			'GopBReference'                       => 'DISABLED',
			'GopClosedCadence'                    => 1,
			'GopSize'                             => 90,
			'GopSizeUnits'                        => 'FRAMES',
			'InterlaceMode'                       => 'PROGRESSIVE',
			'MaxBitrate'                          => 5000000,
			'MinIInterval'                        => 0,
			'NumberBFramesBetweenReferenceFrames' => 2,
			'NumberReferenceFrames'               => 3,
			'ParControl'                          => 'INITIALIZE_FROM_SOURCE',
			'QualityTuningLevel'                  => 'SINGLE_PASS',
			'QvbrSettings'                        => [
				'QvbrQualityLevel'         => 7,
				'QvbrQualityLevelFineTune' => 0,
			],
			'RateControlMode'                     => 'QVBR',
			'RepeatPps'                           => 'DISABLED',
			'SceneChangeDetect'                   => 'ENABLED',
			'Slices'                              => 1,
			'SlowPal'                             => 'DISABLED',
			'Softness'                            => 0,
			'SpatialAdaptiveQuantization'         => 'ENABLED',
			'Syntax'                              => 'DEFAULT',
			'Telecine'                            => 'NONE',
			'TemporalAdaptiveQuantization'        => 'ENABLED',
			'UnregisteredSeiTimecode'             => 'DISABLED',
		],
	];

	return [
		'AccelerationSettings' => ['Mode' => 'DISABLED'],
		'Priority'             => 0,
		'Queue'                => "arn:aws:mediaconvert:us-east-2:956850163503:queues/Default",
		'Role'                 => "arn:aws:iam::956850163503:role/SimpleMediaServerRole",
		'Settings'             => [
			'AdAvailOffset' => 0,
			'Inputs'        => [
				[
					'AudioSelectors' => [
						'Audio Selector 1' => [
							'DefaultSelection' => 'DEFAULT',
							'Offset'           => 0,
							'ProgramSelection' => 1,
						],
					],
					'DeblockFilter'  => 'DISABLED',
					'DenoiseFilter'  => 'DISABLED',
					'FileInput'      => $sourceFile,
					'FilterEnable'   => 'AUTO',
					'FilterStrength' => 0,
					'PsiControl'     => 'USE_PSI',
					'TimecodeSource' => 'EMBEDDED',
					'VideoSelector'  => [
						'AlphaBehavior' => 'DISCARD',
						'ColorSpace'    => 'FOLLOW',
						'Rotate'        => 'AUTO', // DEGREE_0|DEGREES_90|DEGREES_180|DEGREES_270|AUTO
					],
				],
			],
			'OutputGroups'  => [
				[
					'CustomName'          => "Apple HLS", // TODO: Verify this field
					'Name'                => "Apple HLS",
					'OutputGroupSettings' => [
						'HlsGroupSettings' => [
							'CaptionLanguageSetting'  => 'OMIT',
							'ClientCache'             => 'ENABLED',
							'CodecSpecification'      => 'RFC_4281',
							'Destination'             => $destination,
							'DirectoryStructure'      => 'SUBDIRECTORY_PER_STREAM',
							'ManifestCompression'     => 'GZIP',
							'ManifestDurationFormat'  => 'FLOATING_POINT',
							'MinFinalSegmentLength'   => 0.0,
							'MinSegmentLength'        => 0,
							'OutputSelection'         => 'MANIFESTS_AND_SEGMENTS',
							'ProgramDateTime'         => 'EXCLUDE',
							'ProgramDateTimePeriod'   => 600,
							'SegmentControl'          => 'SEGMENTED_FILES',
							'SegmentLength'           => 5,
							'SegmentsPerSubdirectory' => 10,
							'StreamInfResolution'     => 'INCLUDE',
							'TimedMetadataId3Frame'   => 'PRIV',
							'TimedMetadataId3Period'  => 5,
						],
						'Type'             => 'HLS_GROUP_SETTINGS',
					],
					'Outputs'             => [
						[
							'AudioDescriptions' => [
								[
									'AudioTypeControl'    => 'FOLLOW_INPUT',
									'CodecSettings'       => [
										'AacSettings' => [
											'AudioDescriptionBroadcasterMix' => 'NORMAL',
											'Bitrate'                        => 96000,
											'CodecProfile'                   => 'LC',
											'CodingMode'                     => 'CODING_MODE_2_0',
											'RateControlMode'                => 'CBR',
											'RawFormat'                      => 'NONE',
											'SampleRate'                     => 48000,
											'Specification'                  => 'MPEG4',
										],
										'Codec'       => 'AAC', // AAC|MP2|MP3|WAV|AIFF|AC3|EAC3|EAC3_ATMOS|PASSTHROUGH
									],
									'LanguageCodeControl' => 'FOLLOW_INPUT',
								],
							],
							'ContainerSettings' => [
								'Container'    => 'M3U8',
								'M3u8Settings' => [
									'AudioFramesPerPes'  => 4,
									'AudioPids'          => [482, 483, 484, 485, 486, 487, 488, 489, 490, 491, 492],
									'NielsenId3'         => 'NONE',
									'PatInterval'        => 0,
									'PcrControl'         => 'PCR_EVERY_PES_PACKET',
									'PmtInterval'        => 0,
									'PmtPid'             => 480,
									'PrivateMetadataPid' => 503,
									'ProgramNumber'      => 1,
									'Scte35Source'       => 'NONE',
									'TimedMetadata'      => 'NONE',
									'VideoPid'           => 481,
								],
							],
							'NameModifier'      => '_sd',
							'OutputSettings'    => [
								'HlsSettings' => [
									'AudioGroupId'       => 'program_audio',
									'AudioOnlyContainer' => 'AUTOMATIC',
									'IFrameOnlyManifest' => 'EXCLUDE',
								],
							],
							'VideoDescription'  => [
								'AfdSignaling'      => 'NONE',
								'AntiAlias'         => 'ENABLED',
								'CodecSettings'     => $codecSettings,
								'ColorMetadata'     => 'INSERT',
								'DropFrameTimecode' => 'ENABLED',
								'Height'            => 480,
								'RespondToAfd'      => 'NONE',
								'ScalingBehavior'   => 'DEFAULT',
								'Sharpness'         => 50,
								'TimecodeInsertion' => 'DISABLED',
							],
						],
						[
							'AudioDescriptions' => [
								[
									'AudioTypeControl'    => 'FOLLOW_INPUT',
									'CodecSettings'       => [
										'AacSettings' => [
											'AudioDescriptionBroadcasterMix' => 'NORMAL',
											'Bitrate'                        => 96000,
											'CodecProfile'                   => 'LC',
											'CodingMode'                     => 'CODING_MODE_2_0',
											'RateControlMode'                => 'CBR',
											'RawFormat'                      => 'NONE',
											'SampleRate'                     => 48000,
											'Specification'                  => 'MPEG4',
										],
										'Codec'       => 'AAC', // AAC|MP2|MP3|WAV|AIFF|AC3|EAC3|EAC3_ATMOS|PASSTHROUGH
									],
									'LanguageCodeControl' => 'FOLLOW_INPUT',
								],
							],
							'ContainerSettings' => [
								'Container'    => 'M3U8',
								'M3u8Settings' => [
									'AudioFramesPerPes'  => 4,
									'AudioPids'          => [482, 483, 484, 485, 486, 487, 488, 489, 490, 491, 492],
									'NielsenId3'         => 'NONE',
									'PatInterval'        => 0,
									'PcrControl'         => 'PCR_EVERY_PES_PACKET',
									'PmtInterval'        => 0,
									'PmtPid'             => 480,
									'PrivateMetadataPid' => 503,
									'ProgramNumber'      => 1,
									'Scte35Source'       => 'NONE',
									'TimedMetadata'      => 'NONE',
									'TimedMetadataPid'   => 502,
									'VideoPid'           => 481,
								],
							],
							'NameModifier'      => '_hd',
							'OutputSettings'    => [
								'HlsSettings' => [
									'AudioGroupId'       => 'program_audio',
									'AudioOnlyContainer' => 'AUTOMATIC',
									'IFrameOnlyManifest' => 'EXCLUDE',
								],
							],
							'VideoDescription'  => [
								'AfdSignaling'      => 'NONE',
								'AntiAlias'         => 'ENABLED',
								'CodecSettings'     => $codecSettings,
								'ColorMetadata'     => 'INSERT',
								'DropFrameTimecode' => 'ENABLED',
								'Height'            => 1080,
								'RespondToAfd'      => 'NONE',
								'ScalingBehavior'   => 'DEFAULT',
								'Sharpness'         => 50,
								'TimecodeInsertion' => 'DISABLED',
							],
						],
					],
				],
			],
		],
		'StatusUpdateInterval' => 'SECONDS_60',
		'UserMetadata'         => [],
	];
};