<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index(Request $request)
    // {
    //     $searchQuery = $request->get('query');
    //     $searchCategory = $request->get('category');

    //     $products = Product::query()
    //         ->when(request('query'), function ($query) use ($searchQuery) {
    //             $query->where('name', 'like', '%' . $searchQuery . '%');
    //         })
    //         ->latest()->paginate(10);

    //     foreach ($products as $product) {
    //         $product->image_url = Storage::url($product->image);
    //     }

    //     return response()->json($products);
    // }

    public function index(Request $request)
{
    {
        $searchQuery = $request->get('query');
        $searchCategory = $request->get('category');

        $products = Product::query()
            ->when($searchQuery, function ($query) use ($searchQuery) {
                $query->where('name', 'like', '%' . $searchQuery . '%');
            })
            ->when($searchCategory, function ($query) use ($searchCategory) {
                $query->where('category_id', $searchCategory);
            })
            ->with('category')
            ->latest()
            ->paginate(10);

        foreach ($products as $product) {
            $product->image_url = url('storage/' . $product->image);
            // $product->image_url = asset('storage/' . $product->image);
        }

        return response()->json($products);
    }

    // public function index(Request $request)  
// {  
//     $searchQuery = $request->get('query');  
//     $searchCategory = $request->get('category');  
//     $perPage = (int) $request->get('per_page', 10); // Set a default value for items per page  

    //     // Build the products query  
//     $productsQuery = Product::query()  
//         ->when($searchQuery, function ($query) use ($searchQuery) {  
//             $query->where('name', 'like', '%' . $searchQuery . '%');  
//         })  
//         ->when($searchCategory, function ($query) use ($searchCategory) {  
//             $query->where('category_id', $searchCategory);  
//         })  
//         ->latest();  

    //     // Paginate the results  
//     $products = $productsQuery->paginate($perPage);  

    //     // Map and modify products to include the image URLs  
//     $products->getCollection()->transform(function ($product) {  
//         $product->image_url = $product->image ? url('storage/' . $product->image) : null; // Handle null values for the image  
//         return $product;  
//     });  

    //     return response()->json([  
//         'code' => 200, // Optional: add a status code for better response handling  
//         'data' => $products->items(),  
//         'total' => $products->total(),  
//         'current_page' => $products->currentPage(),  
//         'last_page' => $products->lastPage(),  
//     ]);  
// }  


    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //  dd($request->all());
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'category_id' => 'required',
            'quantity' => 'required|integer',
            'selling_price' => 'required|numeric',
            'code' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            // Store the image in storage/app/public/products
            $imagePath = $request->file('image')->store('products', 'public');
            // dd($imagePath);
            // If you need the full URL for frontend access:
            // $imageUrl = asset('storage/'.$imagePath);
        }

        #if image more than 2mb
        if ($request->hasFile('image') && $request->file('image')->getSize() > 2048000) {
            return response()->json(['error' => 'Image size exceeds 2MB.'], 422);
        }

        // Generate Advanced SKU
        $categoryPrefix = 'CAT' . $request->category_id;
        $nameSlug = strtoupper(Str::slug(Str::limit($request->name, 10, ''), ''));
        $datePart = Carbon::now()->format('Ymd');
        $randomSuffix = strtoupper(Str::random(4));
        $sku = "{$datePart}-{$randomSuffix}";
        $product = Product::create([
            'sku' => $sku,
            'name' => $request->name,
            'category_id' => $request->category_id,
            'quantity' => $request->quantity,
            'selling_price' => $request->selling_price,
            'code' => $request->code,
            'image' => $imagePath,
            'description' => $request->description,
            'root' => $request->root,
            'buying_price' => $request->buying_price,
            'supplier_id' => $request->supplier_id,
            'buying_date' => $request->buying_date,
        ]);

        return response()->json($product);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return response()->json($product);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, Product $product)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|min:3',
    //         'category_id' => 'required'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()]);
    //     }

    //     $imgPath = '';
    //     if ($request->hasFile('image')) {
    //         $image = $request->file('image');
    //         unlink(storage_path('app/' . $product->image));
    //         $imageName = time() . $image->getClientOriginalName();
    //         $imgPath = $image->storeAs('public/products', $imageName);
    //     }

    //     if ($request->hasFile('image')) {
    //         $product->name = $request->name;
    //         $product->category_id = $request->category_id;
    //         $product->quantity = $request->quantity;
    //         $product->image = $imgPath;
    //         $product->update();
    //     } else {
    //         $product->name = $request->name;
    //         $product->category_id = $request->category_id;
    //         $product->quantity = $request->quantity;
    //         $product->update();
    //     }

    //     return response()->json($product);
    // }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'category_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        // Handle image replacement if new image uploaded
        if ($request->hasFile('image')) {
            // Remove old image if it exists
            if ($product->image) {
                $oldImagePath = storage_path('app/public/' . str_replace('public/', '', $product->image));
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            // Store new image (same logic as insert)
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // Update other fields
        $product->fill([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'quantity' => $request->quantity,
            'image' => $imagePath,
        ])->save();

        return response()->json($product);
    }




    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            // Delete associated image if exists
            if ($product->image && Storage::exists($product->image)) {
                Storage::delete($product->image);
            }

            // Delete the product record
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product.',
                'error' => $e->getMessage() // Only include in development
            ], 500);
        }
    }


    // ------------------ Bulk Delete --------------------


    public function bulkDelete(Request $request)
    {
        $ids = $request->get('ids');

        if (empty($ids)) {
            return response()->json([
                'errors' => true,
                'message' => 'Products did not selected.',
            ]);
        } else {
            // Fetch products that match the given ids
            $products = Product::whereIn('id', $ids)->get(['id', 'image']);

            foreach ($products as $product) {
                $imagePath = $product->image;

                if ($imagePath) {
                    // Check if the image exists before attempting to delete it
                    if (Storage::exists($imagePath)) {
                        // Delete the image from the storage
                        Storage::delete($imagePath);
                    }
                }

                // Delete the product
                $product->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Products deleted successfully.',
            ]);
        }
    }
}
