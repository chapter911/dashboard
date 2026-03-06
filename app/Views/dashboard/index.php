<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard PLN</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f2f7fb;
            color: #123;
        }

        .container {
            max-width: 980px;
            margin: 28px auto;
            padding: 0 16px;
        }

        .card {
            background: #fff;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 10px 28px rgba(10, 30, 60, 0.1);
        }

        h1 {
            margin: 0 0 8px;
            font-size: 1.5rem;
        }

        p {
            margin: 6px 0;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            border: 0;
            background: #c62828;
            color: #fff;
            padding: 10px 14px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="topbar">
            <h1>Dashboard PLN</h1>
            <form action="<?= site_url('logout') ?>" method="post">
                <?= csrf_field() ?>
                <button class="btn" type="submit">Logout</button>
            </form>
        </div>

        <div class="card">
            <p><strong>Selamat datang, <?= esc($user['nama'] ?: $user['username']) ?></strong></p>
            <p>Username: <?= esc($user['username']) ?></p>
            <p>Group ID: <?= esc($user['group_id']) ?></p>
            <p>Unit ID: <?= esc($user['unit_id']) ?></p>
            <p>Halaman ini adalah placeholder dashboard setelah login berhasil.</p>
        </div>
    </div>
</body>
</html>
