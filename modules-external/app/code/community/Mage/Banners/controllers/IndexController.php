<?php
class Mage_Banners_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {	
		$this->loadLayout();  
		$this->renderLayout();
		//$this->_forward('data');
    }
    
	public function dataAction()
	{
		$this->loadLayout();  
        $b = $this->getLayout()->createBlock('banners/content')->setTemplate('banners/data.phtml');
        $this->getLayout()->setBlock("root", $b);
		$this->renderLayout();
	}
}