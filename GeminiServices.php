<?php
/*
global $classifications;
class GeminiService

{
    public function classifyField(string $resumeText): array
    {
        $classifications = require __DIR__ . '/config/classifications.php';
        $prompt = "Classify the following resume text into one of the following categories: " . implode(', ', $classifications['list']) . ". Resume text: " . $resumeText;
        $response = "...";
        $response->throw("Gemini API request failed with status {$response->status()}: {$response->body()}");

        $json = $response->json('candidates.0.content.parts.0.text');
        if (is_null($json)) {
            throw new \RuntimeException('Gemini returned no candidates.');
        }

        return json_decode($json, true);
    }

    public function FetchMatchText(string $resumeText, string $field): string
    {
        $prompt = "Extract the text from the following resume that matches the field '{$field}': " . $resumeText;
        $response = "...";
        $response->throw("Gemini API request failed with status {$response->status()}: {$response->body()}");

        return $response->body();
    }
}
*/