<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */  
class Amasty_Feed_Model_Writer_Xml extends Amasty_Feed_Model_Writer_Abstract
{
    function write(){
        
        if ($this->isFirstStep()){
            $this->header();
        }
        
        $ret = parent::write();
        
        if ($this->isLastStep()){
            $this->footer();
        }
        
        return $ret;
    }
    
    public function writeRecord($row)
    {
        $writes = array();
        $item = $this->_getFeed()->getXmlItem();
        $fields = $this->_getFeed()->getFields();
        foreach($this->_getFeed()->getLines2fields() as $lines2field){
            $write = $lines2field['tpl'];
            $skip = TRUE;

            if(isset($lines2field['vars'])){
                foreach($lines2field['vars'] as $varOrder => $var){
                    
                    $link = $lines2field['links'][$varOrder];
                    $optional = isset($fields['optional'][$link]) ? ($fields['optional'][$link] == 'yes') : FALSE;
                    $value = $row[$link];

                    $skip = $skip && $optional && $value == '';

                    if (!$skip)
                        $write = str_replace('{' . $var . '}', $value, $write);
                    else 
                        $write = '';
                }
                
                $writes[] = $write;
            }

        }

        $out = !empty($item) ?
                "<" . $item . ">" . implode('', $writes) . "</" . $item . ">" :
                implode('', $writes);    

        fwrite($this->fp, $out);
    }
    
    public function header(){
        fwrite($this->fp, $this->_getFeed()->getXmlHeader());
    }
    
    public function footer(){
        fwrite($this->fp, $this->_getFeed()->getXmlFooter());
    }
}