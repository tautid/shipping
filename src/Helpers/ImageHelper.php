<?php

namespace TautId\Shipping\Helpers;

class ImageHelper
{
    /**
     * Convert image to grayscale and return as base64 data URI
     */
    public static function convertImageToGrayscaleBase64(string $image_path): ?string
    {
        try {
            $image_info = getimagesize($image_path);
            if (!$image_info) return null;

            $mime_type = $image_info['mime'];

            // Create image resource based on type
            $image = match($mime_type) {
                'image/jpeg' => imagecreatefromjpeg($image_path),
                'image/png' => imagecreatefrompng($image_path),
                'image/gif' => imagecreatefromgif($image_path),
                'image/webp' => imagecreatefromwebp($image_path),
                default => null
            };

            if (!$image) return null;

            // Get original dimensions
            $width = imagesx($image);
            $height = imagesy($image);

            // Create a new image with transparency support
            $grayscale_image = imagecreatetruecolor($width, $height);

            // Preserve transparency for PNG and GIF
            if ($mime_type === 'image/png' || $mime_type === 'image/gif') {
                // Enable alpha blending
                imagealphablending($grayscale_image, false);
                imagesavealpha($grayscale_image, true);

                // Set transparent background
                $transparent = imagecolorallocatealpha($grayscale_image, 0, 0, 0, 127);
                imagefill($grayscale_image, 0, 0, $transparent);
                imagealphablending($grayscale_image, true);
            }

            // Copy the original image to the new one
            imagecopy($grayscale_image, $image, 0, 0, 0, 0, $width, $height);

            // Apply grayscale filter
            imagefilter($grayscale_image, IMG_FILTER_GRAYSCALE);

            // Capture output
            ob_start();

            // Output as PNG to preserve transparency
            imagesavealpha($grayscale_image, true);
            imagepng($grayscale_image);
            $image_data = ob_get_contents();
            ob_end_clean();

            // Clean up
            imagedestroy($image);
            imagedestroy($grayscale_image);

            return 'data:image/png;base64,' . base64_encode($image_data);

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Convert image to base64 data URI without grayscale conversion
     */
    public static function convertImageToBase64(string $image_path): ?string
    {
        try {
            if (!file_exists($image_path)) return null;

            $image_data = file_get_contents($image_path);
            $mime_type = mime_content_type($image_path);

            return 'data:' . $mime_type . ';base64,' . base64_encode($image_data);

        } catch (\Exception $e) {
            return null;
        }
    }
}
