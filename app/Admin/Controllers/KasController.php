<?php

namespace App\Admin\Controllers;

use App\Models\CicilanHutang;
use App\Models\Kas;
use App\Models\Pendapatan;
use App\Models\Pengeluaran;
use App\Models\Gaji;
use App\Models\Hutang;
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

            $from = request()->get('from');
            $to   = request()->get('to');

            // ambil daftar tanggal
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
                $hutang = (float) Hutang::whereDate('tanggal_pinjam', $tgl)->sum('jumlah_hutang');
                $cicilan = (float) CicilanHutang::whereDate('tanggal_bayar', $tgl)->sum('nominal_cicilan');

                // update saldo
                $bankRunning += ($modal + $pendapatan) - ($pengeluaranBank + $gaji + $cashTarik);
                $cashRunning += ($cashTarik - $pengeluaranCash);

                $rows[] = [
                    'id'                => Kas::whereDate('tanggal', $tgl)->value('id'),
                    'tanggal'           => $tgl,
                    'modal'             => $modal,
                    'cash_tarik'        => $cashTarik,
                    'cash'              => $cashRunning,
                    'saldo_akhir'       => $bankRunning,
                    'keseluruhan'       => $bankRunning + $cashRunning,
                ];
            }

            $rows = array_reverse($rows);

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

            // grid utama
            $grid->column('tanggal', 'Tanggal')->sortable();
            $grid->column('modal', 'Setor')->display(fn($val) => 'Rp ' . number_format($val ?? 0, 0, ',', '.'));
            $grid->column('cash_tarik', 'Tarik Cash')->display(fn($val) => 'Rp ' . number_format($val ?? 0, 0, ',', '.'));
            $grid->column('cash', 'Saldo Cash')->display(fn($val) => 'Rp ' . number_format($val ?? 0, 0, ',', '.'));
            $grid->column('saldo_akhir', 'Saldo Bank')->display(fn($val) => 'Rp ' . number_format($val ?? 0, 0, ',', '.'));
            $grid->column('keseluruhan', 'Keseluruhan')->display(fn($val) => 'Rp ' . number_format($val ?? 0, 0, ',', '.'));

            $grid->column('detail', 'Detail')->expand(function () {
                $tgl = $this->tanggal;

                $pendapatan = (float) Pendapatan::whereDate('tanggal', $tgl)->sum('jumlah');
                $pengeluaran = (float) Pengeluaran::whereDate('tanggal', $tgl)->sum('jumlah');
                $gaji = (float) Gaji::whereDate('tanggal', $tgl)->sum('jumlah');
                $hutang = (float) Hutang::whereDate('tanggal_pinjam', $tgl)->sum('jumlah_hutang');
                $cicilan = (float) CicilanHutang::whereDate('tanggal_bayar', $tgl)->sum('nominal_cicilan');

                $html = "
                    <table class='table table-bordered' style='margin:0;'>
                        <tr><th width='40%'>Total Pendapatan</th><td>Rp " . number_format($pendapatan, 0, ',', '.') . "</td></tr>
                        <tr><th>Total Pengeluaran</th><td>Rp " . number_format($pengeluaran, 0, ',', '.') . "</td></tr>
                        <tr><th>Total Gaji</th><td>Rp " . number_format($gaji, 0, ',', '.') . "</td></tr>
                        <tr><th>Total Hutang</th><td>Rp " . number_format($hutang, 0, ',', '.') . "</td></tr>
                        <tr><th>Total Cicilan Hutang</th><td>Rp " . number_format($cicilan, 0, ',', '.') . "</td></tr>
                    </table>
                ";

                return $html;
            });

            // menghilangkan tombol
            $grid->disableCreateButton(false);
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableView();
                $actions->disableDelete();
            });

            // filter tanggal dan pdf
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
        ->merge(Hutang::pluck('tanggal_pinjam')->toArray())
        ->merge(CicilanHutang::pluck('tanggal_bayar')->toArray())
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

        $hutang = (float) Hutang::whereDate('tanggal_pinjam', $tgl)->sum('jumlah_hutang');
        $cicilanHutang = (float) CicilanHutang::whereDate('tanggal_bayar', $tgl)->sum('nominal_cicilan');

        $bankRunning += ($modal + $pendapatan) - ($pengeluaranBank + $gaji + $cashTarik);
        $cashRunning += ($cashTarik - $pengeluaranCash);

        $rows[] = [
            'tanggal'            => $tgl,
            'modal'              => $modal,
            'cash_tarik'         => $cashTarik,
            'cash'               => $cashRunning,
            'total_pendapatan'   => $pendapatan,
            'total_pengeluaran'  => $pengeluaranBank + $pengeluaranCash,
            'total_gaji'         => $gaji,
            'total_hutang'       => $hutang,
            'total_cicilan'      => $cicilanHutang,
            'saldo_akhir'        => $bankRunning,
            'keseluruhan'        => $bankRunning + $cashRunning,
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
