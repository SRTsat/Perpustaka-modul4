<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Buku;
use App\Models\User;
use Illuminate\Http\Request;

class PeminjamanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $peminjamans = Peminjaman::with(['user','buku'])->get();
        return view('peminjamans.index', compact('peminjamans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $bukus = Buku::all();
        $users = User::where('role','siswa')->get();

        return view('peminjamans.create', compact('bukus','users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'buku_id' => 'required',
            'tanggal_peminjaman' => 'required|date'
        ]);

        $buku = Buku::findOrFail($request->buku_id);

        if($buku->stok <= 0){
            return back()->with('error','Stok buku habis');
        }

        Peminjaman::create([
            'user_id' => $request->user_id,
            'buku_id' => $request->buku_id,
            'tanggal_peminjaman' => $request->tanggal_peminjaman,
            'status_peminjaman' => 'dipinjam'
        ]);

        // kurangi stok buku
        $buku->stok -= 1;
        $buku->save();

        return redirect()->route('peminjamans.index')
            ->with('success','Buku berhasil dipinjam');
    }

    /**
     * Display the specified resource.
     */
    public function show(Peminjaman $peminjaman)
    {
        return view('peminjamans.show', compact('peminjaman'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Peminjaman $peminjaman)
    {
        $bukus = Buku::all();
        $users = User::where('role','siswa')->get();

        return view('peminjamans.edit', compact('peminjaman','bukus','users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Peminjaman $peminjaman)
    {
        $request->validate([
            'tanggal_pengembalian' => 'required|date'
        ]);

        $peminjaman->update([
            'tanggal_pengembalian' => $request->tanggal_pengembalian,
            'status_peminjaman' => 'dikembalikan'
        ]);

        // tambah stok buku
        $buku = Buku::find($peminjaman->buku_id);
        $buku->stok += 1;
        $buku->save();

        return redirect()->route('peminjamans.index')
            ->with('success','Buku berhasil dikembalikan');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Peminjaman $peminjaman)
    {
        $peminjaman->delete();

        return redirect()->route('peminjamans.index')
            ->with('success','Data peminjaman dihapus');
    }
}