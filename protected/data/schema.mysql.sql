DROP TABLE IF EXISTS `users`;
CREATE TABLE users (
    `user_id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(128) NOT NULL,
    `password` VARCHAR(40) NOT NULL,
    `payload` BLOB NULL COMMENT "Any random data we want to store about the user",
    PRIMARY KEY (`user_id`),
    KEY (`username`)
) Engine=InnoDB;

DROP TABLE IF EXISTS `user_searches`;
CREATE TABLE `user_searches` (
	`search_id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_id` INTEGER UNSIGNED NOT NULL COMMENT "users.user_id foreign key",
	`name` VARCHAR(50) NOT NULL,
	`temporary` TINYINT(1) DEFAULT 1 COMMENT "Unless manually specified, this is a temporary entry",
	`search_data` BLOB NOT NULL COMMENT "A json-encoded blob of all the information we need for the search",
	PRIMARY KEY (`search_id`),
	FOREIGN KEY `fk_user` (`user_id`) REFERENCES `users` (`user_id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) Engine=InnoDB;

DROP TABLE IF EXISTS `repeater_regions`;
CREATE TABLE `repeater_regions` (
	`region_id` INTEGER UNSIGNED NOT NULL,
	`state` VARCHAR(5),
	`country` VARCHAR(2),
	`location_name` VARCHAR(25),
	`area_name` VARCHAR(25),
	PRIMARY KEY (`region_id`),
	KEY (`state`,`country`,`area_name`)
) Engine=MyISAM;

DROP TABLE IF EXISTS `repeaters`;
CREATE TABLE `repeaters` (
	`repeater_id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
	`band` VARCHAR(5) NOT NULL,
	`output_freq` DECIMAL(5,4) NOT NULL,
	`input_freq` DECIMAL(5,4) NOT NULL,
	`ctcss_in` DECIMAL(3,1) default NULL,
	`ctcss_out` DECIMAL(3,1) default NULL,
	`dcs_code` DECIMAL(3,2) default NULL,
	`region_id` INTEGER NOT NULL COMMENT "repeater_regions.region_id foreign key",
	`open` TINYINT(1) default 0 COMMENT "If we don't know if it's open or closed, assume it's closed",
	`geo_location` POINT NOT NULL COMMENT "The lat/lon where the repeater is located",
	`geo_coverage` POLYGON NOT NULL COMMENT "The geographical coverage area of the repeater",
	`import_data` BLOB default NULL COMMENT "A json-encoded blob of the original import data. This allows us to manually correct errors, etc",
	PRIMARY KEY (`repeater_id`),
	KEY (`band`),
	KEY (`region_id`),
	SPATIAL INDEX(`geo_location`),
	SPATIAL INDEX(`geo_coverage`)
) Engine=MyISAM;

DROP TABLE IF EXISTS `repeater_links`;
CREATE TABLE `repeater_links` (
	`source_id` INTEGER UNSIGNED NOT NULL COMMENT "repeaters.repeater_id foreign key",
	`dest_id` INTEGER UNSIGNED NOT NULL COMMENT "repeaters.repeater_id foreign key",
	PRIMARY KEY (`source_id`, `dest_id`)
) Engine=MyISAM;
