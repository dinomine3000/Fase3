<?php
require_once("../../Lib/lib.php");
require_once("../../Lib/db.php");
require_once("../../Lib/lib-coords.php");
require_once("../../Lib/ImageResize.php");
include_once("../../Lib/lang/translator.php");
include_once("config.php");

if (!isset($_SESSION)) session_start();
$name = $_SESSION['username'] ?? '';

$current_lang   = $current_lang ?? 'en';
$_langSwitch    = ($current_lang === 'en') ? 'pt' : 'en';
$_qp            = $_GET; $_qp['lang'] = $_langSwitch;
$_langToggleUrl = '?' . http_build_query($_qp);

set_time_limit(300);
$debug     = false;
$logs      = [];
$success   = false;
$fileTitle = '';

if ($_FILES['userFile']['error'] != 0) {
    $logs[] = ['type' => 'error', 'text' => showUploadFileError($_FILES['userFile']['error'])];
} else {
    $srcName        = $_FILES['userFile']['name'];
    $configurations = getConfiguration();
    $dstDir         = $configurations['destination'];
    $src            = $_FILES['userFile']['tmp_name'];
    $dst            = $dstDir . DIRECTORY_SEPARATOR . $srcName;

    if (copy($src, $dst) === false) {
        $logs[] = ['type' => 'error', 'text' => "Could not write '$src' to '$dst'"];
    } else {
        unlink($src);
        $logs[] = ['type' => 'success', 'text' => "File received and stored."];

        $fileInfo           = finfo_open(FILEINFO_MIME);
        $fileInfoData       = finfo_file($fileInfo, $dst);
        $fileTypeComponents = explode(";", $fileInfoData);
        $mimeTypeParts      = explode("/", $fileTypeComponents[0]);
        $mimeFileName       = $mimeTypeParts[0];
        $typeFileName       = $mimeTypeParts[1];

        $thumbsDir = $dstDir . DIRECTORY_SEPARATOR . "thumbs";
        $pathParts = pathinfo($dst);
        $lat = $lon = "";

        $description = isset($_POST['description']) && $_POST['description'] !== ''
            ? addslashes($_POST['description'])
            : "No description available";

        if (isset($_POST['title']) && $_POST['title'] !== '') {
            $fileTitle = addslashes($_POST['title']);
        } else {
            $pp        = pathinfo($srcName);
            $fileTitle = $pp['filename'];
        }

        $width  = $configurations['thumbWidth'];
        $height = $configurations['thumbHeight'];

        $imageFileNameAux = $imageMimeFileName = $imageTypeFileName = null;
        $thumbFileNameAux = $thumbMimeFileName = $thumbTypeFileName = null;

        switch ($mimeFileName) {
            case "image":
                $exif = @exif_read_data($dst, 'IFD0', true);

                if ($exif === false) {
                    $logs[] = ['type' => 'info', 'text' => "No EXIF header data found."];
                } else {
                    $gps = @$exif['GPS'];
                    if ($gps != NULL) {
                        $latitudeAux  = $gps['GPSLatitude'];
                        $latitudeRef  = $gps['GPSLatitudeRef'];
                        $longitudeAux = $gps['GPSLongitude'];
                        $longitudeRef = $gps['GPSLongitudeRef'];

                        if ($latitudeAux != NULL && $longitudeAux != NULL) {
                            $lat = getCoordFromEXIF($latitudeAux, $latitudeRef);
                            $lon = getCoordFromEXIF($longitudeAux, $longitudeRef);
                            $logs[] = ['type' => 'info', 'text' => "GPS: {$lat}, {$lon}"];
                        } else {
                            $logs[] = ['type' => 'info', 'text' => "File includes GPS block but coordinates are empty."];
                        }
                    } else {
                        $logs[] = ['type' => 'info', 'text' => "No GPS information in file."];
                    }
                }

                $imageFileNameAux  = $dst;
                $imageMimeFileName = "image";
                $imageTypeFileName = $typeFileName;

                $thumbFileNameAux  = $thumbsDir . DIRECTORY_SEPARATOR . $pathParts['filename'] . "." . $typeFileName;
                $thumbMimeFileName = "image";
                $thumbTypeFileName = $typeFileName;

                $resizeObj = new ImageResize($dst);
                $resizeObj->resizeImage($width, $height, 'crop');
                $resizeObj->saveImage($thumbFileNameAux, $typeFileName, 100);
                $resizeObj->close();
                $logs[] = ['type' => 'info', 'text' => "Thumbnail generated ({$width}×{$height})."];
                break;

            case "video":
                $size = "{$width}x{$height}";

                $imageFileNameAux  = $thumbsDir . DIRECTORY_SEPARATOR . $pathParts['filename'] . "-Large.jpg";
                $imageMimeFileName = "image";
                $imageTypeFileName = "jpeg";

                $cmdFirstImage = "$ffmpegBinary -itsoffset -1 -i $dst -vcodec mjpeg -vframes 1 -an -f rawvideo -s 640x480 $imageFileNameAux";
                system($cmdFirstImage, $status);
                $logs[] = ['type' => 'info', 'text' => "Video preview frame generated (status: $status)."];

                $thumbFileNameAux  = $thumbsDir . DIRECTORY_SEPARATOR . $pathParts['filename'] . ".jpg";
                $thumbMimeFileName = "image";
                $thumbTypeFileName = "jpeg";

                $cmdVideoThumb = "$ffmpegBinary -itsoffset -1 -i $dst -vcodec mjpeg -vframes 1 -an -f rawvideo -s $size $thumbFileNameAux";
                system($cmdVideoThumb, $status);
                $logs[] = ['type' => 'info', 'text' => "Video thumbnail generated (status: $status)."];
                break;

            case "audio":
                require_once("Zend/Media/Id3v2.php");

                try {
                    $id3 = new Zend_Media_Id3v2($dst);
                } catch (Exception $e) {
                    $id3 = null;
                }

                if ($id3 && isset($id3->apic) && $id3->apic !== null) {
                    $mimeTypeAudioAPIC = explode("/", $id3->apic->mimeType);
                    $typeAudioAPIC     = $mimeTypeAudioAPIC[1];

                    $imageFileNameAux  = $thumbsDir . DIRECTORY_SEPARATOR . $pathParts['filename'] . "-Large." . $typeAudioAPIC;
                    $imageMimeFileName = "image";
                    $imageTypeFileName = $typeAudioAPIC;

                    $fdMusicImage = fopen($imageFileNameAux, "wb");
                    fwrite($fdMusicImage, $id3->apic->getImageData());
                    fclose($fdMusicImage);

                    $thumbFileNameAux  = $thumbsDir . DIRECTORY_SEPARATOR . $pathParts['filename'] . "." . $typeAudioAPIC;
                    $thumbMimeFileName = "image";
                    $thumbTypeFileName = $typeAudioAPIC;

                    $resizeObj = new ImageResize($imageFileNameAux);
                    $resizeObj->resizeImage($width, $height, 'crop');
                    $resizeObj->saveImage($thumbFileNameAux, $typeAudioAPIC, 100);
                    $resizeObj->close();
                    $logs[] = ['type' => 'info', 'text' => "Album art extracted and used as thumbnail."];
                } else {
                    $targetFallbackId = 4;

                    dbConnect(ConfigFile);
                    $dataBaseName = $GLOBALS['configDataBase']->db;
                    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

                    $targetFallbackId = (int)$targetFallbackId;
                    $fallbackQuery    = "SELECT `imageFileName`, `imageMimeFileName`, `imageTypeFileName`,
                                               `thumbFileName`, `thumbMimeFileName`, `thumbTypeFileName`
                                        FROM `$dataBaseName`.`images-details`
                                        WHERE `id` = $targetFallbackId LIMIT 1";
                    $fallbackResult   = mysqli_query($GLOBALS['ligacao'], $fallbackQuery);

                    if ($fallbackResult && mysqli_num_rows($fallbackResult) > 0) {
                        $row               = mysqli_fetch_assoc($fallbackResult);
                        $imageFileNameAux  = $row['imageFileName'];
                        $imageMimeFileName = $row['imageMimeFileName'];
                        $imageTypeFileName = $row['imageTypeFileName'];
                        $thumbFileNameAux  = $row['thumbFileName'];
                        $thumbMimeFileName = $row['thumbMimeFileName'];
                        $thumbTypeFileName = $row['thumbTypeFileName'];
                        mysqli_free_result($fallbackResult);
                    } else {
                        if ($fallbackResult) mysqli_free_result($fallbackResult);
                        $imageFileNameAux  = $dstDir . DIRECTORY_SEPARATOR . "default" . DIRECTORY_SEPARATOR . "Unknown-Large.jpg";
                        $imageMimeFileName = "image";
                        $imageTypeFileName = "jpeg";
                        $thumbFileNameAux  = $thumbsDir . DIRECTORY_SEPARATOR . "default" . DIRECTORY_SEPARATOR . "Unknown.jpg";
                        $thumbMimeFileName = "image";
                        $thumbTypeFileName = "jpeg";
                    }

                    dbDisconnect();
                    $logs[] = ['type' => 'info', 'text' => "No embedded album art — using default audio thumbnail."];
                }
                break;

            default:
                $imageFileNameAux  = $dstDir . DIRECTORY_SEPARATOR . "default" . DIRECTORY_SEPARATOR . "Unknown-Large.jpg";
                $imageMimeFileName = "image";
                $imageTypeFileName = "jpeg";
                $thumbFileNameAux  = $dstDir . DIRECTORY_SEPARATOR . "default" . DIRECTORY_SEPARATOR . "Unknown.jpg";
                $thumbMimeFileName = "image";
                $thumbTypeFileName = "jpeg";
                break;
        }

        dbConnect(ConfigFile);
        $dataBaseName = $GLOBALS['configDataBase']->db;
        mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

        $latitude      = addslashes($lat);
        $longitude     = addslashes($lon);
        $fileName      = addslashes($dst);
        $imageFileName = addslashes($imageFileNameAux);
        $thumbFileName = addslashes($thumbFileNameAux);

        $query = "INSERT INTO `$dataBaseName`.`images-details`"
               . "(`fileName`, `mimeFileName`, `typeFileName`,"
               . " `imageFileName`, `imageMimeFileName`, `imageTypeFileName`,"
               . " `thumbFileName`, `thumbMimeFileName`, `thumbTypeFileName`,"
               . " `latitude`, `longitude`, `title`, `description`) VALUES"
               . " ('$fileName', '$mimeFileName', '$typeFileName',"
               . "  '$imageFileName', '$imageMimeFileName', '$imageTypeFileName',"
               . "  '$thumbFileName', '$thumbMimeFileName', '$thumbTypeFileName',"
               . "  '$latitude', '$longitude', '$fileTitle', '$description')";

        if (mysqli_query($GLOBALS['ligacao'], $query) == false) {
            $logs[] = ['type' => 'error', 'text' => "Could not insert file record into database: " . dbGetLastError()];
        } else {
            $logs[] = ['type' => 'success', 'text' => "File information saved to database."];
            $success = true;
        }

        dbDisconnect();
    }
}

