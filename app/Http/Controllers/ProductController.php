<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Stripe;




class ProductController extends Controller
{
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function index()
    {
        $products = Product::all();
        return view('products', compact('products'));
    }
  
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function cart()
    {
        return view('cart');
    }

    public function checkout()
    {
        return view('checkout');
    }


    public function order(Request $request): RedirectResponse
    {
        
        if (session('cart')){
            $carts = session('cart');
            $total = 0 ;
            $customerID = 1;
            foreach($carts as $id=>$details){
                echo  $id . "<br>" ;
                $total += $details['price'] * $details['quantity'];
            }
        }

        $order_data = [
            'totalamount'=> $total,
            'customer_id'=> $customerID,
            'coupon_discount'=> 0,
            'paymentMethod'=> 'stripe',
        ] ;
        $order_id = Order::insertGetId($order_data) ;
        echo $order_id ;


foreach ( $carts as $id => $details ){
    $order_details_data = [
        'order_id'=> $order_id,
        'product_id'=> $id,
        'product_qty'=> $details['quantity'],
        'product_price'=> $details['price'],
        'product_subtotal'=> $details['quantity'] * $details['price'],
        'product_discount'=> 0,
    ] ;
    OrderDetails::insert($order_details_data) ;
}
        // dd($carts);

        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
      
        Stripe\Charge::create ([
                "amount" =>$total * 100 ,
                "currency" => "usd",
                "source" => $request->stripeToken,
                "description" => "Test payment from itsolutionstuff.com." 
        ]);
                
        return back()
                ->with('success', 'Payment successful!');
    }
  
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function addToCart($id)
    {
        $product = Product::findOrFail($id);
          
        $cart = session()->get('cart', []);
  
        if(isset($cart[$id])) {
            $cart[$id]['quantity']++;
        } else {
            $cart[$id] = [
                "name" => $product->name,
                "quantity" => 1,
                "price" => $product->price,
                "image" => $product->image
            ];
        }
          
        session()->put('cart', $cart);
        return redirect()->back()->with('success', 'Product added to cart successfully!');
    }
  
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function update(Request $request)
    {
        if($request->id && $request->quantity){
            $cart = session()->get('cart');
            $cart[$request->id]["quantity"] = $request->quantity;
            session()->put('cart', $cart);
            session()->flash('success', 'Cart updated successfully');
        }
    }
  
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function remove(Request $request)
    {
        if($request->id) {
            $cart = session()->get('cart');
            if(isset($cart[$request->id])) {
                unset($cart[$request->id]);
                session()->put('cart', $cart);
            }
            session()->flash('success', 'Product removed successfully');
        }
    }



    
}
