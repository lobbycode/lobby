<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SocialShareButtonsController extends Controller
{
    public function ShareWidget(Request $request)
    {
        $shareComponent = \Share::page(
            $request->url(),
            'text share here',
        )
        
        ->facebook()
        ->twitter()
        ->linkedin()
        ->telegram()
        ->whatsapp()        
        ->reddit();
        
        return view('posts', compact('shareComponent'));
    }
      
}
