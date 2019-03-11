<?php
/**
	*  QR Code
	*  二维码类
	*
	* @author http://weibo.com/yakeing
	* @version 3.1
	*
	*  $text 文字 string
	*  $pixel 输出图片尺寸 ini
	*  $icon 小图标 url (必须是正方形否则出现位置不正)
	*  $distinguish 识别率 L=7% , M=15% , Q=25% , H=30%
	*  $type 输出图片格式 jpg/png (因GIF有版权之争)
	*  $margin 边距 ini 0-4
	*  $color RBG颜色 array('255|255|255', '0|0|0'); 十六进制颜色 FF0000|000000
	*  $stream 输出编码 true/false
	*  $spec 规格 有40种规格的矩阵
	*  qrcode::image($text, $pixel, $icon, $distinguish, $type, $margin, $color, $stream);
	*  注意: 颜色相撞 ff0000 背景变成透明;
	*  使用正方形 ICO 更加正规,不做比例调整
 */
class QrCode{
	public static function image($text, $pixel = 200, $icon = false, $distinguish = 'L', $type = 'PNG', $margin = 0, $color = false, $spec=1, $stream = false){
		//string 文字
		$string = new qrcode_string();
		$string->initial($text, $distinguish, intval($spec));
		$datacode = $string->getByteStream();
		$version = $string->version;
		//pattern 模式
		$pattern = new qrcode_pattern();
		$width = $pattern->getWidth($version);
		$frame = $pattern->newFrame($version);
		//exchange 转换
		$exchange = new qrcode_exchange();
		$masked = $exchange->run($string, $width, $frame, $datacode);
		$tab = $exchange->binarize($masked);
		if(true === $stream){
			echo "array(\n'".implode("',\n'",$tab)."'\n)";
		}else{
			//image 图像
			$img = new qrcode_image();
			$img->ImgColor($tab, $pixel, $icon , $type, $margin, $color);
		}
	}
}//END class qrcode


/*
 * QR Code Image 图像处理
 * Author: weibo.com/yakeing
 * ImageCreate 256色
 * ImageCreateTrueColor 真彩色
 * ImageCopyResized  粗糙速度快
 * ImageCopyResampled  平滑速度慢
*/
class qrcode_image{

	//图像与颜色
	function ImgColor($frame, $pixel, $iconurl, $type, $margin, $color){
		//$pixel = $pixel/count($frame);
		if(is_array($color)){ //RGB
			$colour = array();
			for ($k=0; $k<2; ++$k){
				$v = explode('|', $color[$k]);
				for ($i=0; $i<3; ++$i){
					$t = (int)$v[$i];
					$colour[$k][$i] = (256 > $t) ? $t : 0;
				}
			}
		}else if(preg_match('/^[a-f0-9]{6}\|[a-f0-9]{6}$/i', $color)){ //十六进制
			$colour = array();
			list($color1,$color2) = explode('|', strtoupper($color));
			$arr = array(str_split($color1,2), str_split($color2,2));
			foreach($arr as $value){
				$colour[] = array_map(function ($str){
					return '0x'.$str;
				}, $value);
			}
		}else{
			$colour = array(array(255, 0, 0),array(0, 0, 0));
		}
		$im = $this->image($frame, $pixel, $margin, $colour);
		if(is_file($iconurl)) $im = $this->addIcon($im, $iconurl, $colour);
		$types = $this->format($type);
		return $this->render($im, $types);
	} //END img

	//构造图层
	private function image($frame, $PixelPoint, $blank, $colour){
		$PixelPoint = intval($PixelPoint);
		$blank = intval($blank);
		if($PixelPoint < 1) $PixelPoint = 1;
		$h = count($frame);
		$w = strlen($frame[0]);
		// $imgW = $h * $PixelPoint;
		// $imgH = $w * $PixelPoint;
		$imgW = $imgH = $PixelPoint;
		$chart = ImageCreateTrueColor($w, $h);//真彩
		$black = ImageColorAllocate($chart, $colour[1][0], $colour[1][1], $colour[1][2]);//底色黑
		ImageFill($chart, 0, 0, ImageColorAllocate($chart, $colour[0][0], $colour[0][1], $colour[0][2]));//图形着色->底色白
		for($y=0; $y<$h; ++$y){
			for($x=0; $x<$w; ++$x){
				if ($frame[$y][$x] == '1'){
					ImageSetPixel($chart,$x,$y,$black); //画一个单一像素
				}
			}
		}
		$basics = ImageCreateTrueColor($imgW, $imgH);
		ImageCopyResized($basics, $chart, 0, 0, 0, 0, $imgW, $imgH, $w, $h);//复制新图并调整大小
		ImageDestroy($chart); //销毁一图像
		if($blank > 0){//追加边框
			$rimW = $imgW+$blank;
			$rimH = $imgH+$blank;
			$rim = ImageCreateTrueColor($rimW, $rimH);
			ImageFill($rim, 0, 0, ImageColorAllocate($rim, $colour[0][0], $colour[0][1], $colour[0][2]));
			ImageCopyResized($rim, $basics, $blank/2, $blank/2, 0, 0, $imgW, $imgH, $imgW, $imgH);
			ImageDestroy($basics);
			return $rim;
		}else{
			return $basics;
		}
	} //END image

	//ADD ICON  (3.7948)
	// http://php.net/manual/zh/function.exif-imagetype.php 图像类型常量
	private function addIcon($im, $icon, $colour){
		if(function_exists('exif_imagetype')){
			$imagetype = exif_imagetype($icon);
		}else{
			$IconInfo = getimagesize($icon);
			$imagetype = $IconInfo[2];
		}
		switch($imagetype){
			case 1: //IMAGETYPE_GIF
				$ico = ImageCreateFromGIF($icon);
				break;
			case 2: //IMAGETYPE_JPEG
				$ico = ImageCreateFromJPEG($icon);
				break;
			case 3: //IMAGETYPE_PNG
				$ico = ImageCreateFromPNG($icon);
				break;
			default: exit('Image type or image path error. Support type: GIF JPG PNG');
		}
		$x = ImagesX($ico);
		$y = ImagesY($ico);
		$RadiusIco = $this->radiusImage($ico, $x, $y);
		//white Background
		$BackgX = $x/8+$x;
		$BackgY = $y/8+$y;
		$white_im = ImageCreateTrueColor($BackgX, $BackgY);
		$white_colo = ImageColorAllocate($white_im, $colour[0][0], $colour[0][1], $colour[0][2]);
		ImageFill($white_im, 0, 0, $white_colo);
		$white_icox = $BackgX/2-$x/2+1;
		$white_icoy = $BackgY/2-$y/2+1;
		ImageCopyResized($white_im, $RadiusIco, $white_icox, $white_icoy, 0, 0, $x, $y, $x, $y);
		ImageDestroy($RadiusIco);
		$RadiusWhite = $this->radiusImage($white_im, $BackgX, $BackgY);
		ImageDestroy($white_im);
		//Transparent Background + alpha
		$AlphaIm = ImageCreateTrueColor($BackgX, $BackgY);
		$AlphaColo = ImageColorAllocateAlpha($AlphaIm, 255, 0, 0, 127);//分配颜色 + alpha
		ImageFill($AlphaIm, 0, 0, $AlphaColo);
		ImageCopyResized($AlphaIm, $RadiusWhite, 0, 0, 0, 0, $BackgX, $BackgY, $BackgX, $BackgY);
		ImageDestroy($RadiusWhite);
		imagesavealpha($AlphaIm, true);
		//QR Background Synthesis
		$radiusX = ImagesX($AlphaIm);
		$RadiusY = ImagesY($AlphaIm);
		$qrX = ImagesX($im);
		$qrY = ImagesY($im);
		$w = $qrX/3.7948;
		$h = $qrY/3.7948;
		$newX = $qrX/2-($w/2)+1;
		$newY = $qrY/2-($h/2)+1;
		ImageCopyResampled($im,$AlphaIm,$newX,$newY,0,0,$w,$h,$radiusX,$RadiusY);
		ImageDestroy($AlphaIm);
		return $im;
	} //END addIcon

