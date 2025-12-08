<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - Light MVC</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body class="dashboard-page">
    <nav class="navbar">
        <div class="navbar-brand">Light MVC</div>
        <div class="navbar-menu">
            <a href="<?php echo BASE_URL; ?>/dashboard">Dashboard</a>
            <a href="<?php echo BASE_URL; ?>/dashboard/profile">Profile</a>
            <a href="<?php echo BASE_URL; ?>/login/logout">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1><?php echo htmlspecialchars($title); ?></h1>
        
        <?php if ($title === 'Dashboard'): ?>
            <div class="welcome">
                Welcome, <?php echo htmlspecialchars($user->full_name); ?>!
            </div>

            <div class="card">
                <h3>Your Info</h3>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user->username); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user->email); ?></p>
                <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($user->created_at)); ?></p>
            </div>

            <div class="card">
                <h3>All Users (<?php echo count($users); ?>)</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo $u->id; ?></td>
                            <td><?php echo htmlspecialchars($u->username); ?></td>
                            <td><?php echo htmlspecialchars($u->email); ?></td>
                            <td><?php echo date('M d, Y', strtotime($u->created_at)); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="card">
                <h3>Profile Information</h3>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user->username); ?></p>
                <p><strong>Full Name:</strong> <?php echo htmlspecialchars($user->full_name); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user->email); ?></p>
                <p><strong>Account Created:</strong> <?php echo date('F j, Y, g:i a', strtotime($user->created_at)); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
</body>
</html>