<?php

namespace App\Services;

class LinkPreviewService
{
    public function loadLink(string $url): array
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return [
                'success' => 0,
                'message' => 'Invalid URL'
            ];
        }

        try {
            $html = @file_get_contents($url);

            if (!$html) {
                return [
                    'success' => 0,
                    'message' => 'Cannot fetch URL'
                ];
            }

            preg_match('/<title>(.*?)<\/title>/si', $html, $title);
            preg_match('/<meta name="description" content="(.*?)"/si', $html, $desc);
            preg_match('/<meta property="og:image" content="(.*?)"/si', $html, $img);

            return [
                'success' => 1,
                'meta' => [
                    'title' => $title[1] ?? $url,
                    'description' => $desc[1] ?? '',
                    'image' => $img[1] ?? '',
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => 0,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }
}