	//图片圆角
	//GD 2.0.1 或更高版本
	private function radiusImage($im, $width, $height){
		$image = ImageCreateTrueColor($width, $height); //新建真彩图像
		$bgcolor = ImageColorAllocate($image, 255, 0, 0);
		ImageFill($image, 0, 0, $bgcolor);
		ImageCopyMerge($image, $im, 0, 0, 0, 0, $width, $height, 100);
		//radius Image
		$radiusX = $width/4;
		$radiusY = $height/4;
		$img = ImageCreateTrueColor($radiusX, $radiusY); //新建真彩图像
		$fgcolor = ImageColorAllocate($img, 0, 0, 0);
		$bgcolor = ImageColorAllocate($img, 255, 0, 0);
		ImageFill($img, 0, 0, $bgcolor);
		//180 270 开始角度到结束角度
		ImageFilledArc($img, $radiusX, $radiusY, $radiusX*2, $radiusY*2, 180, 270, $fgcolor, IMG_ARC_PIE);
		imagecolortransparent($img, $fgcolor);
		// Top left
		ImageCopyMerge($image, $img, 0, 0, 0, 0, $width, $height, 100);
		// Bottom left
		$bottom_left  = ImageRotate($img, 90, $bgcolor);  //角度旋转
		ImageCopyMerge($image, $bottom_left, 0, $height - $radiusY+1, 0, 0, $width, $height, 100);
		ImageDestroy($bottom_left);
		// Top right
		$top_right  = ImageRotate($img, 180, $bgcolor);
		ImageCopyMerge($image, $top_right, $width - $radiusX+1, $height - $radiusY+1, 0, 0, $width, $height, 100);
		ImageDestroy($top_right);
		// Bottom right
		$bottom_right  = ImageRotate($img, 270, $bgcolor);
		ImageCopyMerge($image, $bottom_right, $width - $radiusX+1, 0, 0, 0, $width, $height, 100);
		ImageDestroy($bottom_right);
		ImageDestroy($img);
		ImageColorTransparent($image, $bgcolor);  //设置颜色为透明
		return $image;
	} //END radiusImage

	//图像格式
	private function format($fortype){
		$fortype = strtoupper($fortype);
		switch ($fortype){
			case 'JPG':
			case 'JPEG':
				$type = 'jpeg';
			break;
			default:
					$type = 'png';
				break;
		}
		return $type;
	} //END format

	//图像输出
	private function render($im, $type){
		header('Content-type: image/'.$type);
		if($type == 'jpeg'){
			ImageJPEG($im);//合成输出
		}else{
			ImagePNG($im);
		}
		ImageDestroy($im); //结束图形
	} //END render

}//END class qrcode_image





