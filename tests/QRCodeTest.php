<?php
namespace QRCodeTest;
use qr_code\QrCode;
use PHPUnit\Framework\TestCase;
class QRCodeTest extends TestCase{
  public function testQRCode(){
    $text = 'QrCode';
    $pixel = 32;
    $icon = dirname(__FILE__).'/icon.jpg';
    $distinguish ='L';
    $type = 'png';
    $margin = 0;
    $color = 'Ff0f0F,#000000';
    $spec = 10;
    $str = QrCode::image($text, $pixel, $icon, $distinguish, $type, $margin, $color, $spec, array());
    $this->assertTrue(is_array($str));
    $text = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $imag = QrCode::image($text, $pixel, false, 'H', 'jpg', 2, array('235,00,100','68,200,90'), $spec, true);
    $this->assertTrue(is_resource($imag));
  }
}
