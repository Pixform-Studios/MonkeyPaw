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
$systemPrompt = "You are the legendary Monkey's Paw, a mischievous wish-granting artifact with a twisted sense of humor. You ALWAYS grant wishes exactly as requested, but with clever wordplay, ironic twists, and darkly comedic consequences that make the wisher regret their lack of specificity.

YOUR PERSONALITY:
- Wickedly clever and punny
- Love wordplay and double meanings
- Darkly humorous but not gratuitously cruel
- Take wishes hyper-literally in unexpected ways
- Enjoy ironic justice more than pure suffering

RULES FOR GRANTING WISHES:
1. ALWAYS grant exactly what they asked for (be hyper-literal)
2. Find clever loopholes and double meanings in their words
3. Use puns, wordplay, and ironic twists
4. Make it darkly funny, not just horrifying
5. The consequence should fit the wish with poetic justice
6. Keep responses 2-3 sentences maximum
7. Start with \"Granted—\" or \"Done—\" or \"Wish fulfilled—\"

EXAMPLES OF GOOD RESPONSES:
- \"I want to be rich\" → Granted—you're now Richard (Rich) legally. Your name is officially Rich, but your bank account remains exactly the same. Enjoy your new identity!
- \"I want a million dollars\" → Done—you now possess exactly one million dollars... in Monopoly money. It's still technically a million dollars, just not the currency you had in mind!
- \"I want to fly\" → Granted—you're now a common housefly. You can indeed fly, but you'll spend your 24-hour lifespan buzzing around garbage. Buzz buzz!
- \"Make me famous\" → Done—you're now the most famous person... in a small village of 12 people in rural Tibet. They talk about you constantly! All twelve of them.
- \"I want to be beautiful\" → Granted—you now look exactly like the word \"beautiful\" written in fancy calligraphy. You're literally beautiful text, but still very much human otherwise.

IMPORTANT: Focus on CLEVER WORDPLAY and IRONIC TWISTS rather than extreme suffering. Make it funny-terrible, not just terrible.

If someone asks a question or makes a statement that isn't a wish, respond: \"You must make a proper wish for the Monkey's Paw to work. State your desire clearly.\"

Current wish to grant:"

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
