<?php

namespace App\Http\Controllers;
         
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use Illuminate\Support\Facades\Storage;
    
class ProductController extends Controller implements HasMiddleware {

    public static function middleware() {
        return [
            'auth'
        ];
    }

    public function store(StoreProductRequest $request) {
        try {
            $product = Product::create($request->validated());
            return redirect()->back()->with('success', 'Product created successfully: ' . $product->name);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error creating product: ' . $e->getMessage());
        }
    }

    public function edit(Product $product) {
        return view('edit_product', ['product' => $product]);
    }

    public function update(Request $request, Product $product) {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'prijs' => 'required|numeric|min:0',
                'description' => 'required',
                'title' => 'required|max:255',
                'created_date' => 'required|date',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);
            
            $data = $request->only(['name', 'prijs', 'description', 'title', 'created_date']);

            if ($request->hasFile('image')) {
                // Eliminar imagen anterior si existe
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                // Guardar nueva imagen
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $product->update($data);
            return redirect()->route('welcome')->with('success', 'Product updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating product: ' . $e->getMessage());
        }
    }

    public function destroy(Product $product) {
        try {
            // Eliminar la imagen si existe
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->delete();
            return redirect()->route('welcome')->with('success', 'Product deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting product: ' . $e->getMessage());
        }
    }
}
    
