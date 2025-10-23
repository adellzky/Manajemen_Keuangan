<?php

namespace App\Admin\Controllers;

use App\Models\Gaji;
use App\Models\Project;
use App\Models\Tim;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Barryvdh\DomPDF\Facade\Pdf;
use Dcat\Admin\Http\JsonResponse;
use Illuminate\Http\Response;
use Dcat\Admin\Layout\Content;

class TimController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
{
    return Grid::make(new Tim(), function (Grid $grid) {
        $grid->column('nama');
        $grid->column('no_telp');
        $grid->column('atm');
        $grid->column('norek');

        $grid->column('gaji', 'Gaji')->display(function ($val) {
            return 'Rp ' . number_format($val ?? 0, 0, ',', '.');
        });

        //  Kolom Total Hutang (dari relasi Hutang)
        $grid->column('total_hutang', 'Hutang')
            ->display(function () {
                $total = $this->hutang()
                    ->where('status', 'Belum Lunas')
                    ->sum('sisa_hutang');

                return 'Rp ' . number_format($total ?? 0, 0, ',', '.');
            })
            ->expand(function () {
                $hutangList = $this->hutang()
                    ->select('tanggal_pinjam', 'jumlah_hutang', 'sisa_hutang', 'status')
                    ->orderBy('tanggal_pinjam', 'desc')
                    ->get();

                if ($hutangList->isEmpty()) {
                    return "<p style='padding:8px'>Tidak ada data hutang.</p>";
                }

                $html = "<table class='table table-sm'>
                            <thead>
                                <tr>
                                    <th>Tanggal Pinjam</th>
                                    <th>Jumlah Hutang</th>
                                    <th>Sisa Hutang</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>";

                foreach ($hutangList as $item) {
                    $html .= "<tr>
                                <td>" . \Carbon\Carbon::parse($item->tanggal_pinjam)->translatedFormat('d F Y') . "</td>
                                <td>Rp " . number_format($item->jumlah_hutang, 0, ',', '.') . "</td>
                                <td>Rp " . number_format($item->sisa_hutang, 0, ',', '.') . "</td>
                                <td>" . $item->status . "</td>
                              </tr>";
                }

                $html .= "</tbody></table>";

                return $html;
            });

        $grid->column('total_potongan_cicilan', 'Cicilan')
            ->display(function ($value) {
                return 'Rp ' . number_format($value ?? 0, 0, ',', '.');
            })
            ->expand(function () {
                $cicilan = $this->cicilanHutang()
                    ->select('tanggal_bayar', 'nominal_cicilan')
                    ->orderBy('tanggal_bayar', 'desc')
                    ->get();

                if ($cicilan->isEmpty()) {
                    return "<p style='padding:8px'>Tidak ada data cicilan.</p>";
                }

                $html = "<table class='table table-sm'>
                            <thead>
                                <tr>
                                    <th>Tanggal Bayar</th>
                                    <th>Nominal Cicilan</th>
                                </tr>
                            </thead>
                            <tbody>";

                foreach ($cicilan as $item) {
                    $html .= "<tr>
                                <td>" . \Carbon\Carbon::parse($item->tanggal_bayar)->translatedFormat('d F Y') . "</td>
                                <td>Rp " . number_format($item->nominal_cicilan, 0, ',', '.') . "</td>
                              </tr>";
                }

                $html .= "</tbody></table>";

                return $html;
            });

        // Tombol aksi tambahan
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $id = $actions->getKey();

            // Slip gaji
            $actions->append("
                <form method='GET' action='" . url("admin/tim/$id/slip") . "' target='_blank' style='display:inline'>
                    <select name='bulan' style='padding:2px'>
                        " . collect(range(1, 12))->map(function ($m) {
                            return "<option value='$m'>" . \Carbon\Carbon::create()->month($m)->translatedFormat('F') . "</option>";
                        })->implode('') . "
                    </select>
                    <select name='tahun' style='padding:2px'>
                        " . collect(range(2025, now()->year + 5))->map(function ($y) {
                            return "<option value='$y'>$y</option>";
                        })->implode('') . "
                    </select>
                    <button type='submit' class='btn btn-sm btn-primary'>
                        <i class='feather icon-file-text'></i> Slip Gaji
                    </button>
                </form>
            ");

            // Tombol ambil gaji
            $actions->append("<a href='" . url("admin/tim/$id/ambil-gaji") . "' class='btn btn-sm btn-warning'>
                <i class='feather icon-credit-card'></i> Ambil Gaji
            </a>");
        });

        //  Filter
        $grid->filter(function (Grid\Filter $filter) {
            $filter->panel()->expand(false);
            $filter->equal('id', 'Nama Karyawan')->select(Tim::pluck('nama', 'id'));
            $filter->between('gaji', 'Range Gaji');
        });
    });
}


    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new Tim(), function (Show $show) {
            $show->field('nama');
            $show->field('no_telp');
            $show->field('atm');
            $show->field('norek');
            $show->field('gaji')->as(function ($val) {
                return 'Rp ' . number_format($val, 0, ',', '.');
            });
            $show->field('created_at');
            $show->field('updated_at');
            // ✅ Rincian Gaji Per Project
            $show->field('rincian_gaji', '📌 Rincian Gaji Per Project')->unescape()->as(function ($val) use ($show) {
                $grid = new Grid(new Gaji());

                $grid->model()
                    ->where('id_tim', $show->getKey())
                    ->whereNotNull('id_project')
                    ->with('project');

                $grid->column('tanggal', 'Tanggal')->display(
                    fn($v) =>
                    $v ? \Carbon\Carbon::parse($v)->translatedFormat('d F Y') : '-'
                );
                $grid->column('project.nama_project', 'Project');
                $grid->column('jumlah', 'Jumlah')->display(
                    fn($val) =>
                    'Rp ' . number_format($val, 0, ',', '.')
                );

                $grid->disableCreateButton();
                $grid->disableRowSelector();
                $grid->disableActions();
                $grid->disablePagination();

                return $grid->render();
            });

            // ✅ Riwayat Ambil Gaji
            $show->field('riwayat_gaji', '📌 Riwayat Ambil Gaji')->unescape()->as(function ($val) use ($show) {
                $grid = new Grid(new Gaji());

                $grid->model()
                    ->where('id_tim', $show->getKey())
                    ->whereNull('id_project');

                $grid->column('tanggal', 'Tanggal')->display(
                    fn($v) =>
                    $v ? \Carbon\Carbon::parse($v)->translatedFormat('d F Y') : '-'
                );
                $grid->column('metode_bayar', 'Metode');
                $grid->column('jumlah', 'Nominal Ambil')->display(
                    fn($val) =>
                    'Rp ' . number_format($val, 0, ',', '.')
                );

                $grid->disableCreateButton();
                $grid->disableRowSelector();
                $grid->disableActions();
                $grid->disablePagination();

                return $grid->render();
            });
        });
    }


    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Tim(), function (Form $form) {
            $form->display('id');
            $form->text('nama')->required();
            $form->text('no_telp')->required();
            $form->text('atm')->required();
            $form->text('norek')->required();
            // $form->currency('gaji', 'Gaji')
            //     ->symbol('Rp');


            $form->display('created_at');
            $form->display('updated_at');
        });
    }
    public function slip($id)
{
    $tim = \App\Models\Tim::with(['gajis.project'])->findOrFail($id);

    $bulan = request('bulan', now()->month);
    $tahun = request('tahun', now()->year);

    // ✅ Ambil gaji (pendapatan)
    $gajis = $tim->gajis()
        ->with('project')
        ->whereNotNull('id_project')
        ->whereMonth('tanggal', $bulan)
        ->whereYear('tanggal', $tahun)
        ->get();

    // ✅ Ambil potongan (cicilan hutang)
    $potongans = \App\Models\CicilanHutang::whereHas('hutang', function ($q) use ($tim) {
        $q->where('id_tim', $tim->id);
    })
        ->whereMonth('tanggal_bayar', $bulan)
        ->whereYear('tanggal_bayar', $tahun)
        ->get();

    // Hitung total
    $totalPendapatan = $gajis->sum('jumlah');
    $totalPotongan = $potongans->sum('nominal_cicilan');
    $gajiBersih = $totalPendapatan - $totalPotongan;

    // Kirim semua data ke PDF
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.slip-gaji', compact(
        'tim',
        'gajis',
        'potongans',
        'bulan',
        'tahun',
        'totalPendapatan',
        'totalPotongan',
        'gajiBersih'
    ))->setPaper('A4', 'portrait');

    return $pdf->stream("slip-gaji-{$tim->nama}-{$bulan}-{$tahun}.pdf");
}



    public function ambilGaji($id)
    {
        $tim = \App\Models\Tim::findOrFail($id);

        return Content::make(function (Content $content) use ($tim) {
            $content->header("Ambil Gaji - {$tim->nama}");
            $content->description('Form pengambilan gaji');

            $content->body(
                Form::make(new \App\Models\Gaji(), function (Form $form) use ($tim) {
                    $form->hidden('id_tim')->value($tim->id);
                    $form->display('nama_karyawan', 'Nama Karyawan')->default($tim->nama);
                    $form->display('total_gaji', 'Total Gaji Tersedia')->with(function () use ($tim) {
                        return 'Rp ' . number_format($tim->gaji, 0, ',', '.');
                    });

                    $form->number('jumlah', 'Nominal Ambil')
                        ->min(1)
                        ->max($tim->gaji)
                        ->required()
                        ->help('Masukkan nominal tanpa titik/koma, contoh: 3000 (Rp 3.000)');


                    $form->date('tanggal')->default(now());
                    $form->hidden('metode_bayar')->value('Transfer');
                    $form->hidden('id_project')->value(null);
                })->action(url("admin/tim/{$tim->id}/ambil-gaji"))
            );
        });
    }

    public function storeAmbilGaji($id)
    {
        $tim = \App\Models\Tim::findOrFail($id);

        $data = request()->all();

        $data['jumlah'] = preg_replace('/[^\d]/', '', $data['jumlah']);
        $data['jumlah'] = (int) $data['jumlah'];

        if ($data['jumlah'] > $tim->gaji) {
            admin_error('Gagal', 'Nominal gaji melebihi total gaji yang tersedia, gaji tidak bisa diambil.');
            return back();
        }

        // Simpan data gaji
        $gaji = new \App\Models\Gaji();
        $gaji->id_tim = $tim->id;
        $gaji->jumlah = $data['jumlah'];
        $gaji->tanggal = $data['tanggal'];
        $gaji->metode_bayar = $data['metode_bayar'] ?? 'Transfer';
        $gaji->id_project = null;
        $gaji->save();

        $gajiProject = \App\Models\Gaji::where('id_tim', $tim->id)
            ->whereNotNull('id_project')
            ->sum('jumlah');

        $gajiAmbil = \App\Models\Gaji::where('id_tim', $tim->id)
            ->whereNull('id_project')
            ->sum('jumlah');

        $tim->gaji -= $data['jumlah'];
        $tim->save();

        admin_success('Berhasil', "Pengambilan gaji berhasil, sisa gaji Rp " . number_format($tim->gaji, 0, ',', '.'));

        return JsonResponse::make()
            ->redirect(admin_url('tim'));
    }
}
