<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Up Your Account | Time Tracker</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    
    <style>
        :root {
            --primary-color: #FF0000;
            --secondary-color: #000000;
            --background-color: #FFFFFF;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .setup-container {
            max-width: 500px;
            width: 100%;
            padding: 20px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: var(--background-color);
            text-align: center;
            border-top-left-radius: 15px !important;
            border-top-right-radius: 15px !important;
            padding: 30px 20px;
        }
        
        .logo {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .welcome-info {
            background-color: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            margin-bottom: 15px;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(255, 0, 0, 0.25);
            border-color: var(--primary-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 12px;
            font-weight: 500;
            width: 100%;
        }
        
        .btn-primary:hover {
            background-color: #d90000;
            border-color: #d90000;
        }
        
        .password-requirements {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: -10px;
            margin-bottom: 15px;
        }
        
        .password-requirements ul {
            margin: 5px 0;
            padding-left: 20px;
        }
        
        .input-group-text {
            background-color: var(--background-color);
            border-right: none;
            border: 2px solid #e9ecef;
            border-right: none;
        }
        
        .input-group .form-control {
            border-left: none;
            margin-bottom: 0;
        }
        
        .password-toggle {
            cursor: pointer;
            background: none;
            border: none;
            color: #6c757d;
        }
        
        .error-message {
            color: var(--primary-color);
            font-size: 0.9rem;
            margin-top: -10px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="card">
            <div class="card-header">
                <h1 class="logo">Time Tracker</h1>
                <p class="mb-0">Set Up Your Account</p>
            </div>
            
            <div class="card-body p-4">
                <div class="welcome-info">
                    <h6 class="mb-2"><i class='bx bx-user'></i> Welcome, {{ $invitation->name }}!</h6>
                    <p class="mb-1"><strong>Email:</strong> {{ $invitation->email }}</p>
                    <p class="mb-1"><strong>Role:</strong> {{ ucfirst(str_replace('_', ' ', $invitation->role->name)) }}</p>
                    @if($invitation->company)
                        <p class="mb-0"><strong>Company:</strong> {{ $invitation->company }}</p>
                    @endif
                </div>
                
                <p class="text-muted mb-4">Please create a secure password to complete your account setup.</p>
                
                <form method="POST" action="{{ route('invitation.setup', $invitation->token) }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-lock-alt'></i></span>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            <button type="button" class="input-group-text password-toggle" onclick="togglePassword('password')">
                                <i class='bx bx-show' id="password-icon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                        <div class="password-requirements">
                            <strong>Password must contain:</strong>
                            <ul>
                                <li>At least 8 characters</li>
                                <li>At least one uppercase letter</li>
                                <li>At least one lowercase letter</li>
                                <li>At least one number</li>
                                <li>At least one special character</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirm Password *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class='bx bx-lock-alt'></i></span>
                            <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                   id="password_confirmation" name="password_confirmation" required>
                            <button type="button" class="input-group-text password-toggle" onclick="togglePassword('password_confirmation')">
                                <i class='bx bx-show' id="password_confirmation-icon"></i>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class='bx bx-check'></i> Complete Account Setup
                    </button>
                </form>
                
                <div class="text-center mt-4">
                    <small class="text-muted">
                        <i class='bx bx-time'></i> 
                        This invitation expires on {{ $invitation->expires_at->format('F j, Y \a\t g:i A') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('bx-show');
                icon.classList.add('bx-hide');
            } else {
                field.type = 'password';
                icon.classList.remove('bx-hide');
                icon.classList.add('bx-show');
            }
        }
        
        // Real-time password validation
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const requirements = document.querySelector('.password-requirements ul');
            const items = requirements.querySelectorAll('li');
            
            // Check each requirement
            const checks = [
                password.length >= 8,
                /[A-Z]/.test(password),
                /[a-z]/.test(password),
                /\d/.test(password),
                /[!@#$%^&*(),.?":{}|<>]/.test(password)
            ];
            
            items.forEach((item, index) => {
                if (checks[index]) {
                    item.style.color = '#198754';
                    item.style.fontWeight = 'bold';
                } else {
                    item.style.color = '#6c757d';
                    item.style.fontWeight = 'normal';
                }
            });
        });
    </script>
</body>
</html>