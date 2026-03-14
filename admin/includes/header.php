<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Panel'; ?> - ShopEase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="../uploads/favicon.ico">
    <link rel="shortcut icon" href="../uploads/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
            --primary-color: #0ea5e9;
            --secondary-bg: #f8fafc;
        }
        .sidebar { background-color: var(--sidebar-bg); min-height: 100vh; color: white; transition: all 0.3s; }
        .sidebar a { color: #94a3b8; text-decoration: none; display: block; padding: 12px 20px; border-radius: 8px; margin: 4px 10px; transition: 0.3s; }
        .sidebar a:hover { background-color: var(--sidebar-hover); color: white; }
        .sidebar a.active { background-color: var(--primary-color); color: white; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        body { background-color: var(--secondary-bg); font-family: 'Inter', sans-serif; }
        .card { border: none; border-radius: 12px; box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1); transition: transform 0.2s; }
        .card:hover { transform: translateY(-2px); }
        .stats-card h3 { font-weight: 700; }
        .welcome-header { display: flex; align-items: center; gap: 15px; }
        .welcome-logo { width: 40px; height: 40px; object-fit: contain; }
        .required::after { content: " *"; color: #dc3545; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
