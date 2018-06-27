<?php

header('Content-Type: application/json');
const FILE_DIR = '/srv/app/files/images';

try{
// new filename
    $ext = (isset($_GET['type']))? $_GET['type'] : 'jpeg';
    $filename = 'pic_'.date('YmdHis') . '.' . $ext;
    $url = '';

    if( move_uploaded_file($_FILES['webcam']['tmp_name'],implode(DIRECTORY_SEPARATOR, [FILE_DIR, $filename])) ){
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/images/' . $filename;
    }

// Return image url

    echo json_encode(['url' => $url]);
}catch (\Exception $e){
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

