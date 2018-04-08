-- phpMyAdmin SQL Dump
-- version 4.7.3
-- https://www.phpmyadmin.net/
--
-- Hôte : altechbepxath.mysql.db
-- Généré le :  Dim 08 avr. 2018 à 11:10
-- Version du serveur :  5.6.34-log
-- Version de PHP :  7.0.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone

CREATE TABLE `weather` (
  `time` bigint(48) NOT NULL COMMENT 'Measurement tim',
  `id` int(24) NOT NULL,
  `creation_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `lat` double DEFAULT NULL,
  `lon` double DEFAULT NULL,
  `weather_id` int(11) DEFAULT NULL,
  `weather_info` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `temperature` double DEFAULT NULL,
  `pressure` int(11) DEFAULT NULL,
  `humidity` int(11) DEFAULT NULL,
  `temp_min` double DEFAULT NULL,
  `temp_max` double DEFAULT NULL,
  `visibility` int(11) DEFAULT NULL,
  `wind_speed` double DEFAULT NULL,
  `wind_deg` int(11) DEFAULT NULL,
  `clouds` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Meteo';
ALTER TABLE `weather`
  ADD KEY `i_time` (`time`),
  ADD KEY `t_id` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
