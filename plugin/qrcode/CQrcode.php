<?php


/*
 * Created on Mar 23, 2015
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
class CQrcode {
	/**
	 * $value  二维码内容
	 * $dir  写入本地文件夹
	 * $matrixPointSize  二维码尺寸  6-10
	 * $errorCorrectionLevel  二维码错误级别
	 * $islogo  是否有logo
	 */
	public function qrcode($value,$matrixPointSize,$errorCorrectionLevel,$filename,$showLogo=false) {
		require_once dirname(__FILE__) . '/phpqrcode.php';
		
		//生成二维码图片 
		$logo = __DIR__ . "/logo.png";//准备好的logo图片 
		$path = "" ;
	    $QR = $path.$filename.'.png';
		if(!empty($filename)){
	    	QRcode::png($value, $QR, $errorCorrectionLevel, $matrixPointSize, 2); 
		}else{
			QRcode::png($value, false, $errorCorrectionLevel, $matrixPointSize, 2); 
		}
		
		if($showLogo == TRUE){
			if ($logo !== FALSE) { 
			    $QR = imagecreatefromstring(file_get_contents($QR)); 
			    $logo = imagecreatefromstring(file_get_contents($logo)); 
			    $QR_width = imagesx($QR);//二维码图片宽度 
			    $QR_height = imagesy($QR);//二维码图片高度 
			    $logo_width = imagesx($logo);//logo图片宽度 
			    $logo_height = imagesy($logo);//logo图片高度 
			    $logo_qr_width = $QR_width / 5; 
			    $scale = $logo_width/$logo_qr_width; 
			    $logo_qr_height = $logo_height/$scale; 
			    $from_width = ($QR_width - $logo_qr_width) / 2; 
			    //重新组合图片并调整大小 
			    imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,  
			    $logo_qr_height, $logo_width, $logo_height); 
			} 
			imagepng($QR, $path.$filename.'.png'); 
		}
		//输出图片 
		echo '<img src='.$path.$filename.'.png>'; 
	}

}
?>
