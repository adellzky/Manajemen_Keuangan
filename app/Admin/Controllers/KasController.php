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

            // filter tanggal dari request
            $from = request()->get('from');
            $to   = request()->get('to');

            // ambil semua tanggal unik dari ke-4 tabel (format Y-m-d)
            $tanggalList = collect()
                ->merge(Pendapatan::pluck('tanggal')->toArray())
                ->merge(Pengeluaran::pluck('tanggal')->toArray())
                ->merge(Gaji::pluck('tanggal')->toArray())
                ->merge(Kas::pluck('tanggal')->toArray())
                ->filter()
                ->map(fn($d) => date('Y-m-d', strtotime($d)))
                ->unique()
                ->sort() // penting: urut ASC supaya running balance benar
                ->values();

            if ($from) {
                $tanggalList = $tanggalList->filter(fn($tgl) => $tgl >= $from);
            }
            if ($to) {
                $tanggalList = $tanggalList->filter(fn($tgl) => $tgl <= $to);
            }

            $rows = [];
            $bankRunning = 0; // saldo bank kumulatif
            $cashRunning = 0; // cash kumulatif (total cash ditarik sampai hari tertentu)

            foreach ($tanggalList as $tgl) {
                $modal       = (float) Kas::whereDate('tanggal', $tgl)->sum('jumlah');
                $cashTarik   = (float) Kas::whereDate('tanggal', $tgl)->sum('cash');
                $pendapatan  = (float) Pendapatan::whereDate('tanggal', $tgl)->sum('jumlah');

                $pengeluaranBank = (float) Pengeluaran::whereDate('tanggal', $tgl)
                    ->where('sumber_dana', 'bank')
                    ->sum('jumlah');

                $pengeluaranCash = (float) Pengeluaran::whereDate('tanggal', $tgl)
                    ->where('sumber_dana', 'cash')
                    ->sum('jumlah');

                $gaji = (float) Gaji::whereDate('tanggal', $tgl)->sum('jumlah');

                // 🔹 Bank
                $bankRunning += ($modal + $pendapatan) - ($pengeluaranBank + $gaji + $cashTarik);

                // 🔹 Cash realtime (bertambah dari tarik, berkurang dari pengeluaran cash)
                $cashRunning += ($cashTarik - $pengeluaranCash);

                // 🔹 Simpan row
                $rows[] = [
                    'id'                => Kas::whereDate('tanggal', $tgl)->value('id'),
                    'tanggal'           => $tgl,
                    'modal'             => $modal,
                    'cash_tarik'        => $cashTarik,     // cash ditarik hari itu
                    'cash'              => $cashRunning,   // saldo cash realtime
                    'total_pendapatan'  => $pendapatan,
                    'total_pengeluaran' => $pengeluaranBank + $pengeluaranCash,
                    'total_gaji'        => $gaji,
                    'saldo_akhir'       => $bankRunning,
                    'keseluruhan'       => $bankRunning + $cashRunning,
                ];
            }

            // tampilkan terbaru dulu
            $rows = array_reverse($rows);

            // paginator manual tetap sama
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

            // kolom tetap sama
            // $grid->column('id', 'ID')->hide();
            $grid->column('tanggal', 'Tanggal')->sortable();
            $grid->column('modal', 'Setor')
                ->display(fn($val) => 'Rp ' . number_format($val ?? 0, 0, ',', '.'));
            $grid->column('cash_tarik', 'Tarik Cash')
                ->display(fn($val) => 'Rp ' . number_format($val ?? 0, 0, ',', '.'));

            $grid->column('cash', 'Saldo Cash')
                ->display(fn($val) => 'Rp ' . number_format($val ?? 0, 0, ',', '.'));

            $grid->column('saldo_akhir', 'Saldo Bank')
                ->display(fn($val) => 'Rp ' . number_format($val ?? 0, 0, ',', '.'));
            // $grid->column('total_pendapatan', 'Pendapatan')
            //     ->display(fn($val) => 'Rp ' . number_format($val ?? 0, 0, ',', '.'));
            // $grid->column('total_pengeluaran', 'Pengeluaran')
            //     ->display(fn($val) => 'Rp ' . number_format($val ?? 0, 0, ',', '.'));
            // $grid->column('total_gaji', 'Total Gaji')
            //     ->display(fn($val) => 'Rp ' . number_format($val ?? 0, 0, ',', '.'));
            $grid->column('keseluruhan', 'Keseluruhan')
                ->display(fn($val) => 'Rp ' . number_format($val ?? 0, 0, ',', '.'));

            $grid->disableCreateButton(false);
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableView();
                $actions->disableDelete();
                // biarkan edit
            });


            $grid->tools(function (Grid\Tools $tools) {
                $from = request()->get('from');
                $to   = request()->get('to');

                $tools->append('<a href="'.url('admin/keuangan/pdf').'?from='.$from.'&to='.$to.'" target="_blank" class="btn btn-primary">Print PDF</a>');

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
        $bankRunning = 0;
        $cashRunning = 0;

        foreach ($tanggalList as $tgl) {
            $modal       = (float) Kas::whereDate('tanggal', $tgl)->sum('jumlah');
            $cashTarik   = (float) Kas::whereDate('tanggal', $tgl)->sum('cash');
            $pendapatan  = (float) Pendapatan::whereDate('tanggal', $tgl)->sum('jumlah');

            $pengeluaranBank = (float) Pengeluaran::whereDate('tanggal', $tgl)
                ->where('sumber_dana', 'bank')
                ->sum('jumlah');

            $pengeluaranCash = (float) Pengeluaran::whereDate('tanggal', $tgl)
                ->where('sumber_dana', 'cash')
                ->sum('jumlah');

            $gaji = (float) Gaji::whereDate('tanggal', $tgl)->sum('jumlah');

            // 🔹 Sama seperti grid
            $bankRunning += ($modal + $pendapatan) - ($pengeluaranBank + $gaji + $cashTarik);
            $cashRunning += ($cashTarik - $pengeluaranCash);

            $rows[] = [
                'tanggal'           => $tgl,
                'modal'             => $modal,
                'cash_tarik'        => $cashTarik,
                'cash'              => $cashRunning,
                'total_pendapatan'  => $pendapatan,
                'total_pengeluaran' => $pengeluaranBank + $pengeluaranCash,
                'total_gaji'        => $gaji,
                'saldo_akhir'       => $bankRunning,
                'keseluruhan'       => $bankRunning + $cashRunning,
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
            ->default(0);

        $form->currency('cash', 'Cash (Uang Ditarik)')
            ->symbol('Rp')
            ->default(0);

        $form->date('tanggal', 'Tanggal')
            ->default(now())
            ->required();

        $form->textarea('keterangan', 'Keterangan');

        // ❌ jangan simpan saldo ke tabel, cukup tampilkan readonly info
        $form->display('saldo_info', 'Saldo (auto)')
            ->with(function () {
                $totalPendapatan       = \App\Models\Pendapatan::sum('jumlah');
                $totalPengeluaranBank  = \App\Models\Pengeluaran::where('sumber_dana', 'bank')->sum('jumlah');
        $totalPengeluaranCash  = \App\Models\Pengeluaran::where('sumber_dana', 'cash')->sum('jumlah');
                $totalGaji             = \App\Models\Gaji::sum('jumlah');
                $totalKasManual        = \App\Models\Kas::sum('jumlah');
                $totalCashTarik        = \App\Models\Kas::sum('cash');

                $saldoBank = ($totalPendapatan + $totalKasManual) - ($totalPengeluaranBank + $totalGaji + $totalCashTarik);
                $saldoCash = $totalCashTarik - $totalPengeluaranCash;
                $keseluruhan = $saldoBank + $saldoCash;

                return 'Bank: Rp '.number_format($saldoBank,0,',','.')
                     . ' | Cash: Rp '.number_format($saldoCash,0,',','.')
                     . ' | Total: Rp '.number_format($keseluruhan,0,',','.');
            });

        $form->display('created_at', 'Dibuat');
        $form->display('updated_at', 'Diubah');
    });
}

}
