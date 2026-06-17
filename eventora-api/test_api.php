<?php
$base = 'http://127.0.0.1:8000/api/v1';

function req($method, $url, $data = null, $token = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $headers = ['Accept: application/json', 'Content-Type: application/json'];
    if ($token) $headers[] = 'Authorization: Bearer ' . $token;
    
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    
    $headersOutput = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    curl_close($ch);
    
    return ['code' => $httpCode, 'headers' => $headersOutput, 'body' => json_decode($body, true) ?: $body];
}

echo "1. Public Events: ";
$res = req('GET', $base . '/events');
echo $res['code'] . "\n";
echo "Headers:\n";
$headerLines = explode("\r\n", $res['headers']);
foreach ($headerLines as $line) {
    if (str_starts_with(strtolower($line), 'x-') || str_starts_with(strtolower($line), 'strict-') || str_starts_with(strtolower($line), 'content-security-')) {
        echo "  - $line\n";
    }
}

echo "2. Test Auth Rate Limiting (5 requests per minute allowed):\n";
for ($i = 1; $i <= 6; $i++) {
    $res = req('POST', $base . '/auth/login', ['email' => 'test@example.com', 'password' => 'wrong', 'device_name' => 'test']);
    echo "  Request $i: " . $res['code'] . "\n";
}

echo "3. Login with valid creds (Assuming rate limit might block this, waiting 1 min is tedious, we will just see what happens): \n";
$res = req('POST', $base . '/auth/login', ['email' => 'test@example.com', 'password' => 'password', 'device_name' => 'test']);
echo "Login code: " . $res['code'] . "\n";
$token = $res['body']['token'] ?? null;
$orgId = $res['body']['organizations'][0]['id'] ?? null;
echo "Token: " . ($token ? 'Yes' : 'No') . ", OrgID: $orgId\n";

if ($token) {
    echo "4. Get Profile: ";
    $res = req('GET', $base . '/auth/me', null, $token);
    echo $res['code'] . "\n";

    if ($orgId) {
        echo "5. Get Dashboard (Valid Org): ";
        $res = req('GET', $base . "/organizer/$orgId/dashboard", null, $token);
        echo $res['code'] . "\n";

        echo "6. Get Dashboard (Invalid Org ID = 99999): ";
        $res = req('GET', $base . "/organizer/99999/dashboard", null, $token);
        echo $res['code'] . "\n";
        
        echo "7. Get Dashboard (Without Token): ";
        $res = req('GET', $base . "/organizer/$orgId/dashboard");
        echo $res['code'] . "\n";

        // Let's test IDOR - can a user access an org they don't belong to?
        // To do this properly, I should find an org ID the user does NOT belong to.
        echo "8. Get Dashboard (Org ID = 2, might not belong to user): ";
        $res = req('GET', $base . "/organizer/2/dashboard", null, $token);
        echo $res['code'] . "\n";
    }
}
