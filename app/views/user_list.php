<div class="container-fluid">
  <h2 class="mb-4">All Users</h2>

  <!-- User List Card -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>User List</span>
      <button class="btn btn-primary btn-sm" onclick="loadContent('Add User')">
        <i class="fas fa-user-plus"></i> Add User
      </button>
    </div>

    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>User</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th style="width:120px;">Actions</th>
            </tr>
          </thead>

          <tbody>
            <!-- Example Row -->
            <tr>
              <td>1</td>
              <td>
                <div class="d-flex align-items-center">
                  <img src="https://ui-avatars.com/api/?name=John+Doe&background=17a2b8&color=fff&size=64"
                    class="rounded-circle me-2" width="40" height="40" />
                  <span>John Doe</span>
                </div>
              </td>
              <td>john@example.com</td>
              <td><span class="badge bg-info">Admin</span></td>
              <td><span class="badge bg-success">Active</span></td>
              <td>
                <button class="btn btn-sm btn-primary" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" title="Delete" onclick="confirmDeleteUser(1)">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>

            <!-- Example Row -->
            <tr>
              <td>2</td>
              <td>
                <div class="d-flex align-items-center">
                  <img src="https://ui-avatars.com/api/?name=Sarah+Lee&background=ffc107&color=fff&size=64"
                    class="rounded-circle me-2" width="40" height="40" />
                  <span>Sarah Lee</span>
                </div>
              </td>
              <td>sarah@example.com</td>
              <td><span class="badge bg-primary">Editor</span></td>
              <td><span class="badge bg-warning">Pending</span></td>
              <td>
                <button class="btn btn-sm btn-primary" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" title="Delete" onclick="confirmDeleteUser(2)">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>

            <!-- Example Row -->
            <tr>
              <td>3</td>
              <td>
                <div class="d-flex align-items-center">
                  <img src="https://ui-avatars.com/api/?name=Michael+Smith&background=dc3545&color=fff&size=64"
                    class="rounded-circle me-2" width="40" height="40" />
                  <span>Michael Smith</span>
                </div>
              </td>
              <td>michael@example.com</td>
              <td><span class="badge bg-success">User</span></td>
              <td><span class="badge bg-danger">Suspended</span></td>
              <td>
                <button class="btn btn-sm btn-primary" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" title="Delete" onclick="confirmDeleteUser(3)">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>

          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  function confirmDeleteUser(id) {
    Swal.fire({
      title: "Delete this user?",
      text: "This action cannot be undone!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#dc3545",
      cancelButtonColor: "#6c757d",
      confirmButtonText: "Delete"
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire("Deleted!", "The user has been removed.", "success");
        // TODO: Trigger API/PHP deletion here
      }
    });
  }
</script>
