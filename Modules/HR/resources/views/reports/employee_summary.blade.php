<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير الشؤون الإدارية - {{ now()->format('Y-m-d') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: white;
            color: black;
        }

        .report-header {
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .employee-block {
            page-break-inside: avoid;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-top: 15px;
        }

        .stat-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 10px;
            border-radius: 6px;
            text-align: center;
        }

        .stat-value {
            font-weight: 700;
            font-size: 1.1rem;
            display: block;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #6c757d;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                padding: 0;
                background: white;
            }

            .employee-block {
                border: 1px solid #333 !important;
            }
        }
    </style>
</head>

<body class="p-4">
    <div class="container-fluid">
        <div class="report-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-0">Twinx ERP - إدارة الموارد البشرية</h2>
                <h4 class="text-secondary">تقرير شامل للموظفين</h4>
                <div class="mt-2 text-dark">
                    <strong>الفترة:</strong> {{ $fromDate->format('Y-m-d') }} - {{ $toDate->format('Y-m-d') }}
                </div>
            </div>
            <div class="text-end">
                <button onclick="window.print()" class="btn btn-primary no-print mb-2"><i
                        class="bi bi-printer me-2"></i>طباعة</button>
                <div class="text-secondary small">تاريخ التقرير: {{ now()->format('Y-m-d H:i') }}</div>
            </div>
        </div>

        @foreach($reportData as $data)
            <div class="employee-block">
                <div class="row align-items-center mb-4">
                    <div class="col-md-1">
                        <div class="bg-primary text-heading rounded p-2 text-center">
                            <i class="bi bi-person fs-1"></i>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <h4 class="fw-bold mb-1">{{ $data['employee']->full_name }}</h4>
                        <span class="badge bg-secondary me-2">كود: {{ $data['employee']->employee_code }}</span>
                        <span class="badge bg-info text-dark">{{ $data['employee']->position }} -
                            {{ $data['employee']->department }}</span>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <small class="text-secondary d-block">تاريخ التعيين:</small>
                        <strong>{{ $data['employee']->date_of_joining ? $data['employee']->date_of_joining->format('Y-m-d') : '-' }}</strong>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6 border-start">
                        <h6 class="fw-bold border-bottom pb-2">سجل الحضور خلال الفترة</h6>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <span class="stat-value text-success">{{ $data['attendance']['present'] }}</span>
                                <span class="stat-label">حضور (يوم)</span>
                            </div>
                            <div class="stat-card">
                                <span class="stat-value text-danger">{{ $data['attendance']['absent'] }}</span>
                                <span class="stat-label">غياب (يوم)</span>
                            </div>
                            <div class="stat-card">
                                <span class="stat-value text-warning">{{ $data['attendance']['late'] }}</span>
                                <span class="stat-label">تأخير (يوم)</span>
                            </div>
                            <div class="stat-card">
                                <span
                                    class="stat-value text-primary">{{ floor($data['attendance']['total_minutes'] / 60) }}</span>
                                <span class="stat-label">ساعات العمل</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold border-bottom pb-2">الاستحقاقات المالية</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-secondary">إجمالي الراتب الأساسي:</td>
                                <td class="text-end fw-bold">{{ number_format($data['payroll']['total_basic'], 2) }} ج.م
                                </td>
                            </tr>
                            <tr>
                                <td class="text-secondary">الحوافز والبدلات:</td>
                                <td class="text-end fw-bold text-success">+
                                    {{ number_format($data['payroll']['total_allowances'], 2) }} ج.م
                                </td>
                            </tr>
                            <tr>
                                <td class="text-secondary">الخصومات والجزاءات:</td>
                                <td class="text-end fw-bold text-danger">-
                                    {{ number_format($data['payroll']['total_deductions'], 2) }} ج.م
                                </td>
                            </tr>
                            <tr class="border-top">
                                <td class="fw-bold pt-2">صافي المبلغ المدفوع:</td>
                                <td class="text-end fw-bold text-primary fs-5 pt-2">
                                    {{ number_format($data['payroll']['net_salary'], 2) }} ج.م
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="mt-5 row text-center">
            <div class="col-4">
                <div class="border-top pt-3 w-75 mx-auto">توقيع الموظف</div>
            </div>
            <div class="col-4">
                <div class="border-top pt-3 w-75 mx-auto">مدير الموارد البشرية</div>
            </div>
            <div class="col-4">
                <div class="border-top pt-3 w-75 mx-auto">اعتماد المدير العام</div>
            </div>
        </div>
    </div>
</body>

</html>