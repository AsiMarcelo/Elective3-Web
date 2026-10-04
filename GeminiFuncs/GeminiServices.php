<?php

    //    $prompt = "You are going to sort the text from an applicant's resume";
    class GeminiServices{

        public function extractKeywords(string $resumeText): array
        {
            $apiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');

            $payload = [
                'contents' => [
                    ['parts' => [['text' => "Extract the main keywords (skills, tools, job titles) from the following resume text. Resume text: " . $resumeText]]],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'responseSchema' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'keywords' => [
                                'type' => 'ARRAY',
                                'items' => ['type' => 'STRING'],
                            ],
                        ],
                        'required' => ['keywords'],
                    ],
                ],
            ];

            $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent");
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'x-goog-api-key: ' . $apiKey,
                ],
                CURLOPT_POSTFIELDS => json_encode($payload),
            ]);

            $response = curl_exec($ch);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                throw new \RuntimeException('cURL error: ' . $curlError);
            }  

            $data = json_decode($response, true);
            $json = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (is_null($json)) {
                throw new \RuntimeException('Gemini returned no content. Response: ' . $response);
            }

            return json_decode($json, true);
        }

        public function classifyApplicants(string $applicantType) : array {
            //tobefilled
            $applicantClassifications = include __DIR__ . '/../config/classifications.php';
            $list = $applicantClassifications['list'];
            $prompt = "Classify the following keywords into one or more of the following categories: " . implode(', ', $list) . ". Keywords: " . $applicantType;

            $apiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');

            $payload = [
                'contents' => [
                ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'responseSchema' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'fields' => [
                                'type' => 'ARRAY',
                                'items' => [
                                    'type' => 'STRING',
                                    'enum' => $list,
                                ],
                            ],
                        ],
                        'required' => ['fields'],
                    ],
                ],
            ];

            $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent");
            curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-goog-api-key: ' . $apiKey,
            ],
                CURLOPT_POSTFIELDS => json_encode($payload),
            ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \RuntimeException('cURL error: ' . $curlError);
        }

        $data = json_decode($response, true);
        $json = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (is_null($json)) {
            throw new \RuntimeException('Gemini returned no content. Response: ' . $response);
        }

        $decoded = json_decode($json, true);

        return $decoded['fields'];



        }
    }