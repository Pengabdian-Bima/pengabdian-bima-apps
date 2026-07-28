<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Per Produk - UD Flamboyan</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 10px;
        }
        h1 {
            color: #ff970f;
            font-size: 18px;
            text-align: center;
            margin-top: 0;
            margin-bottom: 4px;
        }
        p.subtitle {
            text-align: center;
            color: #666;
            font-size: 11px;
            margin-top: 0;
            margin-bottom: 15px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
            font-size: 10px;
        }
        table.data-table th {
            background-color: #ff970f;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        /* Kesimpulan Section using Table Layout to prevent DomPDF text overlap */
        .conclusion-box {
            margin-top: 15px;
            padding: 12px;
            border: 1px solid #fed7aa;
            background-color: #fff8f0;
            border-radius: 6px;
            page-break-inside: avoid;
        }
        .conclusion-title {
            font-size: 12px;
            font-weight: bold;
            color: #c2410c;
            margin-bottom: 8px;
            border-bottom: 1px solid #fed7aa;
            padding-bottom: 4px;
        }
        table.conclusion-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.conclusion-table td {
            padding: 4px 0;
            vertical-align: top;
            font-size: 10px;
            border: none;
        }
        .label-col {
            width: 30%;
            font-weight: bold;
            color: #333;
        }
        .value-col {
            width: 70%;
            color: #222;
        }
    </style>
</head>
<body>

    <h1>Laporan Per Produk - UD Flamboyan</h1>
    <p class="subtitle">Periode: {{ ucfirst($period) }} | Tanggal Cetak: {{ now()->format('d M Y H:i') }}</p>

    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 25px;">No</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th class="text-right">Harga Jual</th>
                <th class="text-center">Stok</th>
                <th class="text-center">Terjual</th>
                <th class="text-right">Total Omset</th>
                <th class="text-right">Laba Bersih</th>
                <th class="text-center">Margin</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandQty = 0;
                $grandRevenue = 0;
                $grandProfit = 0;
            @endphp
            @foreach($products as $i => $p)
                @php
                    $grandQty += $p['total_qty'];
                    $grandRevenue += $p['total_revenue'];
                    $grandProfit += $p['net_profit'];
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td><strong>{{ $p['name'] }}</strong></td>
                    <td>{{ $p['category'] }}</td>
                    <td class="text-right">Rp {{ number_format($p['price'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $p['stock'] }}</td>
                    <td class="text-center font-bold">{{ number_format($p['total_qty'], 0, ',', '.') }} pcs</td>
                    <td class="text-right">Rp {{ number_format($p['total_revenue'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold">Rp {{ number_format($p['net_profit'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ $p['margin'] }}%</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #fff8f0; font-weight: bold;">
                <td colspan="5" class="text-right">TOTAL:</td>
                <td class="text-center" style="color: #ff970f;">{{ number_format($grandQty, 0, ',', '.') }} pcs</td>
                <td class="text-right" style="color: #ff970f;">Rp {{ number_format($grandRevenue, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #10b981;">Rp {{ number_format($grandProfit, 0, ',', '.') }}</td>
                <td class="text-center">
                    {{ $grandRevenue > 0 ? round(($grandProfit / $grandRevenue) * 100, 1) : 0 }}%
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Kesimpulan Laporan Section -->
    <div class="conclusion-box">
        <div class="conclusion-title">Kesimpulan Laporan Per Produk</div>
        
        <table class="conclusion-table">
            <tr>
                <td class="label-col">1. Produk Terlaris (Volume)</td>
                <td class="value-col">: 
                    @if(isset($conclusion['top_seller']) && $conclusion['top_seller'])
                        {{ $conclusion['top_seller']['name'] }} ({{ number_format($conclusion['top_seller']['total_qty'], 0, ',', '.') }} pcs)
                    @else
                        Belum ada data penjualan
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label-col">2. Omset Terbesar</td>
                <td class="value-col">: 
                    @if(isset($conclusion['top_revenue']) && $conclusion['top_revenue'])
                        {{ $conclusion['top_revenue']['name'] }} (Rp {{ number_format($conclusion['top_revenue']['total_revenue'], 0, ',', '.') }})
                    @else
                        Belum ada data omset
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label-col">3. Laba Bersih Tertinggi</td>
                <td class="value-col">: 
                    @if(isset($conclusion['top_profit']) && $conclusion['top_profit'])
                        {{ $conclusion['top_profit']['name'] }} (Rp {{ number_format($conclusion['top_profit']['net_profit'], 0, ',', '.') }})
                    @else
                        Belum ada data laba
                    @endif
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
