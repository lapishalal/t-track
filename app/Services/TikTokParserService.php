<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Income;
use App\Models\ProductCost;
use App\Models\UploadLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Illuminate\Support\Str; // TAMBAHAN: Diperlukan untuk membuat token unik batch_id

class TikTokParserService
{
    /**
     * Parsing File Pesanan (.csv)
     */
    public function parseOrderCsv($filePath, $shopName, $originalFileName)
    {
        $shopName = trim((string) $shopName);
        if ($shopName === '') {
            throw new \Exception('Nama toko wajib diisi sebelum mengunggah file pesanan.');
        }

        $file = fopen($filePath, 'r');
        $header = fgetcsv($file); // Membaca baris pertama sebagai header

        // PERBAIKAN: Hapus karakter BOM (\ufeff) jika terdeteksi di kolom pertama
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }

        // Validasi kolom krusial untuk memastikan format benar-benar dari TikTok Pesanan
        if (!in_array('Order ID', $header) || !in_array('Order Status', $header) || !in_array('SKU ID', $header)) {
            fclose($file);
            throw new \Exception('Format file Semua Pesanan tidak sesuai standar TikTok Shop.');
        }

        $rowCount = 0;
        
        // TAMBAHAN: Buat ID unik khusus untuk file yang sedang di-upload saat ini
        $batchId = (string) Str::uuid();

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($file)) !== false) {
                // Lewati jika baris kosong atau jumlah kolom tidak sinkron dengan header
                if (count($header) !== count($row)) {
                    continue;
                }

                $data = array_combine($header, $row);

                // 1. Daftarkan / perbarui Kamus HPP Produk secara otomatis jika ada SKU ID baru
                ProductCost::updateOrCreate(
                    ['sku_id' => $data['SKU ID']],
                    [
                        'shop_name'    => $shopName,
                        'product_name' => $data['Product Name'],
                        'variation'    => $data['Variation'] ?? null,
                    ]
                );

                // 2. Simpan atau perbarui data operasional pesanan
                Order::updateOrCreate(
                    ['order_id' => trim($data['Order ID'])],
                    [
                        'batch_id'               => $batchId, // TAMBAHAN: Ikat data pesanan ini ke file induknya
                        'sku_id'                 => $data['SKU ID'],
                        'shop_name'              => $shopName,
                        'order_status'           => $data['Order Status'],
                        'product_name'           => $data['Product Name'],
                        'variation'              => $data['Variation'] ?? null,
                        'quantity'               => (int) $data['Quantity'],
                        'order_amount'           => (float) $data['Order Amount'],
                        'shipping_fee_estimated' => (float) ($data['Shipping Fee After Discount'] ?? 0),
                        'buyer_username'         => $data['Buyer Username'] ?? null,
                        'province'               => $data['Province'] ?? null,
                        'regency_city'           => $data['Regency and City'] ?? null,
                        'tracking_id'            => $data['Tracking ID'] ?? null,
                        
                        // PERBAIKAN: Bersihkan spasi/tab tak terlihat dan atur format d/m/Y H:i:s
                        'created_time'           => $data['Created Time'] ? Carbon::createFromFormat('d/m/Y H:i:s', trim($data['Created Time'])) : null,
                    ]
                );

                $rowCount++;
            }

            // 3. Catat riwayat unggahan ke log
            UploadLog::create([
                'batch_id'            => $batchId, // TAMBAHAN: Simpan id pengelompokan ke dalam log riwayat berkas
                'file_name'           => $originalFileName,
                'file_type'           => 'order',
                'shop_name'           => $shopName,
                'total_rows_imported' => $rowCount,
                'uploaded_by'         => Auth::id(),
            ]);

            DB::commit();
            fclose($file);
            return $rowCount;

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($file);
            throw $e;
        }
    }

    /**
     * Parsing File Income (.xlsx) - Membaca Sheet 'Detail pesanan'
     */
    public function parseIncomeXlsx($filePath, $shopName, $originalFileName)
    {
        $shopName = trim((string) $shopName);
        if ($shopName === '') {
            throw new \Exception('Nama toko wajib diisi sebelum mengunggah file income.');
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('Detail pesanan');

        if (!$sheet) {
            throw new \Exception("Sheet 'Detail pesanan' tidak ditemukan pada file Income.");
        }

        $rows = $sheet->toArray();
        $header = array_shift($rows); // Mengambil baris pertama sebagai header

        // Validasi kolom krusial untuk memastikan format benar-benar dari TikTok Income
        if (!in_array('ID Pesanan/Penyesuaian', $header) || !in_array('Jumlah penyelesaian pembayaran', $header)) {
            throw new \Exception('Format file Income tidak sesuai standar ekspor TikTok Shop.');
        }

        $rowCount = 0;

        // TAMBAHAN: Buat ID unik khusus untuk file keuangan yang sedang di-upload saat ini
        $batchId = (string) Str::uuid();

        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                // Lewati jika baris kosong atau ID Pesanan kosong
                if (empty($row) || !isset($row[0])) {
                    continue;
                }

                $data = array_combine($header, $row);

                // Simpan atau perbarui data riwayat keuangan transaksi
                Income::create([
                    'batch_id'                => $batchId, // TAMBAHAN: Ikat data keuangan ini ke file induknya
                    'order_id'                => $data['ID Pesanan/Penyesuaian'],
                    'shop_name'               => $shopName,
                    'transaction_type'        => $data['Jenis transaksi'],
                    'disbursement_amount'     => (float) ($data['Jumlah penyelesaian pembayaran'] ?? 0),
                    'total_revenue'           => (float) ($data['Total Pendapatan'] ?? 0),
                    'platform_commission_fee' => (float) ($data['Biaya komisi platform'] ?? 0),
                    'payment_fee'             => (float) ($data['Biaya Pembayaran'] ?? 0),
                    'affiliate_commission'    => (float) ($data['Komisi Afiliasi'] ?? 0),
                    'shipping_fee_real'       => (float) ($data['Ongkir'] ?? 0),
                    'payout_time'             => $data['Waktu pembayaran pesanan'] ? Carbon::parse($data['Waktu pembayaran pesanan']) : null,
                ]);

                $rowCount++;
            }

            // Catat riwayat unggahan ke log
            UploadLog::create([
                'batch_id'            => $batchId, // TAMBAHAN: Simpan id pengelompokan ke dalam log riwayat berkas
                'file_name'           => $originalFileName,
                'file_type'           => 'income',
                'shop_name'           => $shopName,
                'total_rows_imported' => $rowCount,
                'uploaded_by'         => Auth::id(),
            ]);

            DB::commit();
            return $rowCount;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
