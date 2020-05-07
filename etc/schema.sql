CREATE TABLE IF NOT EXISTS `video` (
	`id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name`       VARCHAR(100)     NULL,
	`uuid`       CHAR(64)         NOT NULL,
	`type`       VARCHAR(50)      NOT NULL,
	`processed`  TINYINT(1)       NOT NULL DEFAULT '0' COMMENT 'set to true after transcoding occurs',
	`error`      TINYINT(1)       NOT NULL DEFAULT '0' COMMENT 'set to true if transcoding fails',
	`created_dt` DATETIME         NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `video_uuid_idx` (`uuid`)
) ENGINE = InnoDB DEFAULT CHARSET = `utf8mb4`;
