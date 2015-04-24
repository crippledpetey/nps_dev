<?php
/**
 * Our class name should follow the directory structure of
 * our Observer.php model, starting from the namespace,
 * replacing directory separators with underscores.
 * i.e. app/code/local/SmashingMagazine/
 *                     LogProductUpdate/Model/Observer.php
 */
class NPS_PurchaseOrderManagement_Model_Observer
{
    /**
     * Magento passes a Varien_Event_Observer object as
     * the first parameter of dispatched events.
     */
    public function logUpdate(Varien_Event_Observer $observer)
    {
        // Retrieve the product being updated from the event observer
        $product = $observer->getEvent()->getProduct();

        // Write a new line to var/log/product-updates.log
        $name = $product->getName();
        $sku = $product->getSku();
        Mage::log(
            "{$name} ({$sku}) updated",
            null,
            'product-updates.log'
        );
    }
}