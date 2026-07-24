<?php

namespace App\Services;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    public function placeOrder($userId, $products)
    {

        return DB::transaction(function () use ($products, $userId) {

            $totalPrice = 0;
            $orderItems = [];

            foreach ($products as $productData) {
                $product = Product::lockForUpdate()->find($productData['product_id']);

                if (!$product) {
                    throw new Exception("Product with ID {$productData['product_id']} not found.");
                }

                if ($product->stock < $productData['quantity']) {
                    throw new Exception("Insufficient stock for product: {$product->name}");
                }

                $subtotal = $product->price * $productData['quantity'];
                $totalPrice += $subtotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $productData['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ];

                // Update product stock
                $product->stock -= $productData['quantity'];
                $product->save();
            }

            // Create the order
            $order = Order::create([
                'user_id' => $userId,
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            // Create order items
            foreach ($orderItems as &$item) {
                $item['order_id'] = $order->id;
            }
            OrderItem::insert($orderItems);

            return $order;
        });
    }
}
