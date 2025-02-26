<?php
session_start();
// 设置图片的宽度和高度
$width = 100; 
$height = 40;

// 创建一个空白的图像资源
$image = imagecreatetruecolor($width, $height);

// 定义背景颜色
$bgColor = imagecolorallocate($image, 255, 255, 255);
imagefill($image, 0, 0, $bgColor);

// 定义字符集，排除字符 'O' 和 'o'
$characters = 'ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz123456789';
$captcha = '';

// 生成 4 位随机验证码
for ($i = 0; $i < 4; $i++) {
    $captcha .= $characters[rand(0, strlen($characters) - 1)];
}

// 将验证码存储到会话中
$_SESSION['captcha'] = $captcha;

// 定义文本颜色
$textColor = imagecolorallocate($image, 0, 0, 0);

// 在图像上绘制验证码
for ($i = 0; $i < strlen($captcha); $i++) {
    $x = 10 + $i * 20;
    $y = rand(20, 30);
    imagestring($image, 5, $x, $y, $captcha[$i], $textColor);
}

// 添加干扰线
for ($i = 0; $i < 5; $i++) {
    $lineColor = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
    imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $lineColor);
}

// 设置响应头
header('Content-type: image/png');

// 输出图像
imagepng($image);

// 释放图像资源
imagedestroy($image);
?>