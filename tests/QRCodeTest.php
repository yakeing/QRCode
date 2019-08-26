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
    $stream = true;
    $spec = 10;
    $ret = QrCode::image($text, $pixel, $icon, $distinguish, $type, $margin, $color, $spec, $stream);
    $this->assertTrue(is_array($ret));
    $text = 1234567890;
    $ret2 = QrCode::image($text, $pixel, $icon, $distinguish, $type, $margin, $color, $spec, $stream);
    $this->assertTrue(is_array($ret2));
  }
}
