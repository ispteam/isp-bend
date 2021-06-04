@extends('layouts.header_nav')

@section('content')

<!DOCTYPE html>
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">

    <title>Home</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body>
    <!-- How it works "for CLIENT" -->
    <h1 id="CHowUse"> How To Use </h1>
    <div id= "homeContainerBox">
    <div class="homeContainerBox">
         <div class="CHowUseBox">
             <h2>1</h2>
             <h3>Create a request</h3>
             <p>Create your own request filling down all the requiered details of your order</p>
         </div>
         <div class="CHowUseBox">
            <h2>2</h2>
            <h3>Send the Request</h3>
            <p>After getting done with your request, press SUBMIT to send your order.</p>
        </div>
        <div class="CHowUseBox">
            <h2>3</h2>
            <h3>Request Acceptance</h3>
            <p>Your request was sent, recived successfully, and the supplier will quote your request.</p>
        </div>
    </div>
    </div>
    <div id="sendNewRequest">
        <a href="#newRequest" class="senNewRequest">SEND REQUEST</a>
    </div>
    <div class="SHowUseBox">
        <h1>for suppliers</h1>
        <div id= "SHowUseBox">
            <h2>How does it work?</h2>
            <p>Buyers search for the spare parts they need and send a request to suppliers subscribed to our site. If the supplier has the necessary spare parts for sale, our system sends the supplier’s quote to a buyer. The buyer can receive one or more quotes and then choose the supplier. After choosing a supplier, the buyer and the supplier can directly contact one another to carry out the transaction.</p>
            <a href="#supplierAccount" class="SupplierAccounButton">CREATE SUPPLIER ACCOUNT</a>
        </div>
    </div>

</body>
</html>
@stop
