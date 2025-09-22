<?php

namespace App\Admin\Controllers;

use App\Models\Kas;
use App\Models\Pendapatan;
use App\Models\Pengeluaran;
use App\Models\Gaji;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Dcat\Admin\Http\Controllers\AdminController;

class KasController extends AdminController
{
    /**
     * Grid untuk data Kas (rekap + manual kas)
     */
    protected function grid()
    {
        return Grid::make(new Kas(), function (Grid $grid) {
            $perPage = 10;
            $page = (int) request()->get('page', 1);

            // --- ambil semua tanggal unik dari ke-4 tabel ---
            $tanggalList = collect()
                ->merge(Pendapatan::pluck('tanggal')->toArray())
                ->merge(Pengeluaran::pluck('tanggal')->toArray())
                ->merge(Gaji::pluck('tanggal')->toArray())
                ->merge(Kas::pluck('tanggal')->toArray())
                ->filter() // hilangkan null/empty
                ->map(fn($d) => date('Y-m-d', strtotime($d))) // normalisasi format
                ->unique()
                ->sort() // urutkan naik (sesuaikan jika mau desc)
                ->values();

            // --- bangun rows rekap per tanggal ---
            $rows = [];
            foreach ($tanggalList as $tgl) {
                $modal       = (float) Kas::whereDate('tanggal', $tgl)->sum('jumlah');
                $pendapatan  = (float) Pendapatan::whereDate('tanggal', $tgl)->sum('jumlah');
                $pengeluaran = (float) Pengeluaran::whereDate('tanggal', $tgl)->sum('jumlah');
                $gaji        = (float) Gaji::whereDate('tanggal', $tgl)->sum('jumlah');

                $rows[] = [
                    'tanggal'           => $tgl,
                    'modal'             => $modal,
                    'total_pendapatan'  => $pendapatan,
                    'total_pengeluaran' => $pengeluaran,
                    'total_gaji'        => $gaji,
                ];
            }

            // --- (opsional) hitung running balance kumulatif ---
            $running = 0;
            foreach ($rows as $i => $r) {
                $running += ($r['total_pendapatan'] + $r['modal']) - ($r['total_pengeluaran'] + $r['total_gaji']);
                $rows[$i]['saldo_akhir'] = $running;
            }

            // --- buat paginator manual dari array rows ---
            $items = collect($rows);
            $total = $items->count();

            // jika kosong, buat paginator kosong agar grid tidak error
            $slice = $items->slice(($page - 1) * $perPage, $perPage)->values()->all();
            $paginator = new LengthAwarePaginator(
                $slice,
                $total,
                $perPage,
                $page,
                [
                    'path' => Paginator::resolveCurrentPath(),
                    'query' => request()->query(),
                ]
            );

            // set data ke grid (supply paginator sehingga firstItem() tidak null)
            $grid->model()->setData($paginator);

            // --- kolom grid ---
            $grid->column('tanggal', 'Tanggal')->sortable();

            $grid->column('modal', 'Modal (Kas Manual)')
                ->display(fn($val) => 'Rp ' . number_format($val ?? 0, 0, ',', '.'));

            $grid->column('total_pendapatan', 'Total Pendapatan')
                ->display(fn($val) => 'Rp ' . number_format($val ?? 0, 0, ',', '.'));

            $grid->column('total_pengeluaran', 'Total Pengeluaran')
                ->display(fn($val) => 'Rp ' . number_format($val ?? 0, 0, ',', '.'));

            $grid->column('total_gaji', 'Total Gaji')
                ->display(fn($val) => 'Rp ' . number_format($val ?? 0, 0, ',', '.'));

            // saldo akhir kumulatif yg kita masukkan sebelumnya
            $grid->column('saldo_akhir', 'Saldo Akhir')
                ->display(fn($val) => 'Rp ' . number_format($val ?? 0, 0, ',', '.'));

            // jangan panggil $grid->paginate(...) karena kita sudah handle pagination sendiri
            $grid->disableCreateButton(false); // tetep perbolehkan tombol +New jika mau
        });
    }



    /**
     * Form untuk tambah/edit Kas manual
     */
    protected function form()
    {
        return Form::make(new Kas(), function (Form $form) {
            $form->display('id', 'ID');

            $form->currency('jumlah', 'Kas Manual (Modal/Pinjaman)')
                ->symbol('Rp')
                ->required();

            $form->date('tanggal', 'Tanggal')
                ->default(now())
                ->required();

            $form->textarea('keterangan', 'Keterangan');

            $form->display('saldo_akhir', 'Saldo Akhir');

            // update saldo_akhir setelah save
            $form->saved(function (Form $form, $result) {
                $totalPendapatan  = Pendapatan::sum('jumlah');
                $totalPengeluaran = Pengeluaran::sum('jumlah');
                $totalGaji        = Gaji::sum('jumlah');
                $totalKasManual   = Kas::sum('jumlah');

                $saldo = ($totalPendapatan + $totalKasManual) - ($totalPengeluaran + $totalGaji);

                $form->model()->update(['saldo_akhir' => $saldo]);
            });

            $form->display('created_at', 'Dibuat');
            $form->display('updated_at', 'Diubah');
        });
    }
}
