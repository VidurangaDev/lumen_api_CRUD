<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }

    public function store(Request $request)
{
    // Validate incoming request
    $this->validate($request, [
        'title' => 'required|string',
        'price' => 'required|numeric',
        'photo' => 'image|mimes:jpg,jpeg,png|max:2048', // Set max file size limit
        'description' => 'required|string',
    ]);

    $filename = null;  // Initialize $filename to avoid potential undefined variable issues

    // Handle file upload
    if ($request->hasFile('photo')) {
        $file = $request->file('photo');

        // Check extension (you already checked with 'image' rule, so not necessary here)
        $extension = $file->getClientOriginalExtension();
        $allowedExtensions = ['png', 'jpg', 'jpeg'];

        if (in_array($extension, $allowedExtensions)) {
            // Use store() instead of move() for better handling and configurability
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/images', $filename);  // Automatically stores in storage/app/public/images

            // If you want the file to be publicly accessible, ensure you have run: php artisan storage:link
        } else {
            return response()->json(['error' => 'Invalid file type.'], 400);
        }
    }

    // Save the product
    $product = new Product();
    $product->title = $request->input('title');
    $product->price = $request->input('price');
    $product->photo = $filename;  // It will be null if the upload fails
    $product->description = $request->input('description');
    $product->save();

    return response()->json($product, 201);
}


    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        // validation
        $this->validate($request, [
            'title' => 'required',
            'price' => 'required',
            'photo' => 'image|mimes:jpg,png,jpeg|max:2048',
            'description' => 'required',
        ]);

        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // image upload
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $extension = $file->getClientOriginalExtension();
            $allowedFileExtensions = ['pdf', 'png', 'jpg'];

            // Check file extension
            if (in_array($extension, $allowedFileExtensions)) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('images'), $fileName);
                $product->photo = $fileName;
            }
        }

        // text update
        $product->title = $request->input('title');
        $product->price = $request->input('price');
        $product->description = $request->input('description');

        $product->save();

        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();
        return response()->json(['message' => 'Product successfully deleted']);
    }
}
