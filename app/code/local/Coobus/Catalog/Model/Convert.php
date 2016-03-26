<?php

class Coobus_Catalog_Model_Convert extends Mage_Core_Model_Convert
{
	protected $_profileDefaultClass = 'Coobus_Catalog_Model_Convert_Profile';
	
	public function importProfileXml($name)
	{
		parent::importProfileXml($name);
		if (!$this->_xml) {
			return $this;
		}
		$nodes = $this->_xml->xpath("//profile[@name='".$name."']");
		if (!$nodes) {
			return $this;
		}
		$profileNode = $nodes[0];
		$profile = $this->getProfile($name);
		foreach ($profileNode->action as $actionNode) {
			$actAttr = $actionNode->attributes();
			foreach ( $profile->getActions() as $action){
		    	foreach ($actAttr as $key=>$value) 
                	$same = $action->getParam($key) == (string)$value;
		    	if ( $same ) break;
            }
            if ( !$same ) continue;
            if ($actionNode['use'])
                $container = $profile->getContainer((string)$actionNode['use']);
            else
                $container = $action->getContainer();
            $container = $actAttr['name']? $this->getContainer( (string)$actAttr['name']): $container;
			
			foreach ($actionNode->var as $key => $varNode) {
				if ($varNode['name'] == 'default') {
					$defData = array();
					foreach ($varNode->default as $defNode) {
						$defData[(string)$defNode['name']] = (string)$defNode;
					}
					$container->setVar((string)$varNode['name'], $defData);
				}
			}
		}

		return $this;
	}
}