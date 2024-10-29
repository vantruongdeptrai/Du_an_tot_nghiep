<?php

namespace App\Http\Controllers\API;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\Attribute;
use App\Models\AttributeValue;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load category và productVariants để tối ưu hiệu suất
        $products = Product::with(['category', 'productVariants'])->get();
        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'sale_price' => 'nullable|numeric',
            'category_id' => 'required|integer|exists:categories,id',
            'sale_start' => 'nullable|date',
            'sale_end' => 'nullable|date',
            'new_product' => 'nullable|boolean',
            'best_seller_product' => 'nullable|boolean',
            'featured_product' => 'nullable|boolean',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048', // Kiểm tra file ảnh
        ]);
    
        // Xử lý slug cho sản phẩm
        $slug = Str::slug($validatedData['name']);
    
        // Lưu file ảnh vào storage
        $imagePath = $request->hasFile('image') ? $request->file('image')->store('products', 'public') : null;
    
        $product = Product::create([
            'name' => $request->input('name'), // Đảm bảo rằng bạn có giá trị cho trường này
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'sale_price' => $request->input('sale_price'),
            'category_id' => $request->input('category_id'),
            'sale_start' => $request->input('sale_start', now()),
            'sale_end' => $request->input('sale_end', now()->addDays(7)),
            'new_product' => $request->input('new_product', 0),
            'best_seller_product' => $request->input('best_seller_product', 0),
            'featured_product' => $request->input('featured_product', 0),
            'image' => $imagePath,
            'slug' => $slug, 
        ]);
    
        return response()->json(['message' => 'tạo sản phẩm thành công', 'product' => $product], 201);
    }

    public function show(string $id)
    {
        $product = Product::with('productVariants.size', 'productVariants.color')->findOrFail($id);
        // Thêm URL đầy đủ cho ảnh
        $product->image_url = $product->image ? asset('storage/' . $product->image) : null;
        foreach ($product->productVariants as $variant) {
            $variant->image_url = $variant->image ? asset('storage/' . $variant->image) : null;
        }
        return response()->json($product->makeHidden(['variants']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        // Tìm sản phẩm cần cập nhật
        $product = Product::findOrFail($id);

        // Xử lý việc tải hình ảnh lên (nếu có hình ảnh mới)
        if ($request->hasFile('image')) {
            // Xóa hình ảnh cũ nếu có
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $image = $request->file('image');
            $imagePath = $image->store('images/products', 'public');
        } else {
            $imagePath = $product->image; // Nếu không có hình ảnh mới, giữ nguyên hình ảnh cũ
        }

        // Cập nhật thông tin sản phẩm
        $product->update([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'sale_price' => $request->input('sale_price'),
            'category_id' => $request->input('category_id'),
            'sale_start' => $request->input('sale_start', $product->sale_start),
            'sale_end' => $request->input('sale_end', $product->sale_end),
            'new_product' => $request->input('new_product', $product->new_product),
            'best_seller_product' => $request->input('best_seller_product', $product->best_seller_product),
            'featured_product' => $request->input('featured_product', $product->featured_product),
            'image' => $imagePath,
        ]);

        // Cập nhật hoặc thêm mới các biến thể của sản phẩm
        $variants = $request->input('variants', []);

        // Xóa các biến thể cũ không có trong yêu cầu cập nhật
        $existingVariantIds = $product->variants->pluck('id')->toArray();
        $newVariantIds = array_column($variants, 'id');
        $variantIdsToDelete = array_diff($existingVariantIds, $newVariantIds);

        ProductVariant::whereIn('id', $variantIdsToDelete)->delete();

        // Cập nhật hoặc thêm các biến thể mới
        foreach ($variants as $variant) {
            if (isset($variant['id'])) {
                // Nếu đã có variant, thì cập nhật
                $productVariant = ProductVariant::find($variant['id']);
                $productVariant->update([
                    'quantity' => $variant['quantity'],
                    'price' => $variant['price'],
                    'status' => $variant['status'] ?? 0,
                ]);
            } else {
                // Nếu chưa có variant, thì thêm mới
                $sku = strtoupper(str_replace(' ', '-', $product->name) . '-' . Str::random(5));
                $productVariant = ProductVariant::create([
                    'product_id' => $product->id,
                    'quantity' => $variant['quantity'],
                    'price' => $variant['price'],
                    'sku' => $sku,
                    'status' => $variant['status'] ?? 0,
                ]);
            }


        }

        return response()->json($product, 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);
        }

        // xoa cac bien the lien quan
        ProductVariant::where('product_id', $id)->delete();

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json(['message' => 'xóa thành công'], 200);
    }
    public function newproduct(){
        $products = Product::with('productVariants') // Tải các biến thể để kiểm tra số lượng
        ->where('new_product', 1) // Điều kiện new_product = 1
        ->where(function ($query) {
            // Kiểm tra số lượng sản phẩm hoặc số lượng sản phẩm biến thể
            $query->where('quantity', ">=", 1) // Số lượng sản phẩm đơn thể
                  ->orWhereHas('productVariants', function ($q) {
                      $q->where('quantity', ">=", 1); // Số lượng của biến thể
                  });
        })
        ->limit(10)
        ->get();

    if ($products->isEmpty()) {
        return response()->json([
            'message' => "Không có sản phẩm new_product với số lượng 1."
        ], 404);
    }

    $products->transform(function($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'image' => $product->image ? Storage::url($product->image) : null, // URL hình ảnh sản phẩm
            'quantity' => $product->quantity,
            'price' => $this->getCurrentPrice($product), // Lấy giá hiện tại
            'productVariants' => $product->productVariants->map(function($variant) {
                return [
                    'id' => $variant->id,
                    'color_id' => $variant->color_id,
                    'size_id' => $variant->size_id,
                    'quantity' => $variant->quantity,
                    'price' => $this->getCurrentPrice($variant), // Lấy giá hiện tại cho biến thể
                    'sale_price' => $variant->sale_price,
                    'sku' => $variant->sku,
                    'image' => $variant->image ? Storage::url($variant->image) : null, // URL hình ảnh biến thể
                ];
            }),
        ];
    });

    return response()->json([
        'products' => $products
    ]);
    }
    public function bestproduct(){
        $products = Product::with('productVariants') // Tải các biến thể để kiểm tra số lượng
        ->where('best_seller_product', 1) // Điều kiện best_seller_product = 1
        ->where(function ($query) {
            // Kiểm tra số lượng sản phẩm hoặc số lượng sản phẩm biến thể
            $query->where('quantity', ">=", 1) // Số lượng sản phẩm đơn thể
                  ->orWhereHas('productVariants', function ($q) {
                      $q->where('quantity', ">=", 1); // Số lượng của biến thể
                  });
        })
        ->limit(10)
        ->get();

    if ($products->isEmpty()) {
        return response()->json([
            'message' => "Không có sản phẩm best seller với số lượng 1."
        ], 404);
    }

    $products->transform(function($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'image' => $product->image ? Storage::url($product->image) : null, // URL hình ảnh sản phẩm
            'quantity' => $product->quantity,
            'price' => $this->getCurrentPrice($product), // Lấy giá hiện tại
            'productVariants' => $product->productVariants->map(function($variant) {
                return [
                    'id' => $variant->id,
                    'color_id' => $variant->color_id,
                    'size_id' => $variant->size_id,
                    'quantity' => $variant->quantity,
                    'price' => $this->getCurrentPrice($variant), // Lấy giá hiện tại cho biến thể
                    'sale_price' => $variant->sale_price,
                    'sku' => $variant->sku,
                    'image' => $variant->image ? Storage::url($variant->image) : null, // URL hình ảnh biến thể
                ];
            }),
        ];
    });

    return response()->json([
        'products' => $products
    ]);
    }
    public function featuredproduct(){
        $products = Product::with('productVariants') // Tải các biến thể để kiểm tra số lượng
        ->where('featured_product', 1) // Điều kiện featured_product = 1
        ->where(function ($query) {
            // Kiểm tra số lượng sản phẩm hoặc số lượng sản phẩm biến thể
            $query->where('quantity', ">=", 1) // Số lượng sản phẩm đơn thể
                  ->orWhereHas('productVariants', function ($q) {
                      $q->where('quantity', ">=", 1); // Số lượng của biến thể
                  });
        })
        ->limit(10)
        ->get();

    if ($products->isEmpty()) {
        return response()->json([
            'message' => "Không có sản phẩm featured_product với số lượng 1."
        ], 404);
    }

    $products->transform(function($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'image' => $product->image ? Storage::url($product->image) : null, // URL hình ảnh sản phẩm
            'quantity' => $product->quantity,
            'price' => $this->getCurrentPrice($product), // Lấy giá hiện tại
            'productVariants' => $product->productVariants->map(function($variant) {
                return [
                    'id' => $variant->id,
                    'color_id' => $variant->color_id,
                    'size_id' => $variant->size_id,
                    'quantity' => $variant->quantity,
                    'price' => $this->getCurrentPrice($variant), // Lấy giá hiện tại cho biến thể
                    'sale_price' => $variant->sale_price,
                    'sku' => $variant->sku,
                    'image' => $variant->image ? Storage::url($variant->image) : null, // URL hình ảnh biến thể
                ];
            }),
        ];
    });

    return response()->json([
        'products' => $products
    ]);
    }
    private function getCurrentPrice($item)
{
    $now = now();
    if ($item->sale_price && $item->sale_start <= $now && $item->sale_end >= $now) {
        return $item->sale_price; // Nếu giá sale còn hiệu lực
    }

    return $item->price; // Nếu không, trả về giá gốc
}
    public function filterProductByColor(){
        $product = Product::with('productVariants')->where('color_id')->get();
        // nếu không tìm thấy sản phẩm
        if(!$product){
            return response()->json([
                'message'=>"Không tìm thấy sản phẩm"], 404);
        }
        return response()->json([
            'product'=>$product
        ]);
    }
    public function filterProducts(Request $request)
    {
        $colorId = $request->input('color_id');
        $sizeId = $request->input('size_id');
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');

        $query = Product::query();

        $query->whereHas('productVariants', function($q) use ($colorId, $sizeId, $minPrice, $maxPrice) {
            if ($colorId) {
                $q->where('color_id', $colorId);
            }
            if ($sizeId) {
                $q->where('size_id', $sizeId);
            }
            if ($minPrice !== null && $maxPrice !== null) {
                $q->whereBetween('price', [$minPrice, $maxPrice]);
            } elseif ($minPrice !== null) {
                $q->where('price', '>=', $minPrice);
            } elseif ($maxPrice !== null) {
                $q->where('price', '<=', $maxPrice);
            }
        });

        $products = $query->get();

        return response()->json($products);
    }

}
