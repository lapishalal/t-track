<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>T-Track Analytics Report</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; margin: 0; background: #f3f4f6; }
        .page { max-width: 1100px; margin: 24px auto; background: white; padding: 28px; }
        .topbar { display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; border-bottom: 2px solid #e5e7eb; padding-bottom: 18px; }
        h1 { margin: 0; font-size: 24px; }
        h2 { margin: 28px 0 12px; font-size: 15px; text-transform: uppercase; color: #374151; letter-spacing: .04em; }
        .muted { color: #6b7280; font-size: 12px; }
        .print-button { border: 0; border-radius: 6px; background: #111827; color: white; padding: 10px 14px; font-weight: 700; cursor: pointer; }
        .kpi-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-top: 22px; }
        .kpi { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; }
        .kpi span { display: block; color: #6b7280; font-size: 10px; text-transform: uppercase; font-weight: 700; }
        .kpi strong { display: block; margin-top: 8px; font-size: 16px; }
        .target { border: 1px solid #bae6fd; background: #f0f9ff; border-radius: 8px; padding: 14px; margin-top: 18px; }
        .progress { height: 12px; background: #e5e7eb; border-radius: 999px; overflow: hidden; margin: 10px 0; }
        .progress div { height: 100%; background: #0284c7; }
        .bars { border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px; }
        .bar-row { display: grid; grid-template-columns: 220px 1fr 70px; gap: 10px; align-items: center; margin: 10px 0; font-size: 12px; }
        .bar-track { height: 14px; background: #f3f4f6; border-radius: 999px; overflow: hidden; }
        .bar-fill { height: 100%; background: #4f46e5; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th { text-align: left; background: #f9fafb; color: #374151; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; }
        th, td { padding: 9px; border-bottom: 1px solid #e5e7eb; }
        td.num, th.num { text-align: right; }
        @media print {
            body { background: white; }
            .page { margin: 0; max-width: none; padding: 0; }
            .no-print { display: none; }
            @page { size: A4 landscape; margin: 12mm; }
        }
    </style>
</head>
<body>
    <main class="page">
        <div class="topbar">
            <div>
                <h1>T-Track Analytics Report</h1>
                <p class="muted">Toko: {{ $selectedShop }} | Periode: {{ $periodLabel }} | Dibuat: {{ $generatedAt }}</p>
            </div>
            <button class="print-button no-print" onclick="window.print()">Print / Save PDF</button>
        </div>

        <section class="kpi-grid">
            <div class="kpi"><span>Omset Kotor</span><strong>Rp {{ number_format($omsetKotor, 0, ',', '.') }}</strong></div>
            <div class="kpi"><span>Dana Cair</span><strong>Rp {{ number_format($cairBersih, 0, ',', '.') }}</strong></div>
            <div class="kpi"><span>HPP & Overhead</span><strong>Rp {{ number_format($hppOverhead, 0, ',', '.') }}</strong></div>
            <div class="kpi"><span>Net Profit</span><strong>Rp {{ number_format($profitBersih, 0, ',', '.') }}</strong></div>
            <div class="kpi"><span>Order</span><strong>{{ number_format($orderCount, 0, ',', '.') }}</strong></div>
        </section>

        <section class="target">
            <strong>Target Bulanan - {{ $targetMonthLabel }}</strong>
            <div class="progress"><div style="width: {{ $targetProgress }}%"></div></div>
            <p class="muted">
                Progress {{ number_format($targetProgress, 1) }}% dari target Rp {{ number_format($monthlyTarget, 0, ',', '.') }}.
                Omset bulan target: Rp {{ number_format($monthlySales, 0, ',', '.') }}.
            </p>
        </section>

        <h2>Chart SKU Terlaris</h2>
        <section class="bars">
            @forelse($topSkuList as $sku)
                <div class="bar-row">
                    <div>{{ $sku['sku_id'] ?: 'N/A' }}</div>
                    <div class="bar-track"><div class="bar-fill" style="width: {{ ($sku['total_sold'] / $maxTopSkuSold) * 100 }}%"></div></div>
                    <div class="num">{{ number_format($sku['total_sold'], 0, ',', '.') }} pcs</div>
                </div>
            @empty
                <p class="muted">Tidak ada data SKU untuk periode ini.</p>
            @endforelse
        </section>

        <h2>Tabel Profit per SKU</h2>
        <table>
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Produk</th>
                    <th class="num">Terjual</th>
                    <th class="num">Omset</th>
                    <th class="num">Dana Cair</th>
                    <th class="num">Profit</th>
                    <th class="num">Margin</th>
                </tr>
            </thead>
            <tbody>
                @forelse($skuProfitList as $sku)
                    @php($margin = $sku['total_cair'] > 0 ? ($sku['total_profit'] / $sku['total_cair']) * 100 : 0)
                    <tr>
                        <td>{{ $sku['sku_id'] ?: 'N/A' }}</td>
                        <td>{{ $sku['product_name'] }}</td>
                        <td class="num">{{ number_format($sku['total_sold'], 0, ',', '.') }}</td>
                        <td class="num">Rp {{ number_format($sku['total_omset'], 0, ',', '.') }}</td>
                        <td class="num">Rp {{ number_format($sku['total_cair'], 0, ',', '.') }}</td>
                        <td class="num">Rp {{ number_format($sku['total_profit'], 0, ',', '.') }}</td>
                        <td class="num">{{ number_format($margin, 1) }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="7">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </main>
</body>
</html>
