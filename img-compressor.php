<?php
class ICompress {
  // (A) PROPERTIES
  // (A1) DEFAULT SETTINGS
  private $format = "webp"; // COMPRESSED FILE FORMAT
  private $quality = 50; // COMPRESSED QUALITY (0 TO 100)
  private $maxSize = 0; // MAX ALLOWED WIDTH/HEIGHT (0 FOR NONE)

  // (A2) FLAGS
  public $error = "";
  private $allow = ["jpeg", "jpg", "gif", "png", "bmp", "webp"];

  // (B) COMPRESS IMAGE
  function pack ($source, $target=null) {
    // (B1) CHECK SOURCE IMAGE
    if (!is_readable($source)) {
      $this->error = "Cannot read $source";
      return false;
    }

    // (B2) CHECK SOURCE IMAGE FILE TYPE
    $sInfo = pathinfo($source, PATHINFO_ALL);
    $sInfo["extension"] = !isset($sInfo["extension"]) ? "" : strtolower($sInfo["extension"]) ;
    if (!in_array($sInfo["extension"], $this->allow)) {
      $this->error = "Invalid input image - " . $sInfo["extension"];
      return false;
    }

    // (B3) CHECK TARGET (COMPRESSED) IMAGE
    $target = $target===null ? $sInfo["filename"].".". $this->format : $target ;
    $tInfo = pathinfo($target, PATHINFO_ALL);
    $tInfo["extension"] = !isset($tInfo["extension"]) ? "" : strtolower($tInfo["extension"]) ;
    if (!in_array($tInfo["extension"], $this->allow)) {
      $this->error = "Invalid output image - " . $tInfo["extension"];
      return false;
    }

    // (B4) GD OPEN SOURCE IMAGE
    $gdOpen = "imagecreatefrom" . ($sInfo["extension"]=="jpg" ? "jpeg" : $sInfo["extension"]);
    $img = $gdOpen($source);
    if ($img===false) {
      $this->error = "Failed to open $source";
      return false;
    }

    // (B5) RESIZE IMAGE IF NECESSARY
    if ($this->maxSize!=0) {
      // (B5-1) GET SOURCE IMAGE SIZE & ORIENTATION
      $srcW = imagesx($img);
      $srcH = imagesy($img);
      if ($srcW > $srcH) { $srcO = "L"; }
      else if ($srcH > $srcW) { $srcO = "P"; }
      else { $srcO = "S"; }

      // (B5-2) RESIZE
      if (($srcO=="L" || $srcO=="S") && $srcW > $this->maxSize) {
        $newW = $this->maxSize;
        $newH = floor(($this->maxSize / $srcW) * $srcH);
        $img = imagescale($img, $newW, $newH);
      }
      if ($srcO=="P" && $srcH > $this->maxSize) {
        $newW = floor(($this->maxSize / $srcH) * $srcW);
        $newH = $this->maxSize;
        $img = imagescale($img, $newW, $newH);
      }
    }

    // (B6) GD SAVE & COMPRESS IMAGE (IF JPG OR WEBP)
    $gdWrite = "image" . ($tInfo["extension"]=="jpg" ? "jpeg" : $tInfo["extension"]);
    $pass = ($tInfo["extension"]=="jpg" || $tInfo["extension"]=="jpeg" || $tInfo["extension"]=="webp")
      ? $gdWrite($img, $target, $this->quality) : $gdWrite($img, $target);
    if (!$pass) {
      $this->error = "Failed to write to $target";
      return false;
    }

    // (B7) ALL GOOD!
    return true;
  }

  // (C) CHANGE SETTINGS
  function set ($format, $quality, $maxSize) {
    $this->format = $format;
    $this->quality = $quality;
    $this->maxSize = $maxSize;
  }
}

// (D) NEW IMAGE COMPRESSOR
$_IC = new ICompress();
