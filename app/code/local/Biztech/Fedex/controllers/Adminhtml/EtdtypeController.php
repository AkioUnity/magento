<?php
class Biztech_Fedex_Adminhtml_EtdtypeController extends Mage_Adminhtml_Controller_Action
{		

		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("fedex/etdtype")->_addBreadcrumb(Mage::helper("adminhtml")->__("ETD Type  Manager"),Mage::helper("adminhtml")->__("ETD Type  Manager"));
				return $this;
		}
		public function indexAction() 
		{
				
			    $this->_title($this->__("Fedex"));
			    $this->_title($this->__("ETD Type  Manager"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("Fedex"));
				$this->_title($this->__("ETD"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("fedex/etdtype")->load($id);
				if ($model->getId()) {
					Mage::register("etdtype_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("fedex/etdtype");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("ETD Manager"), Mage::helper("adminhtml")->__("ETD Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("ETD Description"), Mage::helper("adminhtml")->__("ETD Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("fedex/adminhtml_etdtype_edit"))->_addLeft($this->getLayout()->createBlock("fedex/adminhtml_etdtype_edit_tabs"));
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("fedex")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{

			$this->_title($this->__("Fedex"));
			$this->_title($this->__("ETD"));
			$this->_title($this->__("New Item"));

	        $id   = $this->getRequest()->getParam("id");
			$model  = Mage::getModel("fedex/etdtype")->load($id);

			$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register("etdtype_data", $model);

			$this->loadLayout();
			$this->_setActiveMenu("fedex/etdtype");

			$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

			$this->_addBreadcrumb(Mage::helper("adminhtml")->__("ETD Manager"), Mage::helper("adminhtml")->__("ETD Manager"));
			$this->_addBreadcrumb(Mage::helper("adminhtml")->__("ETD Description"), Mage::helper("adminhtml")->__("ETD Description"));


			$this->_addContent($this->getLayout()->createBlock("fedex/adminhtml_etdtype_edit"))->_addLeft($this->getLayout()->createBlock("fedex/adminhtml_etdtype_edit_tabs"));

			$this->renderLayout();

		}
		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();


				if ($post_data) {

					try {

						

						$model = Mage::getModel("fedex/etdtype")
						->addData($post_data)
						->setId($this->getRequest()->getParam("id"))
						->save();

						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("ETD was successfully saved"));
						Mage::getSingleton("adminhtml/session")->setEtdtypeData(false);

						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $model->getId()));
							return;
						}
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setEtdtypeData($this->getRequest()->getPost());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					return;
					}

				}
				$this->_redirect("*/*/");
		}



		public function deleteAction()
		{
				if( $this->getRequest()->getParam("id") > 0 ) {
					try {
						$model = Mage::getModel("fedex/etdtype");
						$model->setId($this->getRequest()->getParam("id"))->delete();
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
						$this->_redirect("*/*/");
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					}
				}
				$this->_redirect("*/*/");
		}

		
		public function massRemoveAction()
		{
			try {
				$ids = $this->getRequest()->getPost('etdtype_ids', array());
				foreach ($ids as $id) {
                      $model = Mage::getModel("fedex/etdtype");
					  $model->setId($id)->delete();
				}
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) was successfully removed"));
			}
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
			$this->_redirect('*/*/');
		}
		public function massCancelAction(){
		}
			
		/**
		 * Export order grid to CSV format
		 */
		public function exportCsvAction()
		{
			$fileName   = 'etdtype.csv';
			$grid       = $this->getLayout()->createBlock('fedex/adminhtml_etdtype_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
		} 
		/**
		 *  Export order grid to Excel XML format
		 */
		public function exportExcelAction()
		{
			$fileName   = 'etdtype.xml';
			$grid       = $this->getLayout()->createBlock('fedex/adminhtml_etdtype_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
		}
                
                protected function _isAllowed()
                {
                    return Mage::getSingleton('admin/session')->isAllowed('Fedex/fedex');
                }
}
