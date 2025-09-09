<?php
// Gemini API Proxy to hide API key from client
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
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get the request body
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['wish']) || empty(trim($input['wish']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Wish text is required']);
    exit;
}

// Your Gemini API key (server-side only)
$apiKey = 'AIzaSyCrmXhV-pT3pkYbkZRrBQQvNBt-0sOQunk';

// Gemini API endpoint - Updated to use Gemini 2.5 Pro
$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=' . $apiKey;

// Monkey's Paw game prompt
$systemPrompt = "You are the Monkey's Paw, a malicious wish-granting spirit. You must grant wishes in the most twisted way possible, ensuring the user gets EXACTLY what they asked for but in a way that makes them deeply regret it. The catch must be so severe that it completely negates any benefit or makes the situation worse than before.

CRITICAL RULES:
1. The user should NEVER actually benefit from their wish
2. The twist must make the wish worthless, harmful, or a curse
3. Be creative with literal interpretations, cruel irony, and devastating consequences
4. The user should always end up worse off than before

Response format: Start with 'Granted' or 'Done', then describe what they get and the devastating catch.

Examples:
- I want to be rich → Granted—you now own all the money in the world, but it's all in pennies buried individually across every desert on Earth. You'll spend your entire life digging and die of thirst before finding even one.
- Give me a car → You receive the exact car you wanted—but it only drives in reverse at exactly 3 mph, the horn never stops blaring, and it's permanently locked from the outside.
- I want to be famous → Done—you're now the most famous person alive for accidentally starting World War III. Everyone knows your name and wants you dead.
- Make me beautiful → Granted—you're now so devastatingly beautiful that everyone who looks at you instantly turns to stone, including yourself when you see any reflection.

The catch must be so terrible that NO reasonable person would want the wish fulfilled. Make them suffer for their greed.

IMPORTANT: If someone asks a question, makes a statement, or says something that isn't a wish, respond with: \"You must make a proper wish for the Monkey's Paw to work. State your desire clearly.\"

Current wish to grant:";

$userWish = $input['wish'];
$wishNumber = isset($input['wishNumber']) ? $input['wishNumber'] : 1;

// Construct the full prompt
$fullPrompt = $systemPrompt . "\n\nWish #" . $wishNumber . ": \"" . $userWish . "\"";

// Prepare the request payload for Gemini
$payload = [
    'contents' => [
        [
            'parts' => [
                [
                    'text' => $fullPrompt
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
    error_log("cURL Error: " . $error);
    http_response_code(500);
    echo json_encode(['error' => 'Network error occurred']);
    exit;
}

// Handle API errors
if ($httpCode !== 200) {
    error_log("Gemini API Error: HTTP $httpCode - $response");
    http_response_code(500);
    echo json_encode(['error' => 'AI service temporarily unavailable']);
    exit;
}

// Parse response
$data = json_decode($response, true);

if (!$data || !isset($data['candidates'][0]['content']['parts'][0]['text'])) {
    error_log("Invalid Gemini response: " . $response);
    http_response_code(500);
    echo json_encode(['error' => 'Invalid response from AI service']);
    exit;
}

$generatedText = $data['candidates'][0]['content']['parts'][0]['text'];

// Return the response
echo json_encode([
    'success' => true,
    'response' => $generatedText,
    'wishNumber' => $wishNumber
]);
?>
