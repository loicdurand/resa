<?php

namespace App\Service;

class PhotoService
{

  private $IMAGE_HANDLERS;

  public function __construct()
  {

    // Link image type to correct image loader and saver
    // - makes it easier to add additional types later on
    // - makes the function easier to read
    $this->IMAGE_HANDLERS = [
      IMAGETYPE_JPEG => [
        'load' => 'imagecreatefromjpeg',
        'save' => 'imagejpeg',
        'quality' => 100,
        'mimetype' => 'image/jpeg'
      ],
      IMAGETYPE_PNG => [
        'load' => 'imagecreatefrompng',
        'save' => 'imagepng',
        'quality' => 0,
        'mimetype' => 'image/png'

      ],
      IMAGETYPE_GIF => [
        'load' => 'imagecreatefromgif',
        'save' => 'imagegif',
        'mimetype' => 'image/gif'
      ]
    ];
  }

  /**
   * @param $src - a valid file location
   * @param $dest - a valid file target
   * @param $targetWidth - desired output width
   * @param $targetHeight - desired output height or null
   */
  public function createThumbnail($src, $dest, $targetWidth = null, $targetHeight = null)
  {
    $is_portrait = false;
    // 1. Load the image from the given $src
    // - see if the file actually exists
    // - check if it's of a valid image type
    // - load the image resource

    // get the type of the image
    // we need the type to determine the correct loader
    $type = exif_imagetype($src);

    // if no valid type or no handler found -> exit
    if (!$type || !$this->IMAGE_HANDLERS[$type]) {
      return null;
    }

    // load the image with the correct loader
    $image = call_user_func($this->IMAGE_HANDLERS[$type]['load'], $src);

    // no image found at supplied location -> exit
    if (!$image) {
      return null;
    }


    // 2. Create a thumbnail and resize the loaded $image
    // - get the image dimensions
    // - define the output size appropriately
    // - create a thumbnail based on that size
    // - set alpha transparency for GIFs and PNGs
    // - draw the final thumbnail

    // get original image width and height
    $width = imagesx($image);
    $height = imagesy($image);

    // maintain aspect ratio when no height set
    if ($targetHeight == null) {

      // get width to height ratio
      $ratio = $width / $height;

      // if is portrait
      // use ratio to scale height to fit in square
      if ($width > $height) {
        // $is_portrait = true;
        $targetHeight = floor($targetWidth / $ratio);
      }
      // if is landscape
      // use ratio to scale width to fit in square
      else {
        $targetHeight = $targetWidth;
        $targetWidth = floor($targetWidth * $ratio);
      }
    }

    // create duplicate image based on calculated target size
    $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);

    // set transparency options for GIFs and PNGs
    if ($type == IMAGETYPE_GIF || $type == IMAGETYPE_PNG) {

      // make image transparent
      imagecolortransparent(
        $thumbnail,
        imagecolorallocate($thumbnail, 0, 0, 0)
      );

      // additional settings for PNGs
      if ($type == IMAGETYPE_PNG) {
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
      }
    }

    // copy entire source image to duplicate image and resize
    imagecopyresampled(
      $thumbnail,
      $image,
      0,
      0,
      0,
      0,
      $targetWidth,
      $targetHeight,
      $width,
      $height
    );

    if ($is_portrait) {
      // Content type
      header('Content-type: ' . $this->IMAGE_HANDLERS[$type]['mimetype']);
      // Load
      $source = $this->IMAGE_HANDLERS[$type]['load']($src);
      // Rotate
      $rotate = imagerotate($thumbnail, 90 * 3, 0);
      // Output
      call_user_func(
        $this->IMAGE_HANDLERS[$type]['save'],
        $rotate,
        $dest,
        $this->IMAGE_HANDLERS[$type]['quality']
      );
    } else {

      // 3. Save the $thumbnail to disk
      // - call the correct save method
      // - set the correct quality level

      // save the duplicate version of the image to disk
      call_user_func(
        $this->IMAGE_HANDLERS[$type]['save'],
        $thumbnail,
        $dest,
        $this->IMAGE_HANDLERS[$type]['quality']
      );
    }
  }

  public function rotate($src, $rotation = 90)
  {
    $type = exif_imagetype($src);
    $path = $src;
    $angle = -$rotation;
    if($angle < 0)
      $angle = 360 + $angle;

    // if no valid type or no handler found -> exit
    if (!$type || !$this->IMAGE_HANDLERS[$type]) {
      return null;
    }

    // load the image with the correct loader
    $image = call_user_func($this->IMAGE_HANDLERS[$type]['load'], $src);

    // no image found at supplied location -> exit
    if (!$image) {
      return null;
    }

    // Content type
    header('Content-type: ' . $this->IMAGE_HANDLERS[$type]['mimetype']);
    // Load

    $source = $this->IMAGE_HANDLERS[$type]['load']($path);
    // Rotate
    $rotate = imagerotate($image, $angle, 0);
    // Output
    call_user_func(
      $this->IMAGE_HANDLERS[$type]['save'],
      $rotate,
      $path,
      $this->IMAGE_HANDLERS[$type]['quality']
    );

    return 1;

  }


  //exit;
  // }
}
