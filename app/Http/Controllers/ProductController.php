<?php

namespace App\Http\Controllers;

use App\Models\Product;
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


        $data['variants'] = ProductVariant::latest()->get()->unique('variant');
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
    function store(Request $request)
    {

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
