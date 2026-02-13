<?php
// preview_pdf.php

// Pastikan user sudah login dan memiliki akses
include("../inc/inc.Settings.php");
include("../inc/inc.Utils.php");
include("../inc/inc.DBInit.php");

$dms = $GLOBALS['dms'];
$user = $GLOBALS['user'];

if (!$user->isLoggedIn()) {
    header('Location: ../out/out.Login.php');
    exit;
}

// Ambil ID dokumen dari URL
$docId = isset($_GET['docid']) ? (int)$_GET['docid'] : 0;
$document = $dms->getDocument($docId);

if (!$document || !$document->getAccessMode($user) >= M_READ) {
    die("Akses ditolak atau dokumen tidak ditemukan.");
}

// Ambil path file PDF
$file = $dms->contentDir . $document->getFilePath();
$fileUrl = "get_pdf_content.php?docid=" . $docId; // Gunakan script terpisah untuk streaming

?>

<!DOCTYPE html>
<html>
<head>
    <title>Preview PDF - <?php echo htmlspecialchars($document->getName()); ?></title>
    <script src="../ext/pdfjs/build/pdf.js"></script>
    <style>
        #pdfViewer {
            width: 100%;
            height: 800px;
            border: 1px solid #ccc;
        }
        /* Sembunyikan kontrol download dan print */
        body {
            -webkit-user-select: none; /* Safari */
            -moz-user-select: none; /* Firefox */
            -ms-user-select: none; /* IE */
            user-select: none; /* Standard */
        }
    </style>
</head>
<body>
    <canvas id="pdfViewer"></canvas>

    <script>
        // URL ke file PDF (melalui streaming script)
        var url = '<?php echo $fileUrl; ?>';

        // Inisialisasi PDF.js
        pdfjsLib.getDocument(url).promise.then(function(pdf) {
            // Ambil halaman pertama (bisa diubah untuk multi-halaman)
            pdf.getPage(1).then(function(page) {
                var scale = 1.5;
                var viewport = page.getViewport({ scale: scale });

                // Siapkan canvas
                var canvas = document.getElementById('pdfViewer');
                var context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                // Render PDF ke canvas
                var renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };
                page.render(renderContext);
            });
        }).catch(function(error) {
            console.error('Error loading PDF: ', error);
        });

        // Blokir klik kanan untuk mencegah download
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
    </script>
</body>
</html>
