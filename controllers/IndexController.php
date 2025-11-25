<?php

class ThumbnailFixer_IndexController extends Omeka_Controller_AbstractActionController
{
    public function runAction()
    {
        $db = get_db();
        $files = $db->getTable('File')
            ->findBy([
                'has_derivative_image' => 0,
                'mime_type' => 'application/pdf'
            ]);

        $filesDir = FILES_DIR;
        $thumbnailDir = $filesDir . '/thumbnails';
        $squareThumbnailDir = $filesDir . '/square_thumbnails';
        $fullSizeDir = $filesDir . '/fullsize';

        $fixed = 0;
        foreach ($files as $file) {
            $originalPath = $filesDir . '/original/' . $file->filename;

            if (!file_exists($originalPath)) {
                continue;
            }

            $thumbPath = $thumbnailDir . '/' . $file->filename;
            $squareThumbPath = $squareThumbnailDir . '/' . $file->filename;
            $fullSizePath = $fullSizeDir . '/' . $file->filename;

            $thumbPathJpg = str_replace('pdf', 'jpg', $thumbPath);
            $squareThumbPathJpg = str_replace('pdf', 'jpg', $squareThumbPath);
            $fullSizePathJpg = str_replace('pdf', 'jpg', $fullSizePath);

            $thumbMissing = !file_exists($thumbPathJpg);
            $squareThumbMissing = !file_exists($squareThumbPathJpg);
            $fullSizeMissing = !file_exists($fullSizePathJpg);

            if (!$thumbMissing && !$squareThumbMissing && !$fullSizeMissing) {
                $fixed++;
                $db->query("UPDATE {$db->File} SET has_derivative_image = 1 WHERE id = ?", [$file->id]);
                continue;
            }

            try {
                if (!is_dir($thumbnailDir)) {
                    mkdir($thumbnailDir, 0755, true);
                }
                if (!is_dir($squareThumbnailDir)) {
                    mkdir($squareThumbnailDir, 0755, true);
                }
                if (!is_dir($fullSizeDir)) {
                    mkdir($fullSizeDir, 0755, true);
                }

                if ($thumbMissing) {
                    $this->createPdfThumbnail($originalPath, $thumbPathJpg, 200, 200);
                }
                if ($squareThumbMissing) {
                    $this->createPdfThumbnail($originalPath, $squareThumbPathJpg, 150, 150);
                }
                if ($fullSizeMissing) {
                    $this->createPdfThumbnailFullSize($originalPath, $fullSizePathJpg);
                }
                $db->query("UPDATE {$db->File} SET has_derivative_image = 1 WHERE id = ?", [$file->id]);
                $fixed++;
            } catch (Exception $e) {
                _log("ThumbnailFixer error: " . $e->getMessage());
                var_dump($e->getMessage());
                die();
            }
        }

        $this->view->fixedCount = $fixed;
    }

    protected function createPdfThumbnail($pdfPath, $thumbPath, $maxWidth, $maxHeight)
    {
        // Check if 'convert' command is available
        $convertPath = trim(shell_exec('which convert'));
        if (!$convertPath) {
            throw new Exception("ImageMagick 'convert' command not found.");
        }

        // Build ImageMagick command - first page of PDF [0]
        $cmd = escapeshellcmd($convertPath) . " -thumbnail {$maxWidth}x{$maxHeight} " . escapeshellarg($pdfPath . '[0]') . " " . escapeshellarg($thumbPath);

        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception("ImageMagick convert command failed: " . implode("\n", $output));
        }
    }

    protected function createPdfThumbnailFullSize($pdfPath, $fullSizePath)
    {
        $convertPath = trim(shell_exec('which convert'));
        if (!$convertPath) {
            throw new Exception("ImageMagick 'convert' command not found.");
        }

        $cmd = escapeshellcmd($convertPath) . " " . escapeshellarg($pdfPath . '[0]') . " " . escapeshellarg($fullSizePath);

        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception("ImageMagick convert command failed: " . implode("\n", $output));
        }
    }
}