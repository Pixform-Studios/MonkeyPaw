<?php
// Gemini API Proxy - Converted from Node.js server
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get the request body
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['wish']) || empty(trim($input['wish']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Wish text is required']);
    exit;
}

// Your Gemini API key (server-side only)
$apiKey = 'AIzaSyCrmXhV-pT3pkYbkZRrBQQvNBt-0sOQunk';

$wish = trim($input['wish']);
$wishNumber = isset($input['wishNumber']) ? intval($input['wishNumber']) : 1;
$conversationHistory = isset($input['conversationHistory']) ? trim($input['conversationHistory']) : '';

// Build prompt with conversation context (exactly like Node.js version)
$contextSection = '';
if (!empty($conversationHistory)) {
    $contextSection = "\n\nPrevious conversation:\n" . $conversationHistory . "\n\nNow respond to the current wish with context:";
}

$prompt = "You are a Monkey's Paw. Grant wishes with clever twists. Be concise.

Rules:
- NO theatrics, fluff, or long explanations
- Start response with \"Granted—\" or \"Done—\"
- Give the wish with a clever twist/catch
- Keep it short: 1-2 sentences max
- Be witty but direct
- Reference previous wishes when relevant

Examples:
\"I want a car\" → \"Granted—you receive a toy car. It's technically a car!\"
\"I want money\" → \"Done—you now have Monopoly money. Still counts as money!\"" . $contextSection . "

Current wish: \"" . $wish . "\"";

// Gemini API endpoint
$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=' . $apiKey;

// Prepare the request payload for Gemini (same as Node.js)
$payload = [
    'contents' => [
        [
            'parts' => [
                [
                    'text' => $prompt
                ]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.9,
        'maxOutputTokens' => 256,
        'topP' => 0.8,
        'topK' => 10
    ]
];

// Make the request to Gemini API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Handle cURL errors
if ($error) {
    error_log("Gemini API cURL Error: " . $error);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'AI service temporarily unavailable']);
    exit;
}

// Handle API errors
if ($httpCode !== 200) {
    error_log("Gemini API Error: HTTP $httpCode - $response");
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'AI service temporarily unavailable']);
    exit;
}

// Parse response
$data = json_decode($response, true);

if (!$data || !isset($data['candidates'][0]['content']['parts'][0]['text'])) {
    error_log("Invalid Gemini response: " . $response);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'AI service temporarily unavailable']);
    exit;
}

$generatedText = $data['candidates'][0]['content']['parts'][0]['text'];

// Return the response (same format as Node.js)
echo json_encode([
    'success' => true,
    'response' => $generatedText,
    'wishNumber' => $wishNumber
]);
?>