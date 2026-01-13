<?php
// User Role Configuration & Management

// 1. Define Arrays for each Role
$employees = [
    //Admin Employees
    '26878',
    '40932',
    //Creative Employees
    '27317',
    '27340',
    //CRM Employees
    '25483',
    //Eng Employees
    '22366',
    '41131',
    //Facilities - Clinic Employees
    '17941',
    //Facilities - Housekeeping Employees
    '00273',
    '2685',
    '6913',
    '8923',
    //Facilities - Laundry Employees
    '8035',
    '7705',
    //Facilities - Safety Employees
    '41713',
    //FGW Employees
    '16433',
    '00005',
    //Finance Employees
    '27390',
    '9970',
    //HR Employees
    '25243',
    '18201',
    //IS Employees
    '00030',
    '00088',
    //IT Employees
    '40696',
    '4779',
    //Labels Employees
    '9485',
    '3760',
    //Logistics Employees
    '25275',
    '40952',
    //MWH Employees
    '00651',
    '00678',
    //PMC Employees
    '41004',
    '40517',
    //Prodn - ISO Employees
    '3061',
    '6169',
    //Prodn - Office Employees
    '00501',
    '10246',
    //Prodn - Phase2 Employees
    '3061',
    '6169',
    //Prodn - Phase3 Employees
    '3061',
    '6169',
    //Prodn - Phase4 Employees
    '3061',
    '6169',
    //Purchasing Employees
    '27428',
    '27496',
    //QA Employees
    '20898',
    '40545',
    //QC Employees
    '00026',
    '00351',
    //R&I Employees
    '5796',
    //Sales Employees
    '21518',
];

$department_heads = [
    //Admin Department Heads
    '3131',
    '2730',
    //Creative Department Heads
    '26268',
    '5416',
    //CRM Department Head
    '00124',
    //Eng Department Heads
    '00566',
    '00059',
    //Facilities - Clinic Department Heads
    '00003',
    //Facilities - Housekeeping Department Heads
    '00823',
    '00003',
    //Facilities - Laundry Department Heads
    '00818',
    '2690',
    //Facilities - Safety Department Head
    '00003',
    //FGW Department Head
    '8041',
    //Finance Department Heads
    '27649',
    '00004',
    //HR Department Heads
    '41687',
    '13248',
    //IS Department Head
    '5855',
    //IT Department Head
    '40021',
    '40235',
    //Labels Department Head
    '00139',
    //Logistics Department Head
    '3764',
    '6399',
    //MWH Department Head
    '8041',
    //PMC Department Head
    '28050',
    //Prodn - ISO Department Heads
    '00822',
    '3694',
    //Prodn - Office Department Heads
    '00822',
    '3694',
    //Prodn - Phase1 Department Heads
    '00822',
    '3694',
    //Prodn - Phase2 Department Heads
    '00822',
    '3694',
    //Prodn - Phase3 Department Heads
    '00822',
    '3694',
    //Prodn - Phase4 Department Heads
    '00822',
    '3694',
    //Purchasing Department Heads
    '25233',
    '23560',
    //QA Department Heads
    '11534',
    '40484',
    //QC Department Heads
    '11534',
    '40484',
    //R&I Department Heads
    '1652',
    '00125',
    //Sales Department Heads
    '00077',
    '00446',
];

$facilities_heads = [
    //Head of Facilities Management
    '00003',
    //Supervisor Facilities
    '00823',
    //Supervisor 1 - Housekeeping
    '00273',
    //Supervisor 2 - Housekeeping
    '2685',
    //Supervisor 3 - Housekeeping
    '6913',
    //Supervisor 4 - Housekeeping (LRN2)
    '8923',
];

$executives = [
    '2604',
];


$user_map = [];
//UserIDs are string
foreach ($employees as $id) {
    $user_map[(string) $id] = 'Employee';
}

foreach ($department_heads as $id) {
    $user_map[(string) $id] = 'Department Head';
}

foreach ($facilities_heads as $id) {
    $user_map[(string) $id] = 'Facilities Head';
}

foreach ($executives as $id) {
    $user_map[(string) $id] = 'Executive';
}

return $user_map;
?>