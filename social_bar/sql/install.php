<?php
/**
* 2018 Pimclick - Gaël ROBIN
*
*
*  @author    Gaël ROBIN <gael@luxury-concept.com>
*  @copyright 2018 Pimclick
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*
*/
$sql = array();
//CREATE TABLE IF NOT EXISTS `social_bar` (
    // `id_social_bar` int(11) NOT NULL AUTO_INCREMENT,
    // `facebook_url` TEXT,
    // `youtube_url` TEXT,
    // `instagram_url` TEXT,
    // PRIMARY KEY  (`id_social_bar`)
    // );
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'social_bar` (
    `id_social_bar` int(11) NOT NULL AUTO_INCREMENT,
    `facebook_url` TEXT,
    `youtube_url` TEXT,
    `instagram_url` TEXT,
    PRIMARY KEY  (`id_social_bar`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
$sql[] = 'INSERT INTO `'. _DB_PREFIX_ .'social_bar` VALUES (\'1\',\'https://facebook.com\',\'https://youtube.com\', \'https://instagram.com\')';
foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
