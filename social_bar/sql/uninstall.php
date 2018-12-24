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
$sql[] = 'DROP TABLE `' . _DB_PREFIX_ . 'social_bar`';
foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
