<?php
namespace QRCodeTest;
use qr_code\QrCode;
use PHPUnit\Framework\TestCase;
class QRCodeTest extends TestCase{
  public function testQRCode(){
    $text = 'Test';
    $pixel = 32;
    $icon = false;
    $distinguish ='L';
    $type = 'png';
    $margin = 0;
    $color = 'FF0000|000000';
    $spec = 10;
    $str = QrCode::image($text, $pixel, $icon, $distinguish, $type, $margin, $color, $spec, true);
    $this->assertTrue(is_array($str));
    $text = 1234567890;
    $distinguish ='H';
    $imag = QrCode::image($text, $pixel, $icon, $distinguish, $type, $margin, $color, $spec, false);
    var_dump($imag);
    //$this->assertTrue(is_string($imag));
  }
}
