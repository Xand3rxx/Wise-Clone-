<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the authenticated user dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Get authenticated user information.
        $user = auth()->user();

        return view('application.index', [
            'transactions'      => ($user->role_id == 2) ? $user['transactions'] : Transaction::take(500)->latest()->get(),
            'user'              => $user,
        ]);
    }
}
