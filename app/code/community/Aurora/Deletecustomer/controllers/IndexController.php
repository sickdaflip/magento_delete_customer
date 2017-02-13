<?php

class Aurora_Deletecustomer_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->loadLayout();
            $this->_initLayoutMessages('core/session');
            $this->renderLayout();
            return;
        } else {
            $this->_forward('defaultNoRoute'); // 404 redirect
            return;
        }
    }

    public function sendConfirmationMailAction() {
        $session = Mage::getSingleton('core/session');
        $generalStoreEmail = Mage::getStoreConfig('trans_email/ident_general/email');
        $storeOwner = Mage::getStoreConfig('trans_email/ident_general/name');

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            if ($generalStoreEmail !== NULL && $generalStoreEmail != '') {
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                $resetLink = Mage::getUrl('*/*/deleteCustomerAccount', array('id' => $customer->getId(), 'key' => sha1($customer->getFirstName() . $customer->getEmail() . '#$#(Ddfba*)@!.qpromd;')));

                $sender = Array(
                    'name' => $storeOwner,
                    'email' => $generalStoreEmail);
                $receiver = Array(
                    'name' => $customer->getFirstName() . $customer->getLastName(),
                    'email' => $customer->getEmail());

                $recepientEmail = $customer->getEmail();
                $recepientName = $customer->getName();

                // Get Store ID
                $storeId = Mage::app()->getStore()->getId();
                $name = Mage::app()->getStore()->getFrontendName();

                /* @var $mailTemplate Mage_Core_Model_Email_Template */
                $emailTemplate = Mage::getModel('core/email_template');
                $emailTemplate->setDesignConfig(array('area' => 'frontend'))
                        ->setReplyTo(trim($receiver['email']))
                        ->sendTransactional('deletecustomer_email_template', $sender, $recepientEmail, $recepientName, array('resetlink' => $resetLink, 'customer' => $customer, 'storename' => $name), $storeId);
            }

            if (!$emailTemplate->getSentSuccess()) {
                $session->addError($this->__('Server encountered an unexpected error while sending email to the admin.'));
            } else {
                $session->addSuccess($this->__('To complete Account delete proccess, please click on the link we ve just sent on your e-mail.'));
            }

            $this->_redirect('*/*/index');
            return;
        } else {
            $this->_forward('defaultNoRoute'); // 404 redirect
            return;
        }
    }

    public function deleteCustomerAccountAction() {
        $session = Mage::getSingleton('core/session');
        $customerId = $this->getRequest()->getParam('id');
        $secretKey = $this->getRequest()->getParam('key');

        if (!empty($customerId) && !empty($secretKey)) {
            try {
                $customer = Mage::getModel('customer/customer')->load((int) $customerId);
                if ((sha1($customer->getFirstName() . $customer->getEmail() . '#$#(Ddfba*)@!.qpromd;') === $secretKey) && ($customer->getId() !== NULL)
                ) {
                    Mage::register('isSecureArea', true); // by default is not possible delete customer from frontend                
                    $customer->delete();
                    Mage::unregister('isSecureArea');
                    $session->addSuccess($this->__('Your Account has been deleted.'));
                    $this->_redirect('/'); // go to homepage
                    return;
                } else {
                    $this->_forward('defaultNoRoute'); // 404 redirect
                    return;
                }
            } catch (Exception $e) {
                $session->addError($this->__('Unsuspected error occured.'));
            }

            $this->_redirect('*/*/index');
            return;
        } else {
            $this->_forward('defaultNoRoute'); // 404 redirect
            return;
        }
    }

    public function defaultNoRouteAction() {
        $this->getResponse()->setHeader('HTTP/1.1', '404 Not Found');
        $this->getResponse()->setHeader('Status', '404 File not found');

        $pageId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_NO_ROUTE_PAGE);
        if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
            $this->_forward('defaultNoRoute');
        }
    }

}
