<?php

class Model_Thumbnail
{
    public static function createThumbnail($filename, $toFilename, $toWidth = false, $toHeight = false, $cropToExactSize = false, $quality = 100)
    {
        if (!$toWidth && !$toHeight) throw new Exception('You have to select either a width or height for your thumbnails');

        $ext = strtolower(end(explode('.', $filename)));
        if      ($ext == 'png') $img = imagecreatefrompng($filename);
        else if ($ext == 'gif') $img = imagecreatefromgif($filename);
        else                    $img = imagecreatefromjpeg($filename); 

        $width  = imagesx($img);
        $height = imagesy($img);

        $x = 0;
        $y = 0;

        if ($toWidth && $toHeight && $cropToExactSize) {

            $factor = $toWidth / $width;
            if ($height*$factor > $toHeight) {
                $tHeight = $toHeight / $factor;
                $y = floor(($height - $tHeight) / 2);
                $height = $tHeight;
            }else{
                $factor = $toHeight / $height;
                $tWidth = $toWidth / $factor;
                $x = floor(($width - $tWidth) / 2);
                $width = $tWidth;
            }

        }else{

            if ($toWidth && $toHeight) {
                // Use the supplied values
            }else if ($toWidth) {
                $factor = $toWidth / $width;
                $toHeight = $height * $factor;
            }else{
                $factor = $toHeight / $height;
                $toWidth = $height * $factor;
            }

        }

        $newimg = imagecreatetruecolor($toWidth, $toHeight); 

        $palsize = ImageColorsTotal($img);  //Get palette size for original image
        for ($i = 0; $i < $palsize; $i++) { 
            $colors = ImageColorsForIndex($img, $i);   
            ImageColorAllocate($newimg, $colors['red'], $colors['green'], $colors['blue']);
        } 

        imagecopyresized($newimg, $img, 0, 0, $x, $y, $toWidth, $toHeight, $width, $height);
        imagejpeg($newimg, $toFilename, $quality);
    }
}

