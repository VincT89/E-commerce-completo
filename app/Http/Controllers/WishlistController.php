<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;

class WishlistController extends Controller
{

    public function getWishlistedProducts()
    {
        $item = Cart::instance('wishlist')->content();
        return view('wishlist', ['items' => $item]);
    }

    public function addProductToWishlist(Request $request)
    {
        Cart::instance('wishlist')->add($request->id, $request->name, 1, $request->price)->associate('App\Models\Product');
        return response()->json(['status' => 200, 'message' => 'Product added to wishlist']);
    }

    public function removeProductFromWishlist(Request $request)
    {
        $rowId = $request->rowId;
        Cart::instance('wishlist')->remove($rowId); 
        return redirect()->route('wishlist.list')->with('success', 'Product removed from wishlist');
    }

    public function clearWishlist()
    {
        Cart::instance('wishlist')->destroy();
        return redirect()->route('wishlist.list')->with('success', 'Wishlist cleared');
    }

    public function moveToCart(Request $request)
    {
        $item = Cart::instance('wishlist')->get($request->rowId);
        Cart::instance('wishlist')->remove($request->rowId);
        Cart::instance('cart')->add($item->model->id, $item->model->name, 1, $item->model->regular_price)->associate('App\Models\Product');
        return redirect()->route('wishlist.list')->with('success', 'Product moved to cart');
    }
}
