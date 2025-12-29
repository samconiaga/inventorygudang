<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function index()
    {
        return view('customer.index');
    }

    public function getDataCustomer()
    {
        return response()->json([
            'success' => true,
            'data'    => Customer::orderBy('id', 'desc')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer' => 'required|string|max:255',
        ], [
            'customer.required' => 'Nama Departemen wajib diisi!',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $customer = Customer::create([
            'customer' => trim($request->customer),
            'alamat'   => '-',
            'user_id'  => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Departemen berhasil disimpan!',
            'data'    => $customer,
        ]);
    }

    public function edit(Customer $customer)
    {
        return response()->json([
            'success' => true,
            'message' => 'Edit Data Departemen',
            'data'    => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $validator = Validator::make($request->all(), [
            'customer' => 'required|string|max:255',
        ], [
            'customer.required' => 'Nama Departemen wajib diisi!',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $customer->update([
            'customer' => trim($request->customer),
            'alamat'   => '-',
            'user_id'  => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Departemen berhasil diperbarui!',
            'data'    => $customer,
        ]);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Departemen berhasil dihapus',
        ]);
    }
}
