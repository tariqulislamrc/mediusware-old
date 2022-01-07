<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
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
            ->whereHas('variantPrices', function ($q) {
                return $q->when(request('price_from'), function ($query) {
                    return $query->where('price', '>=', request('price_from'));
                })->when(request('price_to'), function ($query) {
                    return $query->where('price', '<=', request('price_to'));
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
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
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

        if ($request->product_image) {
            foreach ($request->product_image as $image) {
                $fileName = date('Ymd') . '-' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move('upload', $fileName);
                ProductImage::query()->create([

                    'product_id' => $product->id,
                    'file_path' => 'upload/' . $fileName,
                    'thumbnail' => true

                ]);
            }
        }

        $tags = [];

        foreach ($request->product_variant as $key => $variant) {
            foreach ($variant['tags'] as $tag_key => $tag) {
                $productVariant = ProductVariant::create([
                    'variant' => $tag,
                    'product_id' => $product->id,
                    'variant_id' => $variant['option'] ?? null
                ]);
                $tags[$key][$tag] = $productVariant->id;
            }
        }

        foreach ($request->product_variant_prices as $product_variant_price) {
            $title = explode('/', rtrim($product_variant_price['title'], '/'));
            $productVariantPrice = [
                'price' => $product_variant_price['price'],
                'stock' => $product_variant_price['stock'],
                'product_id' => $product->id,
            ];

            foreach ($title as $key => $data) {
                if ($key == 0) {
                    $productVariantPrice['product_variant_one'] = $tags[$key][$data];
                }
                if ($key == 1) {
                    $productVariantPrice['product_variant_two'] = $tags[$key][$data];
                }
                if ($key == 2) {
                    $productVariantPrice['product_variant_three'] = $tags[$key][$data];
                }
            }

            ProductVariantPrice::create($productVariantPrice);
        }

        return response()->json(['success' => 'Product created successfully.']);

    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public
    function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public
    function edit(Product $product)
    {
        $variants = Variant::all();
        return view('products.edit', compact('variants'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public
    function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public
    function destroy(Product $product)
    {
        //
    }
}
