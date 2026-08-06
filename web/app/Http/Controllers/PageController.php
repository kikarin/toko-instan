<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function home(Request $request): Response|RedirectResponse
    {
        if ($request->user()) {
            return redirect($request->user()->homePath());
        }

        return Inertia::render('Landing');
    }
}
