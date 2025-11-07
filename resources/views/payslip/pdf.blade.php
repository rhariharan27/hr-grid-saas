<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payslip</title>
  <style>
    body {
      font-family: 'Arial', sans-serif;
      margin: 20px;
      font-size: 12px;
    }

    .header {
      text-align: center;
      margin-bottom: 20px;
    }

    .header img {
      max-width: 120px;
      margin-bottom: 10px;
    }

    .header h2 {
      margin: 0;
      font-size: 18px;
    }

    .company-details {
      text-align: center;
      margin-bottom: 30px;
    }

    .company-details p {
      margin: 0;
    }

    .section-title {
      background-color: #f2f2f2;
      font-weight: bold;
      padding: 5px;
      margin-top: 20px;
    }

    .details-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    .details-table th, .details-table td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: left;
    }

    .details-table th {
      background-color: #f2f2f2;
    }

    .footer {
      text-align: center;
      font-size: 10px;
      margin-top: 20px;
      border-top: 1px solid #ccc;
      padding-top: 10px;
    }
  </style>
</head>
<body>
<!-- Header -->
<div class="header">
  <img
    src="{{$company['logoBase64']}}" height="100px"
    alt="Company Logo">
  <h2>{{ $company['name'] }}</h2>
  <p>{{ $company['address'] }}</p>
  <p>{{ $company['phone'] }} | {{ $company['email'] }}</p>
</div>

<!-- Payslip Title -->
<h3 style="text-align: center;">Payslip for {{ $payslip->payrollRecord->period }}</h3>

<!-- Employee Details -->
<div class="section-title">Employee Information</div>
<table class="details-table">
  <tr>
    <th>Name</th>
    <td>{{ $payslip->user->first_name }} {{ $payslip->user->last_name }}</td>
  </tr>
  <tr>
    <th>Email</th>
    <td>{{ $payslip->user->email }}</td>
  </tr>
  <tr>
    <th>Employee Code</th>
    <td>{{ $payslip->user->code }}</td>
  </tr>
  <tr>
    <th>Payroll Period</th>
    <td>{{ $payslip->payrollRecord->period }}</td>
  </tr>
</table>

<!-- Salary Details -->
<div class="section-title">Salary Details</div>
<table class="details-table">
  <tr>
    <th>Basic Salary</th>
    <td>{{$currencySymbol}}{{ number_format($payslip->basic_salary, 2) }}</td>
  </tr>
  <tr>
    <th>Total Benefits</th>
    <td>{{$currencySymbol}}{{ number_format($payslip->total_benefits, 2) }}</td>
  </tr>
  <tr>
    <th>Total Deductions</th>
    <td>{{$currencySymbol}}{{ number_format($payslip->total_deductions, 2) }}</td>
  </tr>
  <tr>
    <th>Net Salary</th>
    <td><strong>{{$currencySymbol}}{{ number_format($payslip->net_salary, 2) }}</strong></td>
  </tr>
</table>

<!-- Footer -->
<div class="footer">
  <p>This is a computer-generated payslip and does not require a signature.</p>
  <p>&copy; {{ date('Y') }} {{ $company['name'] }}. All Rights Reserved.</p>
</div>
</body>
</html>
