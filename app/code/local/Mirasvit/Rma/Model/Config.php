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


class Mirasvit_Rma_Model_Config
{
    const FIELD_TYPE_TEXT = 'text';
    const FIELD_TYPE_TEXTAREA = 'textarea';
    const FIELD_TYPE_DATE = 'date';
    const FIELD_TYPE_CHECKBOX = 'checkbox';
    const FIELD_TYPE_SELECT = 'select';
    const IS_MANUAL_CREDITMEMO_1 = 1;
    const IS_MANUAL_CREDITMEMO_0 = 0;
    const IS_MANUAL_CREDITMEMO_2 = 2;

    const CUSTOMER = 1;
    const USER = 2;

    const COMMENT_PUBLIC = 'public';
    const COMMENT_INTERNAL = 'internal';

    const RMA_GRID_COLUMNS_INCREMENT_ID = 'increment_id';
    const RMA_GRID_COLUMNS_ORDER_INCREMENT_ID = 'order_increment_id';
    const RMA_GRID_COLUMNS_CUSTOMER_NAME = 'customer_name';
    const RMA_GRID_COLUMNS_USER_ID = 'user_id';
    const RMA_GRID_COLUMNS_LAST_REPLY_NAME = 'last_reply_name';
    const RMA_GRID_COLUMNS_STATUS_ID = 'status_id';
    const RMA_GRID_COLUMNS_STORE_ID = 'store_id';
    const RMA_GRID_COLUMNS_CREATED_AT = 'created_at';
    const RMA_GRID_COLUMNS_UPDATED_AT = 'updated_at';
    const RMA_GRID_COLUMNS_ACTION = 'action';


    public function getGeneralReturnAddress($store = null)
    {
        return Mage::getStoreConfig('rma/general/return_address', $store);
    }

    public function getGeneralDefaultStatus($store = null)
    {
        return Mage::getStoreConfig('rma/general/default_status', $store);
    }

    public function getGeneralDefaultUser($store = null)
    {
        return Mage::getStoreConfig('rma/general/default_user', $store);
    }

    public function getGeneralIsRequireShippingConfirmation($store = null)
    {
        return Mage::getStoreConfig('rma/general/is_require_shipping_confirmation', $store);
    }

    public function getGeneralShippingConfirmationText($store = null)
    {
        return Mage::getStoreConfig('rma/general/shipping_confirmation_text', $store);
    }

    public function getGeneralIsGiftActive($store = null)
    {
        return Mage::getStoreConfig('rma/general/is_gift_active', $store);
    }

    public function getGeneralIsHelpdeskActive($store = null)
    {
        return Mage::getStoreConfig('rma/general/is_helpdesk_active', $store);
    }

    public function getGeneralIsManualCreditmemo($store = null)
    {
        return Mage::getStoreConfig('rma/general/is_manual_creditmemo', $store);
    }

    public function getGeneralCreditMemoAdjustmentFee($store = null)
    {
        return Mage::getStoreConfig('rma/general/credit_memo_adjustment_fee', $store);
    }

    public function getGeneralIsRefundShippingFee($store = null)
    {
        return Mage::getStoreConfig('rma/general/is_refund_shipping_fee', $store);
    }

    public function getGeneralIsOnlineRefund($store = null)
    {
        return Mage::getStoreConfig('rma/general/is_online_refund', $store);
    }

    public function getGeneralIsSendExchangeOrderConfirmation($store = null)
    {
        return Mage::getStoreConfig('rma/general/is_send_exchange_order_confirmation', $store);
    }

    public function getGeneralBrandAttribute($store = null)
    {
        return Mage::getStoreConfig('rma/general/brand_attribute', $store);
    }

    public function getGeneralFileAllowedExtensions($store = null)
    {
        if (!$extensions = Mage::getStoreConfig('rma/general/file_allowed_extensions', $store)) {
            return array();
        }
        $extensions = explode(',', $extensions);
        $extensions = array_map('trim', $extensions);
        return $extensions;
    }

    public function getGeneralFileSizeLimit($store = null)
    {
        return Mage::getStoreConfig('rma/general/file_size_limit', $store);
    }

    public function getGeneralRmaGridColumns($store = null)
    {
        $value = Mage::getStoreConfig('rma/general/rma_grid_columns', $store);
        return explode(',', $value);
    }

    public function getFrontendIsActive($store = null)
    {
        return Mage::getStoreConfig('rma/frontend/is_active', $store);
    }

    public function getPolicyReturnPeriod($store = null)
    {
        return Mage::getStoreConfig('rma/policy/return_period', $store);
    }

    public function getPolicyAllowInStatuses($store = null)
    {
        $value = Mage::getStoreConfig('rma/policy/allow_in_statuses', $store);
        return explode(',', $value);
    }

    public function getPolicyIsActive($store = null)
    {
        return Mage::getStoreConfig('rma/policy/is_active', $store);
    }

    public function getPolicyPolicyBlock($store = null)
    {
        return Mage::getStoreConfig('rma/policy/policy_block', $store);
    }

    public function getNumberFormat($store = null)
    {
        return Mage::getStoreConfig('rma/number/format', $store);
    }

    public function getNumberCounterStart($store = null)
    {
        return Mage::getStoreConfig('rma/number/counter_start', $store);
    }

    public function getNumberCounterStep($store = null)
    {
        return Mage::getStoreConfig('rma/number/counter_step', $store);
    }

    public function getNumberCounterLength($store = null)
    {
        return Mage::getStoreConfig('rma/number/counter_length', $store);
    }

    public function getNotificationSenderEmail($store = null)
    {
        return Mage::getStoreConfig('rma/notification/sender_email', $store);
    }

    public function getNotificationCustomerEmailTemplate($store = null)
    {
        return Mage::getStoreConfig('rma/notification/customer_email_template', $store);
    }

    public function getNotificationAdminEmailTemplate($store = null)
    {
        return Mage::getStoreConfig('rma/notification/admin_email_template', $store);
    }


    /************************/


    public function isActiveHelpdesk()
    {
        if ($this->getGeneralIsHelpdeskActive() && Mage::helper('mstcore')->isModuleInstalled('Mirasvit_Helpdesk')) {
            return true;
        }
    }
}