<?php 
namespace App\Http\Controllers;
 
 
 
use Illuminate\Http\Request;
 
use DB;
 
 
 
 
class SearchController extends Controller
 
{
 
   public function index()
 
{
 
return view('search.search');
 
}
 
 
 
public function search(Request $request)
 
{
 
if($request->ajax())
 
{
 
$output="";
 
$products=DB::table('Student')->where('firstName','LIKE','%'.$request->search."%")->orwhere('lastName','LIKE','%'.$request->search."%")->orwhere('fatherName','LIKE','%'.$request->search."%")->orwhere('b_form','LIKE','%'.$request->search."%")
->orwhere('session','LIKE','%'.$request->search."%")->orwhere('class','LIKE','%'.$request->search."%")->orwhere('dob','LIKE','%'.$request->search."%")
->orwhere('parmanentAddress','LIKE','%'.$request->search."%")->orwhere('discount_id','LIKE','%'.$request->search."%")
->get();
 return Response($output);
 
   }
 
 
   }
 
 
 
}
 
}