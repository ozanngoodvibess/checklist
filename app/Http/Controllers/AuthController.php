<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    function index()
    {
        return view ('halaman_auth/login');
    }
    function login(Request $request )
    {
        $request->validate([

            'email' => 'required',
            'password' => 'required',
        ],[

            'email.required' => 'phpEmail Wajib Diisi',
            'password.required' => 'Password Tidak Boleh Kosong'
        ]);

        $infologin = [

            'email' => $request->email,
            'password' => $request->password,
        ];

        if(Auth::attempt($infologin)){
            if(Auth::user()->email_verified_at != null){
                if(Auth::user()->role === 'admin'){
                    return redirect ()->route('admin')->with('success','Halo Admin , Anda berhasil login');
                }else if(Auth::user()->role === 'user'){
                    return redirect ()->route('user')->with('success',  'Berhasil login');
                }
            }else{
                Auth::logout();
                return redirect()->route('auth')->withErrors('Akun anda belum Aktif. Harap verefikasi terlebih dahulu');
            }
        }else{
            return redirect()->route('auth')->withErrors('Email atau Password Salah');  
        };
    }
    function create()
    {
        return view('halaman_auth/register');
    }
    function register(Request $request)
    {
        $str = str::random(100);

        $request->validate([
            'noin' => 'required|min:5',
            'fullname' => 'required|min:5',
            'email' => 'required|unique:users',
            'password' => 'required|min:6',
            'gambar' => 'required|image|file',
        ], [
            'noin.required' => 'No Induk Wajib Di Isi',
            'noin.min' => 'No Induk Minimal 5 Angka',
            'fullname.required' => 'No Induk Wajib Di Isi',
            'fullname.min' => 'Full Name Minimal 5 Karakter',
            'email.required' => 'Email Wajib Di Isi',
            'email.unique' => 'Email Telah Terdaftar',       
            'password.required' => 'Password Wajib Di Isi',
            'password.min' => 'Password Minimal 6 Karakter',
            'gambar.required' => 'Gambar Wajib Di Upload',
            'gambar.image' => 'Gambar Yang Di Upload Harus Image',
            'gambar.file' => 'Gambar Harus Berupa File',

          
           ]);

           $gambar_file = $request->file('gambar');
           $gambar_ekstensi = $gambar_file->extension();
           $nama_gambar = date('ymdhis') .".". $gambar_ekstensi;
           $gambar_file->move(public_path('picture/accounts'),$nama_gambar);

           $inforegister =[
            'noin' => $request->noin,
            'name' => $request->name,
            'fullname' => $request->fullname,
            'email' => $request->email,
            'password' => $request->password,
            'gambar' => $nama_gambar,
            'verify_key' => $str
           ];

           User::create($inforegister);

           $details = [
               'noin'=> $inforegister['noin'],
               'nama'=> $inforegister['fullname'],
               'role'=> 'user',
               'datetime'=> date('Y-m-s H:i:s'),
               'websiste'=> 'laravel110 - pendaftaran melalui SMTP + Multiuser + CRUD + Sweetalert',
               'url'=> 'https://'. request()->getHttpHost() . "/" ."verify/". $inforegister['verify_key'],
           




           ];

           Mail::to($inforegister['email'])->send(new AuthMail($details));

           return redirect()->route('auth')->with('success', 'Link verifikasi telah dikirim ke email anda. Cek email untuk melakukan 
           verifikasi');    
           
    }

    function verify($verify_key){
        $keyCheck = User::select('verify_key')
        ->where('verify_key',$verify_key)
        ->exists();

        if($keyCheck){
            $user = User::where('verify_key',$verify_key)->update(['email_verified_at' => date('Y-m-d H:i:s')]);

            return redirect()->route('auth')->with('success','Verifikasi berhasil, Akun anda sudah aktif');
        }else{
            return redirect()->route('auth')->withErrors('Keys tidak valid, pastikan telah melakukan register')->withInput();
        }
    }


}
