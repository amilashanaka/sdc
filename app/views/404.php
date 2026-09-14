<?php 
http_response_code(404); // Set HTTP status code

include_once 'header.php';
?>

<!-- Content Wrapper - Added classes for vertical centering and full height -->
<div class="content-wrapper d-flex align-items-center justify-content-center" id="contentWrapper" style="min-height: 100vh;">
  <div class="container-fluid">
    
    <!-- 404 Content in a Card for UI Consistency -->
    <div class="row justify-content-center">
      <div class="col-xl-6 col-lg-8 col-md-10 col-sm-12">
        <div class="card shadow-lg border-0"> <!-- Improved card with more shadow and no border for modern look -->
          <div class="card-body text-center p-5"> <!-- Increased padding for better spacing -->
            <i class="fas fa-exclamation-triangle text-warning mb-4" style="font-size: 72px; animation: pulse 1.5s infinite;"></i> <!-- Larger icon with pulse animation -->
            <h3 class="card-title fw-bold mb-3">Oops! Page Not Found</h3> <!-- Added fw-bold for emphasis -->
            <p class="text-muted mb-4 lead"> <!-- Used lead class for larger text -->
              The page you're looking for doesn't exist or has been moved. 
              Please check the URL or return to the dashboard.
            </p>
            <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-primary btn-lg"> <!-- Larger button for better touch targets -->
              <i class="fas fa-home me-2"></i> Back to Dashboard
            </a>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<?php include 'footer.php'; ?>

<!-- Custom CSS for animation (add to main.css or inline) -->
<style>
@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}
</style>
  
</body>
</html>