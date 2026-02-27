<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = \Illuminate\Support\Facades\DB::table('projects as p')
            ->leftJoin('users as u', 'p.pic_user_id', '=', 'u.id')
            ->leftJoin('master_customers as c', 'p.customer', '=', 'c.customer_code')
            ->select('p.*', 'u.full_name as pic_name', 'c.customer_code', 'c.customer_name')
            ->orderBy('p.id', 'desc')
            ->get();
        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $users = \App\Models\User::orderBy('full_name')->get();
        $categories = \App\Models\MasterCategory::orderBy('category_name')->get();
        $customers = \Illuminate\Support\Facades\DB::table('master_customers')->orderBy('customer_name')->get();
        return view('projects.create', compact('users', 'categories', 'customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_name' => 'required',
            'project_code' => 'required|unique:projects,project_code',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        Project::create([
            'project_code' => $request->project_code,
            'project_name' => strtoupper($request->project_name),
            'description'  => $request->description,
            'customer'     => $request->customer,
            'category'     => $request->category,
            'model'        => $request->model,
            'year'         => $request->year,
            'pic_user_id'  => $request->pic_user_id,
            'start_date'   => $request->start_date,
            'end_date'     => $request->end_date,
            'die_go'       => $request->die_go,
            'to'           => $request->to,
            'pp1'          => $request->pp1,
            'pp2'          => $request->pp2,
            'pp3'          => $request->pp3,
            'mass_pro'     => $request->mass_pro,
            'status'       => $request->status ?? 'Active'
        ]);

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }

    public function edit(Project $project)
    {
        $users = \App\Models\User::orderBy('full_name')->get();
        $categories = \App\Models\MasterCategory::orderBy('category_name')->get();
        $customers = \Illuminate\Support\Facades\DB::table('master_customers')->orderBy('customer_name')->get();
        return view('projects.edit', compact('project', 'users', 'categories', 'customers'));
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'project_name' => 'required',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $project->update([
            'project_name' => strtoupper($request->project_name),
            'description'  => $request->description,
            'customer'     => $request->customer,
            'category'     => $request->category,
            'model'        => $request->model,
            'year'         => $request->year,
            'pic_user_id'  => $request->pic_user_id,
            'start_date'   => $request->start_date,
            'end_date'     => $request->end_date,
            'die_go'       => $request->die_go,
            'to'           => $request->to,
            'pp1'          => $request->pp1,
            'pp2'          => $request->pp2,
            'pp3'          => $request->pp3,
            'mass_pro'     => $request->mass_pro,
            'status'       => $request->status,
        ]);

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }
}
