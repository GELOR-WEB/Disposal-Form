<?php

// 1. Define Arrays for each Role
$department_groups = [
    'Admin' => [
        'employees' => ['26878', '40932'],
        'heads' => ['3131', '2730']
    ],
    'Creative' => [
        'employees' => ['27317', '27340'],
        'heads' => ['26268', '5416']
    ],
    'CRM' => [
        'employees' => ['25483'],
        'heads' => ['00124']
    ],
    'Engineering' => [ // Maps to 'Eng' comment, expanded for safety
        'employees' => ['22366', '41131'],
        'heads' => ['00566', '00059']
    ],
    'Facilities - Clinic' => [
        'employees' => ['17941'],
        'heads' => ['00003']
    ],
    'Facilities - Housekeeping' => [
        'employees' => ['00273', '2685', '6913', '8923'],
        'heads' => ['00823', '00003']
    ],
    'Facilities - Laundry' => [
        'employees' => ['8035', '7705'],
        'heads' => ['00818', '2690']
    ],
    'Facilities - Safety' => [ // Assuming Safety is separate dept
        'employees' => ['41713'],
        'heads' => ['00003']
    ],
    'FGW' => [
        'employees' => ['16433', '00005'],
        'heads' => ['8041']
    ],
    'Finance' => [
        'employees' => ['27390', '9970'],
        'heads' => ['27649', '00004']
    ],
    'HR' => [
        'employees' => ['25243', '18201'],
        'heads' => ['41687', '13248']
    ],
    'IS' => [
        'employees' => ['00030', '00088'],
        'heads' => ['5855']
    ],
    'IT' => [
        'employees' => ['40696', '4779'],
        'heads' => ['40021', '40235']
    ],
    'Labels' => [
        'employees' => ['9485', '3760'],
        'heads' => ['00139']
    ],
    'Logistics' => [
        'employees' => ['25275', '40952'],
        'heads' => ['3764', '6399']
    ],
    'MWH' => [
        'employees' => ['00651', '00678'],
        'heads' => ['8041']
    ],
    'PMC' => [
        'employees' => ['41004', '40517'],
        'heads' => ['28050']
    ],
    'Production - ISO' => [ // 'Prodn - ISO' in comments
        'employees' => ['3061', '6169'],
        'heads' => ['00822', '3694']
    ],
    'Production - Office' => [ // 'Prodn - Office'
        'employees' => ['00501', '10246'],
        'heads' => ['00822', '3694']
    ],
    'Production - Phase2' => [
        'employees' => ['3061', '6169'],
        'heads' => ['00822', '3694']
    ],
    'Production - Phase3' => [
        'employees' => ['3061', '6169'],
        'heads' => ['00822', '3694']
    ],
    'Production - Phase4' => [
        'employees' => ['3061', '6169'],
        'heads' => ['00822', '3694']
    ],
    'Purchasing' => [
        'employees' => ['27428', '27496'],
        'heads' => ['25233', '23560']
    ],
    'QA' => [
        'employees' => ['20898', '40545'],
        'heads' => ['11534', '40484']
    ],
    'QC' => [
        'employees' => ['00026', '00351'],
        'heads' => ['11534', '40484']
    ],
    'R&I' => [
        'employees' => ['5796'],
        'heads' => ['1652', '00125']
    ],
    'Sales' => [
        'employees' => ['21518'],
        'heads' => ['00077', '00446']
    ]
];

// Special Roles (No Department Grouping or Cross-Dept)
$facilities_heads = [
    '00003', // Head of Facilities Management
    '00823', // Supervisor Facilities
    '00273', // Supervisor 1 - Housekeeping
    '2685',  // Supervisor 2 - Housekeeping
    '6913',  // Supervisor 3 - Housekeeping
    '8923',  // Supervisor 4 - Housekeeping (LRN2)
];

$executives = ['2604'];


$user_map = [];

// Helper to add role safely
function add_role(&$map, $id, $role)
{
    $str_id = (string) $id;
    if (!isset($map[$str_id])) {
        $map[$str_id] = [];
    }
    // Avoid duplicates
    if (!in_array($role, $map[$str_id])) {
        $map[$str_id][] = $role;
    }
}

// Build User Map from Department Groups
foreach ($department_groups as $dept_name => $group) {
    // Process Employees
    if (isset($group['employees'])) {
        foreach ($group['employees'] as $id) {
            add_role($user_map, $id, 'Employee');
        }
    }
    // Process Heads
    if (isset($group['heads'])) {
        foreach ($group['heads'] as $id) {
            add_role($user_map, $id, 'Department Head');
        }
    }
}

// Add Facilities Heads
foreach ($facilities_heads as $id) {
    add_role($user_map, $id, 'Facilities Head');
}

// Add Executives
foreach ($executives as $id) {
    add_role($user_map, $id, 'Executive');
}

// Return both the configuration (for dept lookup) and the map (for role lookup)
return [
    'user_map' => $user_map,
    'department_groups' => $department_groups
];
?>