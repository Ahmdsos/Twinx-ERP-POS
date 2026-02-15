@extends('layouts.app')

@section('title', 'التقارير - Reports')

@section('content')
    <div class="row g-4">
        <!-- Financial Reports -->
        <div class="col-md-4">
            <a href="{{ route('reports.financial.pl', ['type' => 'pl']) }}" class="text-decoration-none">
                <div class="glass-card hover-scale p-4 h-100 text-center">
                    <div class="icon-circle bg-primary bg-opacity-20 text-primary mb-3 mx-auto">
                        <i class="bi bi-graph-up-arrow fs-3"></i>
                    </div>
                    <h4 class="text-heading fw-bold mb-2">القوائم المالية</h4>
                    <p class="text-gray-400 small mb-0">قائمة الدخل (الأرباح والخسائر) والميزانية العمومية</p>
                    <div class="mt-3 badge bg-primary bg-opacity-10 text-primary">Financial Statements</div>
                </div>
            </a>
            </a>
        </div>

        <!-- Balance Sheet Report -->
        <div class="col-md-4">
            <a href="{{ route('reports.financial.bs', ['type' => 'bs']) }}" class="text-decoration-none">
                <div class="glass-card hover-scale p-4 h-100 text-center">
                    <div class="icon-circle bg-info bg-opacity-20 text-info mb-3 mx-auto">
                        <i class="bi bi-bank fs-3"></i>
                    </div>
                    <h4 class="text-heading fw-bold mb-2">{{ __('Balance Sheet') }}</h4>
                    <p class="text-gray-400 small mb-0">الأصول، الالتزامات، وحقوق الملكية</p>
                    <div class="mt-3 badge bg-info bg-opacity-10 text-info">Balance Sheet</div>
                </div>
            </a>
        </div>

        <!-- Inventory Reports -->
        <div class="col-md-4">
            <a href="{{ route('reports.inventory.valuation') }}" class="text-decoration-none">
                <div class="glass-card hover-scale p-4 h-100 text-center">
                    <div class="icon-circle bg-success bg-opacity-20 text-success mb-3 mx-auto">
                        <i class="bi bi-box-seam fs-3"></i>
                    </div>
                    <h4 class="text-heading fw-bold mb-2">{{ __('Stock Valuation') }}</h4>
                    <p class="text-gray-400 small mb-0">قيمة البضاعة في المخازن وتكلفة الأصول الحالية</p>
                    <div class="mt-3 badge bg-success bg-opacity-10 text-success">Stock Valuation</div>
                </div>
            </a>
        </div>

        <!-- Sales Reports -->
        <div class="col-md-4">
            <a href="{{ route('reports.sales.by-product') }}" class="text-decoration-none">
                <div class="glass-card hover-scale p-4 h-100 text-center">
                    <div class="icon-circle bg-warning bg-opacity-20 text-warning mb-3 mx-auto">
                        <i class="bi bi-pie-chart fs-3"></i>
                    </div>
                    <h4 class="text-heading fw-bold mb-2">{{ __('Sales Analysis') }}</h4>
                    <p class="text-gray-400 small mb-0">الأكثر مبيعاً، تحليل العملاء، والأداء الشهري</p>
                    <div class="mt-3 badge bg-warning bg-opacity-10 text-warning">Sales Analysis</div>
                </div>
            </a>
        </div>
    </div>

    <style>
        

        .hover-scale:hover {
            transform: translateY(-5px);
            background: rgba(30, 41, 59, 0.9);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@endsection