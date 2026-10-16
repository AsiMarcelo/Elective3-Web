<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

class PdfToText
{
    public function convert(string $pdfPath): string
    {
        if (!class_exists(\Smalot\PdfParser\Parser::class)) {
            throw new \RuntimeException('PDF parser not installed. Run: composer require smalot/pdfparser');
        }

        try {
            $text = (new \Smalot\PdfParser\Parser())->parseFile($pdfPath)->getText();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Could not read the PDF: ' . $e->getMessage(), 0, $e);
        }

        // Postgres text columns reject NUL bytes and invalid UTF-8, so clean both.
        $text = str_replace("\0", '', $text);
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

        // Tidy whitespace.
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;
        $text = trim($text);

        if ($text === '') {
            throw new \RuntimeException('No text found in the PDF. It may be a scanned image, which needs OCR.');
        }

        return $text;
    }
}