<?php

class Aurora_Deletecustomer_Block_Deletecustomer extends Mage_Core_Block_Template {
    public function getBackUrl() {        
        // the RefererUrl must be set in appropriate controller
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        
        if ($_SERVER['HTTP_REFERER'] !== NULL && $_SERVER['HTTP_REFERER'] != '') {
            return $_SERVER['HTTP_REFERER'];            
        }        
        
        return $this->getUrl('customer/account/');
    }

}
