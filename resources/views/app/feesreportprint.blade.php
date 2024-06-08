
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>

.bg{
 width: 100%;
 background-color:#dcdcdc;
}
.bg2{
 width: 100%;
background-color:#cccccc;
}
.bg3{
  width: 100%;

}
.bg3 tr:nth-child(even) {
    background-color: #81DAF5;
}
.bg3 tr:nth-child(odd) {
    background-color: #82FA58;
}

table {
border-spacing: 0;
border-collapse: separate;

}
table td{
padding-left: 5px;
}
.thead td{
  border-bottom: solid green 2px;
  font-weight: bold;
  color:blue;
}
.red
{
  color:red;
  font-weight: bold;
}
.green {
  color:green;
  font-weight: bold;
}
.logo{
  height: 150px;
  width: 200px;
}
.lefthead{
  width: 30%;
}
.righthead{
  width: 70%;
}
.righthead p{
  margin: 0px;
  padding: 0px;
}
.bg3 tr:last-child {
    background-color: #cccccc;


}
.bg3 tr:last-child td {
border-top: solid #000 2px;
margin-top:10px !important;
}
#footer
{

width:100%;
height:50px;
position:absolute;
bottom:0;
left:0;
}
</style>
</head>

<body >
<div id="admit">
  <table class="bg">
    <tr>
    <td class="lefthead">

     <img class="logo" src="{{url('/img/logo.png')}}">
    </td>

   <td class="righthead">
     <h3>{{$institute->name}}</h3><pre>
<p><strong>Establish:</strong> {{$institute->establish}}</p>
<p><strong>Web:</strong> {{$institute->web}}</p>
<p><strong>Email:</strong> {{$institute->email}}</p>
<p><strong>Phone:</strong> {{$institute->phoneNo}}</p>
<p><strong>Address:</strong> {{$institute->address}}</p>
     </pre>
   </td>
   </tr>

 </table>
 <table class="bg2">
   <tr>
    <td>

  </td>
  <td>  <strong>Fees Collection Report</strong></td>
  <td >

  </td>
</tr>
</table>
<br>
<center><h2>Collection Time Period</h2></center>
<table style="width: 100%;">
<tr>
   <td><td>
  <td>From Date: <strong>{{$rdata['sDate']}}</strong></td>
  <td></td>
  <td>To Date: <strong>{{$rdata['eDate']}}</strong></td>

</tr>
</table>
<br>
<table id="datat" class="bg3">
  <tbody>
    <tr class="thead">
        <td>Payable Amount</td>
        <td>Paid Amount</td>
        <td>Due</td>

    </tr>


    <tr>
      <td><strong>{{$datas->payTotal}}</strong> tk.</td>
      <td><strong>{{$datas->paiTotal}}</strong> tk.</td>
      <td> <strong>{{$datas->dueamount}}</strong> tk.</td>

    </tr>
  </tbody>
</table>
<br>
<center>-----0-----</center>
<div id="footer">
  <p>Print Date: {{date('d/m/Y')}}</p>
</div>
</body>
</html>
