<?php
require_once '../includes/db_connect.php';
require_once '../includes/auth_helper.php';

if (!isLoggedIn() || $_SESSION['user_role'] !== 'Employee') {
    die("Unauthorized access.");
}

// Ensure the user can only view their own payslip
$employee_id = $_SESSION['user_id'];
$month = $_GET['month'] ?? date('Y-m');

// Fetch Payroll Cycle Settings
$setStmt = $pdo->query("SELECT meta_key, meta_value FROM settings WHERE meta_key IN ('payroll_start_day', 'payroll_end_day')");
$settings = $setStmt->fetchAll(PDO::FETCH_KEY_PAIR);
$startDay = (int)($settings['payroll_start_day'] ?? 1);
$endDay = (int)($settings['payroll_end_day'] ?? 30);

// Calculate Cycle Range
$currentMonthObj = new DateTime($month . "-01");
$prevMonthObj = clone $currentMonthObj;
$prevMonthObj->modify("-1 month");

$startDate = $prevMonthObj->format('Y-m') . "-" . str_pad($startDay, 2, '0', STR_PAD_LEFT);
$endDate = $currentMonthObj->format('Y-m') . "-" . str_pad($endDay, 2, '0', STR_PAD_LEFT);

// Calculate total days in this specific cycle
$date1 = new DateTime($startDate);
$date2 = new DateTime($endDate);
$interval = $date1->diff($date2);
$daysInCycle = $interval->days + 1;

// Fetch Employee and Payroll Data
$sql = "SELECT e.*, d.name as dept_name, p.*
        FROM employees e
        LEFT JOIN departments d ON e.department_id = d.id
        JOIN payroll p ON e.id = p.employee_id AND p.month_year = ?
        WHERE e.id = ? AND p.status = 'Paid'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$month, $employee_id]);
$data = $stmt->fetch();

if (!$data) {
    die("Payslip not found or not yet paid.");
}

$fullName = $data['first_name'] . ($data['middle_name'] ? ' ' . $data['middle_name'] : '') . ' ' . $data['last_name'];

$total_earnings = (float)$data['basic_salary'] + (float)$data['house_rent'] + (float)$data['utility'] + (float)$data['fuel'] + (float)$data['mobile'] + (float)$data['medical'];
$total_deductions = (float)$data['deductions'] + (float)($data['loan_deduction'] ?? 0) + (float)($data['provident_fund'] ?? 0) + (float)($data['professional_tax'] ?? 0);

// Format month for display
$dateObj = DateTime::createFromFormat('Y-m', $data['month_year']);
$formattedMonth = $dateObj->format('F Y');

