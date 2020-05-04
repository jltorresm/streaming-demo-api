<?php declare(strict_types=1);

use mef\Db\Driver\PdoDriver;
use mef\Db\TransactionDriver\NestedTransactionDriver;
use mef\Sql\Driver\SqlDriver;
use mef\Sql\Writer\MySqlWriter;

return [
	"uploads" => [
		"type"   => "remote",                           // remote|local
		"local"  => __DIR__ . "/../uploads/",           // for when uploads.type = local
		"remote" => "s3://simple-media-server/source/", // for when uploads.type = remote
	],

	"aws" => [
		"clientOptions"  => ["profile" => "media-server", "region" => "us-east-2", "version" => "latest"],
		"s3"             => ["bucket" => "simple-media-server"],
		"mediaConvert"   => [
			"clientOptions" => ["profile" => "media-server", "region" => "us-east-2", "version" => "latest"],
			"source"        => "s3://simple-media-server/source/",
			"destination"   => "s3://simple-media-server/transcoded/",
			"job"           => require __DIR__ . "/mediaConvert.php",
		],
		"cloudFormation" => [
			"url" => "https://dc0k1t1zgdrge.cloudfront.net/"
		],
	],

	"database" => function (): SqlDriver
	{
		$config = [
			'dsn'      => 'mysql:host=127.0.0.1;dbname=mediaserver;charset=utf8mb4',
			'username' => 'root',
			'password' => 'rootroot',
			'options'  => [],
		];

		$pdo = new PDO($config['dsn'], $config['username'], $config['password'], $config['options']);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo->exec('SET time_zone=\'+0:00\'');
		$pdo->exec('SET sql_mode=\'\'');

		$db = new PdoDriver($pdo);
		$db->setTransactionDriver(new NestedTransactionDriver($db));
		return new SqlDriver(new MySqlWriter($db));
	},
];