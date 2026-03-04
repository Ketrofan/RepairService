<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Repair Service' }}</title>
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            max-width: 1100px;
            margin: 20px auto;
            padding: 0 12px;
        }

        .nav { display:flex; gap:12px; align-items:center; flex-wrap:wrap; margin-bottom:16px; }
        .nav a { text-decoration:none; }

        .card { border:1px solid #ddd; border-radius:10px; padding:12px; margin-bottom:14px; }

        /* Главное: вместо flex — grid (ничего не "заходит" и не вылезает) */
        .row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 12px;
            align-items: end;
        }

        label { display:block; margin-bottom:6px; }

        input, textarea, select {
            width: 100%;
            max-width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 8px;
            min-width: 0;
        }

        /* Запрещаем ручной resize, т.к. textarea будет автодинамической */
        textarea { resize: none; overflow: hidden; }

        button {
            padding: 8px 12px;
            border: 1px solid #333;
            background: #fff;
            border-radius: 8px;
            cursor: pointer;
        }

        .table-wrap { width:100%; overflow-x:auto; }
        table { width:100%; border-collapse: collapse; min-width: 860px; }
        th, td { border-bottom:1px solid #eee; padding:8px; text-align:left; vertical-align:top; }

        .msg-success { background:#e8fff0; border:1px solid #b7f0c8; padding:10px; border-radius:8px; margin-bottom:12px; }
        .msg-error { background:#ffecec; border:1px solid #f5b7b7; padding:10px; border-radius:8px; margin-bottom:12px; }
        .muted { color:#666; }
        .actions { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }

        details { margin-top:6px; }
        summary { cursor:pointer; }
        .prewrap { white-space: pre-wrap; }
        .select-compact {
            width: 165px;
            min-width: 165px;
            height: 34px;
            padding: 4px 16px 4px 10px; /* меньше пустого места справа */
            font-size: 14px;
            line-height: 1.2;
            white-space: nowrap;

            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;

    background-color: #fff;
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'/></svg>");
            background-repeat: no-repeat;
            background-position: right 6px center; 
            background-size: 10px 10px;
}
    </style>
</head>
<body>

<div class="nav">
    <a href="{{ route('requests.create') }}">Создать заявку</a>

    @auth
        <span class="muted">|</span>
        <span>Пользователь: <b>{{ auth()->user()->email }}</b></span>
        <span class="muted">(роль: {{ is_object(auth()->user()->role) ? auth()->user()->role->value : auth()->user()->role }})</span>

        @if(auth()->user()->role?->value === 'dispatcher')
            <a href="{{ route('dispatcher.requests.index') }}">Панель диспетчера</a>
        @endif

        @if(auth()->user()->role?->value === 'master')
            <a href="{{ route('master.requests.index') }}">Панель мастера</a>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Выйти</button>
        </form>
    @else
        <span class="muted">|</span>
        <a href="{{ route('login') }}">Вход</a>
    @endauth
</div>

@if(session('success'))
    <div class="msg-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="msg-error">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="msg-error">
        <b>Ошибки:</b>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@yield('content')

<script>
/**
 * Auto-grow textarea: растёт по контенту, без ручного resize.
 * Включается для textarea с атрибутом data-autogrow="1"
 */
(function () {
    const textareas = document.querySelectorAll('textarea[data-autogrow="1"]');
    const resize = (ta) => {
        ta.style.height = 'auto';
        ta.style.height = (ta.scrollHeight) + 'px';
    };
    textareas.forEach((ta) => {
        resize(ta);
        ta.addEventListener('input', () => resize(ta));
    });
})();

/**
 * Phone input: автопрефикс +7 и строго 10 цифр после него.
 * Включается для input с атрибутом data-phone-ru="1"
 */
(function () {
    const inputs = document.querySelectorAll('input[data-phone-ru="1"]');

    const normalize = (value) => {
        let digits = String(value || '').replace(/\D/g, '');
        // если ввели 7XXXXXXXXXX или 8XXXXXXXXXX — убираем ведущую цифру
        if (digits.startsWith('7') || digits.startsWith('8')) digits = digits.slice(1);
        digits = digits.slice(0, 10);
        return '+7' + digits;
    };

    inputs.forEach((inp) => {
        inp.addEventListener('focus', () => {
            if (!inp.value) inp.value = '+7';
            if (!inp.value.startsWith('+7')) inp.value = normalize(inp.value);
        });

        inp.addEventListener('input', () => {
            inp.value = normalize(inp.value);
        });
    });
})();
</script>

</body>
</html>