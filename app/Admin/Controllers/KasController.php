<?php

namespace App\Admin\Controllers;

use App\Models\Kas;
use App\Models\Pendapatan;
use App\Models\Pengeluaran;
use App\Models\Gaji;
use Barryvdh\DomPDF\Facade\Pdf;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

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

            // === Ambil filter tanggal dari request ===
            $from = request()->get('from');
            $to   = request()->get('to');

            // --- ambil semua tanggal unik dari ke-4 tabel ---
            $tanggalList = collect()
                ->merge(Pendapatan::pluck('tanggal')->toArray())
                ->merge(Pengeluaran::pluck('tanggal')->toArray())
                ->merge(Gaji::pluck('tanggal')->toArray())
                ->merge(Kas::pluck('tanggal')->toArray())
                ->filter()
                ->map(fn($d) => date('Y-m-d', strtotime($d)))
                ->unique()
                ->sort()
                ->values();

            // === Saring berdasarkan filter (jika ada) ===
            if ($from) {
                $tanggalList = $tanggalList->filter(fn($tgl) => $tgl >= $from);
            }
            if ($to) {
                $tanggalList = $tanggalList->filter(fn($tgl) => $tgl <= $to);
            }

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

            // --- hitung saldo kumulatif ---
            $running = 0;
            foreach ($rows as $i => $r) {
                $running += ($r['total_pendapatan'] + $r['modal']) - ($r['total_pengeluaran'] + $r['total_gaji']);
                $rows[$i]['saldo_akhir'] = $running;
            }

            // --- balik urutan rows biar terbaru muncul di atas ---
            $rows = array_reverse($rows);

            // --- buat paginator manual ---
            $items = collect($rows);
            $total = $items->count();
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
            $grid->column('saldo_akhir', 'Saldo Akhir')
                ->display(fn($val) => 'Rp ' . number_format($val ?? 0, 0, ',', '.'));

            $grid->disableCreateButton(false);

            $grid->tools(function (Grid\Tools $tools) {
                $from = request()->get('from');
                $to   = request()->get('to');

                // tombol print PDF + ikutkan query string filter
                $tools->append('<a href="'.url('admin/kas/pdf').'?from='.$from.'&to='.$to.'" target="_blank" class="btn btn-primary">Print PDF</a>');

                // form filter tanggal sederhana
                $tools->append('
                    <form method="GET" style="margin-left:10px; display:inline-block;">
                        Dari: <input type="date" name="from" value="'.$from.'">
                        Sampai: <input type="date" name="to" value="'.$to.'">
                        <button type="submit" class="btn btn-sm btn-success">Filter</button>
                    </form>
                ');
            });
        });
    }


    public function exportPdf()
    {
        $from = request()->get('from');
        $to   = request()->get('to');

        // ambil semua tanggal unik (urut asc supaya running balance benar)
        $tanggalList = collect()
            ->merge(Pendapatan::pluck('tanggal')->toArray())
            ->merge(Pengeluaran::pluck('tanggal')->toArray())
            ->merge(Gaji::pluck('tanggal')->toArray())
            ->merge(Kas::pluck('tanggal')->toArray())
            ->filter()
            ->map(fn($d) => date('Y-m-d', strtotime($d)))
            ->unique()
            ->sort()
            ->values();

        if ($from) {
            $tanggalList = $tanggalList->filter(fn($tgl) => $tgl >= $from);
        }
        if ($to) {
            $tanggalList = $tanggalList->filter(fn($tgl) => $tgl <= $to);
        }

        $rows = [];
        $running = 0;
        foreach ($tanggalList as $tgl) {
            $modal       = (float) Kas::whereDate('tanggal', $tgl)->sum('jumlah');
            $pendapatan  = (float) Pendapatan::whereDate('tanggal', $tgl)->sum('jumlah');
            $pengeluaran = (float) Pengeluaran::whereDate('tanggal', $tgl)->sum('jumlah');
            $gaji        = (float) Gaji::whereDate('tanggal', $tgl)->sum('jumlah');

            $running += ($pendapatan + $modal) - ($pengeluaran + $gaji);

            $rows[] = [
                'tanggal'           => $tgl,
                'modal'             => $modal,
                'total_pendapatan'  => $pendapatan,
                'total_pengeluaran' => $pengeluaran,
                'total_gaji'        => $gaji,
                'saldo_akhir'       => $running,
            ];
        }

        $rows = collect($rows)->map(fn($r) => (object) $r);

        $pdf = Pdf::loadView('pdf.kas', [
                'data' => $rows,
                'from' => $from,
                'to'   => $to,
            ])
            ->setPaper('a4', 'landscape');

        return $pdf->stream('kas-report.pdf');
    }


    public function detail($id)
    {
        return Show::make($id, new Kas(), function (Show $show) {
            $show->field('id');
            $show->field('tanggal', 'Tanggal');
            $show->field('jumlah', 'Modal (Kas Manual)');

            $show->field('created_at');
            $show->field('updated_at');
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
