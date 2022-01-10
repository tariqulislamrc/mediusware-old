<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File as StorageFile;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|Response|View
     */
    public function index()
    {
        session()->put('product_index_url', URL::full());
        $data['products'] = Product::with(['variantPrices.productVariantOne', 'variantPrices.productVariantTwo', 'variantPrices.productVariantThree'])
            ->when(request('title'), function ($q) {
                $q->where('title', 'like', '%' . request('title') . '%');
            })
            ->when(request('date'), function ($q) {
                $date = Carbon::parse(request('date'))->format('Y-m-d');
                $q->whereDate('created_at', $date);
            })
            ->when(request('variant'), function ($q) {
                $q->with(['variantPrices' => function ($q) {
                    return $q->when(request('price_from'), function ($query) {
                        return $query->where('price', '>=', request('price_from'));
                    })->when(request('price_to'), function ($query) {
                        return $query->where('price', '<=', request('price_to'));
                    });
                }])
                    ->whereHas('productVariants', function ($q) {
                        return $q->where('variant', request('variant'));
                    });
            })
            ->when(request('price_from') || request('price_to'), function ($q) {
                $q->whereHas('variantPrices', function ($q) {
                    return $q->when(request('price_from'), function ($query) {
                        return $query->where('price', '>=', request('price_from'));
                    })->when(request('price_to'), function ($query) {
                        return $query->where('price', '<=', request('price_to'));
                    });
                });
            })
            ->latest()->paginate(2);

        $data['variants'] = Variant::latest()->with(['productVariants' => function ($q) {
            return $q->groupBy('variant');
        }])->whereHas('productVariants')->get();
        return view('products.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public
    function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public
    function store(ProductRequest $request)
    {
        $product = Product::create($request->only('title', 'description', 'sku'));

        $this->uploadProductImages($request->product_image, $product);

        $tags = $this->productVariant($request->product_variant, $product);

        $this->productVariantPrice($request->product_variant_prices, $product, $tags);

        return response()->json(['message' => 'Product created successfully.']);

    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return Response
     */
    public
    function show($product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return Response
     */
    public
    function edit($product)
    {
        $product = new ProductResource(Product::with(['images', 'variantPrices.productVariantOne', 'variantPrices.productVariantTwo', 'variantPrices.productVariantThree', 'productVariants'])->find($product));

        $variants = Variant::all();
        return view('products.edit', compact('variants', 'product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public
    function update(ProductRequest $request, Product $product)
    {
        $product->update($request->only('title', 'description', 'sku'));

        foreach ($product->images as $image) {
            StorageFile::delete(storage_path($image->file_path, 'public'));
            $image->delete();
        }
        $this->uploadProductImages($request->product_image, $product);

        $tags = $this->productVariant($request->product_variant, $product, true);

        $this->productVariantPrice($request->product_variant_prices, $product, $tags, true);

        return response()->json(['message' => 'Product updated successfully.', 'goto' => session('product_index_url', route('product.index'))]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return Response
     */
    public
    function destroy(Product $product)
    {
        //
    }


    private function productVariant($product_variant, $product, $update = false): array
    {
        $tags = [];
        foreach ($product_variant as $key => $variant) {
            $updateProductVariant = [];
            foreach ($variant['tags'] as $tag) {
                $productVariant = ProductVariant::firstOrCreate([
                    'variant' => $tag,
                    'product_id' => $product->id,
                    'variant_id' => $variant['option']
                ]);
                $tags[$key][$tag] = $productVariant->id;
                $updateProductVariant [] = $productVariant->id;
            }
            if ($update) {
                ProductVariant::whereNotIn('id', $updateProductVariant)->where('product_id', $product->id)->where('variant_id', $variant['option'] ?? null)->delete();
            }
        }
        return $tags;
    }

    private function productVariantPrice($product_variant_prices, $product, array $tags, $update = false)
    {
        $updatedVariantPrices = [];
        foreach ($product_variant_prices as $product_variant_price) {
            $title = explode('/', rtrim($product_variant_price['title'], '/'));
            $title = explode('/', rtrim($product_variant_price['title'], '/'));
            $productVariantPrice = [
                'product_id' => $product->id,
            ];

            foreach ($title as $key => $data) {
                $productVariantPrice[$this->getVariantKey($key)] = $tags[$key][$data];
            }
            $variantPrice = ProductVariantPrice::firstOrCreate($productVariantPrice);

            $updatedVariantPrices[] = $variantPrice->id;
            $variantPrice->update([
                'price' => $product_variant_price['price'],
                'stock' => $product_variant_price['stock'],
            ]);

            if ($update) {
                ProductVariantPrice::whereNotIn('id', $updatedVariantPrices)->where('product_id', $product->id)->delete();
            }
        }
    }

    private function getVariantKey($key)
    {
        switch ($key) {
            case 0:
                return 'product_variant_one';
            case 1:
                return 'product_variant_two';
            case 2:
                return 'product_variant_three';
        }
    }

    private function uploadProductImages($product_image, $product)
    {
        if ($product_image) {
            foreach ($product_image as $image) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'file_path' => uploadBase64Image($image, 'product')
                ]);
            }
        }
    }
}
