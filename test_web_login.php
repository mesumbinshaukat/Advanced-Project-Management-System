<?php
// Simulate actual web login request
$url = 'http://localhost:8080/login';
$email = 'admin@example.com';
$password = 'admin123';

echo "=== Testing Web Login ===\n\n";

// First, get the login page to extract CSRF token
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "1. GET /login - HTTP $httpCode\n";

if ($httpCode != 200) {
    echo "   ✗ Login page not accessible\n";
    exit(1);
}

// Extract CSRF token
preg_match('/name="csrf_test_name"\s+value="([^"]+)"/', $response, $matches);
$csrfToken = $matches[1] ?? '';

if (empty($csrfToken)) {
    echo "   ✗ CSRF token not found\n";
    exit(1);
}

echo "   ✓ CSRF token extracted\n\n";

// Now attempt login
echo "2. POST /login with credentials...\n";

$postData = http_build_query([
    'email' => $email,
    'password' => $password,
    'csrf_test_name' => $csrfToken
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Status: $httpCode\n";

// Check if redirected (successful login)
if ($httpCode == 302 || $httpCode == 303) {
    preg_match('/Location: (.+)/', $response, $matches);
    $redirectUrl = trim($matches[1] ?? '');
    echo "   ✓ Redirected to: $redirectUrl\n";
    
    if (strpos($redirectUrl, 'dashboard') !== false || strpos($redirectUrl, '/') !== false) {
        echo "\n3. Verifying authenticated session...\n";
        
        // Try to access dashboard
        $ch = curl_init('http://localhost:8080/dashboard');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
        $dashResponse = curl_exec($ch);
        $dashCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "   Dashboard HTTP: $dashCode\n";
        
        if ($dashCode == 200) {
            echo "   ✓ Dashboard accessible\n";
            echo "\n✅ LOGIN SUCCESSFUL!\n\n";
            echo "You can now login with:\n";
            echo "Email: $email\n";
            echo "Password: $password\n";
            
            // Cleanup
            @unlink('cookies.txt');
            exit(0);
        } else if ($dashCode == 302) {
            echo "   ✗ Still redirecting (not authenticated)\n";
        }
    }
} else {
    echo "   ✗ Login failed (no redirect)\n";
    
    // Check for error message
    if (strpos($response, 'Unable to log you in') !== false) {
        echo "   Error: 'Unable to log you in' message found\n";
    }
}

echo "\n✗ LOGIN FAILED\n";
@unlink('cookies.txt');
exit(1);
