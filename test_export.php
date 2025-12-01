<?php

use App\Models\User;
use App\Models\Project;
use App\Exports\ProjectsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

// Mock a delegate user
$user = new User();
$user->role = 'delegate';
$user->camp_id = 1;
$user->id = 999;

Auth::shouldReceive('user')->andReturn($user);

// Create query
$query = Project::query()->where('is_approved', true)->where('camp_id', $user->camp_id);

// Instantiate Export
$export = new ProjectsExport($query);

// Check query
$sql = $export->query()->toSql();
echo "SQL: " . $sql . "\n";

// Check bindings
$bindings = $export->query()->getBindings();
echo "Bindings: " . implode(', ', $bindings) . "\n";

echo "Export class instantiated successfully.\n";
