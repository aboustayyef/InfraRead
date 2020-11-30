<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Source;
use Illuminate\Http\Request;

class AdminSourceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.source.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $source = new Source();
        $categories = Category::all();

        return view('admin.source.create')->with(compact('source'))->with(compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(Source::validationRules());

        Source::Create($request->except(['_token']));

        return redirect('/admin/source')->with('message', 'Source has been successfully created');
    }

    /**
     * Display the specfied resource.
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
    public function edit(Source $source)
    {
        if ($source) {
            $categories = Category::all();

            return view('admin.source.edit')->with(compact('source'))->with(compact('categories'));
        }

        return response('Record Not Found', 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Source $source)
    {
        $request->validate(Source::validationRules(false));

        $source->update($request->except(['_token']));

        return redirect('/admin/source')->with('message', 'Source has been successfully updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Source $source)
    {
        $source->delete();

        return redirect(route('admin.source.index'))->with('message', 'Source Succesfully Deleted');
    }
}
