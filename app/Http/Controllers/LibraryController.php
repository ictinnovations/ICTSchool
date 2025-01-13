<?php
namespace App\Http\Controllers;
use DB;
use App\Models\Marks;
use App\Models\FeeCol;
use App\Models\AddBook;
use App\Models\Student;
use App\Models\Subject;
use App\Models\FeeSetup;
use App\Models\Institute;
use App\Models\Issuebook;
use App\Models\Accounting;
use App\Models\Attendance;
use App\Models\ClassModel;
use App\Models\FeeHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

class formfoo7{
}

class libraryController extends BaseController {
	public function __construct() {
		/*$this->beforeFilter('csrf', array('on'=>'post'));
		$this->beforeFilter('auth');
		$this->beforeFilter('userAccess',array('only'=> array('deleteBook','deleteissueBook')));*/
		$this->middleware('auth');
		 $this->middleware('auth', array('only'=>array('getAddbook','postAddbook','getviewbook','postviewbook','getBook','postUpdateBook')));

	}
	public function getAddbook()
	{
		//$classes = array('All'=>'All')+ClassModel::pluck('name','code');
		$classes = ClassModel::pluck('name','code');
		$classes = $classes->toArray('All' , 'All') ;
		///return View::Make('app.addbook',compact('classes'));
		return View('app.addbook',compact('classes'));
	}


	/**
	* Show the form for creating a new resource.
	*
	* @return Response
	*/
	public function postAddbook(Request $request)
	{
		$rules=[
			'code' => 'required|max:50',
			'title' => 'required|max:250',
			'author' => 'required|max:100',
			'type' => 'required',
			'class' => 'required'
		];
		$validator = \Validator::make($request->all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/library/addbook')->withErrors($validator)->withInput();
		}
		else {
			$book=AddBook::select('*')->where('code',$request->input('code'))->get();
			if(count($book)>0)
			{
				$errorMessages = new Illuminate\Support\MessageBag;
				$errorMessages->add('deplicate', 'Book Code allready exists!!');
				return Redirect::to('/library/addbook')->withInput()->withErrors($errorMessages);
			}
			else {
				$book = new AddBook();
				$book->code = $request->input('code');
				$book->title = $request->input('title');
				$book->author = $request->input('author');
				$book->quantity = $request->input('quantity');
				$book->rackNo = $request->input('rackNo');
				$book->rowNo = $request->input('rowNo');
				$book->type = $request->input('type');
				$book->class = $request->input('class');
				$book->desc = $request->input('desc');
				$book->save();
				return Redirect::to('/library/addbook')->with("success", "Book added to library Succesfully.");

			}
		}

	}


	/**
	* Store a newly created resource in storage.
	*
	* @return Response
	*/
	public function getviewbook()
	{
		//$classes = array('All'=>'All')+ClassModel::pluck('name','code');
		$classes = ClassModel::pluck('name','code');
		$classes = $classes->toArray('All' , 'All') ;
		$formdata = new formfoo7;
		$formdata->class = "";
		$books=array();
		//return View::Make('app.booklist',compact('classes','formdata','books'));
		return View('app.booklist',compact('classes','formdata','books'));
	}
	public function postviewbook(Request $request)
	{

		if($request->input('classcode')=="All"){
			$books=AddBook::leftJoin('Class', function($join) {
				$join->on('Books.class', '=', 'Class.code');
			})
			->select('Books.id', 'Books.code', 'Books.title', 'Books.author','Books.quantity','Books.rackNo','Books.rowNo','Books.type','Books.desc',DB::raw("IFNULL(Class.Name,'All') as class"))

			->orderBy('id', 'desc')->paginate(50);

		}
		else {

			$books = DB::table('Books')
			->join('Class', 'Books.class', '=', 'Class.code')
			->select('Books.id', 'Books.code', 'Books.title', 'Books.author','Books.quantity','Books.rackNo','Books.rowNo','Books.type','Books.desc','Class.Name as class')
			->where('Books.class',$request->input('classcode'))->orderBy('id', 'desc')->paginate(50);
		}
		//$books->setBaseUrl('view-show');
		$books->withPath('view-show');
		$classes = ClassModel::pluck('name','code');
		$classes = $classes->toArray('All' , 'All') ;
		//$classes = array('All' => 'All')+ClassModel::pluck('name','code');
		$formdata = new formfoo7;
		$formdata->class = $request->input('classcode');
		//return View::Make('app.booklist',compact('classes','formdata','books'));
		return View('app.booklist',compact('classes','formdata','books'));

	}


