<?php

class Coobus_Catalog_IndexController extends Mage_Core_Controller_Front_Action
{

	public function indexAction()
	{
		$template = Mage::getConfig()->getNode('global/page/layouts/'.Mage::getStoreConfig("catalog/featuredproducts/layout").'/template');
		
		$this->loadLayout();		

		$this->getLayout()->getBlock('root')->setTemplate($template);
		$this->getLayout()->getBlock('head')->setTitle($this->__(Mage::getStoreConfig("catalog/featuredproducts/meta_title")));
		$this->getLayout()->getBlock('head')->setDescription($this->__(Mage::getStoreConfig("catalog/featuredproducts/meta_description")));
		$this->getLayout()->getBlock('head')->setKeywords($this->__(Mage::getStoreConfig("catalog/featuredproducts/meta_keywords")));
		
		$this->renderLayout();
	}
	
	public function featuredAction()
	{
		$block = $this->getLayout()->createBlock('coobus_catalog/product_featured');
		$block->setTemplate("coobus/catalog/product/grid.phtml");
		$block->setTitle(__("home.product.featured"));
		$this->getResponse()->setBody($block->toHtml());
	}
	
	public function newestAction()
	{
		$block = $this->getLayout()->createBlock('coobus_catalog/product_new');
		$block->setTemplate("coobus/catalog/product/grid.phtml");
		$block->setTitle(__("home.product.newest"));
		$this->getResponse()->setBody($block->toHtml());
	}
	
	public function allAction()
	{
		$block = $this->getLayout()->createBlock('coobus_catalog/product_all');
		$block->setTemplate("coobus/catalog/product/grid.phtml");
		$block->setTitle(__("home.product.all"));
		$this->getResponse()->setBody($block->toHtml());
	}
	
	public function mostviewedAction()
	{
		$block = $this->getLayout()->createBlock('coobus_catalog/product_mostviewed');
		$block->setTemplate("coobus/catalog/product/grid.phtml");
		$block->setTitle(__("home.product.mostviewed"));
		$this->getResponse()->setBody($block->toHtml());
	}

	
	public function tabAction()
	{
		$this->loadLayout();
		$p = $this->getRequest()->getParam('tabs', false);
		$t = $this->getRequest()->getParam('active', false);

		$block = $this->getLayout()->getBlock($p? $p: 'home_tabs');
		$block->setActiveTab( $t );
		$this->getResponse()->setBody($block->toHtml());
	}
}