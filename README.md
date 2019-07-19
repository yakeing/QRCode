# QRCode
This is a class of php QR Code 

[中文文档](https://github.com/yakeing/QRCode/blob/master/ZH.README.md)

### Travis CI

[![Travis-ci](https://api.travis-ci.org/yakeing/QRCode.svg)](https://travis-ci.org/yakeing/QRCode)

### Packagist

[![Version](http://img.shields.io/packagist/v/yakeing/qrcode.svg)](https://github.com/yakeing/yakeing/qrcode/releases)
[![Downloads](http://img.shields.io/packagist/dt/yakeing/qrcode.svg)](https://packagist.org/packages/yakeing/qrcode)

### Github

[![Downloads](https://img.shields.io/github/downloads/yakeing/QRCode/total.svg)](https://github.com/yakeing/QRCode)
[![Size](https://img.shields.io/github/size/yakeing/QrCode/src/QrCode.php.svg)](https://github.com/yakeing/QRCode/blob/master/src/QrCode.php)
[![tag](https://img.shields.io/github/tag/yakeing/QRCode.svg)](https://github.com/yakeing/QRCode/releases)
[![Language](https://img.shields.io/github/license/yakeing/QRCode.svg)](https://github.com/yakeing/QRCode/blob/master/LICENSE)
[![Php](https://img.shields.io/github/languages/top/yakeing/QRCode.svg)](https://github.com/yakeing/QRCode)

### Installation

Use [Composer](https://getcomposer.org) to install the library.

```
    $ composer require yakeing/qrcode
```

### QRCode init

```php

    $text //Enter text (string)
    $pixel //Output image size (ini)
    $icon //Small icon (url) (Must be a square image, otherwise the image will be distorted)
    $distinguish //Recognition rate (L=7% , M=15% , Q=25% , H=30%)
    $type //Output image format (jpg/png) (Due to GIF copyright dispute)
    $margin //Margin white edge (ini 0-4)
    $color //RBG Colour array('255|255|255', '0|0|0'); Hexadecimal Colour FF0000|000000
    $stream //Output source code (true/false)
    $spec //specification Matrix with 0-40 specifications
    qrcode::image($text, $pixel, $icon, $distinguish, $type, $margin, $color, $stream);
```

Donate
---
Your donation makes CODE better.

 [Bitcoin](https://btc.com/1FYbZECgs3V3zRx6P7yAu2nCDXP2DHpwt8)

1FYbZECgs3V3zRx6P7yAu2nCDXP2DHpwt8

 ![Bitcoin](https://raw.githubusercontent.com/yakeing/Content/master/Donate/Bitcoin.png)

 WeChat

 ![WeChat](https://raw.githubusercontent.com/yakeing/Content/master/Donate/WeChat.png)

 Alipay

 ![Alipay](https://raw.githubusercontent.com/yakeing/Content/master/Donate/Alipay.png)

Author
---

weibo: [yakeing](https://weibo.com/yakeing)

twitter: [yakeing](https://twitter.com/yakeing)
