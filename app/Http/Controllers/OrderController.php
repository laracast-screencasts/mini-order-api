<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use OpenApi\Attributes as OA;
use Exception;
use App\Services\OrderService;

class OrderController extends Controller
{
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    #[OA\Post(
        path: "/orders",
        summary: "Place Order",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["products"],
                properties: [
                    new OA\Property(
                        property: "products",
                        type: "array",
                        items: new OA\Items(
                            properties: [
                                new OA\Property(
                                    property: "product_id",
                                    type: "integer",
                                    example: 1
                                ),
                                new OA\Property(
                                    property: "quantity",
                                    type: "integer",
                                    example: 2
                                )
                            ]
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Order placed successfully"
            ),
            new OA\Response(
                response: 400,
                description: "Insufficient stock"
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
    public function placeOrder(OrderRequest $request)
    {
        try {
            $order = $this->orderService->placeOrder(auth()->id(), $request->products);

            return response()->json(['message' => 'Order placed successfully.', 'order_id' => $order->id], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Order creation failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    #[OA\Get(
        path: "/orders",
        summary: "Get Orders",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success"

            )
        ]
    )]
    public function getOrders()
    {
        $orders = auth()->user()->orders()->with('orderItems.product')->get();

        return response()->json($orders, 200);
    }

    #[OA\Get(
        path: "/orders/{id}",
        summary: "Get Order Details",
        tags: ["Orders"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "Order ID",
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success"
            ),
            new OA\Response(
                response: 404,
                description: "Order not found"
            )
        ]
    )]
    public function getOrderDetails($id)
    {
        $order = auth()->user()->orders()->with('orderItems.product')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json($order, 200);
    }
}
