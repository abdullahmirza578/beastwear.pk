<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function show(): View
    {
        return view('admin.inventory.index');
    }

    public function index(): JsonResponse
    {
        $products = Product::with(['category', 'images', 'variants'])
            ->withSum([
                'orderItems as sold_quantity' => fn ($query) => $query->whereHas(
                    'order',
                    fn ($orders) => $orders->whereNotIn('order_status', ['cancelled', 'returned'])
                ),
            ], 'quantity')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => $this->serialize($product));

        return response()->json([
            'categories' => $products
                ->filter(fn (array $product) => $product['category'] !== null)
                ->map(fn (array $product) => $product['category'])
                ->unique('slug')
                ->values(),
            'products' => $products->values(),
        ]);
    }

    public function updateStock(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'mode' => ['required', 'in:add,remove,set'],
            'quantity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'reason' => ['required', 'string', 'in:New stock received,Stock count correction,Damaged / defective,Returned by customer,Manual adjustment'],
        ]);

        $updatedProduct = DB::transaction(function () use ($data, $product, $request): Product {
            $product = Product::query()->lockForUpdate()->findOrFail($product->id);
            $variants = $product->variants()->orderBy('id')->lockForUpdate()->get();
            $currentStock = $variants->isNotEmpty()
                ? (int) $variants->sum('stock_quantity')
                : (int) $product->stock_quantity;
            $targetStock = match ($data['mode']) {
                'add' => $currentStock + $data['quantity'],
                'remove' => max(0, $currentStock - $data['quantity']),
                'set' => $data['quantity'],
            };

            if ($targetStock > 1000000) {
                throw ValidationException::withMessages([
                    'quantity' => 'The resulting stock quantity may not exceed 1,000,000.',
                ]);
            }

            if ($variants->isNotEmpty()) {
                $allocations = $this->distributeStock($variants->pluck('stock_quantity')->all(), $targetStock);
                foreach ($variants as $index => $variant) {
                    $variant->update(['stock_quantity' => $allocations[$index]]);
                }
            }

            $product->update(['stock_quantity' => $targetStock]);
            DB::table('product_stock_adjustments')->insert([
                'product_id' => $product->id,
                'admin_id' => $request->user()->id,
                'stock_before' => $currentStock,
                'stock_after' => $targetStock,
                'reason' => $data['reason'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $product->fresh()->load(['category', 'images', 'variants']);
        });

        return response()->json($this->serialize($updatedProduct));
    }

    public function updateThreshold(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'lowStock' => ['required', 'integer', 'min:0', 'max:1000000'],
        ]);

        $product->update(['low_stock_threshold' => $data['lowStock']]);

        return response()->json($this->serialize($product->fresh()->load(['category', 'images', 'variants'])));
    }

    private function distributeStock(array $currentStocks, int $targetStock): array
    {
        $currentTotal = array_sum($currentStocks);
        $weights = $currentTotal > 0
            ? array_map(fn (int $stock): float => $stock / $currentTotal, $currentStocks)
            : array_fill(0, count($currentStocks), 1 / count($currentStocks));
        $exactAllocations = array_map(fn (float $weight): float => $weight * $targetStock, $weights);
        $allocations = array_map('intval', $exactAllocations);
        $remaining = $targetStock - array_sum($allocations);
        $remainders = array_map(
            fn (float $exact, int $allocated): float => $exact - $allocated,
            $exactAllocations,
            $allocations
        );
        arsort($remainders);

        foreach (array_slice(array_keys($remainders), 0, $remaining) as $index) {
            $allocations[$index]++;
        }

        return $allocations;
    }

    private function serialize(Product $product): array
    {
        $image = $product->images->firstWhere('is_primary', true) ?? $product->images->first();
        $stock = $product->variants->isNotEmpty()
            ? (int) $product->variants->sum('stock_quantity')
            : (int) $product->stock_quantity;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'image' => $image?->url ?? '',
            'sizes' => $product->variants->pluck('size')->filter()->unique()->values(),
            'stock' => $stock,
            'sold' => (int) ($product->sold_quantity ?? 0),
            'lowStock' => (int) $product->low_stock_threshold,
            'costPrice' => (float) ($product->cost_price ?? 0),
            'price' => (float) $product->price,
            'salePrice' => $product->sale_price === null ? null : (float) $product->sale_price,
        ];
    }
}
