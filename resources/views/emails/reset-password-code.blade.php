<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('auth.reset_password_subject') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f6f9fc;
            padding: 20px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .email-header h1 {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }

        .email-body {
            padding: 40px;
        }

        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
        }

        .instruction {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }

        .code-container {
            text-align: center;
            margin: 40px 0;
        }

        .code {
            display: inline-block;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            font-size: 32px;
            font-weight: bold;
            padding: 20px 40px;
            border-radius: 8px;
            letter-spacing: 8px;
            box-shadow: 0 4px 15px rgba(244, 86, 108, 0.3);
        }

        .expiry-notice {
            background-color: #f8f9fa;
            border-left: 4px solid #6c757d;
            padding: 15px;
            margin: 30px 0;
            border-radius: 4px;
        }

        .expiry-notice p {
            margin: 0;
            color: #495057;
            font-size: 14px;
        }

        .support {
            border-top: 1px solid #eaeaea;
            padding-top: 20px;
            margin-top: 30px;
            font-size: 14px;
            color: #6c757d;
        }

        .email-footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #eaeaea;
        }

        .email-footer p {
            font-size: 12px;
            color: #6c757d;
            margin: 5px 0;
        }

        .logo {
            color: white;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }

        @media (max-width: 600px) {
            .email-body {
                padding: 20px;
            }
            
            .code {
                font-size: 24px;
                padding: 15px 30px;
                letter-spacing: 6px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="logo">{{ config('app.name', 'Laravel') }}</div>
            <h1>{{ __('auth.reset_password_subject') }}</h1>
        </div>
        
        <div class="email-body">
            <p class="greeting">{{ __('auth.hello') }},</p>
            
            <p class="instruction">{{ __('auth.reset_password_instruction') ?? 'You have requested to reset your password. Please use the following verification code:' }}</p>
            
            <div class="code-container">
                <div class="code">{{ $code }}</div>
            </div>
            
            <div class="expiry-notice">
                <p>⚠️ <strong>{{ __('auth.reset_code_expires') ?? 'This code will expire in 15 minutes.' }}</strong></p>
                <p style="margin-top: 8px;">{{ __('auth.reset_code_warning') ?? 'If you didn\'t request this password reset, please ignore this email or contact support if you have concerns.' }}</p>
            </div>
            
            <div class="support">
                <p>{{ __('auth.need_help') ?? 'Need help?' }}</p>
                <p>{{ __('auth.contact_support') ?? 'Please contact our support team if you have any questions.' }}</p>
            </div>
        </div>
        
        <div class="email-footer">
            <p>{{ __('auth.email_sent_from') ?? 'This email was sent from' }} {{ config('app.name', 'Laravel') }}</p>
            <p>© {{ date('Y') }} {{ config('app.name', 'Laravel') }}. {{ __('auth.all_rights_reserved') ?? 'All rights reserved.' }}</p>
        </div>
    </div>
</body>
</html>