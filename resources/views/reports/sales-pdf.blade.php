<!DOCTYPE html>
<html><head><meta charset="utf-8">
<style>body{font-family:sans-serif;font-size:12px}h1{color:#ff970f;font-size:18px;text-align:center}table{width:100%;border-collapse:collapse;margin-top:20px}th,td{border:1px solid #ddd;padding:8px;text-align:left}th{background:#ff970f;color:#fff}tr:nth-child(even){background:#f9f9f9}.total{text-align:right;font-weight:bold;margin-top:20px;font-size:14px}</style>
</head><body>
<h1>Laporan Penjualan - UD Flamboyan</h1>
<p>Periode: {{ ucfirst($period) }} | Tanggal Cetak: {{ now()->format('d M Y H:i') }}</p>
<table><thead><tr><th>No</th><th>Kode Pesanan</th><th>Pelanggan</th><th>Tanggal</th><th>Total</th></tr></thead>
<tbody>
@foreach($orders as $i => $order)
<tr><td>{{ $i + 1 }}</td><td>{{ $order->order_code }}</td><td>{{ $order->user->name }}</td><td>{{ $order->created_at->format('d/m/Y') }}</td><td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td></tr>
@endforeach
</tbody></table>
<p class="total">Total Penjualan: Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
</body></html>
