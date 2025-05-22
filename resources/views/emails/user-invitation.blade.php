<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Time Tracker</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #FF0000;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .button {
            display: inline-block;
            background-color: #FF0000;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background-color: #d90000;
        }
        .info-box {
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Time Tracker</h1>
        <p>Project Management Tool</p>
    </div>
    
    <div class="content">
        <h2>Welcome to Time Tracker!</h2>
        
        <p>Hello {{ $invitation->name }},</p>
        
        <p>You have been invited to join our Time Tracker system as a <strong>{{ ucfirst(str_replace('_', ' ', $invitation->role->name)) }}</strong>. To get started, you'll need to set up your account password.</p>
        
        <div class="info-box">
            <strong>Your Account Details:</strong><br>
            <strong>Name:</strong> {{ $invitation->name }}<br>
            <strong>Email:</strong> {{ $invitation->email }}<br>
            <strong>Role:</strong> {{ ucfirst(str_replace('_', ' ', $invitation->role->name)) }}<br>
            @if($invitation->company)
                <strong>Company:</strong> {{ $invitation->company }}<br>
            @endif
        </div>
        
        <p>Click the button below to set up your password and activate your account:</p>
        
        <div style="text-align: center;">
            <a href="{{ route('invitation.show', ['token' => $invitation->token]) }}" class="button">Set Up My Account</a>
        </div>
        
        <div class="info-box" style="background-color: #fff3cd; border-color: #ffc107;">
            <strong>⚠️ Important:</strong><br>
            This invitation link will expire on <strong>{{ $expiresAt }}</strong>. Please set up your account before this date.
        </div>
        
        <p>If you have any questions or need assistance, please contact your administrator.</p>
        
        <p>Welcome to the team!</p>
        
        <div class="footer">
            <p>If you didn't expect this invitation, you can safely ignore this email.</p>
            <p>© {{ date('Y') }} Time Tracker. All rights reserved.</p>
        </div>
    </div>
</body>
</html>