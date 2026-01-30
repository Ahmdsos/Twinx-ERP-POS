@extends('layouts.app')

@section('title', 'استيراد البيانات - Twinx ERP')
@section('page-title', 'استيراد البيانات من CSV')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">استيراد البيانات</li>
@endsection

@section('content')
    @if(session('import_errors'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <h6 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i>بعض الأخطاء أثناء الاستيراد:</h6>
            <ul class="mb-0 small">
                @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Products Import -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-box-seam me-2"></i>المنتجات
                </div>
                <div class="card-body">
                    <p class="text-muted small">استيراد المنتجات مع كل البيانات: الاسم، الباركود، السعر، الكمية، العلامة
                        التجارية، إلخ.</p>
                    <form action="{{ route('import.products') }}" method="POST" enctype="multipart/form-data" class="mb-3">
                        @csrf
                        <div class="mb-3">
                            <input type="file" class="form-control form-control-sm" name="file" accept=".csv">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                                <i class="bi bi-upload me-1"></i>استيراد
                            </button>
                            <button type="submit" formaction="{{ route('import.products') }}"
                                class="btn btn-outline-success btn-sm" title="استيراد Demo Data">
                                <i class="bi bi-play-fill"></i> Demo
                            </button>
                        </div>
                    </form>
                    <a href="{{ route('import.template', 'products') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-download me-1"></i>تحميل القالب (15 منتج)
                    </a>
                </div>
            </div>
        </div>

        <!-- Customers Import -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-people me-2"></i>العملاء
                </div>
                <div class="card-body">
                    <p class="text-muted small">استيراد العملاء مع بيانات الاتصال، العنوان، حد الائتمان، وشروط الدفع.</p>
                    <form action="{{ route('import.customers') }}" method="POST" enctype="multipart/form-data" class="mb-3">
                        @csrf
                        <div class="mb-3">
                            <input type="file" class="form-control form-control-sm" name="file" accept=".csv">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-sm flex-grow-1">
                                <i class="bi bi-upload me-1"></i>استيراد
                            </button>
                            <button type="submit" class="btn btn-outline-success btn-sm" title="استيراد Demo Data">
                                <i class="bi bi-play-fill"></i> Demo
                            </button>
                        </div>
                    </form>
                    <a href="{{ route('import.template', 'customers') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-download me-1"></i>تحميل القالب (10 عميل)
                    </a>
                </div>
            </div>
        </div>

        <!-- Suppliers Import -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-truck me-2"></i>الموردين
                </div>
                <div class="card-body">
                    <p class="text-muted small">استيراد الموردين مع بيانات الشركة، جهة الاتصال، وشروط الدفع.</p>
                    <form action="{{ route('import.suppliers') }}" method="POST" enctype="multipart/form-data" class="mb-3">
                        @csrf
                        <div class="mb-3">
                            <input type="file" class="form-control form-control-sm" name="file" accept=".csv">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning btn-sm flex-grow-1">
                                <i class="bi bi-upload me-1"></i>استيراد
                            </button>
                            <button type="submit" class="btn btn-outline-success btn-sm" title="استيراد Demo Data">
                                <i class="bi bi-play-fill"></i> Demo
                            </button>
                        </div>
                    </form>
                    <a href="{{ route('import.template', 'suppliers') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-download me-1"></i>تحميل القالب (8 مورد)
                    </a>
                </div>
            </div>
        </div>

        <!-- Categories Import -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-tags me-2"></i>الفئات
                </div>
                <div class="card-body">
                    <p class="text-muted small">استيراد فئات المنتجات مع التسلسل الهرمي (فئات رئيسية وفرعية).</p>
                    <form action="{{ route('import.categories') }}" method="POST" enctype="multipart/form-data"
                        class="mb-3">
                        @csrf
                        <div class="mb-3">
                            <input type="file" class="form-control form-control-sm" name="file" accept=".csv">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-info btn-sm flex-grow-1">
                                <i class="bi bi-upload me-1"></i>استيراد
                            </button>
                            <button type="submit" class="btn btn-outline-success btn-sm" title="استيراد Demo Data">
                                <i class="bi bi-play-fill"></i> Demo
                            </button>
                        </div>
                    </form>
                    <a href="{{ route('import.template', 'categories') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-download me-1"></i>تحميل القالب (14 فئة)
                    </a>
                </div>
            </div>
        </div>

        <!-- Warehouses Import -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <i class="bi bi-building me-2"></i>المستودعات
                </div>
                <div class="card-body">
                    <p class="text-muted small">استيراد المستودعات والفروع مع العنوان ومسؤول المستودع.</p>
                    <form action="{{ route('import.warehouses') }}" method="POST" enctype="multipart/form-data"
                        class="mb-3">
                        @csrf
                        <div class="mb-3">
                            <input type="file" class="form-control form-control-sm" name="file" accept=".csv">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-secondary btn-sm flex-grow-1">
                                <i class="bi bi-upload me-1"></i>استيراد
                            </button>
                            <button type="submit" class="btn btn-outline-success btn-sm" title="استيراد Demo Data">
                                <i class="bi bi-play-fill"></i> Demo
                            </button>
                        </div>
                    </form>
                    <a href="{{ route('import.template', 'warehouses') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-download me-1"></i>تحميل القالب (7 مستودع)
                    </a>
                </div>
            </div>
        </div>

        <!-- Units Import -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-dark text-white">
                    <i class="bi bi-rulers me-2"></i>وحدات القياس
                </div>
                <div class="card-body">
                    <p class="text-muted small">استيراد وحدات القياس (قطعة، كرتونة، كيلو، متر، إلخ).</p>
                    <form action="{{ route('import.units') }}" method="POST" enctype="multipart/form-data" class="mb-3">
                        @csrf
                        <div class="mb-3">
                            <input type="file" class="form-control form-control-sm" name="file" accept=".csv">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-dark btn-sm flex-grow-1">
                                <i class="bi bi-upload me-1"></i>استيراد
                            </button>
                            <button type="submit" class="btn btn-outline-success btn-sm" title="استيراد Demo Data">
                                <i class="bi bi-play-fill"></i> Demo
                            </button>
                        </div>
                    </form>
                    <a href="{{ route('import.template', 'units') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-download me-1"></i>تحميل القالب (15 وحدة)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Import All Demo Data -->
    <div class="card mt-4 border-0 shadow">
        <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <h5 class="mb-0 text-white"><i class="bi bi-lightning-charge me-2"></i>استيراد جميع البيانات التجريبية</h5>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <p class="mb-0">استيراد جميع البيانات التجريبية دفعة واحدة: 15 منتج، 10 عملاء، 8 موردين، 14 فئة، 7
                        مستودعات، 15 وحدة قياس.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <form action="{{ route('import.index') }}" method="GET" id="importAllForm">
                        <button type="button" class="btn btn-lg text-white"
                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"
                            onclick="importAllDemo()">
                            <i class="bi bi-cloud-upload me-2"></i>استيراد الكل
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        async function importAllDemo() {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>جاري الاستيراد...';
            btn.disabled = true;

            const imports = [
                { url: '{{ route("import.categories") }}', name: 'الفئات' },
                { url: '{{ route("import.units") }}', name: 'الوحدات' },
                { url: '{{ route("import.warehouses") }}', name: 'المستودعات' },
                { url: '{{ route("import.suppliers") }}', name: 'الموردين' },
                { url: '{{ route("import.customers") }}', name: 'العملاء' },
                { url: '{{ route("import.products") }}', name: 'المنتجات' },
            ];

            for (const imp of imports) {
                try {
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');

                    await fetch(imp.url, {
                        method: 'POST',
                        body: formData
                    });

                    showToast(`✅ تم استيراد ${imp.name}`, 'success');
                } catch (error) {
                    showToast(`❌ فشل استيراد ${imp.name}`, 'danger');
                }
            }

            btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>تم الاستيراد!';
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                location.reload();
            }, 2000);
        }
    </script>
@endpush