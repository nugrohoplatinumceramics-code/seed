<?php
// get_pdf_content.php

include("../inc/inc.Settings.php");
include("../inc/inc.Utils.php");
include("../inc/inc.DBInit.php");

$dms = $GLOBALS['dms'];
$user = $GLOBALS['user'];

if (!$user->isLoggedIn()) {
    exit;
}

$docId = isset($_GET['docid']) ? (int)$_GET['docid'] : 0;
$document = $dms->getDocument($docId);

if (!$document || !$document->getAccessMode($user) >= M_READ) {
    http_response_code(403);
    exit;
}

$file = $dms->contentDir . $document->getFilePath();

if (file_exists($file)) {
    // Header untuk streaming tanpa download
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($file) . '"');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Streaming file
    readfile($file);
    exit;
} else {
    http_response_code(404);
    exit;
}
