<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Stock;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:users');

        $this->middleware(function ($request, $next) {
            $id = $request->route()->parameter('item'); 
                if(!is_null($id)){ 
                    $ItemId = Product::availableItems($id)->where('products.id, $id')->id->exists(); 
                    if(!$ItemId){ 
                        abort(404); 
                    }
                }    
            return $next($request);
        });
    }    

    public function index(Request $request)
    {
        $products = Product::availableItems()
        ->sortOrder($request->sort)
        ->get();
        
        return view('user.index', compact('products'));
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        $quantity = Stock::where('product_id', $product->id)
        ->sum('quantity');

        if($quantity > 9){ 
            $quantity = 9;
        }

        return view('user.show', 
        compact('product', 'quantity'));
    }

}
