<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.user_status_subject') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f6f9fc; padding: 20px; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .email-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 20px; text-align: center; }
        .email-header h1 { font-size: 24px; font-weight: 600; margin: 0; }
        .email-body { padding: 40px; }
        .greeting { font-size: 18px; margin-bottom: 20px; color: #333; }
        .instruction { font-size: 16px; color: #666; margin-bottom: 30px; }
        .status-container { text-align: center; margin: 40px 0; }
        .status { display: inline-block; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; font-size: 24px; font-weight: bold; padding: 20px 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(244,86,108,0.3); }
        .support { border-top: 1px solid #eaeaea; padding-top: 20px; margin-top: 30px; font-size: 14px; color: #6c757d; }
        .email-footer { background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #eaeaea; }
        .email-footer p { font-size: 12px; color: #6c757d; margin: 5px 0; }
        .logo { color: white; font-size: 28px; font-weight: bold; margin-bottom: 10px; }
        @media (max-width: 600px) { .email-body { padding: 20px; } .status { font-size: 20px; padding: 15px 30px; } }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="logo">{{ config('app.name', 'Laravel') }}</div>
            <h1>{{ __('messages.user_status_subject') }}</h1>
        </div>
        
        <div class="email-body">
            <p class="greeting">{{ __('messages.hello') }} {{ $name }},</p>
            
            <p class="instruction">{{ __('messages.status_instruction') }}</p>
            
            <div class="status-container">
                <div class="status">{{ $status }}</div>
            </div>
            
        <div class="support">
            @if($status === 'accepted')
                <p>{{ __('messages.follow_up_account') }}</p>
            @elseif($status === 'rejected')
                <p>{{ __('messages.rejected_follow_up') }}</p>
            @endif
            <p>{{ __('messages.contact_support') }}</p>
        </div>

        </div>
        
        <div class="email-footer">
            <p>{{ __('messages.email_sent_from') }} {{ config('app.name', 'Laravel') }}</p>
            <p>© {{ date('Y') }} {{ config('app.name', 'Laravel') }}. {{ __('messages.all_rights_reserved') }}</p>
        </div>
    </div>
</body>
</html>
