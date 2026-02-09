@extends('layouts.app')

@section('title', 'تنشيط النظام')

@section('content')
    <div class="container d-flex align-items-center justify-content-center" style="min-height: 80vh;">
        <div class="glass-panel p-5 shadow-neon text-center" style="max-width: 600px; width: 100%;">
            <div class="icon-box bg-gradient-purple shadow-neon mx-auto mb-4" style="width: 80px; height: 80px;">
                <i class="bi bi-shield-lock-fill fs-1 text-white"></i>
            </div>

            <h2 class="fw-bold text-white mb-3 tracking-wide">تنشيط Twinx ERP</h2>
            <p class="text-gray-400 mb-5">النظام غير مفعل على هذا الجهاز. يرجى إرسال "بصمة الجهاز" للمورد للحصول على كود
                التفعيل.</p>

            <div class="bg-slate-900 bg-opacity-50 p-4 rounded-4 border border-white-10 mb-5">
                <label class="text-purple-400 x-small fw-bold text-uppercase d-block mb-2">بصمة الجهاز (Machine ID)</label>
                <div class="d-flex align-items-center justify-content-center gap-3">
                    <code class="text-white fs-4 tracking-widest">{{ $machineId }}</code>
                    <button class="btn btn-sm btn-dark-glass" onclick="copyToClipboard('{{ $machineId }}')" title="نسخ">
                        <i class="bi bi-clipboard"></i>
                    </button>
                </div>
            </div>

            <form action="{{ route('system.activate.submit') }}" method="POST">
                @csrf
                <div class="mb-4 text-start">
                    <label class="form-label text-gray-300 small">كود التفعيل (License Key)</label>
                    <textarea name="license_key" class="form-control form-control-dark font-monospace small" rows="5"
                        required placeholder="قم بلصق الكود المستلم هنا..."></textarea>
                </div>

                <button type="submit" class="btn btn-action-purple w-100 py-3 fw-bold fs-5">
                    <i class="bi bi-check2-circle me-2"></i> تفعيل النظام الآن
                </button>
            </form>

            <p class="mt-4 text-gray-500 x-small">Twinx ERP &copy; {{ date('Y') }} - جميع الحقوق محفوظة</p>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text);
            // You could add a toast here
            alert('تم النسخ: ' + text);
        }
    </script>

    <style>
        /* Reuse existing styles from index but scoped to activation */
        .icon-box {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
        }

        .bg-gradient-purple {
            background: linear-gradient(135deg, #a855f7 0%, #7e22ce 100%);
        }

        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
        }

        .form-control-dark {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
            padding: 1rem;
        }

        .btn-action-purple {
            background: linear-gradient(135deg, #a855f7 0%, #7e22ce 100%);
            border: none;
            color: white;
            box-shadow: 0 0 20px rgba(168, 85, 247, 0.3);
            border-radius: 12px;
        }

        .btn-dark-glass {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #94a3b8;
        }
    </style>
@endsection