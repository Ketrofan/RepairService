@extends('layouts.app', ['title' => 'Создание заявки'])

@section('content')
<div class="card">
    <h2>Создать заявку в ремонт</h2>
    <p class="muted">После создания статус будет <b>new</b>.</p>

    <form method="POST" action="{{ route('requests.store') }}">
        @csrf

        <div class="row">
            <div>
                <label>ФИО клиента *</label>
                <input type="text" name="client_name" value="{{ old('client_name') }}" required>
                <div class="muted" style="margin-top:6px;">Только буквы (можно пробел/дефис).</div>
            </div>

            <div>
                <label>Телефон *</label>
                <input type="text"
                       name="phone"
                       value="{{ old('phone') }}"
                       required
                       inputmode="numeric"
                       maxlength="12"
                       placeholder="+7XXXXXXXXXX"
                       data-phone-ru="1">
                <div class="muted" style="margin-top:6px;">Формат: +7.</div>
            </div>
        </div>

        <div class="row" style="margin-top:10px;">
            <div>
                <label>Адрес *</label>
                <input type="text" name="address" value="{{ old('address') }}" required>
            </div>
        </div>

        <div style="margin-top:10px;">
            <label>Описание проблемы *</label>
            <textarea name="problem_text" rows="3" required data-autogrow="1">{{ old('problem_text') }}</textarea>
        </div>

        <div style="margin-top:12px;">
            <button type="submit">Создать</button>
        </div>
    </form>
</div>
@endsection