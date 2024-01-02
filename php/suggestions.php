<?php
header("Content-Type: application/json");

function fetchSuggestions($query, $service) {
    // Define the base URL for suggestions
    $baseUrl = "https://suggestqueries.google.com/complete/search";

    // Specify the data source based on the service
    $client = ($service === 'youtube') ? 'firefox&ds=yt' : 'firefox';

    // Construct the URL for the specific service to fetch JSON data
    $url = "$baseUrl?client=" . $client . "&q=" . urlencode($query);

    // Set headers for proper UTF-8 encoding
    $context = stream_context_create([
        'http' => [
            'header' => 'Accept-Encoding: utf-8',
        ],
    ]);

    // Fetch suggestions from the URL
    $response = file_get_contents($url, false, $context);

    return parseSuggestions($response);
}

function parseSuggestions($jsonData) {
    $suggestions = [];

    // Handle encoding errors
    $jsonData = mb_convert_encoding($jsonData, 'UTF-8', 'UTF-8');

    $data = json_decode($jsonData);

    if (isset($data[1])) {
        foreach ($data[1] as $suggestion) {
            $suggestions[] = $suggestion;
        }
    }

    return $suggestions;
}

// Handle both path and query parameters
$pathParams = explode('/', $_SERVER['REQUEST_URI']);
$queryParams = $_GET;

if (count($pathParams) >= 4) {
    // Path parameter found (e.g., /php/suggestions.php/hello)
    $query = $pathParams[3];
} elseif (isset($queryParams["keyword"])) {
    // Query parameter found (e.g., /php/suggestions.php?keyword=hello)
    $query = $queryParams["keyword"];
} else {
    $query = '';
}

$limit = isset($queryParams["limit"]) ? intval($queryParams["limit"]) : 10;

// Inside your PHP script, log the received keyword
error_log("Request received with keyword: " . $query);

if (!empty($query)) {
    $googleSuggestions = fetchSuggestions($query, 'google');
    $youtubeSuggestions = fetchSuggestions($query, 'youtube');

    // Common suggestions are the intersection of Google and YouTube suggestions
    $commonSuggestions = array_intersect($googleSuggestions, $youtubeSuggestions);

    echo json_encode([
        "google_suggestions" => array_slice($googleSuggestions, 0, $limit),
        "youtube_suggestions" => array_slice($youtubeSuggestions, 0, $limit),
        "common_keywords" => array_slice($commonSuggestions, 0, $limit)
    ]);
} else {
    http_response_code(400);
    echo json_encode(["error" => "No keyword provided"]);
}
?>
