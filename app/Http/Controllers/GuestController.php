<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Inertia\Inertia;

class GuestController extends Controller
{
    public function terminal()
    {
        $categories = Category::all();
        return Inertia::render('Guest/Terminal', compact('categories'));
    }

    public function monitoring()
    {
        return Inertia::render('Guest/Monitoring');
    }

    public function mobileCategories()
    {
        $categories = Category::all();
        return Inertia::render('Guest/MobileCategories', compact('categories'));
    }
}
