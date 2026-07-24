<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    #[OA\Get(
        path: "/products",
        summary: "Get Products",
        tags: ["Products"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "search",
                in: "query",
                required: false,
                description: "Search product by name",
                schema: new OA\Schema(
                    type: "string",
                )
            ),
            new OA\Parameter(
                name: "min_price",
                in: "query",
                required: false,
                description: "Filter products with price greater than or equal to min_price",
                schema: new OA\Schema(
                    type: "number",
                    format: "float",
                )
            ),
            new OA\Parameter(
                name: "max_price",
                in: "query",
                required: false,
                description: "Filter products with price less than or equal to max_price",
                schema: new OA\Schema(
                    type: "number",
                    format: "float",
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success"

            ),
            new OA\Response(
                response: 429,
                description: "Too many requests"
            )
        ]
    )]
    public function index(Request $request)
    {
        $key = 'products_' . md5($request->fullUrl());

        $products = Cache::tags(['products'])->remember($key, 3600, function () use ($request) {
            $products = Product::query();

            if ($request->has('search')) {
                $search = $request->input('search');
                $products->where('name', 'ilike', "%{$search}%")
                        ->orWhere('description', 'ilike', "%{$search}%");
            }

            if($request->min_price){
                $products->where('price','>=',$request->min_price);
            }

            if($request->max_price){
                $products->where('price','<=',$request->max_price);
            }

            return $products->paginate(10)->toArray();
        });

        return response()->json([
            'message' => 'Products retrieved successfully.',
            'products' => $products
        ],200);
    }

    #[OA\Post(
        path: "/products",
        summary: "Create Product",
        tags: ["Products"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "price"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Product Name"),
                    new OA\Property(property: "description", type: "string", example: "Product description"),
                    new OA\Property(property: "stock", type: "integer", example: 100),
                    new OA\Property(property: "price", type: "number", format: "float", example: 19.99)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Product created successfully"
            ),
            new OA\Response(
                response: 422,
                description: "Validation error"
            )
        ]
    )]
    public function create(ProductRequest $request)
    {
        $product = Product::create($request->validated());

        Cache::tags(['products'])->flush();

        return response()->json([
            'message' => 'Product created successfully.',
            'product' => $product
        ], 201);
    }

    #[OA\Get(
        path: "/products/{id}",
        summary: "Get Product by ID",
        tags: ["Products"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID of the product to retrieve",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success"

            )
        ]
    )]
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found.'
            ], 404);
        }

        return response()->json([
            'message' => 'Product retrieved successfully.',
            'product' => $product
        ], 200);
    }

    #[OA\Put(
        path: "/products/{id}",
        summary: "Update Product",
        tags: ["Products"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID of the product to update",
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "price"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Product Name"),
                    new OA\Property(property: "description", type: "string", example: "Product description"),
                    new OA\Property(property: "stock", type: "integer", example: 100),
                    new OA\Property(property: "price", type: "number", format: "float", example: 19.99)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Product updated successfully"
            ),
            new OA\Response(
                response: 404,
                description: "Product not found"
            ),
            new OA\Response(
                response: 422,
                description: "Validation error"
            )
        ]
    )]
    public function update(ProductRequest $request, $id)
    {
        $product = Product::find($id);

        Cache::tags(['products'])->flush();

        if (!$product) {
            return response()->json([
                'message' => 'Product not found.'
            ], 404);
        }

        $product->update($request->validated());

        return response()->json([
            'message' => 'Product updated successfully.',
            'product' => $product
        ], 200);
    }

    #[OA\Delete(
        path: "/products/{id}",
        summary: "Delete Product",
        tags: ["Products"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID of the product to delete",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Product deleted successfully"
            ),
            new OA\Response(
                response: 404,
                description: "Product not found"
            )
        ]
    )]
    public function destroy($id)
    {
        $product = Product::find($id);

        Cache::tags(['products'])->flush();

        if (!$product) {
            return response()->json([
                'message' => 'Product not found.'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.'
        ], 204);
    }
}
