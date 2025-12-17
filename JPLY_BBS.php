<?php
// Define the path to your downloaded Tor IP list file
$torListFile = '/path/to/your/tor_exit_nodes.txt';

// Get the visitor's IP address. Be mindful of proxies and CDNs (e.g., CloudFlare)
// which may use other headers like HTTP_X_FORWARDED_FOR or HTTP_CF_CONNECTING_IP.
$visitorIP = $_SERVER['REMOTE_ADDR'];

// Check for proxy/CDN headers if applicable (uncomment and adjust as needed)
/*
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $visitorIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
} elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $visitorIP = $_SERVER['HTTP_CF_CONNECTING_IP'];
}
*/

// Function to check if the IP is a Tor exit node
function isTorExitNode($ip, $listFile) {
    if (!file_exists($listFile)) {
        // Log an error if the list is missing or outdated
        error_log("Tor exit node list file not found: $listFile");
        return false; // Better to allow access than block legitimate users if the list is down
    }

    // Read the list into an array, one IP per line
    $torIPs = file($listFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Check if the visitor's IP is in the array of Tor IPs
    return in_array($ip, $torIPs);
}

// Perform the check and block if necessary
if (isTorExitNode($visitorIP, $torListFile)) {
    // Deny access
    header("HTTP/1.1 403 Forbidden");
    die("Sorry, access from the Tor network is not allowed on this site.");
}

// The rest of your website's PHP code goes here...
?>
