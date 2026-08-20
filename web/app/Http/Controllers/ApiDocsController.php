<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\View\View;

class ApiDocsController extends Controller
{
    public function ui(): View
    {
        return view('api-docs');
    }

    public function spec(): Response
    {
        $path = resource_path('openapi.yaml');

        return response(file_get_contents($path), 200, [
            'Content-Type' => 'application/yaml; charset=UTF-8',
        ]);
    }
}
