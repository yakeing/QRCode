# QRCode
这是使用php代码写成的二维码生成图片，支撑中文英文各种字符可用于各种场景使用

- 信息获取（名片、地图、WIFI密码、资料）
- 网站跳转（跳转到微博、手机网站、网站）
- 广告推送（用户扫码，直接浏览商家推送的视频、音频广告）
- 手机电商（用户扫码、手机直接购物下单）
- 防伪溯源（用户扫码、即可查看生产地；同时后台可以获取最终消费地)
- 优惠促销（用户扫码，下载电子优惠券，抽奖）
- 会员管理（用户手机上获取电子会员信息、VIP服务）
- 手机支付（扫描商品二维码，通过银行或第三方支付提供的手机端通道完成支付）

### Travis CI

[![Travis-ci](https://api.travis-ci.org/yakeing/QRCode.svg)](https://travis-ci.org/yakeing/QRCode)

### Packagist

[![Version](http://img.shields.io/packagist/v/yakeing/qrcode.svg)](https://github.com/yakeing/qrcode/releases)
[![Downloads](http://img.shields.io/packagist/dt/yakeing/qrcode.svg)](https://packagist.org/packages/yakeing/qrcode)

### Github

[![Downloads](https://img.shields.io/github/downloads/yakeing/QRCode/total.svg)](https://github.com/yakeing/QRCode)
[![Size](https://img.shields.io/github/size/yakeing/QrCode/src/QrCode.php.svg)](https://github.com/yakeing/QRCode/blob/master/src/QrCode.php)
[![tag](https://img.shields.io/github/tag/yakeing/QRCode.svg)](https://github.com/yakeing/QRCode/releases)
[![Language](https://img.shields.io/github/license/yakeing/QRCode.svg)](https://github.com/yakeing/QRCode/blob/master/LICENSE)
[![Php](https://img.shields.io/github/languages/top/yakeing/QRCode.svg)](https://github.com/yakeing/QRCode)

### 安装

可以使用 [Composer](https://getcomposer.org) library 进行自动安装.

```
    $ composer require yakeing/qrcode
```

### qrcode 使用

```php

    $text //输入文字 (字符串)
    $pixel //生成图片尺寸 (整数)
    $icon //中间的小图标 (地址) (最好是正方形图像，否则图像将被扭曲)
    $distinguish //二维码可识别率 (L=7% , M=15% , Q=25% , H=30%)
    $type //生成图片格式 (jpg/png) (因GIF有版权之争)
    $margin //二维码白边距离 (0-4)
    $color //RBG 颜色 array('255|255|255', '0|0|0'); 十六进制颜色 FF0000|000000 (可选择RBG或十六进制的其中一种)
    $stream //是否输出源代码 (true/false)
    $spec //规格 有0-40种规格的矩阵
    qrcode::image($text, $pixel, $icon, $distinguish, $type, $margin, $color, $stream);
```

赞助
---
如果觉得代码对你有所帮助就请给点动力对代码能有持续更新

 [Bitcoin](https://btc.com/1FYbZECgs3V3zRx6P7yAu2nCDXP2DHpwt8) (比特币赞助)

1FYbZECgs3V3zRx6P7yAu2nCDXP2DHpwt8

 ![Bitcoin](https://raw.githubusercontent.com/yakeing/Content/master/Donate/Bitcoin.png)

 微信赞助

 ![WeChat](https://raw.githubusercontent.com/yakeing/Content/master/Donate/WeChat.png)

 支付宝赞助

 ![Alipay](https://raw.githubusercontent.com/yakeing/Content/master/Donate/Alipay.png)

作者
---

微博: [yakeing](https://weibo.com/yakeing)

推特: [yakeing](https://twitter.com/yakeing)
