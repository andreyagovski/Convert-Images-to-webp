<?php

//check if the webp image file exists on the server
if(!function_exists('checkForWebp')){
    function checkForWebp($url){
        $ext = pathinfo($url, PATHINFO_EXTENSION);
        $webp = substr(trim($url), 0, -(strlen($ext))) . 'webp';
        if(file_exists($_SERVER['DOCUMENT_ROOT'] . parse_url ($webp)['path'])){
            return $webp;
        }
        return '';
    }
}

//convert the image file extension to webp no matter if it exists
if(!function_exists('convertImageExtToWebp')){
    function convertImageExtToWebp($url){
        $ext = pathinfo($url, PATHINFO_EXTENSION);
        $webp = substr(trim($url), 0, -(strlen($ext))) . 'webp';
        return $webp;
    }
}


//hooks when get_field('image') is fired, 
//check if there is a webp file of that image field 
//and append it to the returning array
function check_webp_in_field( $value, $post_id, $field )
{    
    if(isset($value['url'])){
        $webp = array();
        $webp['url'] = checkForWebp($value['url']);
        if($webp['url'] != ''){
            $value['webp'] = $webp;
            foreach($value['sizes'] as $key => $size){
                if(is_string($size)){
                    $webp_size = convertImageExtToWebp($size);
                    if($webp_size!=''){
                        $value['webp']['sizes'][$key] = $webp_size;
                    }
                }
            }
        }   
    }

    return $value;
}
add_filter('acf/format_value/type=image', 'check_webp_in_field', 10, 3);


function imageCreateFromAny($filepath) { 
    $type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize() 
    $allowedTypes = array( 
        2,  // [] jpg 
        3,  // [] png 
        6   // [] bmp 
    ); 
    if (!in_array($type, $allowedTypes)) { 
        return false; 
    } 
    switch ($type) {
        case 2 : 
            $im = imageCreateFromJpeg($filepath); 
        break; 
        case 3 : 
            $im = imageCreateFromPng($filepath); 
        break;
        case 6 : 
            $im = imageCreateFromBmp($filepath); 
        break; 
    }
    imagepalettetotruecolor($im);
    return $im;  
}

function convertImagesToWebp($url){
	$ext = pathinfo($url, PATHINFO_EXTENSION);
    $webp = substr(trim($url), 0, -(strlen($ext))) . 'webp';
    $quality = get_option('convert_images_to_webp_quality_webp')!='' ? get_option('convert_images_to_webp_quality_webp') : 70;
    $allowed_files = ['jpg', 'jpeg', 'png', 'bmp'];
    if(in_array($ext, $allowed_files)){
        imagewebp(imageCreateFromAny($url), $webp, $quality);
        return true;
    }else{
        return false;
    }
}

function removeWebpImage($path){
	$ext = pathinfo($path, PATHINFO_EXTENSION);
	unlink(substr(trim($path), 0, -(strlen($ext))) . 'webp');
}