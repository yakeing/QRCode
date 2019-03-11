# QRCode
QR Code class

# Json Wed Sign (RSA)

This is a function wrapping through the Openssl to sign and validate the data, which ensures the integrity and security of the original data.

### Travis CI

[![Travis-ci](https://api.travis-ci.org/yakeing/php_jwsign.svg)](https://travis-ci.org/yakeing/php_jwsign)

### Packagist

[![Version](http://img.shields.io/packagist/v/yakeing/qrcode.svg)](https://packagist.org/packages/yakeing/qrcode/releases)
[![Downloads](http://img.shields.io/packagist/dt/yakeing/qrcode.svg)](https://packagist.org/packages/yakeing/qrcode)

### Github

[![Downloads](https://img.shields.io/github/downloads/yakeing/QRCode/total.svg)](https://github.com/yakeing/QRCode)
[![Size](https://img.shields.io/github/size/yakeing/php_jwsign/src/QRCode/jwsign.php.svg)](https://github.com/yakeing/QRCode/blob/master/src/php_jwsign/jwsign.php)
[![tag](https://img.shields.io/github/tag/yakeing/QRCode.svg)](https://github.com/yakeing/QRCode/releases)
[![Language](https://img.shields.io/github/license/yakeing/QRCode.svg)](https://github.com/yakeing/QRCode/blob/master/LICENSE)
[![Php](https://img.shields.io/github/languages/top/yakeing/QRCode.svg)](https://github.com/yakeing/QRCode)

### Installation

Use [Composer](https://getcomposer.org) to install the library.

```
    $ composer require yakeing/qrcode
```

### JWSign init

```php

    $text //文字 string
    $pixel //输出图片尺寸 ini
    $icon //小图标 url (必须是正方形否则出现位置不正)
    $distinguish //识别率 L=7% , M=15% , Q=25% , H=30%
    $type //输出图片格式 jpg/png (因GIF有版权之争)
    $margin //边距 ini 0-4
    $color //RBG颜色 array('255|255|255', '0|0|0'); 十六进制颜色 FF0000|000000
    $stream //输出编码 true/false
    $spec //规格 有0-40种规格的矩阵
    qrcode::image($text, $pixel, $icon, $distinguish, $type, $margin, $color, $stream);
```

Donate
---
Your donation makes CODE better.

 Bitcoin (比特币赞助)

 1Ff2hTfr4EioWv2ZDLKTedUiF9wBBVYSbU

 ![Bitcoin](https://raw.githubusercontent.com/yakeing/Content/master/Donate/Bitcoin.png)

 WeChat (微信赞助)

 ![WeChat](https://raw.githubusercontent.com/yakeing/Content/master/Donate/WeChat.png)

 Alipay (支付宝赞助)

 ![Alipay](https://raw.githubusercontent.com/yakeing/Content/master/Donate/Alipay.png)

Author
---

weibo: [yakeing](https://weibo.com/yakeing)

twitter: [yakeing](https://twitter.com/yakeing)
