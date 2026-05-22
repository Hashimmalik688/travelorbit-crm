@extends('layouts/contentNavbarLayout')

@section('title', 'Tasks')

@section('content')
<div>
    <div class="to-page-header">
        <div class="to-page-header-left">
            <h1>Tasks</h1>
            <div class="to-breadcrumb">
                <a href="{{ route('dashboard') }}">Dashboard</a> &rsaquo; <a href="#">CRM</a> &rsaquo; Tasks
            </div>
        </div>
        <div class="to-page-header-right">
            <button class="btn btn-orange btn-sm">
                <i class="ph ph-plus me-1"></i> Add Task
            </button>
        </div>
    </div>

    <div class="card animate-in">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Assigned To</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5">
                            <div class="to-empty">
                                <div class="to-empty-icon"><i class="ph ph-check-square"></i></div>
                                <h5>No tasks yet</h5>
                                <p>Create tasks to track follow-ups and to-dos.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
