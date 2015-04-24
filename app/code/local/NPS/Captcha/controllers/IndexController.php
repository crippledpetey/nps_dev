<?php
require_once Mage::getModuleDir('controllers', 'Mage_Contacts') . DS . 'IndexController.php';
class Amit_Captchaown_IndexController extends Mage_Contacts_IndexController {

	public function postAction() {
		$post = $this->getRequest()->getPost();
		if ($post) {
			$translate = Mage::getSingleton('core/translate');
/* @var $translate Mage_Core_Model_Translate */
			$translate->setTranslateInline(false);
			try {
				$postObject = new Varien_Object();
				$postObject->setData($post);

				$error = false;

				if (!Zend_Validate::is(trim($post['name']), 'NotEmpty')) {
					$error = true;
				}

				if (!Zend_Validate::is(trim($post['comment']), 'NotEmpty')) {
					$error = true;
				}

				if (!Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
					$error = true;
				}

				if (Zend_Validate::is(trim($post['hideit']), 'NotEmpty')) {
					$error = true;
				}

				$formId = 'contact_us';
				$captchaModel = Mage::helper('captcha')->getCaptcha($formId);
				if ($captchaModel->isRequired()) {
					if (!$captchaModel->isCorrect($this->_getCaptchaString($this->getRequest(), $formId))) {
						Mage::getSingleton('customer/session')->addError(Mage::helper('captcha')->__('Incorrect CAPTCHA.'));
						$this->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);

						Mage::getSingleton('customer/session')->setCustomerFormData($this->getRequest()->getPost());
						$this->getResponse()->setRedirect(Mage::getUrl('*/*/'));
						return;
					}
				}

				if ($error) {
					throw new Exception();
				}
				$mailTemplate = Mage::getModel('core/email_template');
/* @var $mailTemplate Mage_Core_Model_Email_Template */
				$mailTemplate->setDesignConfig(array('area' => 'frontend'))
				             ->setReplyTo($post['email'])
				             ->sendTransactional(
					             Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE),
					             Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
					             Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT),
					             null,
					             array('data' => $postObject)
				             );

				if (!$mailTemplate->getSentSuccess()) {
					throw new Exception();
				}

				$translate->setTranslateInline(true);

				Mage::getSingleton('customer/session')->addSuccess(Mage::helper('contacts')->__('Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.'));
				$this->_redirect('*/*/');

				return;
			} catch (Exception $e) {
				$translate->setTranslateInline(true);

				Mage::getSingleton('customer/session')->addError(Mage::helper('contacts')->__('Unable to submit your request. Please, try again later'));
				$this->_redirect('*/*/');
				return;
			}

		} else {
			$this->_redirect('*/*/');
		}
	}

	protected function _getCaptchaString($request, $formId) {
		$captchaParams = $request->getPost(Mage_Captcha_Helper_Data::INPUT_NAME_FIELD_VALUE);
		return $captchaParams[$formId];
	}

}