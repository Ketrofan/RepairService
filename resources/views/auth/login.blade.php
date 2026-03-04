@extends('layouts.app', ['title' => 'Вход'])

@section('content')
<div class="card">
    <h2>Вход</h2>
    <p class="muted">Тестовые пользователи: dispatcher@example.com / master1@example.com / master2@example.com, пароль: password</p>

    <form method="POST" action="{{ route('login.perform') }}">
        @csrf

        <div class="row">
            <div>
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div>
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>
        </div>

        <div style="margin-top:12px;">
            <button type="submit">Войти</button>
        </div>
    </form>
</div>
@endsection