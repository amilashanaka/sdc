<?php

/**
 * UserController - Handles user management operations
 * Optimized with comprehensive validation
 */
class UserController extends BaseController
{
    private User $userModel;
    private array $validationErrors = [];

    public function __construct()
    {
        $this->checkAuth();
        $this->userModel = new User();
    }

    /**
     * Display form for creating new user or editing existing one
     */
    public function index(): void
    {
        $encodedId = $this->get('id');
        $userId = $encodedId ? (int) base64_decode($encodedId) : 0;

        if ($userId > 0) {
            $user = new User($userId);
            if (empty($user->row)) {
                $this->setFlash('danger', 'User not found.');
                $this->redirect('user_list');
            }
            $title = 'Edit User';
        } else {
            $user = new User();
            $title = 'Create New User';
        }

        $this->view('user', [
            'user'   => $user,
            'title'  => $title,
            'userId' => $userId,
        ]);
    }

    /**
     * Handle user creation and update (POST only)
     */
    public function save(): void
    {
        if (!$this->isPost()) {
            $this->redirect('user_list');
        }

        $data = $this->post();
        $userId = !empty($data['id']) ? (int) $data['id'] : 0;

        // Server-side validation
        if (!$this->validateUserData($data, $userId)) {
            $this->setFlash('danger', implode('<br>', $this->validationErrors));
            $this->redirectBack($userId);
        }

        // Prepare sanitized data
        $userData = $this->prepareUserData($data, $userId);

        // Handle password
        $passwordResult = $this->handlePassword($data, $userId);
        if ($passwordResult['error']) {
            $this->setFlash('danger', $passwordResult['message']);
            $this->redirectBack($userId);
        }
        if ($passwordResult['password']) {
            $userData['f3'] = $passwordResult['password'];
        }

        // Check for duplicate username/email
        if (!$this->checkDuplicates($userData, $userId)) {
            $this->setFlash('danger', implode('<br>', $this->validationErrors));
            $this->redirectBack($userId);
        }

        // Execute create or update
        try {
            $success = $userId > 0
                ? $this->userModel->update($userId, $userData)
                : $this->userModel->insert($userData);

            $message = $success
                ? ($userId > 0 ? 'User updated successfully!' : 'User created successfully!')
                : ($userId > 0 ? 'Failed to update user.' : 'Failed to create user.');

            $this->setFlash($success ? 'success' : 'danger', $message);

            // Redirect
            $this->redirect($userId > 0
                ? 'user?id=' . base64_encode($userId)
                : 'user_list'
            );
        } catch (Exception $e) {
            error_log("User save error: " . $e->getMessage());
            $this->setFlash('danger', 'An error occurred while saving the user.');
            $this->redirectBack($userId);
        }
    }

    // ─── Private Validation Methods ────────────────────────────────────────────

