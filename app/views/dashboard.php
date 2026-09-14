<?php 

 include_once 'header.php';
 include_once 'loader.php';
  include_once 'sidebar.php';
  include_once 'navbar.php';
?>



  <!-- Content Wrapper -->
  <div class="content-wrapper" id="contentWrapper">
    <div class="container-fluid">
      <h2 class="mb-4">Dashboard</h2>
      
      <!-- Info Boxes -->
      <div class="row">
        <div class="col-lg-3 col-md-6">
          <div class="info-box">
            <div class="info-box-icon bg-primary">
              <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="info-box-content">
              <h3>150</h3>
              <p>New Orders</p>
            </div>
          </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
          <div class="info-box">
            <div class="info-box-icon bg-success">
              <i class="fas fa-chart-line"></i>
            </div>
            <div class="info-box-content">
              <h3>53%</h3>
              <p>Bounce Rate</p>
            </div>
          </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
          <div class="info-box">
            <div class="info-box-icon bg-warning">
              <i class="fas fa-user-plus"></i>
            </div>
            <div class="info-box-content">
              <h3>44</h3>
              <p>User Registrations</p>
            </div>
          </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
          <div class="info-box">
            <div class="info-box-icon bg-danger">
              <i class="fas fa-eye"></i>
            </div>
            <div class="info-box-content">
              <h3>65</h3>
              <p>Unique Visitors</p>
            </div>
          </div>
        </div>
      </div>


    </div>
  </div>

<?php include 'footer.php'; ?>
  

</body>
</html>