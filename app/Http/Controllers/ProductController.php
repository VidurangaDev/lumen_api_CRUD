<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'price' => 'required|numeric',
            'photo' => 'required',
            'description' => 'sometimes|string',
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $filename = null;  // Initialize filename to handle cases where no file is uploaded

        // Handle file upload
        try {
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $extension = $file->getClientOriginalExtension();
                $allowedExtensions = ['png', 'jpg', 'jpeg'];

                if (in_array($extension, $allowedExtensions)) {
                    // Store the file and generate a unique filename
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('images'), $filename); // Store in storage/app/public/images
                } else {
                    return response()->json(['error' => 'Invalid file type.'], 400);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'File upload failed: ' . $e->getMessage()], 500);
        }

        // Create new product instance
        $product = new Product();
        $product->title = $request->input('title');
        $product->price = $request->input('price');
        $product->photo = $filename;  // Store the filename if the photo was uploaded
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
        // Validate the request
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'photo' => 'sometimes|image|mimes:jpg,png,jpeg|max:2048',
            'description' => 'sometimes|string',
        ]);

        // Log validation errors if they occur
        if ($validator->fails()) {
            Log::debug('Validation failed:', $validator->errors()->toArray());
            return response()->json($validator->errors(), 400);
        }

        // Find the product by ID
        $product = Product::find($id);
        if (!$product) {
            Log::debug('Product not found with ID:', $id);
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Log the product data before updating
        Log::debug('Product before update:', $product->toArray());

        // Handle image upload if a new photo is provided
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $extension = $file->getClientOriginalExtension();
            $allowedFileExtensions = ['png', 'jpg', 'jpeg'];

            if (in_array($extension, $allowedFileExtensions)) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('images'), $fileName);  // Save the file in the public/images directory
                $product->photo = $fileName;  // Set the filename in the product's photo attribute
                Log::debug('File uploaded successfully:', ['file' => $fileName]);
            } else {
                Log::debug('Invalid file type uploaded.');
            }
        }

        // Update other fields if they are present
        if ($request->has('title')) {
            $product->title = $request->input('title');
        }

        if ($request->has('price')) {
            $product->price = $request->input('price');
        }

        if ($request->has('description')) {
            $product->description = $request->input('description');
        }

        // Log the updated product data before saving
        Log::debug('Product data before save:', $product->toArray());

        // Save the updated product
        $product->save();

        // Return the updated product
        return response()->json($product, 200);
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
