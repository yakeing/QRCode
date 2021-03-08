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

[![Travis-ci](https://api.travis-ci.com/yakeing/QRCode.svg?branch=master)](https://travis-ci.com/yakeing/QRCode)

### codecov 图标

[![codecov](https://codecov.io/gh/yakeing/QRCode/branch/master/graph/badge.svg)](https://codecov.io/gh/yakeing/QRCode)

### Packagist 图标

[![Version](http://img.shields.io/packagist/v/yakeing/qrcode.svg)](../../releases)
[![Downloads](http://img.shields.io/packagist/dt/yakeing/qrcode.svg)](https://packagist.org/packages/yakeing/stats)

### Github 图标

[![Downloads](https://badging.now.sh/github/downloads/yakeing/QRCode?icon=github)](../../)
[![Size](https://badging.now.sh/github/size/yakeing/QRCode?icon=github)](src)
[![tag](https://badging.now.sh/github/tag/yakeing/QRCode?icon=github)](../../releases)
[![license](https://badging.now.sh/static/label/license/555/MPL-2.0/fe7d37?icon=github)](LICENSE)
[![languages](https://badging.now.sh/static/label/language/555/PHP/34abef?icon=github)](../../search?l=php)

### 安装

可以使用 [Composer](https://getcomposer.org) library 进行自动安装.
当然，你也可以到 [packages](https://packagist.org/packages/yakeing/qrcode) 进行查看详细.

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
    $color //RGB 颜色 array('255,255,255', '0,0,0'); 十六进制颜色 'FF0000,000000' (可选择RBG或十六进制的其中一种)
    $stream //是否输出源代码 (true/false)
    $spec //规格 有0-40种规格的矩阵
    $OutputPath //生成图片并保持的路径
    qrcode::image($text, $pixel, $icon, $distinguish, $type, $margin, $color, $stream, $OutputPath);
```

[赞助](https://github.com/yakeing/Documentation/blob/master/Sponsor/README.md)
---
如果觉得代码对你有所帮助就请给点动力我们对代码能有持续更新


[![Sponsor](https://badging.now.sh/static/label/Sponsor/EA4AAA?icon=heart)](https://github.com/yakeing/Documentation/blob/master/Sponsor/README.md)


作者
---

微博: [yakeing](https://weibo.com/yakeing)

推特: [yakeing](https://twitter.com/yakeing)
