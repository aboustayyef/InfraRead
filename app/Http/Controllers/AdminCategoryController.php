<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::all();

        return view('admin.category.index')->with(compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $category = new Category();
        $categories = Category::all();

        return view('admin.category.create')->with(compact('category'))->with(compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(Category::validationRules());
        $category = Category::create($request->except(['_token']));

        return redirect(route('admin.category.index'))->with('message', 'category Created Succesfully');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        $categories = Category::all();

        return view('admin.category.edit')->with(compact('category'))->with(compact('categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $request->validate(Category::validationRules(false));
        $category->update($request->except(['_token']));

        return redirect(route('admin.category.index'))->with('message', 'Category Modified Succesfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        // First, unset category references of sources in this category
        $sources = $category->sources;
        $sources->each(function ($source) {
            $source->category_id = null;
            $source->save();
        });
        $category->delete();

        return redirect(route('admin.category.index'))->with('message', 'Category Succesfully Deleted');
    }
}
