<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>メンテナンス中 | {{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <style>
            *, *::before, *::after {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            :root {
                --bg-page: #f8fafc;
                --bg-card: #ffffff;
                --border-card: #e2e8f0;
                --text-heading: #111827;
                --text-body: #4b5563;
                --icon-bg: #eef2ff;
                --icon-color: #4f46e5;
                --shadow-card: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.05);
            }

            @media (prefers-color-scheme: dark) {
                :root {
                    --bg-page: #0f172a;
                    --bg-card: #1e293b;
                    --border-card: #334155;
                    --text-heading: #f8fafc;
                    --text-body: #cbd5e1;
                    --icon-bg: rgba(49, 46, 129, 0.5);
                    --icon-color: #818cf8;
                    --shadow-card: 0 10px 15px -3px rgba(0, 0, 0, 0.35), 0 4px 6px -4px rgba(0, 0, 0, 0.25);
                }
            }

            body {
                font-family: 'Figtree', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                background-color: var(--bg-page);
                color: var(--text-heading);
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }

            .container {
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 1.5rem 1rem;
            }

            .logo-wrap {
                margin-bottom: 2rem;
            }

            .logo-svg {
                width: 5rem;
                height: 5rem;
                display: block;
            }

            .card {
                width: 100%;
                max-width: 28rem;
                padding: 2.5rem 2rem;
                background-color: var(--bg-card);
                border: 1px solid var(--border-card);
                border-radius: 1rem;
                box-shadow: var(--shadow-card);
                text-align: center;
            }

            .icon-wrap {
                width: 3.5rem;
                height: 3.5rem;
                margin: 0 auto 1.25rem;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 9999px;
                background-color: var(--icon-bg);
                color: var(--icon-color);
            }

            .icon-svg {
                width: 1.75rem;
                height: 1.75rem;
            }

            h1 {
                font-size: 1.25rem;
                font-weight: 600;
                line-height: 1.4;
                letter-spacing: -0.015em;
                color: var(--text-heading);
            }

            p {
                margin-top: 0.75rem;
                font-size: 0.9375rem;
                line-height: 1.6;
                color: var(--text-body);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="card">
                <div class="icon-wrap">
                    <svg class="icon-svg" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
                    </svg>
                </div>

                <h1>ただいまメンテナンス中です</h1>
                <p>復旧まで今しばらくお待ちください</p>
            </div>
        </div>
    </body>
</html>
