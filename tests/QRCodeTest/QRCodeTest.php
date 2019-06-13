<?php
namespace QRCodeTest;
use QrCode;
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
    $ret = \QrCode::image($text, $pixel, $icon, $distinguish, $type, $margin, $color, $stream);

    $this->assertTrue(is_string($ret));
  }
}