	/**
	* Display the specified resource.
	*
	* @param  int  $id
	* @return Response
	*/
	public function getBook($id)
	{
		//$classes = array('All' => 'All')+ClassModel::pluck('name','code');
		$classes = ClassModel::pluck('name','code');
		$classes = $classes->toArray('All' , 'All') ;
		$book= AddBook::select('*')->find($id);
		//return View::Make('app.bookedit',compact('classes','book'));
		return View('app.bookedit',compact('classes','book'));
	}


	/**
	* Show the form for editing the specified resource.
	*
	* @param  int  $id
	* @return Response
	*/
	public function postUpdateBook(Request $request)
	{
		$rules=[
			'code' => 'required|max:50',
			'title' => 'required|max:250',
			'author' => 'required|max:100',
			'type' => 'required',
			'class' => 'required'
		];
		$validator = \Validator::make($request->all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/library/edit/'.$request->input('id'))->withErrors($validator)->withInput();
		}
		else {

			$book = AddBook::find($request->input('id'));
			//$book->code = $request->input('code');
			$book->title = $request->input('title');
			$book->author = $request->input('author');
			$book->quantity = $request->input('quantity');
			$book->rackNo = $request->input('rackNo');
			$book->rowNo = $request->input('rowNo');
			$book->type = $request->input('type');
			$book->class = $request->input('class');
			$book->desc = $request->input('desc');
			$book->save();
			return Redirect::to('/library/view')->with("success", "Book updated Succesfully.");

		}

	}


	/**
	* Update the specified resource in storage.
	*
	* @param  int  $id
	* @return Response
	*/
	public function deleteBook($id)
	{
		$book = AddBook::find($id);
		$book->delete();
		return Redirect::to('/library/view')->with("success", "Book Deleted Succesfully.");
	}

	public function getissueBook()
	{
		//$students =['' => 'Select Student']+Student::select(DB::raw("CONCAT(firstName,' ',middleName,' ',lastName,'[',regiNo,']') as name,regiNo"))->pluck('name','regiNo');
		//$books = ['' => 'Select Book']+AddBook::select(DB::raw("CONCAT(title,'[',author,']') as name,code"))->pluck('name','code');

		$students = Student::select(DB::raw("CONCAT(firstName,' ',middleName,' ',lastName,'[',regiNo,']') as name,regiNo"))->pluck('name','regiNo');
        $students->prepend('Select Student', '')->toArray() ;

       $books = AddBook::select(DB::raw("CONCAT(title,'[',author,']') as name,code"))->pluck('name','code');
        $books->prepend('Select Book', '')->toArray() ;
		//return View::Make('app.bookissue',compact('students','books'));
		return View('app.bookissue',compact('students','books'));
	}

