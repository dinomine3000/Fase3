<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
        <title>Image Processing</title>

        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

        <link rel="stylesheet" type="text/css" href="../Styles/GlobalStyle.css">
    </head>

    <body>
<?php
    require_once( "../../Lib/lib.php" );
    require_once( "../../Lib/db.php" );
    require_once( "../../Lib/lib-coords.php" );
    require_once( "../../Lib/ImageResize.php" );

    include_once( "config.php" );
    $debug = false;

    // Maximum time allowed for the upload
    set_time_limit( 300 );

    if ( $_FILES['userFile']['error']!=0 ) {
        $msg = showUploadFileError( $_FILES['userFile']['error'] );
        echo "\t\t<p>$msg</p>\n";
        echo "\t\t<p><a href='javascript:history.back()'>Back</a></p>\n";
        echo "\t</body>\n";
        echo "</html>\n";
        die();
    }

    $srcName = $_FILES['userFile']['name'];

    // Read configurations from data base
    $configurations = getConfiguration();
    $dstDir = $configurations['destination'];

    // Destination for the uploaded file
    $src = $_FILES['userFile']['tmp_name'];
    $dst = $dstDir . DIRECTORY_SEPARATOR . /* $USER_ID . DIRECTORY_SEPARATOR . */ $srcName;

    $copyResult = copy($src, $dst);

    if ( $copyResult === false ) {
        $msg = "Could not write '$src' to '$dst'";
        echo "\t\t<p>$msg</p>\n";
        echo "\t\t<p><a href='javascript:history.back()'>Back</a></p>";
        echo "\t</bobdy>\n";
        echo "\t</html>\n";
        die();
    }

    unlink($src);
?>
        <p>File uploaded with success.</p>
<?php
    $fileInfo = finfo_open(FILEINFO_MIME);

    $fileInfoData = finfo_file($fileInfo, $dst);
    
    if ( $debug==true ) {
        echo "<pre>\n";
        print_r( $fileInfoData );
        echo "</pre>\n<br>";
    }
    
    $fileTypeComponents = explode( ";", $fileInfoData);

    $mimeTypeFileUploaded = explode("/", $fileTypeComponents[0]);
    $mimeFileName = $mimeTypeFileUploaded[0];
    $typeFileName = $mimeTypeFileUploaded[1];

    $thumbsDir = $dstDir . DIRECTORY_SEPARATOR . /* $USER_ID . DIRECTORY_SEPARATOR . */ "thumbs";
    $pathParts = pathinfo($dst);

    $lat = $lon = "";

    if ( $_POST['description']!=NULL ) {
        $description = addslashes($_POST['description']);
    }
    else {
        $description = "No description available";
    }

    if ( $_POST['title']!=NULL ) {
        $title = addslashes($_POST['title']);
    }
    else {
        $pathParts = pathinfo($srcName);
        $title = $pathParts['filename'];
    }

    $width = $configurations['thumbWidth'];
    $height = $configurations['thumbHeight'];
?>
        <p>File is of type <?php echo $mimeFileName;?>.</p>
