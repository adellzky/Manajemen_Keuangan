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
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Widgets\Box;
use Dcat\Admin\Widgets\Table;

class HomeController extends Controller
{
    public function index(Content $content)
    {
        //Hitung ringkasan
        $totalPengeluaran = Pengeluaran::sum('jumlah') + Gaji::sum('jumlah');
        $totalPemasukan   = Pendapatan::sum('jumlah');
        $totalProject     = Project::count();
        $totalModal       = Kas::sum('jumlah');
        $saldoAkhir       = $totalPemasukan + $totalModal - $totalPengeluaran;

        // Data bulanan (untuk chart manual yang sudah ada)
        $months      = [];
        $pemasukan   = [];
        $pengeluaran = [];

        for ($i = 1; $i <= 12; $i++) {
            $bulanNama   = date('M', mktime(0, 0, 0, $i, 1));
            $months[]    = $bulanNama;
            $pemasukan[] = (float) Pendapatan::whereMonth('tanggal', $i)->sum('jumlah');
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


        // Project mendekati deadline
        $today     = now();
        $limitDate = now()->addDays(7);

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
                $saldoAkhir,
                $chartHtml,
                $projectTable
            ) {
                //InfoBox Summary
                $row->column(3, $this->infoBox('Total Pemasukan', 'success', $totalPemasukan));
                $row->column(3, $this->infoBox('Total Pengeluaran', 'danger', $totalPengeluaran));
                $row->column(3, $this->infoBox('Total Project', 'primary', $totalProject, false));
                $row->column(3, $this->infoBox('Saldo Akhir', 'info', $saldoAkhir));

                //Chart
                $row->column(12, new Box('Grafik Pemasukan & Pengeluaran (Bulanan)', $chartHtml));

                //Daftar Project
                $row->column(12, new Box('Project Berjalan (Mendekati Deadline)', $projectTable));
            });
    }

    private function infoBox($title, $style, $value, $rupiah = true)
    {
        $display = $rupiah
            ? 'Rp ' . number_format($value ?? 0, 0, ',', '.')
            : $value;

        return <<<HTML
            <div class="info-box bg-$style">
                <span class="info-box-icon"><i class="fa fa-database"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">$title</span>
                    <span class="info-box-number">$display</span>
                </div>
            </div>
        HTML;
    }
}
