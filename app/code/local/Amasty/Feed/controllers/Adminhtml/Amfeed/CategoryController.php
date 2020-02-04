<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
class Amasty_Feed_Adminhtml_Amfeed_CategoryController extends Amasty_Feed_Controller_Abstract
{
    protected $_title     = 'Categories Mapping';
    protected $_modelName = 'category';

    protected function _afterSave($model)
    {
        $model->saveCategoriesMapping();
    }
}