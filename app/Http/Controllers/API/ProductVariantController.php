<?php
namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;


class ProductVariantController extends Controller
{

    public function index()
    {
        return ProductVariant::all();
    }

    public function show($id)
    {
        $productVariant = ProductVariant::findOrFail($id);
        return response()->json($productVariant);
    }
    
    public function store(Request $request)
    {

        // Define validation rules
        $rules = [
            'product_id' => 'required|integer|exists:products,id',
            'colors' => 'required|array|min:1',
            'colors.*' => 'required|integer|exists:colors,id',
            'sizes' => 'required|array|min:1',
            'sizes.*' => 'required|integer|exists:sizes,id',
            'quantities' => 'required|array',
            'quantities.*' => 'required|integer|min:0',
            'prices' => 'required|array',
            'prices.*' => 'required|numeric|min:0',
            'status' => 'required|boolean',
            'images' => 'sometimes|array',
            'images.*' => 'nullable|string', // Adjust based on your image handling
        ];

        // Validate the request
        $validatedData = $request->validate($rules);

        // Ensure 'colors' and 'sizes' arrays have the same length
        if (count($validatedData['colors']) !== count($validatedData['sizes'])) {
            return response()->json([
                'message' => 'The number of colors and sizes must match.'
            ], 422);
        }

        $createdVariants = [];

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Iterate through each color-size pair using a single loop
            for ($i = 0; $i < count($validatedData['colors']); $i++) {
                $color_id = $validatedData['colors'][$i];
                $size_id = $validatedData['sizes'][$i];
                $quantityKey = "{$color_id}-{$size_id}";
                $priceKey = "{$color_id}-{$size_id}";

                $productVariant = new ProductVariant();
                $productVariant->product_id = $validatedData['product_id'];
                $productVariant->color_id = $color_id;
                $productVariant->size_id = $size_id;
                $productVariant->quantity = $validatedData['quantities'][$quantityKey] ?? 0;
                $productVariant->price = $validatedData['prices'][$priceKey] ?? 0;
                $productVariant->status = $validatedData['status'];

                // Generate a unique SKU
                $randomString = Str::upper(Str::random(5)); // Uppercase for consistency
                $productVariant->sku = "SKU-{$validatedData['product_id']}-{$color_id}-{$size_id}-{$randomString}";

                // Assign image if available
                if (isset($validatedData['images'][$quantityKey])) {
                    $productVariant->image = $validatedData['images'][$quantityKey];
                }

                $productVariant->save();
                $createdVariants[] = $productVariant;
            }

            // Commit the transaction if all variants are saved successfully
            DB::commit();

            return response()->json([
                'message' => 'Product variants created successfully.',
                'variants' => $createdVariants
            ], 201);

        } catch (\Exception $e) {
            // Rollback the transaction in case of any errors
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create product variants.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'color_id' => 'required|integer',
            'size_id' => 'required|integer',
            'quantity' => 'required|integer',
            'price' => 'required|numeric',
            'sku' => 'required|string|unique:product_variants,sku,' . $id,
            'status' => 'required|boolean',
            'image' => 'nullable|string',
        ]);

        $productVariant = ProductVariant::findOrFail($id);
        $productVariant->product_id = $request->product_id;
        $productVariant->color_id = $request->color_id;
        $productVariant->size_id = $request->size_id;
        $productVariant->quantity = $request->quantity;
        $productVariant->price = $request->price;
        $productVariant->sku = $request->sku;
        $productVariant->status = $request->status;

        // Cập nhật trường ảnh
        if ($request->has('image')) {
            $productVariant->image = $request->image;
        }

        $productVariant->save();
        return response()->json($productVariant);
    }

    public function destroy($id)
    {
        $productVariant = ProductVariant::findOrFail($id);
        $productVariant->delete();
        return response()->json(null, 204);
    }

}