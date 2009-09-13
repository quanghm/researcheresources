/*
SQLyog Community Edition- MySQL GUI v5.22a
Host - 5.0.27-community-nt : Database - papershare
*********************************************************************
Server version : 5.0.27-community-nt
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

create database if not exists `papershare`;

USE `papershare`;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/*Table structure for table `tbl_request` */

DROP TABLE IF EXISTS `tbl_request`;

CREATE TABLE `tbl_request` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(300) NOT NULL,
  `author` varchar(100) NOT NULL,
  `journal` varchar(100) NOT NULL,
  `year` int(11) NOT NULL,
  `issue` varchar(10) NOT NULL,
  `pages` varchar(15) character set utf8 collate utf8_unicode_ci default NULL,
  `field` varchar(30) NOT NULL,
  `download_link` varchar(300) NOT NULL,
  `date_request` date NOT NULL,
  `requester` varchar(15) NOT NULL,
  `supplier` varchar(15) NOT NULL,
  `status` tinyint(4) NOT NULL default '0',
  `previous_suppliers` varchar(300) NOT NULL,
  `stored_link` varchar(100) NOT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

/*Data for the table `tbl_request` */

insert  into `tbl_request`(`id`,`title`,`author`,`journal`,`year`,`issue`,`pages`,`field`,`download_link`,`date_request`,`requester`,`supplier`,`status`,`previous_suppliers`,`stored_link`) values (1,'a','asa','asfas',2007,'123','499--500','Mathematics','fdasfs','2007-07-04','testacc1','testacc3',-2,'arrPreviousSuppliers[]=testacc2&arrPreviousSuppliers[]=testacc4&',''),(2,'df','sdsg','sdsf',343,'12','12','Economics','asfsdf','2007-07-10','testacc5','namcoideptrai',-2,'',''),(3,'werwe','ugju','werwe',2313,'123','232','Economics','we3','2007-07-10','namcoideptrai','testacc1',-2,'',''),(4,'An adaptive algorithm for selecting profitable keywords for search-based advertising services','Paat Rusmevichientong   	  ','Proceedings of the 7th ACM conference on Electronic commerce',2006,'n/a','','Computer Science','http://portal.acm.org/citation.cfm?id=1134707.1134736','2007-07-10','testacc1','quocle79',-1,'',''),(5,'QuocTest PaperName01','QuocTestAuthor01','QuocTestJournal01',2007,'01','10','Computer Science','http://QuocTestLink.com.01','2007-07-13','quocle79','cuong',0,'',''),(6,'QuocTest PaperName02','QuocTestAuthor02','QuocTestJournal02',2007,'02','10','Computer Science','http://QuocTestLink.com.02','2007-07-13','quocle79','cuong',0,'',''),(7,'QuocTest PaperName03','QuocTestAuthor03','QuocTestJournal03',2007,'03','10','Computer Science','http://QuocTestLink.com.03','2007-07-13','quocle79','cuong',0,'',''),(8,'aeresr','zxczxc','aesd',1981,'245245','dfgdfg','Economics','fhdaf','2007-07-20','testacc1','namcoideptrai',-1,'',''),(9,'Localized construction of bounded degree and planar spanner for wireless ad hoc networks','Yu Wang, Xiang-Yang Li','Unknown',2000,'Unknown','','Computer Science','http://portal.acm.org/citation.cfm?id=1147594&coll=Portal&dl=GUIDE&CFID=29485479&CFTOKEN=17146431','2007-07-24','miti','quocle79',0,'','');

/*Table structure for table `tbl_update` */

DROP TABLE IF EXISTS `tbl_update`;

CREATE TABLE `tbl_update` (
  `lastupdate` date NOT NULL,
  `username` varchar(15) character set utf8 NOT NULL,
  UNIQUE KEY `lastupdate` (`lastupdate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Data for the table `tbl_update` */

/*Table structure for table `tbl_user` */

DROP TABLE IF EXISTS `tbl_user`;

CREATE TABLE `tbl_user` (
  `ID` int(10) unsigned zerofill NOT NULL auto_increment,
  `username` varchar(15) collate utf8_unicode_ci NOT NULL,
  `password` varchar(255) collate utf8_unicode_ci NOT NULL,
  `email` varchar(50) collate utf8_unicode_ci NOT NULL,
  `field` varchar(50) collate utf8_unicode_ci NOT NULL,
  `join_date` date NOT NULL,
  `request_number` int(11) NOT NULL default '0',
  `request_handle_number` int(11) NOT NULL default '0',
  `request_pending_number` int(11) NOT NULL default '0',
  `supplier` binary(1) NOT NULL default '0',
  `admin` binary(1) NOT NULL default '0',
  PRIMARY KEY  (`username`),
  KEY `ID` (`ID`),
  FULLTEXT KEY `field` (`field`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `tbl_user` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
