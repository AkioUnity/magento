<?php

class MagicToolbox_MagicZoom_Model_Resource_Setup extends Mage_Eav_Model_Entity_Setup
{

    /**
     * Default entites and attributes
     *
     * @return array
     */
    public function getDefaultEntities()
    {
        return array(
            'catalog_product'               => array(
                'entity_model'                  => 'catalog/product',
                'attribute_model'               => 'catalog/resource_eav_attribute',
                'table'                         => 'catalog/product',
                'additional_attribute_table'    => 'catalog/eav_attribute',
                'entity_attribute_collection'   => 'catalog/product_attribute_collection',
                'default_group'                 => 'General',
                'attributes'                    => array(
                    'product_videos'                => array(
                        'group'                         => 'General',
                        'type'                          => 'text',
                        'backend'                       => '',
                        'frontend'                      => '',
                        'label'                         => 'Product Videos',
                        'input'                         => 'hidden',
                        'class'                         => '',
                        'global'                        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'note'                          => 'Provide links to video separated by a space or new line',
                        'visible'                       => true,
                        'required'                      => false,
                        'user_defined'                  => false,
                        'searchable'                    => false,
                        'filterable'                    => false,
                        'comparable'                    => false,
                        'visible_on_front'              => false,
                        'unique'                        => false,
                        'apply_to'                      => '',
                        'is_configurable'               => false,
                        'attribute_set'                 => 'Default',
                        'system'                        => true,
                        'default'                       => '',
                        'sort_order'                    => 13,
                        'wysiwyg_enabled'               => false,
                        'is_html_allowed_on_front'      => false,
                        'visible_in_advanced_search'    => true,
                        'used_in_product_listing'       => false,
                        'used_for_sort_by'              => false,
                    ),
                ),
            ),
        );
    }
}
