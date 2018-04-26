<?php

class Model_Uploader
{
    public static function handleRequestAlbum(Model_FileCollection $album)
    {
        $targetDir = APPLICATION_PATH . '/../public/uploads/albums/' . $album->id . '/';

        return self::handleRequest($targetDir, function($filename) use($targetDir, $album) {

            // Generate a UUID(v4) for the filename
            $uuid = sprintf('%s-%s-%04x-%04x-%s',
                bin2hex(openssl_random_pseudo_bytes(4, $strong1)),
                bin2hex(openssl_random_pseudo_bytes(2, $strong2)),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                bin2hex(openssl_random_pseudo_bytes(6, $strong3))
            );
            if (!$strong1 || !$strong2 || !$strong3) $uuid = uniqid();

            $newFilename = $uuid . '.' . end(explode('.', $filename));
            $newFilename = $targetDir . $newFilename;
            rename("{$filename}.part", $newFilename);

            $filename = $newFilename;

            if (!is_file($filename)) return;
            Model_Thumbnail::createThumbnail($filename, $filename . '.thumb.jpg', 253, 168, true);
            $file = $album->addFile($filename);

            return $file;

        });
    }

    public static function handleRequestMarina($year = false)
    {
        $targetDir = APPLICATION_PATH . '/../public/uploads/marinas/';

        return self::handleRequest($targetDir, function($filename) use($targetDir, $year) {

            $title = end(explode('/', $filename));
            $title = implode('.', array_slice(explode('.', $title), 0, -1));
            $ext = end(explode('.', $filename));

            // Generate a UUID(v4) for the filename
            $uuid = sprintf('%s-%s-%04x-%04x-%s',
                bin2hex(openssl_random_pseudo_bytes(4, $strong1)),
                bin2hex(openssl_random_pseudo_bytes(2, $strong2)),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                bin2hex(openssl_random_pseudo_bytes(6, $strong3))
            );
            if (!$strong1 || !$strong2 || !$strong3) $uuid = uniqid();

            $newFilename = $uuid . '.' . end(explode('.', $filename));
            $newFilename = $targetDir . $newFilename;
            rename("{$filename}.part", $newFilename);

            $filename = $newFilename;

            if (!is_file($filename)) return;

            $file = Model_DbTable_FileCollections::addMarina($title, $ext, $filename, $year);
            return $file;

        });
    }

    public static function handleRequestDocs(Model_FileCollection $folder)
    {
        $targetDir = APPLICATION_PATH . '/../public/uploads/documents/';

        return self::handleRequest($targetDir, function($filename) use($targetDir, $folder) {

            $title = end(explode('/', $filename));
            $title = implode('.', array_slice(explode('.', $title), 0, -1));

            // Generate a UUID(v4) for the filename
            $uuid = sprintf('%s-%s-%04x-%04x-%s',
                bin2hex(openssl_random_pseudo_bytes(4, $strong1)),
                bin2hex(openssl_random_pseudo_bytes(2, $strong2)),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                bin2hex(openssl_random_pseudo_bytes(6, $strong3))
            );
            if (!$strong1 || !$strong2 || !$strong3) $uuid = uniqid();

            $newFilename = $uuid . '.' . end(explode('.', $filename));
            $newFilename = $targetDir . $newFilename;
            rename("{$filename}.part", $newFilename);

            $filename = $newFilename;

            if (!is_file($filename)) return;

            $file = $folder->addFile($filename, $title);
            return $file;

        });
    }

    public static function handleRequest($targetDir, $completed)
    {
        // Make sure file is not cached (as it happens for example on iOS devices)
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        // 5 minutes execution time
        @set_time_limit(5 * 60);

        // Target directory for this album
        if (!file_exists($targetDir)) {
            @mkdir($targetDir);
        }

        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }
        $filePath = $targetDir . $fileName;

        // Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;


        // Open temp file
        if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }

        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }

            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        }

        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);

        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1) {
            return $completed($filePath);
        }

        // Return Success JSON-RPC response
        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
    }
}
