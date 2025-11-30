<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Get all winners with details
$stmt = $conn->query("
    SELECT w.*, m.name as member_name, m.phone, a.period_name, a.amount 
    FROM winners w 
    JOIN members m ON w.member_id = m.id 
    JOIN arisan_periods a ON w.arisan_id = a.id 
    ORDER BY w.draw_date DESC
");
$winners = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pemenang Arisan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #fafafa;
        }
        .footer {
            margin-top: 50px;
            text-align: right;
        }
        .footer p {
            margin-bottom: 50px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #333; color: #fff; border: none; cursor: pointer; border-radius: 5px;">
            Cetak Laporan
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #ccc; color: #333; border: none; cursor: pointer; border-radius: 5px; margin-left: 10px;">
            Tutup
        </button>
    </div>

    <div class="header">
        <h1>Laporan Pemenang Arisan</h1>
        <p>Dicetak pada: <?php echo date('d F Y H:i'); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 50px;">No</th>
                <th>Nama Pemenang</th>
                <th>Periode Arisan</th>
                <th>Jumlah</th>
                <th>Tanggal Undian</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($winners)): ?>
                <tr>
                    <td colspan="5" style="text-align: center;">Belum ada data pemenang</td>
                </tr>
            <?php else: ?>
                <?php foreach ($winners as $index => $winner): ?>
                    <tr>
                        <td style="text-align: center;"><?php echo $index + 1; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($winner['member_name']); ?></strong><br>
                            <small><?php echo htmlspecialchars($winner['phone']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($winner['period_name']); ?></td>
                        <td>Rp <?php echo number_format($winner['amount'], 0, ',', '.'); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($winner['draw_date'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Mengetahui,<br>Admin Arisan</p>
        <br><br>
        <p>____________________</p>
    </div>
</body>
</html>
