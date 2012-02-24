/*
 Source Server Type    : MySQL
 Source Server Version : 50146

 Target Server Type    : MySQL
 Target Server Version : 50146
 File Encoding         : utf-8
*/

SET NAMES utf8;

-- ----------------------------
--  Table structure for `tables`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `%tableprefix%tch_tables` (
  `match_id` smallint NOT NULL DEFAULT '0',
  `table_no` smallint NOT NULL DEFAULT '0',
  `result` smallint NOT NULL DEFAULT '0',
  `player_id` smallint NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `matches`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `%tableprefix%tch_matches` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `opponent` varchar(100) NOT NULL DEFAULT '',
  `matchdate` date NOT NULL DEFAULT '0000-00-00',
  `league` varchar(50) NOT NULL DEFAULT '',
  `team` varchar(20) NOT NULL DEFAULT 'A',
  `round` smallint(6) DEFAULT NULL,
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `rating`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `%tableprefix%tch_rating` (
  `period_id` smallint NOT NULL DEFAULT '0',
  `player_id` smallint NOT NULL DEFAULT '0',
  `rating` smallint NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `ratingperiod`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `%tableprefix%tch_ratingperiod` (
  `id` smallint NOT NULL DEFAULT '0',
  `ratingdate` date NOT NULL DEFAULT '0000-00-00',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `players`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `%tableprefix%tch_players` (
  `id` smallint NOT NULL AUTO_INCREMENT,
  `firstname` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) NOT NULL DEFAULT '',
  `signature` char(6) NOT NULL DEFAULT '',
  `ssf_id` int(10) DEFAULT NULL,
  `fide_id` int(10) DEFAULT NULL,
  `primary_member` bool NOT NULL DEFAULT true,
  `player_info` varchar(40) DEFAULT NULL,
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
