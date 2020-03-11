<?php

namespace QikkerOnline\NovaTinyMCE\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UploadsController extends Controller
{
    public function uploadImage(Request $request)
    {
        //throw new \Exception('Method not implemented');
        return response()->json([
            'location' => 'https://picsum.photos/200'
        ]);
    }
}
