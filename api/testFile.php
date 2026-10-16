<?php
    include __DIR__ . '/vendor/autoload.php';

    use Dotenv\Dotenv;

    Dotenv::createImmutable(__DIR__, 'envi.env')->load();

    require __DIR__ . '/GeminiFuncs/GeminiServices.php';

    $sampleResume = "Juan Dela Cruz
    Objective: Entry-level software developer position.
    Skills: PHP, JavaScript, MySQL, Git, REST APIs, Laravel, HTML/CSS.
    Education: BS Computer Engineering, 2026.
    Experience: Intern at XYZ Corp — built internal tools using PHP and MySQL.";    
    $gemini = new GeminiServices();

    try {
        $result = $gemini->extractKeywords($sampleResume);
        echo "Success!\n";
        print_r($result);
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    try {
        $keywordResult = $gemini->extractKeywords($sampleResume);
        echo "Keywords extracted:\n";
        print_r($keywordResult);

        $keywordString = implode(', ', $keywordResult['keywords']);

        $fieldResult = $gemini->classifyApplicants($keywordString);
        echo "Classified fields:\n";
        print_r($fieldResult);

    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
?>