$hasError = false;
foreach ($logs as $l) {
    if ($l['type'] === 'error') { $hasError = true; break; }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upload Result — Smiki</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&family=Outfit:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../wiki/styles/wiki.css?v=3">
<link rel="stylesheet" href="../wiki/styles/form.css?v=3">
<style>
.log-entry {
  display: flex; align-items: flex-start; gap: 10px; padding: 0.65rem 0.85rem;
  border-radius: 7px; font-size: 13px; border: 1px solid var(--border);
  transition: background .25s, border-color .25s;
}
.log-entry svg { width: 14px; height: 14px; flex-shrink: 0; margin-top: 1px; }
.log-entry.success { background: rgba(40,160,80,0.07); border-color: rgba(40,160,80,0.2); color: #2a7a3a; }
.log-entry.error   { background: rgba(180,50,50,0.07); border-color: rgba(180,50,50,0.2); color: #b44; }
.log-entry.info    { background: var(--bg3); color: var(--muted); }
[data-theme="dark"] .log-entry.success { background: rgba(60,180,90,0.09); border-color: rgba(60,180,90,0.22); color: #5dbf74; }
[data-theme="dark"] .log-entry.error   { background: rgba(220,80,80,0.09); border-color: rgba(220,80,80,0.25); color: #e07070; }

.result-icon {
  width: 56px; height: 56px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 1rem;
}
.result-icon.ok  { background: rgba(40,160,80,0.1); color: #2a7a3a; }
.result-icon.err { background: rgba(180,50,50,0.1); color: #b44; }
[data-theme="dark"] .result-icon.ok  { background: rgba(60,180,90,0.1); color: #5dbf74; }
[data-theme="dark"] .result-icon.err { background: rgba(220,80,80,0.1); color: #e07070; }
.result-icon svg { width: 26px; height: 26px; }
</style>
</head>
<body>

<script>(function(){const s=localStorage.getItem('smiki-theme')||'light';document.documentElement.setAttribute('data-theme',s);})();</script>

<header class="site-header">
  <div class="container-lg py-0">
    <div class="d-flex align-items-center gap-3" style="height:56px">
      <a class="logo" href="../home.php">Portal <span class="logo-wiki">Wiki</span></a>
      <div style="flex:1"></div>
      <a href="<?php echo $_langToggleUrl; ?>" class="lang-toggle" title="<?php echo lang('switch_language'); ?>" style="text-decoration:none"><?php echo strtoupper($_langSwitch); ?></a>
      <button class="theme-toggle" onclick="toggleTheme()" title="<?php echo lang('toggle_theme'); ?>">
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        <svg class="icon-sun"  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
      <?php if (!empty($name)): ?>
      <a href="../wiki/profile.php?user=<?php echo urlencode($name); ?>" class="hbtn icon" style="text-decoration:none" title="<?php echo lang('my_profile'); ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </a>
      <form action="../auth/logout.php" method="POST" style="margin:0">
        <button type="submit" class="hbtn icon" title="Logout">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</header>

<div class="container-lg py-4">
  <div class="section-heading">Upload Result</div>

  <div class="form-card" style="max-width:560px;margin:0 auto">

    <div class="result-icon <?php echo $hasError ? 'err' : 'ok'; ?>">
      <?php if ($hasError): ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?php else: ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
      <?php endif; ?>
    </div>

    <div class="form-title" style="text-align:center;margin-bottom:<?php echo empty($fileTitle) ? '1.25rem' : '.3rem'; ?>">
      <?php echo $hasError ? 'Upload failed' : 'Upload complete'; ?>
    </div>
    <?php if (!empty($fileTitle)): ?>
    <div style="text-align:center;font-family:'Outfit',sans-serif;font-size:12px;color:var(--muted);margin-bottom:1.25rem">
      <?php echo htmlspecialchars(stripslashes($fileTitle)); ?>
    </div>
    <?php endif; ?>

    <div class="d-flex flex-column gap-2" style="margin-bottom:1.5rem">
      <?php foreach ($logs as $l): ?>
      <div class="log-entry <?php echo htmlspecialchars($l['type']); ?>">
        <?php if ($l['type'] === 'success'): ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        <?php elseif ($l['type'] === 'error'): ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?php else: ?>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12.01" y2="16"/><line x1="12" y1="8" x2="12" y2="12"/></svg>
        <?php endif; ?>
        <?php echo htmlspecialchars($l['text']); ?>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="form-actions">
      <?php if ($success): ?>
      <a href="list.php" class="hbtn primary" style="text-decoration:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        View Files
      </a>
      <?php endif; ?>
      <a href="formUpload.php" class="hbtn" style="text-decoration:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
        Upload Another
      </a>
    </div>

  </div>
</div>

<script>
function toggleTheme() {
  const html = document.documentElement;
  const next = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
  html.setAttribute('data-theme', next);
  localStorage.setItem('smiki-theme', next);
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
