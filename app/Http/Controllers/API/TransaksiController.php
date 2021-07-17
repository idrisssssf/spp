<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use App\Models\Santri;
use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PDF;

class TransaksiController extends Controller
{
    public function createTransaksi(Request $request)
    {
        date_default_timezone_set("Asia/Jakarta");
        $jam_sekarang = date("H:i", strtotime("now"));
        $tanggal_sekarang = date("Y-m-d", strtotime("now"));
        $waktu_sekarang = date("Y-m-d H:i", strtotime("now"));
        $waktu = Carbon::now();

        $request->validate([
            'nis'               => 'required',
            'jumlah_bulan'      => 'required',
            'total_bayar'       => 'required',
            'spp'               => 'required',
            'infaq'             => 'required',
            'id_admin'          => 'required'
        ]);

        $santri = Santri::where('nis', $request->nis)->first();
        if(!$santri)
        {
            return response()->json([
                'message' => 'NIS tidak terdaftar'
            ], 401);
        }

        for($i = 0; $i < $request->jumlah_bulan; $i++)
        {
            $transaksi = Transaksi::where('nis', $request->nis)->orderBy('id_transaksi', 'desc')->first();
            if($transaksi == null)
            {
                $bulan = env("AWAL_BULAN_AJARAN", 7);
                $tahun = $waktu->year;
            } else if($transaksi != null && $transaksi->bulan < 12 )
            {
                $bulan = $transaksi->bulan+1;
                $tahun = $waktu->year;
            } else if($transaksi != null && $transaksi->bulan >= 12)
            {
                    $bulan = 1;
                    $tahun = $waktu->year;
            }

            $transaksi = new Transaksi();
            $transaksi->nis = $request->nis;
            $transaksi->total_bayar = 50000;
            $transaksi->spp = 35000;
            $transaksi->infaq = 15000;
            $transaksi->bulan = $bulan;
            $transaksi->tahun = $tahun;
            $transaksi->status_transaksi = "Tunai";
            $transaksi->id_admin = $request->id_admin;
            $transaksi->tanggal_transaksi = $tanggal_sekarang;
            $transaksi->save();
            // dd($transaksi);
        }

        $tunggakan = $santri->jumlah_tunggakan-$request->jumlah_bulan;
        $santri->update(['jumlah_tunggakan' => $tunggakan]);

        $total_transaksi = 50000*$request->jumlah_bulan;

        return response()->json([
            'message'           => 'Transaksi Berhasil',
            'id_transaksi'      => $transaksi->id_transaksi,
            'nis'               => $transaksi->nis,
            'total_bayar'       => $total_transaksi,
            'status_transaksi'  => $transaksi->status_transaksi,
            'tanggal_transaksi' => $transaksi->tanggal_transaksi,
            'admin'             => $transaksi->id_admin
            // 'transaksi' => $transaksi
        ], 200);
    }

    public function index()
    {
        return "Ini Controller Transaksi";
    }

    public function getUangMasuk()
    {
        $bulan = Carbon::now();
        $uang = Transaksi::whereMonth('tanggal_transaksi', $bulan->month)->sum('total_bayar');

        return response()->json([
            'message'       => 'Data Uang Masuk Bulan Ini',
            'uang_masuk'    => $uang
        ], 200);
    }

    public function getSantriBayar()
    {
        $bulan = Carbon::now();
        $santri = Transaksi::whereMonth('tanggal_transaksi', $bulan->month)->groupBy('nis')->select('nis', DB::raw('count(nis) as total'))->get();
        

        return response()->json([
            'message'       => 'Data Uang Masuk Bulan Ini',
            'santri'    => count($santri)
        ], 200);
    }

    public function getTransaksi()
    {
        $transaksi = Transaksi::orderBy('id_transaksi', 'DESC')->get();

        return response()->json(array(
            'message' => 'Riwayat Transaksi Berhasil Ditampilkan',
            'transaksi' => $transaksi->toArray()),
            200
        );
    }

    //API getTransaksi per bulan
    public function getLaporanTransaksi(Request $request)
    {
        $transaksi = DB::table('transaksis')
            ->join('santris', 'transaksis.nis', '=', 'santris.nis')
            ->join('kelas', 'santris.id_kelas', '=', 'kelas.id_kelas')
            ->select('transaksis.nis', 'santris.nama_santri', 'kelas.nama_kelas', '');
    }

    public function getTransaksiSantri($nis)
    {
        $transaksi = Transaksi::where('nis', $nis)->get();

        return response()->json(array(
            'message' => 'Riwayat Pembayaran Berhasil Ditampilkan',
            'transaksi' => $transaksi->toArray()),
            200
        );
    }
}
