@extends('layouts.auth')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card p-4">
                    <div class="text-center mb-4">
                        <img src="{{ asset('images/logo.png') }}" alt="Twinx ERP" class="img-fluid mb-3"
                            style="max-height: 120px;">
                        <h4 class="fw-bold text-primary">Twinx ERP</h4>
                        <p class="text-secondary small">تسجيل الدخول للنظام</p>
                    </div>

                    <form method="POST" action="{{ route('login.submit') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control" required autofocus
                                value="admin@twinx.com">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">كلمة المرور</label>
                            <input type="password" name="password" class="form-control" required value="password">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary fw-bold">دخول</button>
                        </div>

                        @if($errors->any())
                            <div class="alert alert-danger mt-3 text-center small mb-0 p-2">
                                {{ $errors->first() }}
                            </div>
                        @endif
                    </form>
                </div>
                <div class="text-center mt-3 text-secondary small">
                    &copy; {{ date('Y') }} Twinx ERP System
                </div>
            </div>
        </div>
    </div>
@endsection