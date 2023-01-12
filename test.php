<?php
$targetDir = 'backup/';
if ($_FILES["fileParam"]["tmp_name"] != "") {
    $tmp_name = $_FILES["fileParam"]["tmp_name"];
    // basename() may prevent filesystem traversal attacks;
    // further validation/sanitation of the filename may be appropriate
    
    $name = basename($_FILES["fileParam"]["name"]);
    if(move_uploaded_file($tmp_name, $targetDir . "/" . $name)) {
        print "Uploaded.";
    } else {
        print "Upload failed.";
    }
    
}
?>