<?php
    /**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
    $installer = $this;
    $installer->startSetup();
    
    $installer->run("
                alter table `{$this->getTable('amfeed/profile')}` 
                add column `frm_price_dec_point` varchar(3) default '.' after frm_price,
                add column `frm_price_thousands_sep` varchar(3) default ',' after frm_price_dec_point;
    ");
    $installer->endSetup();
