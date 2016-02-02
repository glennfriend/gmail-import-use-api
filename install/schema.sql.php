<?php exit; ?>

-- phpMyAdmin SQL Dump
-- version 4.4.6
-- http://www.phpmyadmin.net
--
-- 主機: localhost
-- 產生時間： 2016 年 02 月 02 日 04:01
-- 伺服器版本: 5.5.44-0ubuntu0.14.04.1
-- PHP 版本： 5.5.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 資料庫： `gmail_import`
--

-- --------------------------------------------------------

--
-- 資料表結構 `inboxes`
--

CREATE TABLE IF NOT EXISTS `inboxes` (
  `id` int(11) unsigned NOT NULL,
  `parent_id` int(11) NOT NULL COMMENT '可能會有 -1 的值, 不能放置 UNSIGNED 屬性',
  `from_email` varchar(255) NOT NULL,
  `to_email` varchar(255) NOT NULL,
  `from_name` varchar(100) NOT NULL,
  `to_name` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body_snippet` text NOT NULL,
  `email_create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `message_id` varchar(80) NOT NULL COMMENT 'always 68 byte',
  `reference_message_ids` text NOT NULL COMMENT 'references field',
  `properties` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 已匯出資料表的索引
--

--
-- 資料表索引 `inboxes`
--
ALTER TABLE `inboxes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `message_id` (`message_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- 在匯出的資料表使用 AUTO_INCREMENT
--

--
-- 使用資料表 AUTO_INCREMENT `inboxes`
--
ALTER TABLE `inboxes`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
