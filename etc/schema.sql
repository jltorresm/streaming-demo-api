CREATE TABLE IF NOT EXISTS `video` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(100) NULL,
	`uuid` char(64) NOT NULL,
	`type` varchar(50) NOT NULL,
	`created_dt` DATETIME NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `video_sha256_idx` (`uuid`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4;
