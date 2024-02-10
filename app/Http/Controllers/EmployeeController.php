<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $searchQuery = $request->input('query');

        $employees = Employee::query()
            ->when(request('query'), function ($query, $searchQuery) {
                $query->where('name', 'like', '%' . $searchQuery . '%')
                    ->orWhere('email', 'like', "%$searchQuery%");
            })->latest()->paginate(10);

        foreach ($employees as $employee) {
            $employee->image_url = Storage::url($employee->image);
        }
        return response()->json($employees);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }



        $imgPath = '';
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . $image->getClientOriginalName();
            $imgPath = $image->storeAs('public/employees', $imageName);
        }

        $employee = Employee::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'salary' => $request->salary,
            'address' => $request->address,
            'nid' => $request->nid,
            'joining_date' => $request->joining_date,
            'image' => $imgPath,
        ]);

        return response()->json($employee);
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        $employee->image_url = Storage::url($employee->image);
        return response()->json($employee);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }


        $employee->name = $request->name;
        $employee->email = $request->email;
        $employee->phone = $request->phone;
        $employee->salary = $request->salary;
        $employee->address = $request->address;
        $employee->nid = $request->nid;
        $employee->joining_date = $request->joining_date;

        $imgPath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            unlink(storage_path('app/' . $employee->image));
            $imageName = time() . $image->getClientOriginalName();
            $imgPath = $image->storeAs('public/employees', $imageName);
        }

        $employee->image = $imgPath ?? $employee->image;

        return $employee->update();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        $image = $employee->image;

        if ($image) {
            unlink(storage_path('app/' . $image));
            $employee->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.',
            ]);
        } else {

            $employee->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.',
            ]);
        }
    }
}
