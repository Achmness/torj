<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

include "db.php";

$fullname = isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Admin';

// Fetch all users
$users = [];
$result = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Debug Café</title>
    <link rel="stylesheet" href="../style/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .btn-add-user {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: #ECB212;
            color: #3d2d00;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            margin-bottom: 20px;
        }
        
        .btn-add-user:hover {
            background: #d4a010;
            transform: translateY(-2px);
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-edit, .btn-delete {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-edit {
            background: #5d4037;
            color: white;
        }
        
        .btn-edit:hover {
            background: #8B6F47;
        }
        
        .btn-delete {
            background: #e74c3c;
            color: white;
        }
        
        .btn-delete:hover {
            background: #c0392b;
        }
        
        .user-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            justify-content: center;
            align-items: center;
        }
        
        .user-modal.open {
            display: flex;
        }
        
        .user-modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            min-width: 400px;
            max-width: 500px;
        }
        
        .user-modal-content h3 {
            margin: 0 0 20px;
            color: #3d2d00;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #3d2d00;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #E8E0D5;
            border-radius: 8px;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #ECB212;
        }
        
        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-save, .btn-cancel {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-save {
            background: #ECB212;
            color: #3d2d00;
        }
        
        .btn-save:hover {
            background: #d4a010;
        }
        
        .btn-cancel {
            background: #95a5a6;
            color: white;
        }
        
        .btn-cancel:hover {
            background: #7f8c8d;
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../images/logo.png" alt="Debug Café" class="sidebar-logo">
            <h1 class="sidebar-brand">Debug Café</h1>
            <p class="sidebar-tagline">Coffee & Code</p>
        </div>
        <nav class="nav-links">
            <a href="admin.php" class="nav-item">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="neworder.php" class="nav-item">
                <i class="fas fa-shopping-cart"></i>
                <span>New Order</span>
            </a>
            <a href="orders.php" class="nav-item">
                <i class="fas fa-list"></i>
                <span>Orders</span>
            </a>
            <a href="transactions.php" class="nav-item">
                <i class="fas fa-exchange-alt"></i>
                <span>Transactions</span>
            </a>
            <a href="products.php" class="nav-item">
                <i class="fas fa-box"></i>
                <span>Manage Products</span>
            </a>
            <a href="users.php" class="nav-item active">
                <i class="fas fa-users"></i>
                <span>Manage Users</span>
            </a>
        </nav>
        <a href="logout.php" class="nav-logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <h2 class="dashboard-title">Manage Users</h2>
            <div class="user-greeting">
                <span>Welcome, <?php echo $fullname; ?></span>
                <div class="user-avatar"></div>
            </div>
        </header>

        <button class="btn-add-user" onclick="openAddUserModal()">
            <i class="fas fa-user-plus"></i> Add New User
        </button>

        <section class="recent-orders">
            <div class="orders-table-wrapper">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                                <tr class="order-row">
                                    <td>#<?php echo (int)$user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $user['role']; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-edit" onclick='editUser(<?php echo json_encode($user); ?>)'>
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn-delete" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['fullname']); ?>')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="no-orders">No users found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- Add/Edit User Modal -->
    <div id="userModal" class="user-modal">
        <div class="user-modal-content">
            <h3 id="modalTitle">Add New User</h3>
            <form id="userForm">
                <input type="hidden" id="userId" name="userId">
                
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Leave blank to keep current">
                </div>
                
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="customer">Customer</option>
                        <option value="cashier">Cashier</option>
                        <option value="barista">Barista</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="modal-buttons">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Save
                    </button>
                    <button type="button" class="btn-cancel" onclick="closeUserModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddUserModal() {
            document.getElementById('modalTitle').textContent = 'Add New User';
            document.getElementById('userForm').reset();
            document.getElementById('userId').value = '';
            document.getElementById('password').placeholder = 'Enter password';
            document.getElementById('password').required = true;
            document.getElementById('userModal').classList.add('open');
        }

        function editUser(user) {
            document.getElementById('modalTitle').textContent = 'Edit User';
            document.getElementById('userId').value = user.id;
            document.getElementById('fullname').value = user.fullname;
            document.getElementById('email').value = user.email;
            document.getElementById('role').value = user.role;
            document.getElementById('password').value = '';
            document.getElementById('password').placeholder = 'Leave blank to keep current';
            document.getElementById('password').required = false;
            document.getElementById('userModal').classList.add('open');
        }

        function closeUserModal() {
            document.getElementById('userModal').classList.remove('open');
        }

        function deleteUser(userId, userName) {
            if (!confirm(`Are you sure you want to delete user "${userName}"?`)) {
                return;
            }

            fetch('../api/delete_user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('User deleted successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to delete user'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete user');
            });
        }

        document.getElementById('userForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const userId = document.getElementById('userId').value;
            const formData = {
                user_id: userId || null,
                fullname: document.getElementById('fullname').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
                role: document.getElementById('role').value
            };

            fetch('../api/save_user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert(userId ? 'User updated successfully!' : 'User added successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to save user'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to save user');
            });
        });

        document.getElementById('userModal').addEventListener('click', function(e) {
            if (e.target === this) closeUserModal();
        });
    </script>
</body>
</html>
