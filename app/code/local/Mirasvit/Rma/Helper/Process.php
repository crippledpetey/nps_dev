<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   RMA
 * @version   1.0.9
 * @build     742
 * @copyright Copyright (C) 2015 Mirasvit (http://mirasvit.com/)
 */


class Mirasvit_Rma_Helper_Process extends Mage_Core_Helper_Abstract
{

    public function getConfig()
    {
        return Mage::getSingleton('rma/config');
    }

    /**
    * save function for backend
    */
    public function createOrUpdateRmaFromPost($data, $items)
    {
        $rma = Mage::getModel('rma/rma');
        if (isset($data['rma_id']) && $data['rma_id']) {
            $rma->load((int)$data['rma_id']);
            $rmaIsNew = false;
        } else {
            unset($data['rma_id']);
            $rmaIsNew = true;
        }
        if ($data['street2'] != '') {
            $data['street'] .= "\n". $data['street2'];
            unset($data['street2']);
        }

        $order = Mage::getModel('sales/order')->load((int)$data['order_id']);
        $rma->addData($data);
        $rma->setCustomerId($order->getCustomerId());
        $rma->setStoreId($order->getStoreId());
        if (!$rma->getUserId()) {
            if ($user = Mage::getSingleton('admin/session')->getUser()) {
                $rma->setUserId($user->getId());
            }
        }
        $rma->save();
        Mage::helper('mstcore/attachment')->saveAttachment('rma_return_label', $rma->getId(), 'return_label');

        foreach ($items as $item) {
            // if ((int)$item['qty_requested'] == 0) {
            //     continue;
            // }
            $rmaItem = Mage::getModel('rma/item');
            if (isset($item['item_id']) && $item['item_id']) {
                $rmaItem->load((int)$item['item_id']);
            } else {
                unset($item['item_id']);
            }
            if (!(int)$item['reason_id']) {
                unset($item['reason_id']);
            }
            if (!(int)$item['resolution_id']) {
                unset($item['resolution_id']);
            }
            if (!(int)$item['condition_id']) {
                unset($item['condition_id']);
            }
            $rmaItem->addData($item)
                    ->setRmaId($rma->getId());
            $orderItem = Mage::getModel('sales/order_item')->load((int)$item['order_item_id']);
            $rmaItem->initFromOrderItem($orderItem);
            $rmaItem->save();
        }

        if ($rmaIsNew && $rma->getTicketId()) {
            $this->closeTicketByRma($rma);
        }

        if ((isset($data['reply']) && trim($data['reply']) != '')
        || Mage::helper('mstcore/attachment')->hasAttachments()){
            $isNotify = $isVisible = true;
            if ($data['reply_type'] == 'internal') {
                $isNotify = $isVisible = false;
            }
            $user = Mage::getSingleton('admin/session')->getUser();
            $rma->addComment(trim($data['reply']), false, false, $user, $isNotify, $isVisible);
        }

        Mage::helper('rma/process')->notifyRmaChange($rma);
        return $rma;
    }

    /**
    * save function for frontend
    */
    public function createRmaFromPost($data, $items, $customer = false)
    {
        $order = Mage::getModel('sales/order')->load((int)$data['order_id']);
        if ($customer && $order->getCustomerId() != $customer->getId()) {
            throw new Exception("Error Processing Request 1");
        }

        $address = $order->getShippingAddress();
        if (!$address) {
            $address = $order->getBillingAddress();
        }

        $rma = Mage::getModel('rma/rma');
        $rma->addData($data)
            ->setStoreId($order->getStoreId())
            ->setEmail($order->getCustomerEmail())
            ->setFirstname($address->getFirstname())
            ->setLastname($address->getLastname())
            ->setCompany($address->getCompany())
            ->setTelephone($address->getTelephone())

            ->setStreet(implode("\n", $address->getStreet()))
            ->setCity($address->getCity())
            ->setCountryId($address->getCountryId())
            ->setRegionId($address->getRegionId())
            ->setRegion($address->getRegion())

            ;
        if (isset($data['is_gift'])) {
            $rma->addData($data['gift']);
            $rma->setIsGift(true);
        }
        if ($order->getCustomerId()) {
            $rma->setCustomerId($order->getCustomerId());
        }

        $rma->save();
        $collection = $order->getItemsCollection();

        foreach ($collection as $orderItem) {
            $rmaItem = Mage::getModel('rma/item');
            $rmaItem->setRmaId($rma->getId());
            foreach ($items as $k => $item) {
                if ((int)$k == $orderItem->getId()) {
                    $rmaItem->addData($item);
                    $rmaItem->setOrderItemId((int)$k);
                    break;
                }
            }
            //if customer does not want to return the item
            //we add it to RMA for ability to add latter
            if (!$rmaItem->getIsReturn()) {
                $rmaItem->setQtyRequested(0);
            }

            $rmaItem->initFromOrderItem($orderItem);
            $rmaItem->save();
        }

        Mage::helper('rma/process')->notifyRmaChange($rma);
        if ($data['comment'] != '') {
            $rma->addComment($data['comment'], false, $rma->getCustomer(), false, false, true, true);
        }
        return $rma;
    }

    /**
    * save comment function for frontend
    */
    public function createCommentFromPost($rma, $post)
    {
        $comment = false;
        if (isset($post['comment'])) {
            $comment = $post['comment'];
        }
        unset($post['id']);
        unset($post['comment']);
        $fields = array();
        foreach ($post as $code => $value) {
            if (!$value) {
                continue;
            }
            $field = Mage::getModel('rma/field')->getCollection()
                        ->addFieldToFilter('code', $code)
                        ->getFirstItem();
            if ($field->getId()) {
                $fields[] = "{$field->getName()}: {$value}";
                $rma->setData($code, $value);
            }
        }
        if (count($fields)) {
            if ($comment) {
                $comment .= "\n";
            }
            $comment .= implode("\n", $fields);
        }
        if (trim($comment) == '' && !Mage::helper('mstcore/attachment')->hasAttachments()
            && !isset($post['shipping_confirmation'])) {
            throw new Mage_Core_Exception(Mage::helper('rma')->__('Please, post not empty message'));
        }
        if (trim($comment) != '') {
            $rma->addComment($comment, false, $rma->getCustomer(), false, false, true);
        }
    }

    public function notifyRmaChange($rma)
    {
        if ($rma->getStatusId() != $rma->getOrigData('status_id')) {
            Mage::app()->setCurrentStore($rma->getStoreId());

            $status = $rma->getStatus();

            if ($message = $status->getCustomerMessage()) {
                $message = Mage::helper('rma/mail')->parseVariables($message, $rma);
                Mage::helper('rma/mail')->sendNotificationCustomerEmail($rma, $message);
            }

            if ($message = $status->getAdminMessage()) {
                $message = Mage::helper('rma/mail')->parseVariables($message, $rma);
                Mage::helper('rma/mail')->sendNotificationAdminEmail($rma, $message);
            }

            if ($message = $status->getHistoryMessage()) {
                $message = Mage::helper('rma/mail')->parseVariables($message, $rma);
                $isNotified = $status->getCustomerMessage() != '';
                $rma->addComment($message, true, false, false, $isNotified, true);
            }
            if($status->getCustomerMessage() || $status->getHistoryMessage()) {
                if ($rma->getUser()) {
                    $rma->setLastReplyName($rma->getUser()->getName())
                        ->save();
                }
            }

        } elseif ($rma->getUserId() != $rma->getOrigData('user_id')) {
            $status = $rma->getStatus();
            $message = $status->getAdminMessage();
            $message = Mage::helper('rma/mail')->parseVariables($message, $rma);
            Mage::helper('rma/mail')->sendNotificationAdminEmail($rma, $message);
        }
    }

    public function processEmail($email, $code)
    {
        $rma = false;
        $customer = false;
        $user = false;
        $triggeredByCustomer = true;

        // если у нас есть код, то ок
        // если кода нет, то такую ситуцию мы не обрабатываем

        $guestId = str_replace('RMA-', '', $code);
        //try to find RMA for this email
        $rmas = Mage::getModel('rma/rma')->getCollection()
                    ->addFieldToFilter('guest_id', $guestId)
                    ;
        if (!$rmas->count()) {
            echo 'Can\'t find a RMA by guest id '.$guestId;
           return false;
        }

        $rma = $rmas->getFirstItem();

        //try to find staff user for this email
        $users = Mage::getModel('admin/user')->getCollection()
            ->addFieldToFilter('email', $email->getFromEmail());
        if ($users->count()) {
            $user = $users->getFirstItem();
            $triggeredByCustomer = false;
            $rma->setUserId($user->getId());
            $rma->save();
        } else {
            $customers = Mage::getModel('customer/customer')->getCollection()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('email', $email->getFromEmail());
            if ($customers->count()) {
                $customer = $customers->getLastItem(); //если мы можем найти кастомера по емейлу - ОК
            } else { //если кастомер ответил с другого емейла или это гость - создаем его временно
                $customer = new Varien_Object();
                $customer->setName($email->getSenderName());
                $customer->setEmail($email->getFromEmail());
            }
        }

        //add message to rma
        $body = Mage::helper('helpdesk/string')->parseBody($email->getBody(), $email->getFormat());
        $message = $rma->addComment($body, false, $customer, $user, true, true, true, $email);
        return $rma;
   }

   public function closeTicketByRma($rma)
   {
       $ticket = Mage::getModel('helpdesk/ticket')->load($rma->getTicketId());
       $ticket->addMessage($this->__("Ticket was converted to the RMA #%s", $rma->getIncrementId()), false, $rma->getUser(), Mirasvit_Helpdesk_Model_Config::USER, Mirasvit_Helpdesk_Model_Config::MESSAGE_INTERNAL);
       $ticket->close();
   }
}