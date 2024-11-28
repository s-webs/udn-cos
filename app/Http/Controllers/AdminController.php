<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Table;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        $selectedTableId = $user->table->first() ? $user->table->first()->id : null;
        $tables = Table::with('categories')
            ->get()
            ->map(function ($table) {
                $table->isOccupied = $table->user()->exists();
                return $table;
            });

        $categoryIds = $user->table
            ->flatMap(function ($table) {
                return $table->categories->pluck('id');
            })
            ->unique();

        $tickets = Ticket::query()->whereIn('category_id', $categoryIds)
            ->where('status', 'waiting')
            ->orderBy('ticket_number', 'asc')
            ->with('category')
            ->get();

        return Inertia::render('Admin/Dashboard', compact('tables', 'selectedTableId', 'tickets'));
    }

    public function assignTable(Request $request, $tableId)
    {
        $user = User::query()->findOrFail($request->user);
        $table = Table::query()->findOrFail($tableId);

        if (!$user || !$table) {
            return response()->json(['error' => 'Таблица или пользователь не найдены'], 404);
        }

        if ($user->table()->exists()) {
            $user->table()->detach();
        }

        return $user->table()->attach($tableId);
    }

    public function unAssignTable(Request $request)
    {
        $user = Auth::user();

        if (!$user->table()->exists()) {
            return response()->json(['error' => 'User does not have a table assigned'], 400);
        }
        $user->table()->detach();
    }

    public function category()
    {
        $categories = Category::all();

        return Inertia::render('Admin/Category', [
            'categories' => $categories,
        ]);
    }

    public function addCategory(Request $request)
    {
        $category = new Category();
        $category->name_kz = $request->name_kz;
        $category->name_ru = $request->name_ru;
        $category->save();

        return redirect()->back()->with('message', 'Категория успешно добавлена!');
    }

    public function updateCategory($id, Request $request)
    {
        $category = Category::query()->findOrFail($id);
        $category->name_kz = $request->name_kz;
        $category->name_ru = $request->name_ru;
        $category->save();

        return redirect()->back()->with('message', 'Категория успешно обновлена!');
    }

    public function deleteCategory($id)
    {
        $category = Category::query()->findOrFail($id);
        $category->delete();
    }

    public function tables()
    {
        $categories = Category::all();
        $tables = Table::query()->orderBy('number', 'asc')->get();

        // Получаем связанные категории для каждой таблицы
        $tables = $tables->map(function ($table) {
            $table->selectedCategories = $table->categories->pluck('id')->toArray();
            return $table;
        });

        return Inertia::render('Admin/Tables', compact('tables', 'categories'));
    }

    public function createTable(Request $request)
    {
        $table = new Table();
        $table->name_kz = $request->name_kz;
        $table->name_ru = $request->name_ru;
        $table->number = $request->number;
        $table->save();

        return redirect()->back()->with('message', 'Категория успешно добавлена!');
    }

    public function deleteTable($id)
    {
        $table = Table::query()->findOrFail($id);
        $table->delete();
    }

    public function updateTable($id, Request $request)
    {
        $table = Table::query()->findOrFail($id);
        $table->name_kz = $request->name_kz;
        $table->name_ru = $request->name_ru;
        $table->number = $request->number;
        $table->save();

        if ($request->has('selectedCategories')) {
            $table->categories()->sync($request->selectedCategories);
        }

        return redirect()->back()->with('message', 'Стол успешно обновлен!');
    }

    public function updateCategories(Request $request, Table $table)
    {
        $validated = $request->validate([
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
        ]);

        $table->categories()->sync($validated['categories']);
        return response()->json(['message' => 'Categories updated successfully']);
    }
}
