<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Config;
use Mail;

class CommonFunctions extends Model
{

	public static  function create_jpeg_thumbnail($imgSrc,$thumbDirectory,$thumbnail_width,$thumbnail_height,$image) {

         //$imgSrc is a FILE - Returns an image resource.
        $thumbDirectory = trim($thumbDirectory);
		$imageSourceExploded = explode('/', $imgSrc);
		$imageName = $imageSourceExploded[count($imageSourceExploded)-1];
		$imageDirectory = str_replace($imageName, '', $imgSrc);
		$filetype = explode('.',$imageName);
		$filetype = strtolower($filetype[count($filetype)-1]);

		//getting the image dimensions
		list($width_orig, $height_orig) = getimagesize($imgSrc);


		//$myImage = imagecreatefromjpeg($imgSrc);
		if ($filetype == 'jpg'  or $filetype == 'JPG' ) {
			$myImage = imagecreatefromjpeg("$imageDirectory/$imageName");
		} else
		if ($filetype == 'jpeg'  or $filetype == 'JPEG' ) {
			$myImage = imagecreatefromjpeg("$imageDirectory/$imageName");
		} else
		if ($filetype == 'png'  or $filetype == 'PNG' ) {
			$myImage = imagecreatefrompng("$imageDirectory/$imageName");
		} else
		if ($filetype == 'gif' or $filetype == 'GIF') {
			$myImage = imagecreatefromgif("$imageDirectory/$imageName");
		}

        $ratio_orig = $width_orig/$height_orig;

		if ($thumbnail_width/$thumbnail_height > $ratio_orig) {
            $new_height = $thumbnail_width/$ratio_orig;
            $new_width = $thumbnail_width;
		}  else{
            $new_width = $thumbnail_height*$ratio_orig;
            $new_height = $thumbnail_height;
		}

		$x_mid = $new_width/2;  //horizontal middle
		$y_mid = $new_height/2; //vertical middle

        $process = imagecreatetruecolor(round($new_width), round($new_height));

		if($ratio_orig>=1){
            imagecopyresampled($process, $myImage, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);

            $thumb = imagecreatetruecolor($thumbnail_width, $thumbnail_height);

            imagecopyresampled($thumb, $process, 0, 0, ($x_mid-($thumbnail_width/2)), ($y_mid-($thumbnail_height/2)), $thumbnail_width, $thumbnail_height, $thumbnail_width, $thumbnail_height);
		}else{
			$ratio_desc = ceil(($thumbnail_height/$height_orig)*100);
			$new_height = round(($ratio_desc/100)*$height_orig);
			$new_width = round(($ratio_desc/100)*$width_orig);

			$thumb = imagecreatetruecolor($thumbnail_width, $thumbnail_height) ;
			// fill rest color with grey background
			$grey = imagecolorallocate($thumb, 62, 62, 62);
			imagefill($thumb, 0, 0, $grey);

			imagecopyresampled($thumb, $myImage, round(($thumbnail_width-$new_width)/2), 0, 0, 0, $new_width, $thumbnail_height, $width_orig, $height_orig);
		}

		$thumbImageName = $image;
		$destination = $thumbDirectory=='' ? $thumbImageName : $thumbDirectory."/".$thumbImageName;
		imagejpeg($thumb, $destination, 100);
		return $thumbImageName;
	}

  public static function calculateDetailAmount($basic_amount){
		$tax                = Config::get('constants.tax');
		$additional_charges = Config::get('constants.additional_charges');
		$final_price        = (float)$basic_amount + (((float)$basic_amount*(float)$tax)/100) + (float)$additional_charges;
		return $final_price;
	}

	public static function saleTax($basic_amount){
		$tax                = Config::get('constants.tax');
		$additional_charges = Config::get('constants.additional_charges');
		$final_price        = (((float)$basic_amount*(float)$tax)/100)+(float)$additional_charges;

		return ($final_price);
	}

	public static function send_larvel_default_mail($email_file_path,$data) {
		$response=Mail::send($email_file_path,$data, function($message) use ($data){
			$message->to($data['email']);
			$message->subject($data['subject']);
		});
		
	//	return $response;  
		
		return true;
	}
	
	public static function send_larvel_default_mail_with_attachment($email_file_path,$data) {
		$response=Mail::send($email_file_path,$data, function($message) use ($data){
			$message->to($data['email'],$data['name']);
			$message->subject($data['subject']);
			$message->attach($data['path_to_pdf_file'], [
				'mime' => $data['mime']
			]);
		});
	//	return $response;  
		
		return true;
	}
}
