<?php

namespace App\Admin\Controllers;


use App\Models\Project;
use App\Models\Kas;
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
            $grid->column('keterangan', 'Keterangan');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id_project', 'Project')
                    ->select(Project::pluck('nama_project', 'id')->toArray());
                $filter->between('tanggal', 'Tanggal')->date();
            });

            $grid->paginate(10);
        });
    }

    /**
     * Detail view untuk Kas
     */
    protected function detail($id)
    {
        return Show::make($id, new Kas(), function (Show $show) {
            $show->field('id', 'ID');
            $show->field('project.nama_project', 'Project');
            $show->field('jumlah', 'Jumlah')
                ->as(fn($v) => $v !== null ? 'Rp ' . number_format((float) $v, 0, ',', '.') : '-');
            $show->field('saldo_akhir', 'Saldo Akhir')
                ->as(fn($v) => $v !== null ? 'Rp ' . number_format((float) $v, 0, ',', '.') : '-');
            $show->field('tanggal', 'Tanggal');
            $show->field('keterangan', 'Keterangan');
            $show->field('created_at', 'Dibuat');
            $show->field('updated_at', 'Diubah');
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

            $form->display('saldo_akhir', 'Saldo Akhir (otomatis)');

            $form->saving(function (Form $form) {
                if (!$form->saldo_akhir) {
                    $form->input('saldo_akhir', 0);
                }
            });

            // Hitung ulang saldo setelah simpan
            $form->saved(function (Form $form, $result) {
                $projectId = $form->id_project;

                // Ambil semua transaksi project ini urut dari paling awal
                $kasList = Kas::where('id_project', $projectId)
                            ->orderBy('tanggal')
                            ->orderBy('id')
                            ->get();

                $saldo = 0;
                foreach ($kasList as $kas) {
                    $saldo += $kas->jumlah;   // tambahkan jumlah transaksi
                    $kas->saldo_akhir = $saldo;
                    $kas->save();
                }
            });


            $form->display('created_at', 'Dibuat');
            $form->display('updated_at', 'Diubah');
        });
    }
}
