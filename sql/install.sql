CREATE TABLE IF NOT EXISTS `#__telegram` (
`id` int(11) NOT NULL auto_increment,
`message` text,
`article_id` int(11) NOT NULL,
`published` tinyint(4),
`url` text,
PRIMARY KEY  (`id`)
) /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;
