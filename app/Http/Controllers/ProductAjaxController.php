<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use DataTables;

class ProductAjaxController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTotal(){
        $total = 0;
        $productData = Product::latest()->get();

        foreach($productData as $pro){
            $pData =  json_decode($pro->data);
            $total += $pData->price * $pData->quantity;   
        }
        return $total;
    }
    public function index(Request $request)
    {
        $total = $this->getTotal();

        if ($request->ajax()) {
            $product = new Product();
            $data = Product::latest()->get();         
           
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('name', function ($data) {
                        $pname =  json_decode($data->data);                      
                            return $pname->name;    
                    })
                    ->addColumn('quantity', function ($data) {
                        $pquantity =  json_decode($data->data);                                           
                            return $pquantity->quantity;    
                    })
                    ->addColumn('price', function ($data) {
                        $pprice =  json_decode($data->data);                      
                            return '$'.$pprice->price;    
                    })
                   
                    ->editColumn('created_at', function($data) {
                        return date('Y-m-d ', strtotime($data->created_at));
                    })
                    ->addColumn('total_value_number', function ($data) {
                        $tData =  json_decode($data->data);
                       $total_value_number =$tData->quantity * $tData->price;                        
                            return '$'.$total_value_number;    
                    })
                   
                    ->addColumn('action', function($row){
   
                           $btn = '<a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Edit" class="edit btn btn-primary btn-sm editProduct">Edit</a>';
   
                           $btn = $btn.' <a href="javascript:void(0)" data-toggle="tooltip"  data-id="'.$row->id.'" data-original-title="Delete" class="btn btn-danger btn-sm deleteProduct">Delete</a>';
    
                            return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }
      
        return view('productAjax',compact('total'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

      $data = $request->only('name','quantity','price');

     if(isset($request->product_id)){

        $udata['data'] = json_encode($data);
        $udata['created_at'] = now();
        $udata['updated_at'] = now();
        Product::where('id' , $request->product_id)->update($udata);
     }else{
        $test['data'] = json_encode($data);
        $test['created_at'] = now();
        $test['updated_at'] = now();
        Product::insert($test);
     }
      
     $total = $this->getTotal();
   
        return response()->json(['success'=>'Product saved successfully.','total'=>$total]);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $array =[];
        $product = Product::find($id);
        
        $pData =  json_decode($product->data);
        $array['id'] = $product->id;
        $array['data'] = $pData;
        return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Product::find($id)->delete();
        $total = $this->getTotal();
     
        return response()->json(['success'=>'Product deleted successfully.','total'=>$total]);
    }
}