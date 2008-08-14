-- phpMyAdmin SQL Dump
-- version 2.10.0.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Mar 25, 2007 at 09:56 PM
-- Server version: 5.0.37
-- PHP Version: 5.2.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Database: `db_paper_share`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `tbl_request`
-- 

CREATE TABLE `tbl_request` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(300) character set utf8 collate utf8_unicode_ci NOT NULL,
  `author` varchar(100) character set utf8 collate utf8_unicode_ci NOT NULL,
  `journal` varchar(300) character set utf8 NOT NULL,
  `download_link` varchar(300) character set utf8 NOT NULL,
  `issue` varchar(10) character set utf8 collate utf8_unicode_ci NOT NULL,
  `year` int(11) NOT NULL,
  `field` varchar(30) character set utf8 collate utf8_unicode_ci NOT NULL,
  `date_request` date NOT NULL,
  `requester` varchar(15) character set utf8 collate utf8_unicode_ci NOT NULL,
  `status` tinyint(10) NOT NULL default '0',
  `supplier` varchar(15) character set utf8 collate utf8_unicode_ci NOT NULL,
  `previous_suppliers` varchar(300) character set utf8 NOT NULL,
  `stored_link` varchar(100) character set utf8 NOT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

-- 
-- Dumping data for table `tbl_request`
-- 

INSERT INTO `tbl_request` (`id`, `title`, `author`, `journal`, `download_link`, `issue`, `year`, `field`, `date_request`, `requester`, `status`, `supplier`, `previous_suppliers`, `stored_link`) VALUES 
(10, 'title', 'author', 'journal', 'http://localhost/', '123', 1982, 'Mathematics', '2007-03-20', 'hmquang', -2, 'tester1', '$arrPreviousSuppliers[]=tester1&$arrPreviousSuppliers[]=tester1&$arrPreviousSuppliers[]=tester2&', ''),
(11, 'test', 'author', 'Tuyá»ƒn táº­p tháº±ng ngá»‘', 'http://localhost/', '123', 2006, 'Mathematics', '2007-03-20', 'hmquang', -1, 'quanghm', 'arrPreviousSuppliers[]=tester1&arrPreviousSuppliers[]=tester2&arrPreviousSuppliers[]=tester3&', ''),
(12, 'Title 2', 'author', 'journal', 'http://localhost/', '123', 1982, 'Mathematics', '2007-03-20', 'quanghm', 0, 'tester1', '', ''),
(13, 'title', 'author', 'journal', 'http://localhost/', '1234', 2000, 'Mathematics', '2007-03-21', 'testacc1', 0, '', '', '');

-- --------------------------------------------------------

-- 
-- Table structure for table `tbl_user`
-- 

CREATE TABLE `tbl_user` (
  `id` int(10) unsigned zerofill NOT NULL auto_increment,
  `username` varchar(15) collate utf8_unicode_ci NOT NULL,
  `password` varchar(255) collate utf8_unicode_ci NOT NULL,
  `email` varchar(50) collate utf8_unicode_ci NOT NULL,
  `field` varchar(50) collate utf8_unicode_ci NOT NULL,
  `supplier` tinyint(4) NOT NULL default '0',
  `join_date` varchar(20) collate utf8_unicode_ci NOT NULL,
  `request_number` int(11) NOT NULL default '0',
  `request_handle_number` int(11) NOT NULL default '0',
  `request_pending_number` int(11) NOT NULL default '0',
  PRIMARY KEY  (`username`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=33 ;

-- 
-- Dumping data for table `tbl_user`
-- 

INSERT INTO `tbl_user` (`id`, `username`, `password`, `email`, `field`, `supplier`, `join_date`, `request_number`, `request_handle_number`, `request_pending_number`) VALUES 
(0000000032, 'hmquang', '$1$Tb4.gM1.$mTC7QiMhd5MnYW31ffBgs0', 'q@a.com', 'Mathematics', 0, '2007-03-22', 0, 0, 0),
(0000000030, 'hoangmanhquang', '$1$KN2.LP3.$UcsvKXITbiSmAH/xKXD90.', 'quang.com', 'Mathematics', 1, '2007-03-22', 0, 0, 0),
(0000000029, 'quang1', '$1$MM2.lV3.$9.9mJJQ0cFTeCr6v2D5P2/', 'quang@test', 'Mathematics', 1, '2007-03-22', 0, 0, 0),
(0000000031, 'quanghm', '$1$2j0.he..$4FKO/tbZ.J9R18aLaGisf0', '', 'Choose a Field of Study...', 0, '2007-03-22', 0, 0, 0),
(0000000028, 'quangquac', '$1$8i5.vL3.$1lKUYWr/YOjuZ7vvf0JWY1', 'test@test', 'Choose a Field of Study...', 1, '2007-03-22', 0, 0, 0),
(0000000026, 'testacc1', '$1$AW..Jj5.$XFrFCUpPNokiRFC24qOQg/', 'testacc1@yahoo.com', 'Mathematics', 1, '2007-03-21', 1, 1, 29),
(0000000027, 'testing', '$1$gA0.p5/.$e/cCclL5cLx2PABIPSck91', 'quang@test', 'Mathematics', 1, '2007-03-22', 0, 0, 0);