	public function postissueBook(Request $request)
	{

		$rules=[
			'regiNo' => 'required',
			'bookCode' => 'required',
			'quantity' => 'required',
			'issueDate' => 'required',
			'returnDate' => 'required',

		];
		$validator = \Validator::make($request->all(), $rules);
		if ($validator->fails())
		{
			return Redirect::to('/library/issuebook')->withErrors($validator)->withInput();
		}
		else {


			/*$availabeQuantity=DB::table('bookStock')->select('quantity')->where('code',$request->input('code'))->first();

			if($request->input('quantity')>$availabeQuantity->quantity)
			{
			$errorMessages = new Illuminate\Support\MessageBag;
			$errorMessages->add('deplicate', 'This book quantity not availabe right now!');
			return Redirect::to('/library/issuebook')->withErrors($errorMessages)->withInput();

		}*/
		$data=$request->all();
		$issueData = [];
		$now=\Carbon\Carbon::now();
		foreach ($data['bookCode'] as $key => $value){
			$issueData[] = [
				'regiNo' => $data['regiNo'],
				'issueDate' => $this->parseAppDate($data['issueDate']),
				'code' => $value,
				'quantity' => $data['quantity'][$key],
				'returnDate' => $this->parseAppDate($data['returnDate'][$key]),
				'fine' => $data['fine'][$key],
				'created_at' => $now,
				'updated_at' => $now,
			];

		}
		Issuebook::insert($issueData);
		/*  $issuebook = new Issuebook();
		$issuebook->code = $request->input('code');
		$issuebook->quantity = $request->input('quantity');
		$issuebook->regiNo = $request->input('regiNo');
		$issuebook->issueDate = $this->parseAppDate($request->input('issueDate'));
		$issuebook->returnDate = $this->parseAppDate($request->input('returnDate'));
		$issuebook->fine = $request->input('fine');
		$issuebook->save();*/
		return Redirect::to('/library/issuebook')->with("success","Succesfully book borrowed for '".$request->input('regiNo')."'.");

	}

}
public function getissueBookview()
{

	//return View::Make('app.bookissueview');
	return View('app.bookissueview');
}
public function postissueBookview(Request $request)
{

	if($request->input('status')!="")
	{
		$books = Issuebook::select('*')
		->Where('Status','=',$request->input('status'))
		->get();
		//return View::Make('app.bookissueview',compact('books'));
		return View('app.bookissueview',compact('books'));
	}
	if($request->input('regiNo')!="" || $request->input('code') !="" || $request->input('issueDate') !="" || $request->input('returnDate') !="")
	{

		$books = Issuebook::select('*')->where('regiNo','=',$request->input('regiNo'))
		->orWhere('code','=',$request->input('code'))
		->orWhere('issueDate','=',$this->parseAppDate($request->input('issueDate')))
		->orWhere('returnDate','=',$this->parseAppDate($request->input('returnDate')))

		->get();
		//return View::Make('app.bookissueview',compact('books'));
		return View('app.bookissueview',compact('books'));

	}
	else {

		return Redirect::to('/library/issuebookview')->with("error","Pleae fill up at least one feild!");

	}

}
public function getissueBookupdate($id)
{
	$book= Issuebook::find($id);
	//return View::Make('app.bookissueedit',compact('book'));
	return View('app.bookissueedit',compact('book'));
}
public function postissueBookupdate(Request $request)
{
	$rules=[
		'regiNo' => 'required|max:20',
		'code' => 'required|max:50',
		'issueDate' => 'required',
		'returnDate' => 'required',
		'status' => 'required',

	];
	$validator = \Validator::make($request->all(), $rules);
	if ($validator->fails())
	{
		return Redirect::to('/library/issuebookupdate/'.$request->input('id'))->withErrors($validator);
	}
	else {

		$book = Issuebook::find($request->input('id'));
		$book->code = $request->input('code');
		$book->regiNo = $request->input('regiNo');
		$book->issueDate = $this->parseAppDate($request->input('issueDate'));
		$book->returnDate = $this->parseAppDate($request->input('returnDate'));
		$book->fine = $request->input('fine');
		$book->Status = $request->input('status');
		$book->save();
		return Redirect::to('/library/issuebookview')->with("success","Succesfully book record updated.");

	}
}

public function deleteissueBook($id)
{
	$book= Issuebook::find($id);
	$book->delete();
	return Redirect::to('/library/issuebookview')->with("success","Succesfully book record deleted.");
}
public function getsearch()
{
	
	$classes = ClassModel::pluck('name', 'id');
	$classes->toArray('All' , 'All') ;
	//$classes = array('All' => 'All')+ClassModel::pluck('name','code');
	//dd($classes);
	//return View::Make('app.booksearch',compact('classes'));
	return View('app.booksearch',compact('classes'));
}
public function postsearch(Request $request)
{
	if($request->input('code')!="" || $request->input('title')!="" || $request->input('author') !="")
	{
		$query=AddBook::leftJoin('Class', function($join) {
			$join->on('Books.class', '=', 'Class.code');

		})
		->join('bookStock','Books.code', '=', 'bookStock.code')
		->select('Books.id', 'Books.code', 'Books.title', 'Books.author','bookStock.quantity','Books.rackNo','Books.rowNo','Books.type','Books.desc',DB::raw("IFNULL			(Class.Name,'All') as class"));
		if($request->input('code')!="") $query->where('Books.code','=',$request->input('code'));
		if($request->input('title')!="")$query->orWhere('Books.title','LIKE','%'.$request->input('title').'%');
		if($request->input('author') !="")$query->orWhere('Books.author','LIKE','%'.$request->input('author').'%');


		$books=$query->get();


		//$classes = array('All' => 'All')+ClassModel::pluck('name','code');
		$classes = ClassModel::pluck('name','code');
		$classes = $classes->toArray('All' , 'All') ;
		//return View::Make('app.booksearch',compact('books','classes'));
		return View('app.booksearch',compact('books','classes'));

	}
	else {

		return Redirect::to('/library/search')->with("error","Pleae fill up at least one feild!");

	}
}
public function postsearch2(Request $request)
{
	$rules=[
		'type' => 'required',
		'class' => 'required',


	];
	$validator = \Validator::make($request->all(), $rules);
	if ($validator->fails())
	{
		return Redirect::to('/library/search')->withErrors($validator);
	}
	else {
		if($request->input('class')=="All"){
			$books=AddBook::leftJoin('Class', function($join) {
				$join->on('Books.class', '=', 'Class.code');
			})
			->join('bookStock','Books.code', '=', 'bookStock.code')
			->select('Books.id', 'Books.code', 'Books.title', 'Books.author','bookStock.quantity','Books.rackNo','Books.rowNo','Books.type','Books.desc',DB::raw("IFNULL(Class.Name,'All') as class"))
			->where('Books.type',$request->input('type'))
			->get();

		}
		else {

			$books = DB::table('Books')
			->join('Class', 'Books.class', '=', 'Class.code')
			->join('bookStock','Books.code', '=', 'bookStock.code')
			->select('Books.id', 'Books.code', 'Books.title', 'Books.author','bookStock.quantity','Books.rackNo','Books.rowNo','Books.type','Books.desc','Class.Name as class')
			->where('Books.class',$request->input('class'))
			->where('Books.type',$request->input('type'))->get();
		}
		//$classes = array('All' => 'All')+ClassModel::pluck('name','code');
		$classes = ClassModel::pluck('name','code');
		$classes = $classes->toArray('All' , 'All') ;
		//return View::Make('app.booksearch',compact('books','classes'));
		return View('app.booksearch',compact('books','classes'));

	}
}

public function getReports()
{

	//return View::Make('app.libraryReports');
	return View('app.libraryReports');
}

public function Reportprint($do)
{
	if($do=="today")
	{
		$todayReturn = DB::table('issueBook')
		->join('Student', 'Student.regiNo', '=', 'issueBook.regiNo')
		->join('Books','Books.code','=','issueBook.code')
		->join('Class','Class.code','=','Student.class')
		->select('Books.title', 'Books.author','Books.type','issueBook.quantity','issueBook.fine','Student.firstName','Student.middleName','Student.lastName','Student.rollNo','Class.name as class')
		->where('issueBook.returnDate',date('Y-m-d'))
		->where('issueBook.Status','Borrowed')
		->get();
		$rdata =array('name'=>'Today Return List','total'=>count($todayReturn));

		$datas=$todayReturn;
		$institute=Institute::select('*')->first();
		$pdf = PDF::loadView('app.libraryreportprinttex',compact('datas','rdata','institute'));
		return $pdf->stream('today-books-return-List.pdf');

	}
	else if($do=="expire")
	{
		$expires = DB::table('issueBook')
		->join('Student', 'Student.regiNo', '=', 'issueBook.regiNo')
		->join('Books','Books.code','=','issueBook.code')
		->join('Class','Class.code','=','Student.class')
		->select('Books.title', 'Books.author','Books.type','issueBook.quantity','issueBook.fine','Student.firstName','Student.middleName','Student.lastName','Student.rollNo','Class.name as class')
		->where('issueBook.returnDate','<',date('Y-m-d'))
		->where('issueBook.Status','Borrowed')
		->get();
		$rdata =array('name'=>'Today Expire List','total'=>count($expires));

		$datas=$expires;
		$institute=Institute::select('*')->first();
		$pdf = PDF::loadView('app.libraryreportprinttex',compact('datas','rdata','institute'));
		return $pdf->stream('books-expire-List.pdf');
	}
	else {
		$books = AddBook::select('*')->where('type',$do)->get();
		$rdata =array('name'=>$do,'total'=>count($books));

		$datas=$books;
		$institute=Institute::select('*')->first();
		$pdf = PDF::loadView('app.libraryreportbooks',compact('datas','rdata','institute'));
		return $pdf->stream('books-expire-List.pdf');
	}
	return $do;
}
public function getReportsFine()
{
	//return View::Make('app.libraryfinereport');
	return View('app.libraryfinereport');
}
public function ReportsFineprint($month)
{
	$sqlraw="select sum(fine) as totalFine from issueBook where Status='Returned' and EXTRACT(YEAR_MONTH FROM returnDAte) = EXTRACT(YEAR_MONTH FROM '".$month."')";
	$fines = DB::select(DB::RAW($sqlraw));
	if($fines[0]->totalFine)
	{

		$total=$fines[0]->totalFine;
	}
	else
	{
		$total=0;
	}
	$institute=Institute::select('*')->first();
	$rdata =array('month'=>date('F-Y', strtotime($month)),'name'=>'Monthly Fine Collection Report','total'=>$total);
	$pdf = PDF::loadView('app.libraryfinereportprint',compact('rdata','institute'));
	return $pdf->stream('libraryfinereportprint.pdf');


}
private function  parseAppDate($datestr)
{

	if($datestr=="" or $datestr== NULL)
	return $datestr="0000-00-00";
	$date = explode('/', $datestr);
	return $date[2].'-'.$date[1].'-'.$date[0];
}

public function checkBookAvailability($code,$quantity)
{
	$availabeQuantity=DB::table('bookStock')
	->select('quantity')
	->where('code',$code)->first();
	$result = "Yes";
	if($quantity>$availabeQuantity->quantity)
	$result = "No";
	return ["isAvailable" => $result ];
	

}

}
