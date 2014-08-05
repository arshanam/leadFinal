<?

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Image library
 *
 * @author kgifford
 */
class Image {

    /**
     * Resizes an image
     *
     * @param object $image
     * @param int $width
     * @param int $height
     * @param float $scale
     * @return object The resized image
     */
    function resizeImage($image, $width, $height, $scale, $type = "auto") {
        list($imagewidth, $imageheight, $imageType) = getimagesize($image);
        $imageType = image_type_to_mime_type($imageType);
        $newImageWidth = floor($width * $scale);
        $newImageHeight = floor($height * $scale);
        $newImage = imagecreatetruecolor($newImageWidth, $newImageHeight);
        switch ($imageType) {
            case "image/gif":
                $source = imagecreatefromgif($image);
                break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                $source = imagecreatefromjpeg($image);
                break;
            case "image/png":
            case "image/x-png":
                $source = imagecreatefrompng($image);
                break;
        }
        imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newImageWidth, $newImageHeight, $width, $height);
        $type = ($type == 'auto') ? $imageType : $type;
        switch ($type) {
            case "image/gif":
                imagegif($newImage, $image);
                break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                imagejpeg($newImage, $image, 90);
                break;
            case "image/png":
            case "image/x-png":
                imagepng($newImage, $image);
                break;
        }

        chmod($image, 0777);
        return $image;
    }

    /**
     * Resizes an image to a thumbnail
     *
     * @param string $thumb_image_name
     * @param object $image
     * @param int $width
     * @param int $height
     * @param int $start_width
     * @param int $start_height
     * @param float $scale
     * @param bool $apply_watermark
     * @return object The thumbnail image
     */
    function createThumbnailImage($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale, $apply_watermark = FALSE) {
        list($imagewidth, $imageheight, $imageType) = getimagesize($image);
        $imageType = image_type_to_mime_type($imageType);

        $newImageWidth = floor($width * $scale);
        $newImageHeight = floor($height * $scale);
        $newImage = imagecreatetruecolor($newImageWidth, $newImageHeight);
        switch ($imageType) {
            case "image/gif":
                $source = imagecreatefromgif($image);
                break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                $source = imagecreatefromjpeg($image);
                break;
            case "image/png":
            case "image/x-png":
                $source = imagecreatefrompng($image);
                break;
        }
        imagecopyresampled($newImage, $source, 0, 0, $start_width, $start_height, $newImageWidth, $newImageHeight, $width, $height);

        if($apply_watermark) {
            $watermark = imagecreatefrompng('images/logo-beckett-101x101.png');
            $watermark_width = imagesx($watermark);
            $watermark_height = imagesy($watermark);
            imagecopy($newImage, $watermark, ($newImageWidth - $watermark_width - 10), ($newImageHeight - $watermark_height - 10), 0, 0, $watermark_width, $watermark_height);
        }

        switch ($imageType) {
            case "image/gif":
                imagegif($newImage, $thumb_image_name);
                break;
            case "image/pjpeg":
            case "image/jpeg":
            case "image/jpg":
                imagejpeg($newImage, $thumb_image_name, 90);
                break;
            case "image/png":
            case "image/x-png":
                imagepng($newImage, $thumb_image_name);
                break;
        }
        chmod($thumb_image_name, 0777);
        return $thumb_image_name;
    }

    /**
     * Gets the height of an image
     *
     * @param object $image
     * @return int
     */
    function getHeight($image) {
        $size = getimagesize($image);
        $height = $size[1];
        return $height;
    }

    /**
     * Gets the width of an image
     *
     * @param object $image
     * @return int
     */
    function getWidth($image) {
        $size = getimagesize($image);
        $width = $size[0];
        return $width;
    }

}