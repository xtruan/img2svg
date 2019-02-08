<?php
$target_dir = "uploads/";
$target_file = $target_dir . time() . "_" . str_replace(' ', '_', basename($_FILES["fileToUpload"]["name"]));
$uploadOk = 1;
$hasError = 0;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check !== false) {
        //echo "File is an image - " . $check["mime"] . ". ";
        $uploadOk = 1;
    } else {
        writeError("File is not an image.");
        $uploadOk = 0;
    }
}
// Check if file already exists
if (file_exists($target_file)) {
    writeError("Sorry, file already exists.");
    $uploadOk = 0;
}
// Check file size
if ($_FILES["fileToUpload"]["size"] > 32 * 1024 * 1024) {
    writeError("Sorry, your file is too large.");
    $uploadOk = 0;
}
// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" 
    && $imageFileType != "gif" ) {
    writeError("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    writeError("Sorry, your file was not uploaded.");
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        // convert image to intermediate pnm
        $output = exec('convert "' . $target_file . '" "'  . $target_file . '.pnm"', $fullOutput, $exitCode);
        if ($exitCode == 0) {
            // trace intermediate pnm to final svg
            $output = exec('potrace -s -o "' . $target_file . '.svg" "' . $target_file . '.pnm"', $fullOutput, $exitCode);
            if ($exitCode == 0) {
                // delete intermediate pnm
                exec('rm -f "' . $target_file . '.pnm"');
                // redirect to final svg
                header("Location: " . $target_file . ".svg");
                die();
            } else {
                writeError("Sorry, there was an error tracing the image: " . $output);
            }
        } else {
            writeError("Sorry, there was an error converting the image: " . $output);
        }
    } else {
        writeError("Sorry, there was an error uploading your file.");
    }
}

function writeError($message) {
    if ($hasError == 0) {
        echo '<!DOCTYPE html><head><link href="css/img2svg.css" rel="stylesheet"></head>';
    }
    $hasError = 1;
    echo '<div>' . $message . '</div>';
}
?>