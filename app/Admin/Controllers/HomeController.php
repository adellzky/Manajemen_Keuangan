<?php

namespace App\Admin\Controllers;

use App\Models\Pendapatan;
use App\Models\Pengeluaran;
use App\Models\Project;
use App\Models\Kas;
use App\Models\Gaji;
use App\Http\Controllers\Controller;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Widgets\Box;
use Dcat\Admin\Widgets\Table;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        // Ringkasan umum
        $totalPemasukan   = (float) Pendapatan::sum('jumlah');
        $totalProject     = Project::where('status', 'proses')->count(); // ✅ hanya yg status = proses

        // NOTE: total pengeluaran di dashboard tetap total pengeluaran + gaji
        $totalPengeluaran = (float) Pengeluaran::sum('jumlah') + (float) Gaji::sum('jumlah');


        /*
         * Hitung saldo dengan agregat (lebih andal daripada mengambil baris terakhir)
         *
         * Asumsi kolom:
         * - Kas::jumlah  -> "setor" ke bank (modal)
         * - Kas::cash    -> jumlah cash yang ditarik (dari bank ke cash)
         * - Pengeluaran.sumber_dana -> 'bank' atau 'cash'
         * - Gaji::jumlah -> total gaji (mengurangi bank)
         */
        $totalModal           = (float) Kas::sum('jumlah');       // setoran/penambahan ke bank
        $totalCashTarik       = (float) Kas::sum('cash');         // total tarik cash (mengurangi bank, menambah cash)
        $totalPendapatanAll   = (float) $totalPemasukan;          // alias
        $totalPengeluaranBank = (float) Pengeluaran::where('sumber_dana', 'bank')->sum('jumlah');
        $totalPengeluaranCash = (float) Pengeluaran::where('sumber_dana', 'cash')->sum('jumlah');
        $totalGaji            = (float) Gaji::sum('jumlah');

        // Rumus running balance (sesuai logika di grid)
        $saldoBank   = $totalModal + $totalPendapatanAll - $totalPengeluaranBank - $totalGaji - $totalCashTarik;
        $saldoCash   = $totalCashTarik - $totalPengeluaranCash;
        $keseluruhan = $saldoBank + $saldoCash;

        // Data bulanan (grafik) - tetap seperti sebelumnya
        $months      = [];
        $pemasukan   = [];
        $pengeluaran = [];

        for ($i = 1; $i <= 12; $i++) {
            $bulanNama     = date('M', mktime(0, 0, 0, $i, 1));
            $months[]      = $bulanNama;
            $pemasukan[]   = (float) Pendapatan::whereMonth('tanggal', $i)->sum('jumlah');
            $pengeluaran[] = (float) Pengeluaran::whereMonth('tanggal', $i)->sum('jumlah')
                            + (float) Gaji::whereMonth('tanggal', $i)->sum('jumlah');
        }

        $chartId = 'chart_' . uniqid();
        $chartHtml = '
        <div id="'.$chartId.'" style="width:100%;height:350px;"></div>
        <script>
        Dcat.ready(function () {
            function loadChart() {
                var chartDom = document.getElementById("'.$chartId.'");
                if (!chartDom) return;
                var chart = echarts.init(chartDom);
                var option = {
                    tooltip: { trigger: "axis" },
                    legend: { data: ["Pemasukan", "Pengeluaran"] },
                    xAxis: { type: "category", data: '.json_encode($months).' },
                    yAxis: { type: "value" },
                    series: [
                        { name: "Pemasukan", type: "bar", data: '.json_encode($pemasukan).', itemStyle: {color: "#4caf50"} },
                        { name: "Pengeluaran", type: "bar", data: '.json_encode($pengeluaran).', itemStyle: {color: "#f44336"} }
                    ]
                };
                chart.setOption(option);
            }

            if (typeof echarts === "undefined") {
                var script = document.createElement("script");
                script.src = "https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js";
                script.onload = loadChart;
                document.head.appendChild(script);
            } else {
                loadChart();
            }
        });
        </script>';

        // Projects mendekati deadline
        $today     = now();
        $limitDate = now()->addDays(20);

        $projects = Project::where('status', 'proses')
            ->whereBetween('tanggal_selesai', [$today, $limitDate])
            ->orderBy('tanggal_selesai', 'asc')
            ->get(['nama_project', 'tanggal_mulai', 'tanggal_selesai', 'status']);

        $projectRows = [];
        foreach ($projects as $p) {
            $projectRows[] = [
                $p->nama_project,
                $p->tanggal_mulai,
                $p->tanggal_selesai,
                ucfirst($p->status),
            ];
        }

        $projectTable = new Table(
            ['Nama Project', 'Tanggal Mulai', 'Tanggal Selesai', 'Status'],
            $projectRows
        );

        return $content
            ->header('Dashboard')
            ->description('Ringkasan Keuangan & Project')
            ->body(function (Row $row) use (
                $totalPemasukan,
                $totalPengeluaran,
                $totalProject,
                $saldoBank,
                $saldoCash,
                $keseluruhan,
                $chartHtml,
                $projectTable
            ) {
                // Tampilan 3 kolom per baris agar rapi
                // Baris pertama
                $row->column(4, $this->infoBox('Total Pendapatan', 'success', $totalPemasukan, true, 'fa fa-arrow-circle-up'));
                $row->column(4, $this->infoBox('Total Pengeluaran', 'danger', $totalPengeluaran, true, 'fa fa-arrow-circle-down'));
                $row->column(4, $this->infoBox('Project (Proses)', 'primary', $totalProject, false, 'fa fa-briefcase'));

                $row->column(4, $this->infoBox('Saldo Bank', 'info', $saldoBank, true, 'fa fa-university'));
                $row->column(4, $this->infoBox('Saldo Cash', 'warning', $saldoCash, true, 'fa fa-money'));
                $row->column(4, $this->infoBox('Keseluruhan', 'success', $keseluruhan, true, 'fa fa-database'));




                // Chart dan project
                $row->column(12, new Box('Grafik Pemasukan & Pengeluaran (Bulanan)', $chartHtml));
                $row->column(12, new Box('Project Berjalan (Mendekati Deadline)', $projectTable));
            });
    }

    private function infoBox($title, $style, $value, $rupiah = true, $icon = 'fa-database')
    {
        $display = $rupiah
            ? 'Rp ' . number_format($value ?? 0, 0, ',', '.')
            : $value;

        return <<<HTML
            <div class="info-box bg-$style">
                <span class="info-box-icon"><i class="fa $icon"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">$title</span>
                    <span class="info-box-number">$display</span>
                </div>
            </div>
        HTML;
    }

}
