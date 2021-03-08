# QRCode

This is a class of php QR Code, This library helps you generate QR codes in a jiffy. 

[中文文档](https://github.com/yakeing/QRCode/blob/master/ZH.README.md)

### Travis CI badge

[![Travis-ci](https://api.travis-ci.com/yakeing/QRCode.svg?branch=master)](https://travis-ci.com/yakeing/QRCode)

### codecov badge

[![codecov](https://codecov.io/gh/yakeing/QRCode/branch/master/graph/badge.svg)](https://codecov.io/gh/yakeing/QRCode)

### Github badge

[![Downloads](https://badging.now.sh/github/downloads/yakeing/QRCode?logo=github)](../../)
[![Size](https://badging.now.sh/github/size/yakeing/QRCode?icon=github)](src)
[![tag](https://badging.now.sh/github/tag/yakeing/QRCode?icon=github)](../../releases)
[![license](https://badging.now.sh/static/label/license/555/MPL-2.0/fe7d37?icon=github)](LICENSE)
[![languages](https://badging.now.sh/static/label/language/555/PHP/34abef?icon=github)](../../search?l=php)

### Installation

Use [Composer](https://getcomposer.org) to install the library.
Of course, You can go to [Packagist](https://packagist.org/packages/yakeing/qrcode) to view.

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
    $color //RGB Colour array('255,255,255', '0,0,0'); Hexadecimal Colour FF0000,000000
    $stream //Output source code (true/false)
    $spec //specification Matrix with 0-40 specifications
    $OutputPath //Path to generate pictures
    qrcode::image($text, $pixel, $icon, $distinguish, $type, $margin, $color, $stream, $OutputPath);
```

[Sponsor](https://github.com/yakeing/Documentation/blob/master/Sponsor/README.md)
---
If you've got value from any of the content which I have created, then I would very much appreciate your support by payment donate.

[![Sponsor](https://badging.now.sh/static/label/Sponsor/EA4AAA?icon=heart)](https://github.com/yakeing/Documentation/blob/master/Sponsor/README.md)

Author
---

weibo: [yakeing](https://weibo.com/yakeing)

twitter: [yakeing](https://twitter.com/yakeing)
