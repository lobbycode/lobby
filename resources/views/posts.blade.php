@extends('layouts.app')
@section('content')

        <style>
            div#social-links {
                margin: 0 auto;
                max-width: 500px;
            }
            div#social-links ul li {
                display: inline-block;
            }          
            div#social-links ul li a {
                padding: 20px;
                border: 1px solid #ccc;
                margin: 1px;
                font-size: 30px;
                color: #222;
                background-color: #ccc;
            }
        </style>
    
        <div class="container mt-4">
            <h2 class="mb-5 text-center">share</h2>
            {!! $shareComponent !!}
        </div>
        @endsection