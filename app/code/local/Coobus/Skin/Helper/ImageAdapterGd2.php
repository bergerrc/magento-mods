<?php

class Coobus_Skin_Helper_ImageAdapterGd2 extends Varien_Image_Adapter_Gd2
{
    public function cropArea($x=0, $y=0, $w=0, $h=0, $offSetX=0, $offSetY=0)
    {
        if( $x == 0 && $y == 0 && $w == 0 && $h == 0 ) {
            return;
        }

        $canvas = imagecreatetruecolor($w, $h);

        if ($this->_fileType == IMAGETYPE_PNG) {
            $this->_saveAlpha($canvas);
        }

		imagecopy($canvas, $this->_imageHandler, $offSetX, $offSetY, $x, $y, $this->_imageSrcWidth, $this->_imageSrcHeight);

        $this->_imageHandler = $canvas;
        $this->refreshImageDimensions();
    }
    
    private function refreshImageDimensions()
    {
        $this->_imageSrcWidth = imagesx($this->_imageHandler);
        $this->_imageSrcHeight = imagesy($this->_imageHandler);
    }
    
    /*
     * Fixes saving PNG alpha channel
     */
    private function _saveAlpha($imageHandler)
    {
        $background = imagecolorallocate($imageHandler, 0, 0, 0);
        ImageColorTransparent($imageHandler, $background);
        imagealphablending($imageHandler, false);
        imagesavealpha($imageHandler, true);
    }
}
