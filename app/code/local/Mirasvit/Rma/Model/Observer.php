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


class Mirasvit_Rma_Model_Observer extends Mage_Core_Model_Abstract
{
	public function onHelpdeskProcessEmail($observer)
	{
		$event = $observer->getEvent();
		$ticket = $event->getTicket();
		$text = $event->getBody();
		if (!$rmaId = $ticket->getRmaId()) {
			return;
		}
		$rma = Mage::getModel('rma/rma')->load($rmaId);
		if (!$rma->getId()) {
			return;
		}
		$rma->addComment($text, false, $customer, $user, true, true, true, true);
	}
}
