<?php 
/*Simple Stegano Engine | Made by: https://github.com/BryanApolonio*/
/*Core Logic for LSB - Simple Stegano Engine*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payload_text']) && isset($_FILES['base_image'])) {
    
    if ($_FILES['base_image']['type'] !== 'image/png') {
        die("ERROR: Only PNG images are supported.");
    }

    $target_img = $_FILES['base_image']['tmp_name'];
    $data_to_hide = $_POST['payload_text'];
    
    $img = imagecreatefrompng($target_img);
    if (!$img) die("ERROR: Failed to process image.");

    $data = $data_to_hide . "\0"; 
    $binData = "";
    for ($i = 0; $i < strlen($data); $i++) {
        $binData .= str_pad(decbin(ord($data[$i])), 8, "0", STR_PAD_LEFT);
    }

    $width = imagesx($img); 
    $height = imagesy($img);
    $binIndex = 0; 
    $dataLen = strlen($binData);

    if ($dataLen > ($width * $height * 3)) {
        die("ERROR: Payload too large for this image.");
    }

    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            if ($binIndex >= $dataLen) break 2;

            $rgb = imagecolorat($img, $x, $y);
            $r = ($rgb >> 16) & 0xFF; 
            $g = ($rgb >> 8) & 0xFF; 
            $b = $rgb & 0xFF;

            $r = ($r & 0xFE) | (int)$binData[$binIndex++];
            if ($binIndex < $dataLen) $g = ($g & 0xFE) | (int)$binData[$binIndex++];
            if ($binIndex < $dataLen) $b = ($b & 0xFE) | (int)$binData[$binIndex++];

            $newColor = imagecolorallocate($img, $r, $g, $b);
            imagesetpixel($img, $x, $y, $newColor);
        }
    }

    ob_clean(); 
    $filename = "fragment-" . time() . ".png";
    
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate'); 
    
    imagepng($img); 
    imagedestroy($img);
    
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Stegano | LSB Encoder</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <header>
            <div class="logo"><span>// </span>SIMPLE STEGANO</div>
            <p class="subtitle">Steganographic Data Injection</p>
        </header>

        <form action="" method="POST" enctype="multipart/form-data" class="upload-box">
            <div class="field-group">
                <label class="card-tag">Carrier Image (PNG only)</label>
                <input type="file" name="base_image" accept="image/png" required>
            </div>
            
            <div class="field-group">
                <label class="card-tag">Payload Data (HTML, CSS or JS)</label>
                <textarea name="payload_text" placeholder="Paste your code here..." required></textarea>
            </div>

            <button type="submit" class="btn-execute">Encode Payload into Image</button>
        </form>

        <footer class="simple-footer">
           BASE: PHP GD | MADE BY: <a href="https://github.com/BryanApolonio" target="_blank" class="footer-link">Bryan Apolonio</a>
        </footer>
    </div>
</body>
</html>
