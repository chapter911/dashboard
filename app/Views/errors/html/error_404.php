<!doctype html>
<html
    lang="en"
    class="light-style"
    dir="ltr"
    data-theme="theme-default"
    data-template="blank-template"
>
<head>
    <?php
    $appName = 'Dashboard PLN';
    $assetsBase = function_exists('base_url') ? rtrim(base_url('assets/'), '/') . '/' : '/assets/';
    $homeUrl = function_exists('base_url') ? base_url('/') : '/';
    $loginUrl = function_exists('base_url') ? base_url('login') : '/login';
    ?>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>404 | <?= esc($appName) ?></title>

    <link rel="stylesheet" href="<?= esc($assetsBase) ?>vendor/fonts/tabler-icons.css" />
    <link rel="stylesheet" href="<?= esc($assetsBase) ?>vendor/css/core.css" />
    <link rel="stylesheet" href="<?= esc($assetsBase) ?>vendor/css/theme-default.css" />

    <style>
        :root {
            --error-primary: #0a66c2;
            --error-primary-deep: #074c91;
            --error-primary-rgb: 10, 102, 194;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at 15% 20%, rgba(var(--error-primary-rgb), 0.18), transparent 42%),
                radial-gradient(circle at 80% 10%, rgba(var(--error-primary-rgb), 0.12), transparent 34%),
                linear-gradient(160deg, #f5f9ff 0%, #eef4ff 45%, #f8fbff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
        }

        .error-shell {
            width: 100%;
            max-width: 900px;
            position: relative;
        }

        .error-card {
            border: 0;
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 1.2rem 2.8rem rgba(13, 37, 76, 0.16);
            background: #fff;
        }

        .error-band {
            height: 10px;
            background: linear-gradient(90deg, var(--error-primary), var(--error-primary-deep));
        }

        .error-content {
            padding: 2.5rem 2rem;
        }

        .error-code {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--error-primary);
            background: rgba(var(--error-primary-rgb), 0.1);
            border-radius: 999px;
            padding: 0.45rem 0.8rem;
        }

        .error-number {
            font-size: clamp(3.2rem, 12vw, 6rem);
            line-height: 1;
            margin: 0.85rem 0 0.65rem;
            color: #1f2c4d;
            letter-spacing: -0.02em;
        }

        .error-title {
            color: #2d3b5f;
            font-size: clamp(1.3rem, 2.8vw, 2rem);
            margin-bottom: 0.8rem;
        }

        .error-message {
            color: #5f6c8a;
            max-width: 620px;
            margin: 0 auto 1.5rem;
            font-size: 1rem;
        }

        .error-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary-custom {
            background: linear-gradient(90deg, var(--error-primary), var(--error-primary-deep));
            color: #fff !important;
            border: 0;
            box-shadow: 0 0.45rem 1.2rem rgba(var(--error-primary-rgb), 0.35);
        }

        .btn-primary-custom:hover {
            color: #fff !important;
            transform: translateY(-1px);
        }

        .error-footnote {
            margin-top: 1.25rem;
            color: #7a86a6;
            font-size: 0.86rem;
        }

        @media (max-width: 575.98px) {
            .error-content {
                padding: 2rem 1.2rem;
            }

            .error-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="error-shell">
        <div class="card error-card text-center">
            <div class="error-band"></div>
            <div class="error-content">
                <span class="error-code">
                    <i class="ti ti-alert-circle"></i>
                    Page Not Found
                </span>

                <h1 class="error-number">404</h1>
                <h2 class="error-title"><?= lang('Errors.pageNotFound') ?></h2>

                <p class="error-message">
                    <?php if (ENVIRONMENT !== 'production') : ?>
                        <?= nl2br(esc($message)) ?>
                    <?php else : ?>
                        <?= lang('Errors.sorryCannotFind') ?>
                    <?php endif; ?>
                </p>

                <div class="error-actions">
                    <a href="<?= esc($homeUrl) ?>" class="btn btn-primary btn-primary-custom">
                        <i class="ti ti-home me-1"></i> Kembali Ke Beranda
                    </a>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.history.back();">
                        <i class="ti ti-arrow-left me-1"></i> Halaman Sebelumnya
                    </button>
                    <a href="<?= esc($loginUrl) ?>" class="btn btn-outline-primary">
                        <i class="ti ti-login me-1"></i> Login
                    </a>
                </div>

                <div class="error-footnote">
                    <?= esc($appName) ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
