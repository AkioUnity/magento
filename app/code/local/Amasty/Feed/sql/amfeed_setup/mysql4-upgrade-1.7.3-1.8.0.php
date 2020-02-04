<?php
    /**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */
    $installer = $this;
    $installer->startSetup();
    
    function csvConvert($csv, $data, $relations, $default = NULL){
       
        $ret = array();
        
        foreach($csv['name'] as $order => $attribute){
            if (isset($data[$order]) && isset($relations[$data[$order]])){
                
                $ret[$order] = $relations[$data[$order]];
                
            } else {
                $ret[$order] = $default;
            }
        }
        
        return $ret;
    }
    
    $attributesRel = array(
        0 => 'attribute',
        1 => 'custom_field',
        2 => 'text',
        3 => 'meta_tags',
        4 => 'images',
        5 => 'parent_attribute'
    );
    
    $foramtRel = array(
        0 => 'as_is',
        1 => 'strip_tags',
        2 => 'html_escape',
        3 => 'date',
        4 => 'price',
        5 => 'lowercase',
        6 => 'integer'
    );

    $profilesData = $installer->getConnection()->fetchAll("
        SELECT
            feed_id, xml_body, csv
        FROM
            {$installer->getTable('amfeed/profile')}
    ");
    
    $helper = Mage::helper('amfeed');
    
    foreach ($profilesData as $profilesItem){
        
        $feed_id = $profilesItem['feed_id'];
        
        $pathXML = $helper->getDownloadPath('feeds', $feed_id.'_backupXML_1.8.0');
        $pathCSV = $helper->getDownloadPath('feeds', $feed_id.'_backupCSV_1.8.0');

        $csv = $profilesItem['csv'];
        $xml = $profilesItem['xml_body'];
        
        
        file_put_contents($pathCSV, $csv);
        file_put_contents($pathXML, $xml);
        
        
        $csv = unserialize($csv);
        

        if ($csv){
            
            if (!isset($csv['type'][0]) || !is_numeric($csv['type'][0]))
                continue;
            
            $type = csvConvert($csv, $csv['type'], $attributesRel, 'attribute');
            $format = csvConvert($csv, $csv['format'], $foramtRel, 'as_is');
            $parent = array();
            $name = $csv['name'];

            foreach($type as $order => $t){
                if ($t == 'parent_attribute'){


                    $type[$order] = 'attribute';
                    $parent[$order] = 'yes';
                    $name[$order] = $csv['parent_attribute'][$order];
                } else {
                    $parent[$order] = 'no';
                }
            }

            $csv['type'] = $type;
            $csv['format'] = $format;
            $csv['parent'] = $parent;
            $csv['name'] = $name;
        }
        
        if (!empty($xml)){
            $lines = explode("\n", $xml);
    
            $attributesRel = array(
                0 => 'attribute',
                1 => 'custom_field',
                2 => 'text',
                3 => 'meta_tags',
                4 => 'images',
                5 => 'parent_attribute'
            );

            $foramtRel = array(
                0 => 'as_is',
                1 => 'strip_tags',
                2 => 'html_escape',
                3 => 'date',
                4 => 'price',
                5 => 'lowercase',
                6 => 'integer'
            );
//            print_r($xml);
//                        exit(1);
            foreach ($lines as $key => $line) {
                $regex = "#{(.*?)}#";

                preg_match($regex, $line, $vars);
                
                if (isset($vars[1])){
                    $params = explode('|', $vars[1]);
                    
                    if (!is_numeric($params[0]))
                        continue;
                    
                    $type = isset($params[0]) ? $attributesRel[$params[0]] : 'attribute';


                    $plTpl = '{type=":insert_type" value=":value" format=":insert_format" length=":insert_length" optional=":insert_optional" parent=":parent"}';

                    $repl = array(
                        ':insert_type' => $type == 'parent_attribute' ? 'attribute' : $type,
                        ':value' =>  $params[1],
                        ':insert_format' => isset($params[2]) && isset($foramtRel[$params[2]]) ? $foramtRel[$params[2]] : 'as_is',
                        ':insert_length' => isset($params[3]) ? $params[3] : '', 
                        ':insert_optional' => isset($params[4]) && $params[4] == 1 ? 'yes' : 'no',
                        ':parent' => $type == 'parent_attribute' ? 'yes' : 'no',
                    );

                    $placeholder = strtr($plTpl, $repl);

                    $lines[$key] = str_replace($vars[0], $placeholder, $lines[$key]);
                }
            }
            
            $xml = implode("\n", $lines);
            
            
        }
        
        $query = "
                UPDATE `{$installer->getTable('amfeed/profile')}`
                    SET `csv` = '" . serialize($csv) . "', `xml_body` = '" . $xml . "'
                    WHERE `feed_id` = $feed_id;
            ";


        $installer->run($query);
        
       

    }

    //$installer->run("
    //            ALTER TABLE `{$this->getTable('amfeed/profile')}` 
    //            ADD COLUMN `frm_dont_use_category_in_url` TINYINT(1) NOT NULL DEFAULT '0' after frm_image_url;
    //");
    $installer->endSetup();