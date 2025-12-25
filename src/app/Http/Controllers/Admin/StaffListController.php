<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class StaffListController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'general')
            ->orderBy('id', 'asc')
            ->get();

        return view('admin.staff.index', compact('users'));
    }
}
