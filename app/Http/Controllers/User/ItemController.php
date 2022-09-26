<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Stock;
use App\Models\PrimaryCategory;
use App\Mail\TestMail;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendThanksMail;

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
        // dd($request);
        // 同期的に送信
        // Mail::to('test@example.com')
        // ->send(new TestMail());

        // 非同期に送信
        SendThanksMail::dispatch();


        $categories = PrimaryCategory::with('secondary')
        ->get();

        $products = Product::availableItems()
        ->selectCategory($request->category ?? '0')
        ->searchKeyword($request->keyword)
        ->sortOrder($request->sort)
        ->paginate($request->pagination ?? '20');
        
        return view('user.index', 
        compact('products', 'categories'));
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