    /**
     * Comprehensive server-side validation
     */
    private function validateUserData(array $data, int $userId): bool
    {
        $this->validationErrors = [];

        // Username validation (only for new users)
        if ($userId === 0) {
            if (empty($data['f1'])) {
                $this->validationErrors[] = 'Username is required.';
            } elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $data['f1'])) {
                $this->validationErrors[] = 'Username must be 3-50 characters (letters, numbers, underscore only).';
            }
        }

        // Email validation
        if (empty($data['f2'])) {
            $this->validationErrors[] = 'Email is required.';
        } elseif (!filter_var($data['f2'], FILTER_VALIDATE_EMAIL)) {
            $this->validationErrors[] = 'Please provide a valid email address.';
        }

        // Full name validation
        if (empty($data['f5'])) {
            $this->validationErrors[] = 'Full name is required.';
        } elseif (strlen($data['f5']) < 2 || strlen($data['f5']) > 100) {
            $this->validationErrors[] = 'Full name must be between 2 and 100 characters.';
        }

        // Phone validation (if provided)
        if (!empty($data['f6']) && !preg_match('/^\+?[0-9\s\-\(\)]{10,20}$/', $data['f6'])) {
            $this->validationErrors[] = 'Primary phone number format is invalid.';
        }

        if (!empty($data['f8']) && !preg_match('/^\+?[0-9\s\-\(\)]{10,20}$/', $data['f8'])) {
            $this->validationErrors[] = 'Secondary phone number format is invalid.';
        }

        // Address validation (optional but length check)
        if (!empty($data['f7']) && strlen($data['f7']) > 500) {
            $this->validationErrors[] = 'Address must not exceed 500 characters.';
        }

        return empty($this->validationErrors);
    }

    /**
     * Check for duplicate username or email
     */
    private function checkDuplicates(array $userData, int $userId): bool
    {
        // Check duplicate username (only for new users)
        if ($userId === 0 && !empty($userData['f1'])) {
            $existingUser = $this->userModel->whereFirst('f1', $userData['f1']);
            if ($existingUser) {
                $this->validationErrors[] = 'Username already exists. Please choose a different username.';
            }
        }

        // Check duplicate email
        if (!empty($userData['f2'])) {
            $existingEmail = $this->userModel->whereFirst('f2', $userData['f2']);
            if ($existingEmail && ($userId === 0 || $existingEmail->id != $userId)) {
                $this->validationErrors[] = 'Email address already exists. Please use a different email.';
            }
        }

        return empty($this->validationErrors);
    }

    // ─── Private Helper Methods ────────────────────────────────────────────────

    /**
     * Prepare and sanitize user data
     */
    private function prepareUserData(array $data, int $userId): array
    {
        $currentUserId = $_SESSION['user_id'] ?? 0;

        $clean = [
            'f2'         => filter_var($this->sanitize($data['f2'] ?? ''), FILTER_SANITIZE_EMAIL),
            'f5'         => $this->sanitize($data['f5'] ?? ''),
            'f6'         => $this->sanitize($data['f6'] ?? ''),
            'f8'         => $this->sanitize($data['f8'] ?? ''),
            'f7'         => $this->sanitize($data['f7'] ?? ''),
            'f4'         => (int) ($data['f4'] ?? 0),
            'status'     => (int) ($data['status'] ?? 1),
            'updated_by' => $currentUserId,
        ];

        // Only include username for new users
        if ($userId === 0) {
            $clean['f1'] = $this->sanitize($data['f1'] ?? '');
            $clean['created_by'] = $currentUserId;
        }

        // Remove empty values except for numeric fields
        return array_filter($clean, function($v, $k) {
            return $v !== '' || in_array($k, ['f4', 'status', 'updated_by', 'created_by']);
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Handle password validation and hashing
     */
    private function handlePassword(array $data, int $userId): array
    {
        // If password field is empty, skip password update
        if (empty($data['f3'])) {
            return ['error' => false, 'password' => null];
        }

        // Password confirmation check
        if (!isset($data['f3_confirm']) || $data['f3'] !== $data['f3_confirm']) {
            return [
                'error'   => true,
                'message' => 'Passwords do not match.'
            ];
        }

        // Password length check
        if (strlen($data['f3']) < 8) {
            return [
                'error'   => true,
                'message' => 'Password must be at least 8 characters long.'
            ];
        }

        // Password complexity check
        $hasUpperCase = preg_match('/[A-Z]/', $data['f3']);
        $hasLowerCase = preg_match('/[a-z]/', $data['f3']);
        $hasNumber = preg_match('/\d/', $data['f3']);

        if (!($hasUpperCase && $hasLowerCase && $hasNumber)) {
            return [
                'error'   => true,
                'message' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.'
            ];
        }

        // Hash password securely
        return [
            'error'    => false,
            'password' => password_hash($data['f3'], PASSWORD_ARGON2ID, [
                'memory_cost' => 65536,
                'time_cost'   => 4,
                'threads'     => 3
            ])
        ];
    }

    /**
     * Redirect back to user form
     */
    private function redirectBack(int $userId): void
    {
        $redirect = $userId > 0
            ? 'user?id=' . base64_encode($userId)
            : 'user';

        $this->redirect($redirect);
    }
}