/*
 * QR Code Exchange 转换
*
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/
class qrcode_exchange{

	//二进制转换
	function binarize($frame){
		$len = count($frame);
		foreach ($frame as &$frameLine) {
			for($i=0; $i<$len; ++$i) {
				$frameLine[$i] = (ord($frameLine[$i])&1)?'1':'0';
			}
		}
		return $frame;
	}//END binarize

	//run 初始run
	function run($input, $width, $frame, $datacode){
		$this->runLength = array();
		$this->runLength = array_fill(0, 177 + 1, 0);
		$raw = new qrcode_string();
		$raw->datacode = $datacode;
		$raw->setString($input);
		$filler = new qrcode_exchange();
		$filler->initframe($width, $frame);
		for($i=0; $i<$raw->dataLength + $raw->eccLength; ++$i) {
			$code = $raw->getCode();
			$bit = 0x80;
			for($j=0; $j<8; ++$j) {
				$addr = $filler->nexts();
				$filler->setFrameAt($addr, 0x02 | (($bit & $code) != 0));
				$bit = $bit >> 1;
			}
		}
		$j = $this->getRemainder($input->version);
		for($i=0; $i<$j; ++$i) {
			$addr = $filler->nexts();
			$filler->setFrameAt($addr, 0x02);
		}
		return $this->mask($width, $filler->frame, $input->level);
	}//END run

	//initframe 初始框架数据
	private function initframe($width, $frame){
			$this->width = $width;
			$this->frame = $frame;
			$this->x = $width - 1;
			$this->y = $width - 1;
			$this->dir = -1;
			$this->bit = -1;
	}//END initframe

	//get 得到剩余
	private function getRemainder($version){
			return qrcode_table::$capacity[$version][2];
	}//END getRemainder

	//set设置框架
	private function setFrameAt($at, $val){
			$this->frame[$at['y']][$at['x']] = chr($val);
	}//END setFrameAt

	//next 紧邻
	private function nexts(){
		do {
			if($this->bit == -1) {
				$this->bit = 0;
				return array('x'=>$this->x, 'y'=>$this->y);
			}
			$x = $this->x;
			$y = $this->y;
			$w = $this->width;
			if($this->bit == 0) {
				--$x;
				++$this->bit;
			} else {
				++$x;
				$y += $this->dir;
				--$this->bit;
			}
			if($this->dir < 0) {
				if($y < 0) {
					$y = 0;
					$x -= 2;
					$this->dir = 1;
					if($x == 6) {
						--$x;
						$y = 9;
					}
				}
			} else {
				if($y == $w) {
					$y = $w - 1;
					$x -= 2;
					$this->dir = -1;
					if($x == 6) {
						--$x;
						$y -= 8;
					}
				}
			}
			if($x < 0 || $y < 0) return null;
			$this->x = $x;
			$this->y = $y;
		} while(ord($this->frame[$y][$x]) & 0x80);
		return array('x'=>$x, 'y'=>$y);
	}//END next

	//mask 掩码
	private function mask($width, $frame, $level){
		$minDemerit = PHP_INT_MAX;
		$bestMaskNum = 0;
		$bestMask = array();
		$checked_masks = array(0,1,2,3,4,5,6,7);
			$howManuOut = 8-(2 % 9);
			for ($i = 0; $i <  $howManuOut; ++$i) {
				$remPos = rand (0, count($checked_masks)-1);
				unset($checked_masks[$remPos]);
				$checked_masks = array_values($checked_masks);
			}
		$bestMask = $frame;
		foreach($checked_masks as $i) {
			$mask = array_fill(0, $width, str_repeat("\0", $width));
			$demerit = 0;
			$blacks = 0;
			$blacks  = $this->makeMaskNo($i, $width, $frame, $mask);
			$blacks += $this->writeFormatInformation($width, $mask, $i, $level);
			$blacks  = (int)(100 * $blacks / ($width * $width));
			$demerit = (int)((int)(abs($blacks - 50) / 5) * 10);
			$demerit += $this->evaluateSymbol($width, $mask);
			if($demerit < $minDemerit) {
				$minDemerit = $demerit;
				$bestMask = $mask;
				$bestMaskNum = $i;
			}
		}
		return $bestMask;
	}//END mask

	//make 做掩码开关
	private function makeMaskNo($maskNo, $width, $s, &$d, $maskGenOnly = false) {
		$b = 0;
		$bitMask = array();
		$bitMask = $this->generateMaskNo($maskNo, $width, $s, $d);
		if ($maskGenOnly) return;
		$d = $s;
		for($y=0; $y<$width; ++$y) {
			for($x=0; $x<$width; ++$x) {
				if($bitMask[$y][$x] == 1) $d[$y][$x] = chr(ord($s[$y][$x]) ^ (int)$bitMask[$y][$x]);
				$b += (int)(ord($d[$y][$x]) & 1);
			}
		}
		return $b;
	}//NED makeMaskNo

	//generate 生成掩码开关
	private function generateMaskNo($maskNo, $width, $frame){
		$bitMask = array_fill(0, $width, array_fill(0, $width, 0));
		for($y=0; $y<$width; ++$y) {
			for($x=0; $x<$width; ++$x) {
				if(ord($frame[$y][$x]) & 0x80) {
					$bitMask[$y][$x] = 0;
				} else {
					$maskFunc = call_user_func(array($this, 'mask'.$maskNo), $x, $y);
					$bitMask[$y][$x] = ($maskFunc == 0)?1:0;
				}
			}
		}
		return $bitMask;
	}//NED generateMaskNo

	private function mask0($x, $y) { return ($x+$y)&1; }
	private function mask1($x, $y) { return ($y&1); }
	private function mask2($x, $y) { return ($x%3); }
	private function mask3($x, $y) { return ($x+$y)%3; }
	private function mask4($x, $y) { return (((int)($y/2))+((int)($x/3)))&1; }
	private function mask5($x, $y) { return (($x*$y)&1)+($x*$y)%3; }
	private function mask6($x, $y) { return ((($x*$y)&1)+($x*$y)%3)&1; }
	private function mask7($x, $y) { return ((($x*$y)%3)+(($x+$y)&1))&1; }

	//write 写信息
	private function writeFormatInformation($width, &$frame, $mask, $level){
		$blacks = 0;
		$format =  $this->getFormatInfo($mask, $level);
		for($i=0; $i<8; ++$i) {
			if($format & 1) {
				$blacks += 2;
				$v = 0x85;
			} else {
				$v = 0x84;
			}
			$frame[8][$width - 1 - $i] = chr($v);
			if($i < 6) {
				$frame[$i][8] = chr($v);
			} else {
				$frame[$i + 1][8] = chr($v);
			}
			$format = $format >> 1;
		}
		for($i=0; $i<7; ++$i) {
			if($format & 1) {
				$blacks += 2;
				$v = 0x85;
			} else {
				$v = 0x84;
			}
			$frame[$width - 7 + $i][8] = chr($v);
			if($i == 0) {
				$frame[8][7] = chr($v);
			} else {
				$frame[8][6 - $i] = chr($v);
			}
			$format = $format >> 1;
		}
		return $blacks;
	}//END writeFormatInformation

	//get 获得格式信息
	private function getFormatInfo($mask, $level){
		if($mask < 0 || $mask > 7) return 0;
		if($level < 0 || $level > 3) return 0;
		return qrcode_table::$formatInfo[$level][$mask];
	}//END getFormatInfo

	//evaluate 评价符号
	private function evaluateSymbol($width, $frame){
		$head = 0;
		$demerit = 0;
		for($y=0; $y<$width; ++$y) {
			$head = 0;
			$this->runLength[0] = 1;
			$frameY = $frame[$y];
			if ($y>0) $frameYM = $frame[$y-1];
			for($x=0; $x<$width; ++$x) {
				if(($x > 0) && ($y > 0)) {
					$b22 = ord($frameY[$x]) & ord($frameY[$x-1]) & ord($frameYM[$x]) & ord($frameYM[$x-1]);
					$w22 = ord($frameY[$x]) | ord($frameY[$x-1]) | ord($frameYM[$x]) | ord($frameYM[$x-1]);
					if(($b22 | ($w22 ^ 1))&1) $demerit += 3;
				}
				if(($x == 0) && (ord($frameY[$x]) & 1)) {
					$this->runLength[0] = -1;
					$head = 1;
					$this->runLength[$head] = 1;
				} else if($x > 0) {
					if((ord($frameY[$x]) ^ ord($frameY[$x-1])) & 1) {
						++$head;
						$this->runLength[$head] = 1;
					} else {
						++$this->runLength[$head];
					}
				}
			}
			$demerit += $this->calcN1N3($head+1);
		}
		for($x=0; $x<$width; ++$x) {
			$head = 0;
			$this->runLength[0] = 1;
			for($y=0; $y<$width; ++$y) {
				if($y == 0 && (ord($frame[$y][$x]) & 1)) {
					$this->runLength[0] = -1;
					$head = 1;
					$this->runLength[$head] = 1;
				} else if($y > 0) {
					if((ord($frame[$y][$x]) ^ ord($frame[$y-1][$x])) & 1) {
						++$head;
						$this->runLength[$head] = 1;
					}else{
						++$this->runLength[$head];
					}
				}
			}
			$demerit += $this->calcN1N3($head+1);
		}
		return $demerit;
	}//END evaluateSymbol

	//calc 计算n1 n3模式
	private function calcN1N3($length){
		$demerit = 0;
		for($i=0; $i<$length; ++$i) {
			if($this->runLength[$i] >= 5) {
				$demerit += (3 + ($this->runLength[$i] - 5));
			}
			if($i & 1) {
				if(($i >= 3) && ($i < ($length-2)) && ($this->runLength[$i] % 3 == 0)) {
					$fact = (int)($this->runLength[$i] / 3);
					if(($this->runLength[$i-2] == $fact) && ($this->runLength[$i-1] == $fact) && ($this->runLength[$i+1] == $fact) && ($this->runLength[$i+2] == $fact)) {
							if(($this->runLength[$i-3] < 0) || ($this->runLength[$i-3] >= (4 * $fact))) {
								$demerit += 40;
							} else if((($i+3) >= $length) || ($this->runLength[$i+3] >= (4 * $fact))) {
								$demerit += 40;
							}
					}
				}
			}
		}
		return $demerit;
	}//END calcN1N3
}//END class qrcode_exchange





/*
 * QR Code String 文本处理
*
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/
class qrcode_string{

	//initial 初始化 public
	function initial($dataStr, $level, $spec){
		$this->dataStr = $dataStr; //Str 字符串
		$this->level = $this->GetGrade($level); //level 识别率
		if($spec < 1 ){
			$spec = 1;
		}elseif($spec > 40 ){
			$spec = 40;
		}
		$this->version = $spec; //version 版本
		$this->splitString();
	}//END initial

	//get 获取当前识别率 lovel
	private function GetGrade($i){
		return qrcode_table::$grade[strtoupper($i)];
	}//END GetGrade

	//----------------------------------------NO: 第四分支 附加 to------------------------------------------

	//set 字符 public
	function setString($input){
		$this->newcode();
		$spec = array(0,0,0,0,0);
		$this->getEccSpec($input->version, $input->level, $spec);

		$this->version = $input->version;
		$this->b1 = $this->rsBlockNum1($spec);
		$this->dataLength = $this->rsDataLength($spec);
		$this->eccLength = $this->rsEccLength($spec);
		$this->ecccode = array_fill(0, $this->eccLength, 0);
		$this->blocks = $this->rsBlockNum($spec);
		$ret = $this->init($spec);
		if($ret < 0)  return null;
		$this->count = 0;
		return $this;
	}//END setString

	//new 新类
	private function newinit(){
		$this->alpha_to = $this->index_of = $this->genpoly = array();   // Generator polynomial
		$this->mm = $this->nn = $this->nroots = $this->fcr = $this->prim = $this->iprim = $this->pad = $this->gfpoly = NULL;
	}//NED newinit

	//new 新类
	private function newcode(){
		$this->ecccode = $this->rsblocks = array(); //of RSblock
		$this->blocks = $this->count = $this->dataLength = $this->eccLength = $this->b1 = NULL;
	}//NED newcode

	//ECC 获得ECC规格 ok
	private function getEccSpec($version, $level, array &$spec){
		if (count($spec) < 5) $spec = array(0,0,0,0,0);
		$b1   = qrcode_table::$eccTable[$version][$level][0];
		$b2   = qrcode_table::$eccTable[$version][$level][1];
		$data = $this->getDataLength($version, $level);
		$ecc  = $this->getECCLength($version, $level);
		if($b2 == 0) {
			$spec[0] = $b1;
			$spec[1] = (int)($data / $b1);
			$spec[2] = (int)($ecc / $b1);
			$spec[3] = 0;
			$spec[4] = 0;
		} else {
			$spec[0] = $b1;
			$spec[1] = (int)($data / ($b1 + $b2));
			$spec[2] = (int)($ecc  / ($b1 + $b2));
			$spec[3] = $b2;
			$spec[4] = $spec[1] + 1;
		}
	}//END getEccSpec



	//get 获取ECC长度
	private function getECCLength($version, $level){
		return qrcode_table::$capacity[$version][3][$level];
	}//END getECCLength

	private function rsBlockNum($spec)     { return $spec[0] + $spec[3]; }
	private function rsEccLength($spec)    { return ($spec[0] + $spec[3]) * $spec[2]; }
	private function rsDataLength($spec)   { return ($spec[0] * $spec[1]) + ($spec[3] * $spec[4]);    }
	private function rsBlockNum1($spec)    { return $spec[0]; }

	private function rsDataCodes1($spec)   { return $spec[1]; }
	private function rsEccCodes1($spec)    { return $spec[2]; }
	private function rsBlockNum2($spec)    { return $spec[3]; }
	private function rsDataCodes2($spec)   { return $spec[4]; }
	private function rsEccCodes2($spec)    { return $spec[2]; }

	//init 初始init
	private function init(array $spec){
		$this->items = array();
		$dl = $this->rsDataCodes1($spec);
		$el = $this->rsEccCodes1($spec);
		$rs = $this->init_rs(8, 0x11d, 0, 1, $el, 255 - $dl - $el);
		$blockNo = 0;
		$dataPos = 0;
		$eccPos = 0;
		for($i=0; $i<$this->rsBlockNum1($spec); ++$i) {
			$ecc = array_slice($this->ecccode,$eccPos);
			$this->rsblocks[$blockNo] = $this->qrrsblock($dl, array_slice($this->datacode, $dataPos), $el,  $ecc, $rs);
			$this->ecccode = array_merge(array_slice($this->ecccode,0, $eccPos), $ecc);
			$dataPos += $dl;
			$eccPos += $el;
			++$blockNo;
		}
		if($this->rsBlockNum2($spec) == 0) return 0;
		$dl = $this->rsDataCodes2($spec);
		$el = $this->rsEccCodes2($spec);
		$rs = $this->init_rs(8, 0x11d, 0, 1, $el, 255 - $dl - $el);
		if($rs == NULL) return -1;
		for($i=0; $i<$this->rsBlockNum2($spec); ++$i) {
			$ecc = array_slice($this->ecccode,$eccPos);
			$this->rsblocks[$blockNo] = $this->qrrsblock($dl, array_slice($this->datacode, $dataPos), $el, $ecc, $rs);
			$this->ecccode = array_merge(array_slice($this->ecccode,0, $eccPos), $ecc);
			$dataPos += $dl;
			$eccPos += $el;
			++$blockNo;
		}
		return 0;
	}//END init

	//get 获取带吗 public
	function getCode(){
		if($this->count < $this->dataLength) {
			$row = $this->count % $this->blocks;
			$col = $this->count / $this->blocks;
			if($col >= $this->rsblocks[0]->dataLength) $row += $this->b1;
			$ret = $this->rsblocks[$row]->data[$col];
		} else if($this->count < $this->dataLength + $this->eccLength) {
			$row = ($this->count - $this->dataLength) % $this->blocks;
			$col = ($this->count - $this->dataLength) / $this->blocks;
			$ret = $this->rsblocks[$row]->ecc[$col];
		} else {
			return 0;
		}
		++$this->count;
		return $ret;
	}//END get Code


	//init RS
	private function init_rs($symsize, $gfpoly, $fcr, $prim, $nroots, $pad){
		foreach($this->items as $rs) {
			if($rs->pad != $pad) continue;
			if($rs->nroots != $nroots) continue;
			if($rs->mm != $symsize) continue;
			if($rs->gfpoly != $gfpoly) continue;
			if($rs->fcr != $fcr) continue;
			if($rs->prim != $prim) continue;
			return $rs;
		}
		$rs = $this->init_rs_char($symsize, $gfpoly, $fcr, $prim, $nroots, $pad);
		array_unshift($this->items, $rs);
		return $rs;
	}//END init_rs

	//init RS字符
	private function init_rs_char($symsize, $gfpoly, $fcr, $prim, $nroots, $pad){
		$rs = null;
		// Check parameter ranges
		if($symsize < 0 || $symsize > 8) return $rs;
		if($fcr < 0 || $fcr >= (1<<$symsize)) return $rs;
		if($prim <= 0 || $prim >= (1<<$symsize)) return $rs;
		if($nroots < 0 || $nroots >= (1<<$symsize)) return $rs; // Can't have more roots than symbol values!
		if($pad < 0 || $pad >= ((1<<$symsize) -1 - $nroots)) return $rs; // Too much padding
		$rs = new qrcode_string();
		$rs->newinit();
		$rs->mm = $symsize;
		$rs->nn = (1<<$symsize)-1;
		$rs->pad = $pad;
		$rs->alpha_to = array_fill(0, $rs->nn+1, 0);
		$rs->index_of = array_fill(0, $rs->nn+1, 0);
		// PHP style macro replacement ;)
		$NN =& $rs->nn;
		$A0 =& $NN;
		// Generate Galois field lookup tables
		$rs->index_of[0] = $A0; // log(zero) = -inf
		$rs->alpha_to[$A0] = 0; // alpha**-inf = 0
		$sr = 1;
		for($i=0; $i<$rs->nn; ++$i) {
			$rs->index_of[$sr] = $i;
			$rs->alpha_to[$i] = $sr;
			$sr <<= 1;
			if($sr & (1<<$symsize)) {
					$sr ^= $gfpoly;
			}
			$sr &= $rs->nn;
		}
		if($sr != 1){
			// field generator polynomial is not primitive!
			$rs = NULL;
			return $rs;
		}
		$rs->genpoly = array_fill(0, $nroots+1, 0);
		$rs->fcr = $fcr;
		$rs->prim = $prim;
		$rs->nroots = $nroots;
		$rs->gfpoly = $gfpoly;
		for($iprim=1;($iprim % $prim) != 0;$iprim += $rs->nn)
		; // intentional empty-body loop!
		$rs->iprim = (int)($iprim / $prim);
		$rs->genpoly[0] = 1;
		for ($i = 0,$root=$fcr*$prim; $i < $nroots; ++$i, $root += $prim) {
			$rs->genpoly[$i+1] = 1;
			// Multiply rs->genpoly[] by  @**(root + x)
			for ($j = $i; $j > 0; --$j) {
				if ($rs->genpoly[$j] != 0) {
					$rs->genpoly[$j] = $rs->genpoly[$j-1] ^ $rs->alpha_to[$rs->modnn($rs->index_of[$rs->genpoly[$j]] + $root)];
				} else {
					$rs->genpoly[$j] = $rs->genpoly[$j-1];
				}
			}
			// rs->genpoly[0] can never be zero
			$rs->genpoly[0] = $rs->alpha_to[$rs->modnn($rs->index_of[$rs->genpoly[0]] + $root)];
		}
		// convert rs->genpoly[] to index form for quicker encoding
		for ($i = 0; $i <= $nroots; ++$i) $rs->genpoly[$i] = $rs->index_of[$rs->genpoly[$i]];
		return $rs;
	}//END init_rs_char

	//modnn
	private function modnn($x){
		while ($x >= $this->nn) {
			$x -= $this->nn;
			$x = ($x >> $this->mm) + ($x & $this->nn);
		}
		return $x;
	}//END modnn

	//qrrsblock RSB锁
	private function qrrsblock($dl, $data, $el, &$ecc, $rs){
		$qrcode = new qrcode_string();
		$rs->encode_rs_char($data, $ecc);
		$qrcode->dataLength = $dl;
		$qrcode->data = $data;
		$qrcode->eccLength = $el;
		$qrcode->ecc = $ecc;
		return $qrcode;
	}//END qrrsblock

	//encode 编码RS字符
	private function encode_rs_char($data, &$parity){
			$MM       =& $this->mm;
			$NN       =& $this->nn;
			$ALPHA_TO =& $this->alpha_to;
			$INDEX_OF =& $this->index_of;
			$GENPOLY  =& $this->genpoly;
			$NROOTS   =& $this->nroots;
			$FCR      =& $this->fcr;
			$PRIM     =& $this->prim;
			$IPRIM    =& $this->iprim;
			$PAD      =& $this->pad;
			$A0       =& $NN;
			$parity = array_fill(0, $NROOTS, 0);
			for($i=0; $i< ($NN-$NROOTS-$PAD); ++$i) {
				$feedback = $INDEX_OF[$data[$i] ^ $parity[0]];
				if($feedback != $A0) {
					$feedback = $this->modnn($NN - $GENPOLY[$NROOTS] + $feedback);
					for($j=1;$j<$NROOTS;++$j) {
						$parity[$j] ^= $ALPHA_TO[$this->modnn($feedback + $GENPOLY[$NROOTS-$j])];
					}
				}
				array_shift($parity);
				if($feedback != $A0) {
					array_push($parity, $ALPHA_TO[$this->modnn($feedback + $GENPOLY[0])]);
				} else {
					array_push($parity, 0);
				}
			}
	}//END encode_rs_char

	//----------------------------------------NO: 第三分支 流系列 to------------------------------------------

	//get 得到字节流 public
	function getByteStream(){
		$bstream = $this->getBitStream();
		if($bstream == null) return null;
		return $bstream->toByte();
	}// END getByteStream

	//get  编码流
	private function getBitStream(){
		$bstream = $this->mergeBitStream();
		if($bstream == null) return null;
		$ret = $this->appendPaddingBit($bstream);
		if($ret < 0) return null;
		return $bstream;
	}//END getBitStream

	//to 字节
	private function toByte(){
		$size = $this->size();
		if($size == 0) return array();
		$data = array_fill(0, (int)(($size + 7) / 8), 0);
		$bytes = (int)($size / 8);
		$p = 0;
		for($i=0; $i<$bytes; ++$i) {
			$v = 0;
			for($j=0; $j<8; ++$j) {
				$v = $v << 1;
				$v |= $this->data[$p];
				++$p;
			}
			$data[$i] = $v;
		}
		if($size & 7) {
			$v = 0;
			for($j=0; $j<($size & 7); ++$j) {
				$v = $v << 1;
				$v |= $this->data[$p];
				++$p;
			}
			$data[$bytes] = $v;
		}
		return $data;
	}// toByte

	//merge 合并流
	private function mergeBitStream(){
		if($this->convertData() < 0) return null;
		$bstream = new qrcode_string();
		$bstream->data = array();
		foreach($this->items as $item){
			$ret = $bstream->append_size($item->bstream);
			if($ret < 0) return null;
		}
		return $bstream;
	}//END mergeBitStream

	//size 数组数量
	private function size(){
		return count($this->data);
	}//END size

	//append 追加
	private function append($mode, $size, $data){
		try {
			$entry = new qrcode_string();
			$entry->data = array();
			$entry->assignment($mode, $size, $data);
			$this->items[] = $entry;
			return 0;
		} catch (Exception $e){
			return -1;
		}
	}//END append

	//size 追加size
	private function append_size($arg){
		if (is_null($arg)) return -1;
		if($arg->size() == 0) return 0;
		if($this->size() == 0){
			$this->data = $arg->data;
			return 0;
		}
		$this->data = array_values(array_merge($this->data, $arg->data));
		return 0;
	}//END append_size

	//QRinputItem 赋值
	private function assignment($mode, $size, $data, $bstream = null){
		$setData = array_slice($data, 0, $size);
		if (count($setData) < $size) $setData = array_merge($setData, array_fill(0,$size-count($setData),0));
		if(!$this->check($mode, $size, $setData)){
			//ERR::Exception('Error m:'.$mode.',s:'.$size.',d:'.join(',',$setData));
			return null;
		}
		$this->mode = $mode;
		$this->size = $size;
		$this->data = $setData;
		$this->bstream = $bstream;
		return $this;
	}//END assignment

	//check  检查
	private function check($mode, $size, $data){
		if($size <= 0) return false;
		switch($mode) {
			case 0: return $this->checkModeNum($size, $data);   break;
			case 1: return $this->checkModeAn($size, $data);    break;
			// case 3: return self::checkModeKanji($size, $data); break;
			case 2: return true; break;
			case 4: return true; break;
			default: break;
		}
		return false;
	}//END check

	//check 检查数模式
	private function checkModeNum($size, $data){
		for($i=0; $i<$size; ++$i) {
			if((ord($data[$i]) < ord('0')) || (ord($data[$i]) > ord('9'))) return false;
		}
		return true;
	}//END checkModeNum

	//check 检查单模式
	private function checkModeAn($size, $data){
		for($i=0; $i<$size; ++$i) {
			if ($this->lookAnTable(ord($data[$i])) == -1) return false;
		}
		return true;
	}//END checkModeAn

	//append 附加填充位
	private function appendPaddingBit($bstream){
		$bits = $bstream->size();
		$maxwords = $this->getDataLength($this->version, $this->level);
		$maxbits = $maxwords * 8;
		if ($maxbits == $bits) return 0;
		if ($maxbits - $bits < 5) return $bstream->appendNum($maxbits - $bits, 0);
		$bits += 4;
		$words = (int)(($bits + 7) / 8);
		$padding = new qrcode_string();
		$padding->data = array();
		$ret = $padding->appendNum($words * 8 - $bits + 4, 0);
		if($ret < 0) return $ret;
		$padlen = $maxwords - $words;
		if($padlen > 0){
			$padbuf = array();
			for($i=0; $i<$padlen; ++$i){
					$padbuf[$i] = ($i&1)?0x11:0xec;
			}
			$ret = $padding->appendBytes($padlen, $padbuf);
			if($ret < 0) return $ret;
		}
		$ret = $bstream->append_size($padding);
		return $ret;
	}//END appendPaddingBit

			//get 得到数据长度
	private function getDataLength($version, $level){
					return qrcode_table::$capacity[$version][1] - qrcode_table::$capacity[$version][3][$level];
	}//END getDataLength

	//appendNum 追加数
	private function appendNum($bits, $num){
		if ($bits == 0)  return 0;
		$bstream = new qrcode_string();
		$bstream->data = array();
		$b = $this->newFromNum($bits, $num);
		if(is_null($b)) return -1;
		$ret = $this->append_size($b);
		unset($b);
		return $ret;
	}//END appendNum

	//new  新数
	private function newFromNum($bits, $num){
		$bstream = new qrcode_string();
		$bstream->data = array();
		$bstream->allocate($bits);
		$mask = 1 << ($bits - 1);
		for($i=0; $i<$bits; ++$i){
			if($num & $mask){
				$bstream->data[$i] = 1;
			} else {
				$bstream->data[$i] = 0;
			}
			$mask = $mask >> 1;
		}
		return $bstream;
	}//END newFromNum

	//allocate 分配
	private function allocate($setLength){
		$this ->data = array_fill(0, $setLength, 0);
		return 0;
	}//END allocate

	//append 追加字节
	private function appendBytes($size, $data){
		if ($size == 0) return 0;
		$bstream = new qrcode_string();
		$bstream->data = array();
		$b = $bstream->newFromBytes($size, $data);
		if(is_null($b)) return -1;
		$ret = $this->append_size($b);
		unset($b);
		return $ret;
	}//END appendBytes

	//new 新的字节
	private function newFromBytes($size, $data){
		$bstream = new qrcode_string();
		$bstream->data = array();
		$bstream->allocate($size * 8);
		$p=0;
		for($i=0; $i<$size; ++$i) {
			$mask = 0x80;
			for($j=0; $j<8; ++$j) {
				if($data[$i] & $mask) {
					$bstream->data[$p] = 1;
				} else {
					$bstream->data[$p] = 0;
				}
				++$p;
				$mask = $mask >> 1;
			}
		}
		return $bstream;
	}//END newFromBytes

	//set 设置版本
	private function setVersion($version){
		$this->version = $version;
	}//END setVersion

	//convert 转换数据
	private function convertData(){
		$ver = $this->estimateVersion();
		if($ver > $this->version) $this->setVersion($ver);
		for(;;){//to 死循环 break 条件停止
				$bits = $this->createBitStream();
				if($bits < 0) return -1;
				$ver = $this->getMinimumVersion((int)(($bits + 7) / 8), $this->level);
				if($ver < 0){
					//ERR::newerr( __LINE__ ,'ERROR: WRONG VERSION');
					return -1;
				} else if($ver > $this->version){
					$this->setVersion($ver);
				} else {
					break;
				}
			}
			return 0;
	}//END convertData

	//estimate 估计版本
	private function estimateVersion(){
		$version = 0;
		$prev = 0;
		do {
			$prev = $version;
			$bits = $this->estimateBitStreamSize($prev);
			$version = $this->getMinimumVersion((int)(($bits + 7) / 8), $this->level);
			if ($version < 0){
					return -1;
			}
		} while ($version > $prev);
		return $version;
	}//END estimateVersion

	//create 创建流
	private function createBitStream(){
		$total = 0;
		foreach($this->items as $item){
			$bits = $item->encodeBitStream($this->version);
			if($bits < 0) return -1;
			$total += $bits;
		}
		return $total;
	}//END createBitStream

	//encodeBitStream 编码比特流
	private function encodeBitStream($version){
		try {
			unset($this->bstream);
			$words = $this->maximumWords($this->mode, $version);
			if($this->size > $words){
				$st1 = $st2 = new qrcode_string();
				$st1->data = $st2->data = array();
				$st1->assignment($this->mode, $words, $this->data);
				$st2->assignment($this->mode, $this->size - $words, array_slice($this->data, $words));
				$st1->encodeBitStream($version);
				$st2->encodeBitStream($version);

				$this->bstream = new qrcode_string();
				$this->bstream->data = array();
				$this->append_size($st1->bstream);
				$this->append_size($st2->bstream);
				unset($st1);
				unset($st2);
			} else {
				$ret = 0;
				switch($this->mode){
					case 0: $ret = $this->encodeModeNum($version);    break;
					case 1: $ret = $this->encodeModeAn($version);    break;
					case 2: $ret = $this->encodeMode8($version);    break;
				//--------------------------  case 3: $ret = $this->encodeModeKanji($version);break; ----------
					case 4: $ret = $this->encodeModeStructure();    break;
					default: break;
				}
				if($ret < 0) return -1;
			}
			return $this->size();
		} catch (Exception $e){
			return -1;
		}
	}//END encodeBitStream

	//maximum 最大词
	private function maximumWords($mode, $version){
		if($mode == 4) return 3;
		if($version <= 9){
			$l = 0;
		} else if($version <= 26){
			$l = 1;
		} else {
			$l = 2;
		}
		$bits = qrcode_table::$lengthTableBits[$mode][$l];
		$words = (1 << $bits) - 1;
		if($mode == 3) $words *= 2; // the number of bytes is required
		return $words;
	}//END maximumWords

	//encode 数编码模式
	private function encodeModeNum($version){
		try {
			$words = (int)($this->size / 3);
			$bstream = new qrcode_string();
			$bstream->data = array();
			$val = 0x1;
			$bstream->appendNum(4, $val);
			$bstream->appendNum($this->lengthIndicator(0, $version), $this->size);
			for($i=0; $i<$words; ++$i){
				$val  = (ord($this->data[$i*3  ]) - ord('0')) * 100;
				$val += (ord($this->data[$i*3+1]) - ord('0')) * 10;
				$val += (ord($this->data[$i*3+2]) - ord('0'));
				$bstream->appendNum(10, $val);
			}
			if($this->size - $words * 3 == 1){
				$val = ord($this->data[$words*3]) - ord('0');
				$bstream->appendNum(4, $val);
			} else if($this->size - $words * 3 == 2){
				$val  = (ord($this->data[$words*3  ]) - ord('0')) * 10;
				$val += (ord($this->data[$words*3+1]) - ord('0'));
				$bstream->appendNum(7, $val);
			}
			$this->bstream = $bstream;
			return 0;

		} catch (Exception $e){
			return -1;
		}
	}//END encodeModeNum

	//encode 编码单方式
	private function encodeModeAn($version){
			try {
				$words = (int)($this->size / 2);
				$bstream = new qrcode_string();
				$bstream->data = array();
				$bstream->appendNum(4, 0x02);
				$bstream->appendNum($this->lengthIndicator(1, $version), $this->size);
				for($i=0; $i<$words; ++$i) {
					$val  = (int)$this->lookAnTable(ord($this->data[$i*2  ])) * 45;
					$val += (int)$this->lookAnTable(ord($this->data[$i*2+1]));
					$bstream->appendNum(11, $val);
				}
				if($this->size & 1) {
					$val = $this->lookAnTable(ord($this->data[$words * 2]));
					$bstream->appendNum(6, $val);
				}
				$this->bstream = $bstream;
				return 0;
			} catch (Exception $e) {
				return -1;
			}
	}//END encodeModeAn

	//encode 编码8模式
	private function encodeMode8($version){
		try {
			$bstream = new qrcode_string();
			$bstream->data = array();
			$bstream->appendNum(4, 0x4);
			$bstream->appendNum($this->lengthIndicator(2, $version), $this->size);
			for($i=0; $i<$this->size; ++$i) {
				$bstream->appendNum(8, ord($this->data[$i]));
			}
			$this->bstream = $bstream;
			return 0;
		} catch (Exception $e) {
			return -1;
		}
	}//END encodeMode8

	//encode 编码结构模式
	private function encodeModeStructure(){
		try {
			$bstream = new qrcode_string();
			$bstream->data = array();
			$bstream->appendNum(4, 0x03);
			$bstream->appendNum(4, ord($this->data[1]) - 1);
			$bstream->appendNum(4, ord($this->data[0]) - 1);
			$bstream->appendNum(8, ord($this->data[2]));
			$this->bstream = $bstream;
			return 0;
		} catch (Exception $e) {
			return -1;
		}
	}// END encodeModeStructure

	//get  获得最小版本
	private function getMinimumVersion($size, $level){
		for($i=1; $i<= 40; ++$i){
			$words  = qrcode_table::$capacity[$i][1] - qrcode_table::$capacity[$i][3][$level];
			if($words >= $size) return $i;
		}
		return -1;
	}//END getMinimumVersion

	//estimate 估计比特流大小
	private function estimateBitStreamSize($version){
		$bits = 0;
		foreach($this->items as $item){
			$bits += $item->estimateBitStreamSizeOfEntry($version);
		}
		return $bits;
	}//END estimateBitStreamSize

	//estimate 输入估计比特流大小
	private function estimateBitStreamSizeOfEntry($version){
		$bits = 0;
		if($version == 0) $version = 1;
		switch($this->mode){
			case 0: $bits = $this->estimateBitsModeNum($this->size); break;
			case 1: $bits = $this->estimateBitsModeAn($this->size); break;
			case 2: $bits = $this->estimateBitsMode8($this->size); break;
			// --------------- case 3: $bits = $this->estimateBitsModeKanji($this->size); break;
			case 4:    return 20;
			default: return 0;
		}
		$l = $this->lengthIndicator($this->mode, $version);
		$m = 1 << $l;
		$num = (int)(($this->size + $m - 1) / $m);
		$bits += $num * (4 + $l);
		return $bits;
	}//END estimateBitStreamSizeOfEntry

	//----------------------------------------NO: 第二分支 字符串系列 to------------------------------------------

	//split 拆分字符串
	private function splitString(){
		while (strlen($this->dataStr) > 0){
			if($this->dataStr == '') return 0;
			$mode = $this->identifyMode(0);
			switch ($mode){
				case 0: $length = $this->eatNum(); break;
				case 1: $length = $this->eatAn(); break;
				//----------------  case 3: $length = $this->eatKanji(); break;
				default: $length = $this->eat8(); break;
			}
			if($length == 0) return 0;
			if($length < 0)  return -1;
			$this->dataStr = substr($this->dataStr, $length);
		}
		$items = $this->items;
		unset($this->dataStr);//delete
		return $items;
	}//END splitString

	//Num  数字
	private function eatNum(){
		$ln = $this->lengthIndicator(0, $this->version);
		$p = 0;
		while($this->isdigitat($this->dataStr, $p)){
			++$p;
		}
		$run = $p;
		$mode = $this->identifyMode($p);
		if($mode == 2){
			$dif = $this->estimateBitsModeNum($run) + 4 + $ln
				+ $this->estimateBitsMode8(1)         // + 4 + l8
				- $this->estimateBitsMode8($run + 1); // - 4 - l8
			if($dif > 0) return $this->eat8();
		}
		if($mode == 1){
			$dif = $this->estimateBitsModeNum($run) + 4 + $ln
				+ $this->estimateBitsModeAn(1)        // + 4 + la
				- $this->estimateBitsModeAn($run + 1);// - 4 - la
			if($dif > 0) return $this->eatAn();
		}
		$ret = $this->append(0, $run, str_split($this->dataStr));
		if($ret < 0) return -1;
		return $run;
	}//END eatNum

	//An 单个
	private function eatAn(){
		$la = $this->lengthIndicator(1,  $this->version);
		$ln = $this->lengthIndicator(0, $this->version);
		$p = 0;
		while($this->isalnumat($this->dataStr, $p)){
			if($this->isdigitat($this->dataStr, $p)){
				$q = $p;
				while($this->isdigitat($this->dataStr, $q)){
					++$q;
				}
				$dif = $this->estimateBitsModeAn($p) // + 4 + la
					+ $this->estimateBitsModeNum($q - $p) + 4 + $ln
					- $this->estimateBitsModeAn($q); // - 4 - la
				if($dif < 0){
					break;
				} else {
					$p = $q;
				}
			} else {
				++$p;
			}
		}
		$run = $p;
		if(!$this->isalnumat($this->dataStr, $p)){
			$dif = $this->estimateBitsModeAn($run) + 4 + $la
				+ $this->estimateBitsMode8(1) // + 4 + l8
				- $this->estimateBitsMode8($run + 1); // - 4 - l8
			if($dif > 0) return $this->eat8();
		}
		$ret = $this->append(1, $run, str_split($this->dataStr));
		if($ret < 0) return -1;
		return $run;
	}//END eatAn

	/*----------------------------------
	//Kanji  汉字
	function eatKanji(){
		$p = 0;
		while($this->identifyMode($p) == 3){
			$p += 2;
		}
		$ret = $this->append(3, $p, str_split($this->dataStr));
		if($ret < 0) return -1;
		return $run;
	}//END eatKanji
	------------------------------ */

	//eat8
	private function eat8(){
		$la = $this->lengthIndicator(1, $this->version);
		$ln = $this->lengthIndicator(0, $this->version);
		$p = 1;
		$dataStrLen = strlen($this->dataStr);
		while($p < $dataStrLen){
			$mode = $this->identifyMode($p);
			if($mode == 3) break; //xxxxxxx
			if($mode == 0){
				$q = $p;
				while($this->isdigitat($this->dataStr, $q)){
					++$q;
				}
				$dif = $this->estimateBitsMode8($p) // + 4 + l8
					+ $this->estimateBitsModeNum($q - $p) + 4 + $ln
					- $this->estimateBitsMode8($q); // - 4 - l8
				if($dif < 0){
					break;
				} else {
					$p = $q;
				}
			} else if($mode == 1){
				$q = $p;
				while($this->isalnumat($this->dataStr, $q)){
					++$q;
				}
				$dif = $this->estimateBitsMode8($p)  // + 4 + l8
					+ $this->estimateBitsModeAn($q - $p) + 4 + $la
					- $this->estimateBitsMode8($q); // - 4 - l8
				if($dif < 0){
					break;
				} else {
					$p = $q;
				}
			} else {
				++$p;
			}
		}
		$run = $p;
		$ret = $this->append(2, $run, str_split($this->dataStr));
		if($ret < 0) return -1;
		return $run;
	}//END eat8

	//length 长度指示
	private function lengthIndicator($mode, $version){
		if ($mode == 4) return 0;
		if ($version <= 9){
			$l = 0;
		} else if ($version <= 26){
			$l = 1;
		} else {
			$l = 2;
		}
		return qrcode_table::$lengthTableBits[$mode][$l];
	}//END lengthIndicator

	//estimate 估算位数 数字
	private function estimateBitsModeNum($size){
		$w = (int)$size / 3;
		$bits = $w * 10;
		switch($size - $w * 3){
			case 1: $bits += 4; break;
			case 2: $bits += 7; break;
			default: break;
		}
		return $bits;
	}//END estimateBitsModeNum

	//estimate 估算位数 模式8
	private function estimateBitsMode8($size){
		return $size * 8;
	}//END estimateBitsMode8

	/*-------------------------
	//estimate 估计比特模式汉字
	function estimateBitsModeKanji($size){
			return (int)(($size / 2) * 13);
	}//END estimate
	-------------------------------------------*/

	//estimate 估算位数 单个
	private function estimateBitsModeAn($size){
		$w = (int)($size / 2);
		$bits = $w * 11;
		if($size & 1) $bits += 6;
		return $bits;
	}//END estimateBitsModeAn

	//----------------------------------------NO: 第一分支 模式系列 to------------------------------------------

	//identify 匹配ID模式
	private function identifyMode($pos){
		if($pos >= strlen($this->dataStr)) return -1;
		$c = $this->dataStr[$pos];
		if($this->isdigitat($this->dataStr, $pos)){
			return 0;
		}else if($this->isalnumat($this->dataStr, $pos)){
			return 1;
		}
		/*----------------------------------------------
		else if($this->modeHint == 3){ // modeHint =0/2  不可能是3
			if($pos+1 < strlen($this->dataStr)){
				$d = $this->dataStr[$pos+1];
				$word = (ord($c) << 8) | ord($d);
				if(($word >= 0x8140 && $word <= 0x9ffc) || ($word >= 0xe040 && $word <= 0xebbf)){
					return 3;
				}
			}
		}
		---------------------------------- */
		return 2;
	}//END identifyMode

	//isdigitat  是否数字
	private function isdigitat($str, $pos){
			if ($pos >= strlen($str)) return false;
			return ((ord($str[$pos]) >= ord('0'))&&(ord($str[$pos]) <= ord('9')));
	}//END isdigitat

	//isalnumat  是否
	private function isalnumat($str, $pos){
			if ($pos >= strlen($str)) return false;
			return ($this->lookAnTable(ord($str[$pos])) >= 0);
	}//END isalnumat

	//Table  查询表
	private function lookAnTable($c){
			return (($c > 127) ? -1 : qrcode_table::$anTable[$c]);
	}//END lookAnTable

}//END class qrcode_string





