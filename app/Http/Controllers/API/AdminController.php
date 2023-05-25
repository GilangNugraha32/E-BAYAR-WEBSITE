<?php

namespace App\Http\Controllers\API;

use App\Helper\ApiFormatter;
use App\Http\Controllers\Controller;
use App\Models\admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage as Storage;

class AdminController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(request $request)
    {
        $admin=DB::table('tb_admin')->get();
        return view('layouts.dataadmin', ['admin'=>$admin]);
        
    }
    
    public function getAll()
    {
        try {
            $data = admin::all();
            
            if($data){
                return ApiFormatter::createApi(200, 'Success', $data);
            }else{
                return ApiFormatter::createApi(400, 'Failed');
            }
        } catch (Exception $error) {
            return ApiFormatter::createApi(400,'Failed');
        }
    }
    
    public function login(request $request): RedirectResponse{
        
        $request->validate([
            'username'=>'required',
            'password'=>'required',
        ]);
        
        $admin = admin::where('username', $request->username)->first();
        if ($admin == null) {
            return redirect('/login')->with('error', 'Username yang anda masukkan salah');
        } else if (!$admin|| !Hash::check($request->password, $admin->password)){
            return redirect('/login')->with('error', 'Password yang anda masukkan salah');
        }
        $token =$admin->createToken('token')->plainTextToken;
        if($token){
            return redirect('/dashboard')->with('success', 'Anda Berhasil login');
        }else{
            return redirect('/login')->with('error', 'Silahkan masukkan kembali username dan password anda');
        }
    }
    
    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create()
    {
        //
    }
    
    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {
        $rules = [
            'username'=>'required',
            'password'=>'required',
            'nama'=>'required',
            'jenis_kelamin'=>'required',
            'gambar'=>'required',
        ];
        
        $text =[
            'username.required' => 'Kolom username Tidak boleh kosong',
            'username.unique' => 'Username Sudah Terdaftar',
            'password.required' => 'Kolom password Tidak boleh kosong',
            'password.unique' => 'password Sudah Terdaftar',
            'nama.required' => 'Kolom nama Tidak boleh kosong',
            'jenis_kelamin.required' => 'Kolom jenis_kelamin Tidak boleh kosong',
            'gambar.required' => 'Kolom gambar Tidak boleh kosong',
        ];
        $validasi = Validator::make($request->all(), $rules, $text);
        
        if ($validasi->fails()){
            return response()->json(['succes' => 0,'text' => $validasi->errors()->first()], 422);
        }
        try {
            
            $filename = '';
            
            if ($request->file('gambar')) {
                $file = $request->file('gambar');
                $generateFilename = join('', [uniqid(), now()->timestamp]);
                $extention = $file->getClientOriginalExtension();
                $filename = join('.', [$generateFilename, $extention]);
                $filename = $file->storeAs('admin',  $filename);
            }
            
            $tb_admin = admin::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'nama' => $request->nama,
                'jenis_kelamin' => $request->jenis_kelamin,
                'gambar' =>$filename,
            ]);
            $data = admin::where('id','=',$tb_admin->id)->get();
            
            if($data){
                return ApiFormatter::createApi(200, 'Success', $data);
            }else{
                return ApiFormatter::createApi(400, 'Failed');
            }
        } catch (Exception $error) {
            return ApiFormatter::createApi(400,'Failed');
        }
    }
    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function show($id)
    {
        $data = admin::where('id','=',$id)->get();
        
        if($data){
            return ApiFormatter::createApi(200, 'Success',$data);
        }else{
            return ApiFormatter::createApi(400,'Failed'); 
        }
    }
    
    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function edit($id)
    {
        //
    }
    
    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $id)
    {
        try {
            $request ->validate([
                'username' => 'required',
                'password' => 'required',
                'nama' => 'required',
                'jenis_kelamin' => 'required',
                'gambar' => 'required',
            ]);
            
            $tb_admin = admin::findOrFail($id);
            
            if ($request->file('gambar')) {
                if (Storage::exists($tb_admin->gambar)) {
                    Storage::delete([$tb_admin->gambar]);
                }
                $file = $request->file('gambar');
                $generateFilename = join('', [uniqid(), now()->timestamp]);
                $extention = $file->getClientOriginalExtension();
                $filename = join('.', [$generateFilename, $extention]);
                $filename = $file->storeAs('admin',  $filename);
            }
            
            $tb_admin->update([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'nama' => $request->nama,
                'jenis_kelamin' => $request->jenis_kelamin,
                'gambar' => $filename
            ]);
            
            $data = admin::where('id','=',$tb_admin->id)->get();
            
            if($data){
                return ApiFormatter::createApi(200, 'Success',$data);
            }else{
                return ApiFormatter::createApi(400,'Failed'); 
            }
        } catch (Exception $error) {
            return ApiFormatter::createApi(400,'Failed');
        }
    }
    
    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        $tb_admin = admin::findOrfail($id);
        
        $data = $tb_admin->delete();
        if($data){
            return ApiFormatter::createApi(200, 'Success',$data);
        }else{
            return ApiFormatter::createApi(400,'Failed'); 
        }
    }
}
