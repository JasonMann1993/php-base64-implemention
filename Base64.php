<?php
/**
 * base64字符十进制的值 转化为 base64对应的字符
 */
function normalToBase64Char($num)
{
    if ($num >= 0 && $num <= 25) {
        return chr(ord('A') + $num);
    } else if ($num >= 26 && $num <= 51) {
        return chr(ord('a') + ($num - 26));
    } else if ($num >= 52 && $num <= 61) {
        return chr(ord('0') + ($num - 52));
    } else if ($num == 62) {
        return '+';
    } else {
        return '/';
    }
}

/**
 * base64字符的ascii  转化为 对应的base64的十进制的值
 */
function base64CharToInt($num)
{
    if ($num >= 65 && $num <= 90) {
        return ($num - 65);
    } else if ($num >= 97 && $num <= 122) {
        return ($num - 97) + 26;
    } else if ($num >= 48 && $num <= 57) {
        return ($num - 48) + 52;
    } else if ($num == 43) {
        return 62;
    } else {
        return 63;
    }
}

/**
 * 手动实现 base64转码
 */
function encode($content)
{
    $len = strlen($content);
    $loop = intval($len / 3);//完整组合
    $rest = $len % 3;//剩余字节数，需要补齐
    $ret = "";
    //首先计算完整组合
    for ($i = 0; $i < $loop; $i++) {
        $base_offset = 3 * $i;
        //每三个字节组合成一个无符号的24位的整数
        $int_24 = (ord($content[$base_offset]) << 16)
            | (ord($content[$base_offset + 1]) << 8)
            | (ord($content[$base_offset + 2]) << 0);
        //6位一组，每一组都进行Base64字符串转换
        $ret .= normalToBase64Char($int_24 >> 18);
        echo base_convert($int_24 >> 12 & 0x3f,10,2)."\n";
        $ret .= normalToBase64Char(($int_24 >> 12) & 0x3f);
        $ret .= normalToBase64Char(($int_24 >> 6) & 0x3f);
        $ret .= normalToBase64Char($int_24 & 0x3f);
    }
    //需要补齐的情况
    if ($rest == 0) {
        return $ret;
    } else if ($rest == 1) {
        //剩余1个字节，此时需要补齐4位
        $int_12 = ord($content[$loop * 3]) << 4;
        $ret .= normalToBase64Char($int_12 >> 6);
        $ret .= normalToBase64Char($int_12 & 0x3f);
        $ret .= "==";
        return $ret;
    } else {
        //剩余2个字节，需要补齐2位
        $int_18 = ((ord($content[$loop * 3]) << 8) | ord($content[$loop * 3 + 1])) << 2;
        $ret .= normalToBase64Char($int_18 >> 12);
        $ret .= normalToBase64Char(($int_18 >> 6) & 0x3f);
        $ret .= normalToBase64Char($int_18 & 0x3f);
        $ret .= "=";
        return $ret;
    }
}

function decode($content)
{
    $len = strlen($content);
    if ($content[$len - 1] == '=' && $content[$len - 2] == '=') {
        //说明加密的时候，剩余1个字节，补齐了4位，也就是左移了4位，所以除了最后包含的2个字符，前面的所有字符可以4个字符一组
        $last_chars = substr($content, -4);
        $full_chars = substr($content, 0, $len - 4);
        $type = 1;
    } else if ($content[$len - 1] == '=') {
        //说明加密的时候，剩余2个字节，补齐了2位，也就是左移了2位，所以除了最后包含的3个字符，前面的所有字符可以4个字符一组
        $last_chars = substr($content, -4);
        $full_chars = substr($content, 0, $len - 4);
        $type = 2;
    } else {
        $type = 3;
        $full_chars = $content;
    }

    //首先处理完整的部分
    $loop = strlen($full_chars) / 4;
    $ret = "";
    for ($i = 0; $i < $loop; $i++) {
        $base_offset = 4 * $i;
        $int_24 = (base64CharToInt(ord($full_chars[$base_offset])) << 18)
            | (base64CharToInt(ord($full_chars[$base_offset + 1])) << 12)
            | (base64CharToInt(ord($full_chars[$base_offset + 2])) << 6)
            | (base64CharToInt(ord($full_chars[$base_offset + 3])) << 0);
        $ret .= chr($int_24 >> 16);
        $ret .= chr(($int_24 >> 8) & 0xff);
        $ret .= chr($int_24 & 0xff);
    }
    //紧接着处理补齐的部分
    if ($type == 1) {
        $l_char = chr(((base64CharToInt(ord($last_chars[0])) << 6)
                | (base64CharToInt(ord($last_chars[1])))) >> 4);
        $ret .= $l_char;
    } else if ($type == 2) {
        $l_two_chars = ((base64CharToInt(ord($last_chars[0])) << 12)
                | (base64CharToInt(ord($last_chars[1])) << 6)
                | (base64CharToInt(ord($last_chars[2])) << 0)) >> 2;
        $ret .= chr($l_two_chars >> 8);
        $ret .= chr($l_two_chars & 0xff);
    }
    return $ret;
}

//echo base64_encode('123')."\n";
echo encode('123')."\n";
//$a = '我';
//echo $a[0].$a[1].$a[2];
//echo base_convert('3f',16,2);