/*
 * QR Code Table 表
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/
class qrcode_table{

	//Info 格式信息
	public static $formatInfo = array(
		array(0x77c4, 0x72f3, 0x7daa, 0x789d, 0x662f, 0x6318, 0x6c41, 0x6976),
		array(0x5412, 0x5125, 0x5e7c, 0x5b4b, 0x45f9, 0x40ce, 0x4f97, 0x4aa0),
		array(0x355f, 0x3068, 0x3f31, 0x3a06, 0x24b4, 0x2183, 0x2eda, 0x2bed),
		array(0x1689, 0x13be, 0x1ce7, 0x19d0, 0x0762, 0x0255, 0x0d0c, 0x083b)
	);

	//ECC表
	public static $eccTable = array(
		array(array( 0,  0), array( 0,  0), array( 0,  0), array( 0,  0)),
		array(array( 1,  0), array( 1,  0), array( 1,  0), array( 1,  0)), // 1
		array(array( 1,  0), array( 1,  0), array( 1,  0), array( 1,  0)),
		array(array( 1,  0), array( 1,  0), array( 2,  0), array( 2,  0)),
		array(array( 1,  0), array( 2,  0), array( 2,  0), array( 4,  0)),
		array(array( 1,  0), array( 2,  0), array( 2,  2), array( 2,  2)), // 5
		array(array( 2,  0), array( 4,  0), array( 4,  0), array( 4,  0)),
		array(array( 2,  0), array( 4,  0), array( 2,  4), array( 4,  1)),
		array(array( 2,  0), array( 2,  2), array( 4,  2), array( 4,  2)),
		array(array( 2,  0), array( 3,  2), array( 4,  4), array( 4,  4)),
		array(array( 2,  2), array( 4,  1), array( 6,  2), array( 6,  2)), //10
		array(array( 4,  0), array( 1,  4), array( 4,  4), array( 3,  8)),
		array(array( 2,  2), array( 6,  2), array( 4,  6), array( 7,  4)),
		array(array( 4,  0), array( 8,  1), array( 8,  4), array(12,  4)),
		array(array( 3,  1), array( 4,  5), array(11,  5), array(11,  5)),
		array(array( 5,  1), array( 5,  5), array( 5,  7), array(11,  7)), //15
		array(array( 5,  1), array( 7,  3), array(15,  2), array( 3, 13)),
		array(array( 1,  5), array(10,  1), array( 1, 15), array( 2, 17)),
		array(array( 5,  1), array( 9,  4), array(17,  1), array( 2, 19)),
		array(array( 3,  4), array( 3, 11), array(17,  4), array( 9, 16)),
		array(array( 3,  5), array( 3, 13), array(15,  5), array(15, 10)), //20
		array(array( 4,  4), array(17,  0), array(17,  6), array(19,  6)),
		array(array( 2,  7), array(17,  0), array( 7, 16), array(34,  0)),
		array(array( 4,  5), array( 4, 14), array(11, 14), array(16, 14)),
		array(array( 6,  4), array( 6, 14), array(11, 16), array(30,  2)),
		array(array( 8,  4), array( 8, 13), array( 7, 22), array(22, 13)), //25
		array(array(10,  2), array(19,  4), array(28,  6), array(33,  4)),
		array(array( 8,  4), array(22,  3), array( 8, 26), array(12, 28)),
		array(array( 3, 10), array( 3, 23), array( 4, 31), array(11, 31)),
		array(array( 7,  7), array(21,  7), array( 1, 37), array(19, 26)),
		array(array( 5, 10), array(19, 10), array(15, 25), array(23, 25)), //30
		array(array(13,  3), array( 2, 29), array(42,  1), array(23, 28)),
		array(array(17,  0), array(10, 23), array(10, 35), array(19, 35)),
		array(array(17,  1), array(14, 21), array(29, 19), array(11, 46)),
		array(array(13,  6), array(14, 23), array(44,  7), array(59,  1)),
		array(array(12,  7), array(12, 26), array(39, 14), array(22, 41)), //35
		array(array( 6, 14), array( 6, 34), array(46, 10), array( 2, 64)),
		array(array(17,  4), array(29, 14), array(49, 10), array(24, 46)),
		array(array( 4, 18), array(13, 32), array(48, 14), array(42, 32)),
		array(array(20,  4), array(40,  7), array(43, 22), array(10, 67)),
		array(array(19,  6), array(18, 31), array(34, 34), array(20, 61)),//40
		);

	//length 长度表位
	public static $lengthTableBits = array(array(10, 12, 14), array( 9, 11, 13), array( 8, 16, 16), array( 8, 10, 12));

	//grade 识别等级
	public static $grade = array('L'=> 0, 'M'=> 1, 'Q'=> 2,'H'=> 3);

	//an 单一表
	public static $anTable = array(
		-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
		-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
		36, -1, -1, -1, 37, 38, -1, -1, -1, -1, 39, 40, -1, 41, 42, 43,
		0,  1,  2,  3,  4,  5,  6,  7,  8,  9, 44, -1, -1, -1, -1, -1,
		-1, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24,
		25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, -1, -1, -1, -1, -1,
		-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
		-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1);

	//versionPattern 版本模式表
	public static $versionPattern = array(
		0x07c94, 0x085bc, 0x09a99, 0x0a4d3, 0x0bbf6, 0x0c762, 0x0d847, 0x0e60d,
		0x0f928, 0x10b78, 0x1145d, 0x12a17, 0x13532, 0x149a6, 0x15683, 0x168c9,
		0x177ec, 0x18ec4, 0x191e1, 0x1afab, 0x1b08e, 0x1cc1a, 0x1d33f, 0x1ed75,
		0x1f250, 0x209d5, 0x216f0, 0x228ba, 0x2379f, 0x24b0b, 0x2542e, 0x26a64,
		0x27541, 0x28c69);

	//Pattern 排列方式表
	public static $alignmentPattern = array(
		array( 0,  0),
		array( 0,  0), array(18,  0), array(22,  0), array(26,  0), array(30,  0), // 1- 5
		array(34,  0), array(22, 38), array(24, 42), array(26, 46), array(28, 50), // 6-10
		array(30, 54), array(32, 58), array(34, 62), array(26, 46), array(26, 48), //11-15
		array(26, 50), array(30, 54), array(30, 56), array(30, 58), array(34, 62), //16-20
		array(28, 50), array(26, 50), array(30, 54), array(28, 54), array(32, 58), //21-25
		array(30, 58), array(34, 62), array(26, 50), array(30, 54), array(26, 52), //26-30
		array(30, 56), array(34, 60), array(30, 58), array(34, 62), array(30, 54), //31-35
		array(24, 50), array(28, 54), array(32, 58), array(26, 54), array(30, 58) );//35-40

	//capacity 容量
	public static $capacity = array(
		array(  0,    0, 0, array(   0,    0,    0,    0)),
		array( 21,   26, 0, array(   7,   10,   13,   17)), // 1
		array( 25,   44, 7, array(  10,   16,   22,   28)),
		array( 29,   70, 7, array(  15,   26,   36,   44)),
		array( 33,  100, 7, array(  20,   36,   52,   64)),
		array( 37,  134, 7, array(  26,   48,   72,   88)), // 5
		array( 41,  172, 7, array(  36,   64,   96,  112)),
		array( 45,  196, 0, array(  40,   72,  108,  130)),
		array( 49,  242, 0, array(  48,   88,  132,  156)),
		array( 53,  292, 0, array(  60,  110,  160,  192)),
		array( 57,  346, 0, array(  72,  130,  192,  224)), //10
		array( 61,  404, 0, array(  80,  150,  224,  264)),
		array( 65,  466, 0, array(  96,  176,  260,  308)),
		array( 69,  532, 0, array( 104,  198,  288,  352)),
		array( 73,  581, 3, array( 120,  216,  320,  384)),
		array( 77,  655, 3, array( 132,  240,  360,  432)), //15
		array( 81,  733, 3, array( 144,  280,  408,  480)),
		array( 85,  815, 3, array( 168,  308,  448,  532)),
		array( 89,  901, 3, array( 180,  338,  504,  588)),
		array( 93,  991, 3, array( 196,  364,  546,  650)),
		array( 97, 1085, 3, array( 224,  416,  600,  700)), //20
		array(101, 1156, 4, array( 224,  442,  644,  750)),
		array(105, 1258, 4, array( 252,  476,  690,  816)),
		array(109, 1364, 4, array( 270,  504,  750,  900)),
		array(113, 1474, 4, array( 300,  560,  810,  960)),
		array(117, 1588, 4, array( 312,  588,  870, 1050)), //25
		array(121, 1706, 4, array( 336,  644,  952, 1110)),
		array(125, 1828, 4, array( 360,  700, 1020, 1200)),
		array(129, 1921, 3, array( 390,  728, 1050, 1260)),
		array(133, 2051, 3, array( 420,  784, 1140, 1350)),
		array(137, 2185, 3, array( 450,  812, 1200, 1440)), //30
		array(141, 2323, 3, array( 480,  868, 1290, 1530)),
		array(145, 2465, 3, array( 510,  924, 1350, 1620)),
		array(149, 2611, 3, array( 540,  980, 1440, 1710)),
		array(153, 2761, 3, array( 570, 1036, 1530, 1800)),
		array(157, 2876, 0, array( 570, 1064, 1590, 1890)), //35
		array(161, 3034, 0, array( 600, 1120, 1680, 1980)),
		array(165, 3196, 0, array( 630, 1204, 1770, 2100)),
		array(169, 3362, 0, array( 660, 1260, 1860, 2220)),
		array(173, 3532, 0, array( 720, 1316, 1950, 2310)),
		array(177, 3706, 0, array( 750, 1372, 2040, 2430)) );//40

}//END class qrcode_table




/*
 * QR Code Pattern 40种模式
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/
class qrcode_pattern{

		public $frames = array();

	//get 获得宽度 public
	function getWidth($version){
		return qrcode_table::$capacity[$version][0];
	}//END getWidth

	//new 新框架 public
	function newFrame($version){
		if($version < 1 || $version > 40) return null;
		if(!isset($this->frames[$version])) $this->frames[$version] = $this->createFrame($version);
		if(is_null($this->frames[$version])) return null;
		return $this->frames[$version];
	}//END newFrame

	//create 创建框架
	private function createFrame($version){
		$width = qrcode_table::$capacity[$version][0];
		$frameLine = str_repeat ("\0", $width);
		$frame = array_fill(0, $width, $frameLine);
		// Finder pattern
		$this->putFinderPattern($frame, 0, 0);
		$this->putFinderPattern($frame, $width - 7, 0);
		$this->putFinderPattern($frame, 0, $width - 7);
		// Separator
		$yOffset = $width - 7;
		for($y=0; $y<7; ++$y){
			$frame[$y][7] = "\xc0";
			$frame[$y][$width - 8] = "\xc0";
			$frame[$yOffset][7] = "\xc0";
			++$yOffset;
		}
		$setPattern = str_repeat("\xc0", 8);
		$this->set($frame, 0, 7, $setPattern);
		$this->set($frame, $width-8, 7, $setPattern);
		$this->set($frame, 0, $width - 8, $setPattern);
		// Format info
		$setPattern = str_repeat("\x84", 9);
		$this->set($frame, 0, 8, $setPattern);
		$this->set($frame, $width - 8, 8, $setPattern, 8);
		$yOffset = $width - 8;
		for($y=0; $y<8; ++$y,++$yOffset){
			$frame[$y][8] = "\x84";
			$frame[$yOffset][8] = "\x84";
		}
		// Timing pattern
		for($i=1; $i<$width-15; ++$i){
			$frame[6][7+$i] = chr(0x90 | ($i & 1));
			$frame[7+$i][6] = chr(0x90 | ($i & 1));
		}
		// Alignment pattern
		$this->putAlignmentPattern($version, $frame, $width);
		// Version information
		if($version >= 7){
			$vinf =  $this->getVersionPattern($version);
			$v = $vinf;
			for($x=0; $x<6; ++$x){
				for($y=0; $y<3; ++$y){
					$frame[($width - 11)+$y][$x] = chr(0x88 | ($v & 1));
					$v = $v >> 1;
				}
			}
			$v = $vinf;
			for($y=0; $y<6; ++$y){
				for($x=0; $x<3; ++$x){
					$frame[$y][$x+($width - 11)] = chr(0x88 | ($v & 1));
					$v = $v >> 1;
				}
			}
		}
		// and a little bit...
		$frame[$width - 8][8] = "\x81";
		return $frame;
	}//END createFrame

	//get 获得版本模式
	private function getVersionPattern($version){
		if($version < 7 || $version > 40){
			return 0;
		}else{
			return qrcode_table::$versionPattern[$version -7];
		}
	} //END getVersionPattern

	//put 搜索模式
	private function putFinderPattern(&$frame, $ox, $oy){
		$finder = array(
			"\xc1\xc1\xc1\xc1\xc1\xc1\xc1",
			"\xc1\xc0\xc0\xc0\xc0\xc0\xc1",
			"\xc1\xc0\xc1\xc1\xc1\xc0\xc1",
			"\xc1\xc0\xc1\xc1\xc1\xc0\xc1",
			"\xc1\xc0\xc1\xc1\xc1\xc0\xc1",
			"\xc1\xc0\xc0\xc0\xc0\xc0\xc1",
			"\xc1\xc1\xc1\xc1\xc1\xc1\xc1"
		);
		for($y=0; $y<7; ++$y){
			$this->set($frame, $ox, $oy+$y, $finder[$y]);
		}
	} //END putFinderPattern

	//put 排列方式
	private function putAlignmentPattern($version, &$frame, $width){
		if($version < 2) return;
		$d = qrcode_table::$alignmentPattern[$version][1] - qrcode_table::$alignmentPattern[$version][0];
		if($d < 0){
			$w = 2;
		} else {
			$w = (int)(($width - qrcode_table::$alignmentPattern[$version][0]) / $d + 2);
		}
		if($w * $w - 3 == 1){
			$x = qrcode_table::$alignmentPattern[$version][0];
			$y = qrcode_table::$alignmentPattern[$version][0];
			$this->putAlignmentMarker($frame, $x, $y);
			return;
		}
		$cx = qrcode_table::$alignmentPattern[$version][0];
		for($x=1; $x<$w - 1; ++$x){
			$this->putAlignmentMarker($frame, 6, $cx);
			$this->putAlignmentMarker($frame, $cx,  6);
			$cx += $d;
		}
		$cy = qrcode_table::$alignmentPattern[$version][0];
		for($y=0; $y<$w-1; ++$y){
			$cx = qrcode_table::$alignmentPattern[$version][0];
			for($x=0; $x<$w-1; ++$x){
				$this->putAlignmentMarker($frame, $cx, $cy);
				$cx += $d;
			}
			$cy += $d;
		}
	} //END putAlignmentPattern

	//put 对准标记
	private function putAlignmentMarker(array &$frame, $ox, $oy){
		$finder = array(
			"\xa1\xa1\xa1\xa1\xa1",
			"\xa1\xa0\xa0\xa0\xa1",
			"\xa1\xa0\xa1\xa0\xa1",
			"\xa1\xa0\xa0\xa0\xa1",
			"\xa1\xa1\xa1\xa1\xa1"
		);
		$yStart = $oy-2;
		$xStart = $ox-2;
		for($y=0; $y<5; ++$y){
			$this->set($frame, $xStart, $yStart+$y, $finder[$y]);
		}
	} //END putAlignmentMarker

	//set 填入数据
	private function set(&$srctab, $x, $y, $repl, $replLen = false){
		$srctab[$y] = substr_replace($srctab[$y], ($replLen !== false)?substr($repl,0,$replLen):$repl, $x, ($replLen !== false)?$replLen:strlen($repl));
	} //END set
}//END class qrcode_pattern