// Calculate worked days
$workedDays = $daysInCycle - (float)$data['leaves_count'] - ((float)$data['halfdays_count'] * 0.5) - floor((float)$data['lates_count'] / 3);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - <?= $fullName ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 40px; color: #333; }
        .payslip-container { max-width: 900px; margin: 0 auto; border: 1px solid #000; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 28px; text-transform: uppercase; letter-spacing: 2px; }
        .header p { margin: 5px 0; font-size: 14px; line-height: 1.4; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 20px; }
        .info-item { display: flex; margin-bottom: 8px; font-size: 14px; }
        .info-label { width: 150px; font-weight: 600; }
        .info-value { flex: 1; padding-bottom: 2px; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 10px; font-size: 14px; text-align: left; }
        th { background-color: #f5f5f5; text-align: center; font-weight: 700; }
        
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }
        
        .footer-row td { border: none; padding-top: 20px; }
        .net-pay-box { border: 2px solid #000; padding: 10px 20px; display: inline-block; float: right; font-size: 18px; font-weight: 700; }
        
        .system-note { margin-top: 50px; text-align: center; font-size: 11px; color: #666; font-style: italic; border-top: 1px dashed #ddd; padding-top: 10px; clear: both; }

        .no-print-btn { background: #6c4cf1; color: #fff; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-bottom: 20px; display: inline-block; width: 120px; text-align: center; text-decoration: none; font-weight: 600; }

        @media print {
            @page { 
                margin: 0; 
                size: auto;
            }
            body { 
                margin: 0; 
                padding: 1.5cm;
            }
            .no-print { display: none !important; }
            .payslip-container { 
                border: 1px solid #000; 
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="display: flex; gap: 10px;">
        <a href="javascript:window.print()" class="no-print-btn">Print Payslip</a>
        <a href="payroll.php" class="no-print-btn" style="background: #e2e8f0; color: #333;">Back</a>
    </div>

    <div class="payslip-container">
        <div class="header">
            <h1>Payslip</h1>
            <p class="font-bold">Richmond Tech Group</p>
            <p>Address: Office # 14, Hillview Apt, Block-D North Nazimabad, Karachi, Pakistan.</p>
            <p>Tel: +92 330-2784784</p>
        </div>

        <div class="info-grid">
            <div>
                <div class="info-item">
                    <span class="info-label">Date of Joining</span>
                    <span class="info-value">: <?= date('M d, Y', strtotime($data['joining_date'])) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Pay Period</span>
                    <span class="info-value">: <?= $formattedMonth ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Worked Days</span>
                    <span class="info-value">: <?= $workedDays ?></span>
                </div>
            </div>
            <div>
                <div class="info-item">
                    <span class="info-label">Employee Name</span>
                    <span class="info-value">: <?= $fullName ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Designation</span>
                    <span class="info-value">: <?= $data['job_title'] ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Department</span>
                    <span class="info-value">: <?= $data['dept_name'] ?></span>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 35%;">Earnings</th>
                    <th style="width: 15%;">Amount</th>
                    <th style="width: 35%;">Deductions</th>
                    <th style="width: 15%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Basic</td>
                    <td class="text-right"><?= number_format($data['basic_salary'], 0) ?></td>
                    <td>Provident Fund</td>
                    <td class="text-right"><?= number_format($data['provident_fund'] ?? 0, 0) ?></td>
                </tr>
                <tr>
                    <td>House Rent</td>
                    <td class="text-right"><?= number_format($data['house_rent'], 0) ?></td>
                    <td>Professional Tax</td>
                    <td class="text-right"><?= number_format($data['professional_tax'] ?? 0, 0) ?></td>
                </tr>
                <tr>
                    <td>Utility</td>
                    <td class="text-right"><?= number_format($data['utility'], 0) ?></td>
                    <td>Loan</td>
                    <td class="text-right"><?= number_format($data['loan_deduction'] ?? 0, 0) ?></td>
                </tr>
                <tr>
                    <td>Mobile</td>
                    <td class="text-right"><?= number_format($data['mobile'], 0) ?></td>
                    <td>Deduction</td>
                    <td class="text-right"><?= number_format($data['deductions'], 0) ?></td>
                </tr>
                <tr>
                    <td>Fuel</td>
                    <td class="text-right"><?= number_format($data['fuel'], 0) ?></td>
                    <td></td>
                    <td class="text-right">-</td>
                </tr>
                <tr>
                    <td>Medical</td>
                    <td class="text-right"><?= number_format($data['medical'], 0) ?></td>
                    <td></td>
                    <td class="text-right">-</td>
                </tr>
                <tr>
                    <td class="font-bold">Total Earnings</td>
                    <td class="text-right font-bold"><?= number_format($total_earnings, 0) ?></td>
                    <td class="text-right font-bold">Total Deductions</td>
                    <td class="text-right font-bold"><?= number_format($total_deductions, 0) ?></td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 30px; overflow: hidden;">
            <div class="net-pay-box">
                Net Pay: PKR <?= number_format($data['net_payable'], 0) ?>
            </div>
        </div>

        <div class="system-note">
            This is a computer-generated slip and does not require a signature.
        </div>
    </div>
</body>
</html>
