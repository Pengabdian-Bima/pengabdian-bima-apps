<!DOCTYPE html>
<html><head><meta charset="utf-8">
<style>body{font-family:sans-serif;font-size:12px}h1{color:#ff970f;font-size:18px;text-align:center}table{width:100%;border-collapse:collapse;margin-top:20px}th,td{border:1px solid #ddd;padding:8px;text-align:left}th{background:#ff970f;color:#fff}tr:nth-child(even){background:#f9f9f9}</style>
</head><body>
<h1>Laporan Produk Terlaris - UD Flamboyan</h1>
<p>Periode: {{ ucfirst($period) }} | Tanggal Cetak: {{ now()->format('d M Y H:i') }}</p>
<table><thead><tr><th>No</th><th>Nama Produk</th><th>Jumlah Terjual</th><th>Total Pendapatan</th></tr></thead>
<tbody>
@foreach($bestSelling as $i => $item)
<tr><td>{{ $i + 1 }}</td><td>{{ $item['product_name'] }}</td><td>{{ $item['total_qty'] }}</td><td>Rp {{ number_format($item['total_revenue'], 0, ',', '.') }}</td></tr>
@endforeach
</tbody></table>
</body></html>
