<?php
if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
// Add this function to your functions.php or a global utility file

/**
 * Redirects based on the result and optionally sets a session message.
 *
 * @param array $result - The result array returned from the operation
 * @param string $successRedirect - The URL to redirect to on success
 * @param string $errorRedirect - The URL to redirect to on error
 * @param string $successMessage - Optional success message to set in session
 * @param string $errorMessage - Optional error message to set in session
 */
function handleRedirection($result, $successRedirect, $errorRedirect = null, $successMessage = null, $errorMessage = null)
{

    // Check if 'error', 'status', and 'code' fields exist in $result
    $hasError = isset($result['error']);
    $hasStatus = isset($result['status']);
    $hasCode = isset($result['code']);
    $hasMessage = isset($result['message']);


 

    // Handle success case
    if (($hasStatus && $result['status'] > 0) || ($hasCode && $result['code'] == 200) || ($hasMessage && $result['message'])) {
       
     

        // If there is an error but it's not 'No data found', store it in the session
        if ($hasError && $result['error'] != null && $result['error'] != 'No data found') {
            $_SESSION['error'] = $result['error'];
        }

        // Set the success message in the session, using either the provided one or a default
        $_SESSION['message'] = array(
            'title' => $successMessage['title'] ?? 'Success',
            'text' => $successMessage['message'] ?? ($hasMessage ? $result['message'] : 'Operation completed successfully.'),
            'icon' => 'success'
        );


        // Redirect to success URL
        header('Location: ' . $successRedirect);
    } 
    // Handle error case
    else {
        // If there is an error, set it in the session
        if ($hasError && $result['error'] && $result['error'] != 'No data found') {
            $_SESSION['error'] = $errorMessage ?? $result['error'];
        }

        // Redirect to error URL or fallback to success URL if no errorRedirect is provided
        $redirectUrl = $errorRedirect ?? $successRedirect;
        header('Location: ' . $redirectUrl);
    }

    exit;
}


