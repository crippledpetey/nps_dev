<?php

class Bc_Manufacturer_Adminhtml_ManufacturerController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('manufacturer/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Manufacturer Manager'), Mage::helper('adminhtml')->__('Manufacturer Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('manufacturer/manufacturer')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('manufacturer_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('manufacturer/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Manufacturer Manager'), Mage::helper('adminhtml')->__('Manufacturer Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Manufacturer News'), Mage::helper('adminhtml')->__('Manufacturer News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('manufacturer/adminhtml_manufacturer_edit'))
				->_addLeft($this->getLayout()->createBlock('manufacturer/adminhtml_manufacturer_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('manufacturer')->__('Manufacturer does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
        $data = $this->getRequest()->getPost();
        //DebugBreak();
        //If the manufacturer already exits or not
        $collection=Mage::getModel('manufacturer/manufacturer')->getCollection()->addFieldToFilter('menufecturer_name',$data['menufecturer_name']);
        $manufacturer_data=$collection->getData();
        if(count($collection)>0 && $manufacturer_data[0]['status']==1 && $this->getRequest()->getParam('id')!=$manufacturer_data[0]['manufacturer_id']){
            $this->_forward('edit');
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Manufacturer Already Exists'));
          
          //Mage::getSingleton('adminhtml/session')->setFormData(false);
        }else{
		if ($data) {
			if(isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
				try {	
					/* Starting upload */	
					$uploader = new Varien_File_Uploader('filename');
					
					// Any extention would work
	           		$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(false);
					
					// Set the file upload mode 
					// false -> get the file directly in the specified folder
					// true -> get the file in the product like folders 
					//	(file.jpg will go in something like /media/f/i/file.jpg)
					$uploader->setFilesDispersion(false);
							
					// We set media/Manufacturer as the upload dir
					$path = Mage::getBaseDir('media') . DS ."Manufacturer". DS ;
					$uploader->save($path, str_replace(" ","_",$_FILES['filename']['name']));
                    
				} catch (Exception $e) {
		      
		        }
	              
                  //If the uploaded file is not image it will mnot allow to save manufacturer
                  $fileName=$_FILES['filename']['name'];
                  $fileName=explode(".", $fileName);
		        //this way the name is saved in DB
                try{
                        if($uploader->chechAllowedExtension($fileName[1]))
	  			            $data['filename'] = str_replace(" ","_",$_FILES['filename']['name']);
                        else{
                                Mage::getSingleton('adminhtml/session')->addError("Upload Image Files Only");
                                Mage::getSingleton('adminhtml/session')->setFormData($data);
                                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                                return;
                            }
                }catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
                
			}
	  			
	  			
			$model = Mage::getModel('manufacturer/manufacturer');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	
				
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('manufacturer')->__('Manufacturer was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('manufacturer')->__('Unable to find manufacturer to save'));
        $this->_redirect('*/*/');
        }
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('manufacturer/manufacturer');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Manufacturer was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $manufacturerIds = $this->getRequest()->getParam('manufacturer');
        if(!is_array($manufacturerIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($manufacturerIds as $manufacturerId) {
                    $manufacturer = Mage::getModel('manufacturer/manufacturer')->load($manufacturerId);
                    $manufacturer->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($manufacturerIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $manufacturerIds = $this->getRequest()->getParam('manufacturer');
        if(!is_array($manufacturerIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($manufacturerIds as $manufacturerId) {
                    $manufacturer = Mage::getSingleton('manufacturer/manufacturer')
                        ->load($manufacturerId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($manufacturerIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'manufacturer.csv';
        $content    = $this->getLayout()->createBlock('manufacturer/adminhtml_manufacturer_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'manufacturer.xml';
        $content    = $this->getLayout()->createBlock('manufacturer/adminhtml_manufacturer_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}
