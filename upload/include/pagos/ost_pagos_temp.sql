/*
Navicat MySQL Data Transfer

Source Server         : tag24d
Source Server Version : 50149
Source Host           : crmtest.tuagencia24.com:3306
Source Database       : osticket1911

Target Server Type    : MYSQL
Target Server Version : 50149
File Encoding         : 65001

Date: 2018-03-08 13:35:59
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ost_pagos_temp
-- ----------------------------
DROP TABLE IF EXISTS `ost_pagos_temp`;
CREATE TABLE `ost_pagos_temp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `fechadepago` date DEFAULT NULL,
  `formadepago` varchar(255) DEFAULT NULL,
  `referencia` varchar(255) DEFAULT NULL,
  `emisor` varchar(255) DEFAULT NULL,
  `receptor` varchar(255) DEFAULT NULL,
  `moneda` varchar(255) DEFAULT NULL,
  `monto` double DEFAULT NULL,
  `concepto` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
