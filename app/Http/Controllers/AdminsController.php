<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminsController extends Controller
{
    /**
     * Display Template.
     */
    public function TemplateAdmin()
    {
        return view('TemplateAdmin.main');
    }
    /**
     * Display DASHBOARD.
     */
    public function Dashboard()
    {
        return view('TemplateAdmin.dashboard');
    }
    /**
     * Display LAPORAN.
     */
    public function laporan()
    {
        return view('TemplateAdmin.laporan');
    }
    /**
     * Display GALERY.
     */
    public function galery()
    {
        return view('TemplateAdmin.galery');
    }
    /**
     * Display FOTOGRAFY.
     */
    public function fotografy()
    {
        return view('TemplateAdmin.fotografy');
    }
    /**
     * Display PROFILE.
     */
    public function profile()
    {
        return view('TemplateAdmin.profile');
    }
   




    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
