<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        //inizializzazione variabili per la paginazione
        $page = $request->query("page") ?? 1;
        $size = $request->query("size") ?? 12;
        $order = $request->query("order") ?? -1;

        //inizializzazione variabili per l'ordinamento
        $o_column = "";
        $o_order = "";

        //switch per l'ordinamento dei prodotti
        switch ($order) {
            case 1:
                $o_column = "created_at";
                $o_order = "DESC";
                break;
            case 2:
                $o_column = "created_at";
                $o_order = "ASC";
                break;
            case 3:
                $o_column = "regular_price";
                $o_order = "ASC";
                break;
            case 4:
                $o_column = "regular_price";
                $o_order = "DESC";
                break;
            default:
                $o_column = "id";
                $o_order = "DESC";
        }
        //recupero i brand tramite la query
        $brands = Brand::orderBy('name', 'ASC')->get();

        //recupero i brand selezionati dall'utente tramite la query
        $q_brands = $request->query("brands");

        //recupero le categorie tramite la query
        $categories = Category::orderBy("name", "ASC")->get();

        //recupero le categorie selezionate dall'utente tramite la query
        $q_categories = $request->query("categories");

        //recupero il prezzo minimo e massimo tramite la query
        $prange = $request->query("prange");

        if (!$prange)
            $prange = "0,500";

        $from = explode(",", $prange)[0];
        $to = explode(",", $prange)[1];

        //recupero i prodotti  selezionati, ordinati e paginati
        $products = Product::where(function ($query) use ($q_brands) {
            $query->whereIn('brand_id', explode(',', $q_brands))
                ->orWhereRaw("'" . $q_brands . "'=''");
        })
            ->where(function ($query) use ($q_categories) {
                $query->whereIn('category_id', explode(',', $q_categories))
                    ->orWhereRaw("'" . $q_categories . "'=''");
            })->whereBetween('regular_price', array($from, $to))
            ->orderBy('created_at', 'DESC')
            ->orderBy($o_column, $o_order)
            ->paginate($size);


        return view('shop', [
            'products' => $products,
            'page' => $page,
            'size' => $size,
            'order' => $order,
            'brands' => $brands,
            'q_brands' => $q_brands,
            'categories' => $categories,
            'q_categories' => $q_categories,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function productDetails($slug)
    {
        $products = Product::where('slug', $slug)->first();

        $rproducts = Product::where('slug', '!=', $slug)->inRandomOrder('id')->get()->take(8);

        return view('details', [
            'product' => $products,
            'rproducts' => $rproducts
        ]);
    }

    public function getCartAndWishlistCount()
    {
        $cartCount = Cart::instance('cart')->content()->count();
        $wishlistCount = Cart::instance('wishlist')->content()->count();

        return response()->json([
            'status' => 200,
            'cartCount' => $cartCount,
            'wishlistCount' => $wishlistCount
        ]);
    }
}
