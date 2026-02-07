@extends('layouts.app')
@section('title', 'Info Jabatan')
@section('page_title', 'Detail Pekerjaan')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <div class="bg-white rounded-[3rem] p-10 border border-slate-100 shadow-sm relative overflow-hidden">
        <i class="fas fa-briefcase absolute -right-5 -bottom-5 text-8xl text-slate-50"></i>
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Divisi & Unit</p>
        <h3 class="text-3xl font-black text-cdi uppercase italic tracking-tighter leading-none">{{ Auth::user()->karyawan->divisi }}</h3>
        
        <div class="mt-10 space-y-4 relative z-10">
            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-white">
                <span class="text-[10px] font-black uppercase text-slate-400">Status Pegawai</span>
                <span class="font-black text-cdi uppercase italic text-xs">{{ Auth::user()->karyawan->status }}</span>
            </div>
            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-white">
                <span class="text-[10px] font-black uppercase text-slate-400">NIP</span>
                <span class="font-black text-cdi-orange uppercase italic text-xs">{{ Auth::user()->karyawan->nip }}</span>
            </div>
        </div>
    </div>
</div>
@endsection