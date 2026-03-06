<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Login Dashboard PLN') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-sky: #d7eefc;
            --bg-cream: #fff7e8;
            --ink: #0f2942;
            --ink-soft: #4b6072;
            --brand: #0078c8;
            --brand-deep: #005d9b;
            --accent: #ff9f1c;
            --danger: #c62828;
            --ok: #1b8d41;
            --card: #ffffff;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--ink);
            min-height: 100vh;
            background:
                radial-gradient(circle at 12% 10%, #ffffff 0, #ffffff55 26%, transparent 46%),
                radial-gradient(circle at 88% 88%, #ffe5b0 0, #ffe5b055 20%, transparent 44%),
                linear-gradient(130deg, var(--bg-sky), var(--bg-cream));
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .layout {
            width: min(960px, 100%);
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            background: var(--card);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 22px 60px rgba(20, 53, 84, 0.18);
            animation: fadeIn 500ms ease-out;
        }

        .panel {
            padding: 36px;
        }

        .hero {
            background: linear-gradient(150deg, #0068ad, #0f82cf 55%, #3aabe6);
            color: #f2fbff;
            position: relative;
        }

        .hero::after {
            content: '';
            position: absolute;
            right: -80px;
            bottom: -80px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(255, 204, 96, 0.35);
            filter: blur(1px);
        }

        .logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            letter-spacing: .3px;
        }

        .logo-badge {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: linear-gradient(145deg, #ffd07d, #ff9f1c);
            color: #513100;
            display: grid;
            place-items: center;
            font-size: 14px;
            font-weight: 800;
        }

        .hero h1 {
            margin: 18px 0 10px;
            font-size: clamp(1.5rem, 3.5vw, 2.05rem);
            line-height: 1.2;
        }

        .hero p {
            margin: 0;
            color: #d8f0ff;
            line-height: 1.7;
            max-width: 34ch;
            font-size: .95rem;
        }

        .form-panel h2 {
            margin: 0;
            font-size: 1.45rem;
        }

        .subtitle {
            margin: 8px 0 26px;
            color: var(--ink-soft);
            font-size: .92rem;
        }

        .field {
            margin-bottom: 14px;
        }

        label {
            display: block;
            font-size: .86rem;
            margin-bottom: 7px;
            color: var(--ink-soft);
            font-weight: 600;
        }

        input {
            width: 100%;
            border: 1px solid #d4e3ef;
            border-radius: 12px;
            padding: 12px 13px;
            font: inherit;
            transition: border-color 160ms ease, box-shadow 160ms ease;
        }

        input:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 4px #0078c822;
        }

        .btn {
            width: 100%;
            border: none;
            border-radius: 12px;
            padding: 12px;
            background: linear-gradient(130deg, var(--brand), var(--brand-deep));
            color: #fff;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            margin-top: 8px;
        }

        .btn:hover { filter: brightness(1.04); }

        .alert {
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 14px;
            font-size: .85rem;
            line-height: 1.55;
        }

        .alert-error {
            background: #ffe9e9;
            color: var(--danger);
            border: 1px solid #ffcaca;
        }

        .alert-success {
            background: #e8f8ee;
            color: var(--ok);
            border: 1px solid #b5e5c5;
        }

        .error-list {
            margin: 0;
            padding-left: 18px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 860px) {
            .layout {
                grid-template-columns: 1fr;
                max-width: 520px;
            }

            .hero p { max-width: unset; }
        }
    </style>
</head>
<body>
    <?= $this->renderSection('content') ?>
</body>
</html>
