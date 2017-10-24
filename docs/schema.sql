CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT NULL,
  `campid` varchar(100) DEFAULT NULL,
  `ip` varchar(50) DEFAULT NULL,
  `country` varchar(2) DEFAULT NULL,
  `state` varchar(5) DEFAULT NULL,
  `referrer` varchar(255) DEFAULT NULL,
  `platform` tinyint(1) DEFAULT '0',
  `eventsource` varchar(100) DEFAULT NULL,
  `eventid` tinyint(1) DEFAULT '0',
  `trackingcode` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_index` (`created`,`ip`,`eventid`),
  KEY `eventid` (`eventid`),
  KEY `created` (`created`),
  KEY `state` (`state`),
  KEY `country` (`country`),
  KEY `eventsource` (`eventsource`),
  KEY `trackingcode` (`trackingcode`),
  KEY `ip` (`ip`),
  KEY `referrer` (`referrer`)
) ENGINE=InnoDB;