<?php

    $imageFileNameAux = $imageMimeFileName = $imageTypeFileName = null;

    $thumbFileNameAux = $thumbMimeFileName = $thumbTypeFileName = null;

    switch ($mimeFileName) {
        case "image":
            $exif = @exif_read_data($dst, 'IFD0', true);
        
            if ( $exif===false ) {
?>
        <p>No exif header data found.</p>
<?php
            }
            else {
                if ( $debug==true ) {
                    echo "<pre>";
                    foreach ($exif as $key => $section) {
                        foreach ($section as $name => $val) {
                            echo "$key.$name: <br>\n";
                            print_r( $val );
                            echo "<br>\n";
                        }
                    }
                    echo "</pre>\n<br>";
                }

                $gps = @$exif['GPS'];
                if ( $gps!=NULL ) {
                    $latitudeAux = $gps['GPSLatitude'];
                    $latitudeRef = $gps['GPSLatitudeRef'];
                    $longitudeAux = $gps['GPSLongitude'];
                    $longitudeRef = $gps['GPSLongitudeRef'];
                                        
                    if ( ($latitudeAux!=NULL ) && ( $longitudeAux!=NULL ) ) {

                        if ( $debug==true ) {
                            echo '$latitudeAux: '; print_r($latitudeAux);  echo "<br>\n";
                            echo '$latitudeRef: '; print_r($latitudeRef);  echo "<br>\n";
                            echo '$longitudeAux: '; print_r($longitudeAux); echo "<br>\n";
                            echo '$longitudeRef: '; print_r($longitudeRef); echo "<br>\n";
                        }                    
                        
                        $lat = getCoordFromEXIF($latitudeAux, $latitudeRef);
                        $lon = getCoordFromEXIF($longitudeAux, $longitudeRef);
?>
        <p>File latitude: <?php echo $lat;?></p>
        <p>File longitude: <?php echo $lon;?></p>
<?php
                    }
                    else {
?>
        <p>File include GPS information.</p>
<?php
                    }
                }
                else {
?>
        <p>File does not have GPS information.</p>
<?php
                }
            }

            $imageFileNameAux = $dst;
            $imageMimeFileName = "image";
            $imageTypeFileName = $typeFileName;

            $thumbFileNameAux = $thumbsDir . DIRECTORY_SEPARATOR . $pathParts['filename'] . "." . $typeFileName;
            $thumbMimeFileName = "image";
            $thumbTypeFileName = $typeFileName;

            $resizeObj = new ImageResize( $dst );
            $resizeObj->resizeImage($width, $height, 'crop');
            $resizeObj->saveImage($thumbFileNameAux, $typeFileName, 100);
            $resizeObj->close();
            break;

        case "video":
            $size = "$width" . "x" . "$height";

            $imageFileNameAux = $thumbsDir . DIRECTORY_SEPARATOR . $pathParts['filename'] . "-Large.jpg";
            $imageMimeFileName = "image";
            $imageTypeFileName = "jpeg";
            echo "\t\t<p>Generating video 1st image...</p>\n";

            // -itsoffset -1 -> "moves" the film one second forward
            // -i $dst -> input file
            // -vcodec mjpeg -> codec do tipo mjpeg
            // -vframes 1 -> obter uma frame
            // -s 640x480 -> dimensão do output
            $cmdFirstImage = " $ffmpegBinary -itsoffset -1 -i $dst -vcodec mjpeg -vframes 1 -an -f rawvideo -s 640x480 $imageFileNameAux";
        
            echo "\t\t<p><code>$cmdFirstImage</code></p>\n";
            system($cmdFirstImage, $status);
            echo "\t\t<p>Status from the generation of video 1st image: $status.</p>\n";

            $thumbFileNameAux = $thumbsDir . DIRECTORY_SEPARATOR . $pathParts['filename'] . ".jpg";
            $thumbMimeFileName = "image";
            $thumbTypeFileName = "jpeg";
            echo "\t\t<p>Generating video thumb...</p>\n";

            $cmdVideoThumb = "$ffmpegBinary -itsoffset -1  -i $dst -vcodec mjpeg -vframes 1 -an -f rawvideo -s $size $thumbFileNameAux";
            echo "\t\t<p><code>$cmdVideoThumb</code></p>\n";
            system($cmdVideoThumb, $status);
            echo "\t\t<p>Status from the generation of video thumb: $status.</p>\n";
            break;
        case "audio":
            require_once( "Zend/Media/Id3v2.php" );

            try {
                $id3 = new Zend_Media_Id3v2($dst);
            } catch (Exception $e) {
                $id3 = null;
            }

            // Check if the ID3 tags exist AND there is embedded album art
            if ($id3 && isset($id3->apic) && $id3->apic !== null) {
                // Extract embedded artwork
                $mimeTypeAudioAPIC = explode("/", $id3->apic->mimeType);
                $typeAudioAPIC = $mimeTypeAudioAPIC[1];

                $imageFileNameAux = $thumbsDir . DIRECTORY_SEPARATOR . $pathParts['filename'] . "-Large." . $typeAudioAPIC;
                $imageMimeFileName = "image";
                $imageTypeFileName = $typeAudioAPIC;

                $fdMusicImage = fopen($imageFileNameAux, "wb");
                fwrite($fdMusicImage, $id3->apic->getImageData());
                fclose($fdMusicImage);

                $thumbFileNameAux = $thumbsDir . DIRECTORY_SEPARATOR . $pathParts['filename'] . "." . $typeAudioAPIC;
                $thumbMimeFileName = "image";
                $thumbTypeFileName = $typeAudioAPIC;

                // Generate thumbnail from extracted image
                $resizeObj = new ImageResize($imageFileNameAux);
                $resizeObj->resizeImage($width, $height, 'crop');
                $resizeObj->saveImage($thumbFileNameAux, $typeAudioAPIC, 100);
                $resizeObj->close();
            } else {
                $targetFallbackId = 4;

                dbConnect(ConfigFile);
                $dataBaseName = $GLOBALS['configDataBase']->db;
                mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

                $targetFallbackId = (int)$targetFallbackId;

                $fallbackQuery = "SELECT `imageFileName`, `imageMimeFileName`, `imageTypeFileName`, `thumbFileName`, `thumbMimeFileName`, `thumbTypeFileName` 
                                  FROM `$dataBaseName`.`images-details` 
                                  WHERE `id` = $targetFallbackId 
                                  LIMIT 1";

                $fallbackResult = mysqli_query($GLOBALS['ligacao'], $fallbackQuery);

                if ($fallbackResult && mysqli_num_rows($fallbackResult) > 0) {
                    $row = mysqli_fetch_assoc($fallbackResult);
                    
                    $imageFileNameAux  = $row['imageFileName'];
                    $imageMimeFileName = $row['imageMimeFileName'];
                    $imageTypeFileName = $row['imageTypeFileName'];
                    
                    $thumbFileNameAux  = $row['thumbFileName'];
                    $thumbMimeFileName = $row['thumbMimeFileName'];
                    $thumbTypeFileName = $row['thumbTypeFileName'];
                    
                    mysqli_free_result($fallbackResult);
                } else {
                    if ($fallbackResult) {
                        mysqli_free_result($fallbackResult);
                    }
                    // Emergency safety fallback if database record missing
                    $imageFileNameAux  = $dstDir . DIRECTORY_SEPARATOR . "default" . DIRECTORY_SEPARATOR . "Unknown-Large.jpg";
                    $imageMimeFileName = "image";
                    $imageTypeFileName = "jpeg";
                    
                    $thumbFileNameAux  = $thumbsDir . DIRECTORY_SEPARATOR . "default" . DIRECTORY_SEPARATOR . "Unknown.jpg";
                    $thumbMimeFileName = "image";
                    $thumbTypeFileName = "jpeg";
                }

                dbDisconnect();
            }
            break;
        default:
            $imageFileNameAux = $dstDir . DIRECTORY_SEPARATOR . "default" . DIRECTORY_SEPARATOR . "Unknown-Large.jpg";
            $imageMimeFileName = "image";
            $imageTypeFileName = "jpeg";

            $thumbFileNameAux = $dstDir . DIRECTORY_SEPARATOR . "default" . DIRECTORY_SEPARATOR . "Unknown.jpg";
            $thumbMimeFileName = "image";
            $thumbTypeFileName = "jpeg";
            break;
    }

    // Write information about file into the data base
    dbConnect( ConfigFile );
    $dataBaseName = $GLOBALS['configDataBase']->db;
    
    mysqli_select_db( $GLOBALS['ligacao'], $dataBaseName );

    $latitude = addslashes($lat);
    $longitude = addslashes($lon);

    $fileName = addslashes($dst);
    $imageFileName = addslashes($imageFileNameAux);
    $thumbFileName = addslashes($thumbFileNameAux);

    $query = 
            "INSERT INTO `$dataBaseName`.`images-details`" .
            "(`fileName`, `mimeFileName`, `typeFileName`, `imageFileName`, `imageMimeFileName`, `imageTypeFileName`, `thumbFileName`, `thumbMimeFileName`, `thumbTypeFileName`, `latitude`, `longitude`, `title`, `description`) values " .
            "('$fileName', '$mimeFileName', '$typeFileName', '$imageFileName', '$imageMimeFileName', '$imageTypeFileName', '$thumbFileName', '$thumbMimeFileName', '$thumbTypeFileName', '$latitude', '$longitude', '$title', '$description')";

    if ( mysqli_query( $GLOBALS['ligacao'], $query )==false ) {
        $msg = "Information about file could not be inserted into the data base. Details : " . dbGetLastError() ;
    }
    else {
        $msg = "Information about file was inserted into data base.";
    }

    dbDisconnect();
?>
        <p><?php echo $msg;?></p>
        <p><a href='javascript:history.back()'>Back</a></p>
    </body>
</html>