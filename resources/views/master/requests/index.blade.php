@extends('layouts.app', ['title' => 'Панель мастера'])

@section('content')
<div class="card">
    <h2>Панель мастера</h2>
    <p class="muted">Показываются заявки, назначенные на вас, в статусах <b>assigned</b> и <b>in_progress</b>.</p>
</div>

<div class="card">
    <h3>Мои заявки</h3>

    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Клиент</th>
                <th>Телефон</th>
                <th>Адрес</th>
                <th>Проблема</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            @forelse($requests as $r)
                @php $st = is_object($r->status) ? $r->status->value : $r->status; @endphp
                <tr>
                    <td>#{{ $r->id }}</td>
                    <td>{{ $r->client_name }}</td>
                    <td>{{ $r->phone }}</td>
                    <td>{{ $r->address }}</td>
                    <td>
                        <div>{{ \Illuminate\Support\Str::limit($r->problem_text, 80) }}</div>

                        @if(mb_strlen($r->problem_text) > 80)
                            <details>
                                <summary>Развернуть</summary>
                                <div class="prewrap">{{ $r->problem_text }}</div>
                            </details>
                        @endif
                    </td>
                    <td><b>{{ $st }}</b></td>
                    <td>
                        <div class="actions">
                            @if($st === 'assigned')
                                <form method="POST" action="{{ route('master.requests.take', $r) }}">
                                    @csrf
                                    <button type="submit">Взять в работу</button>
                                </form>
                            @endif

                            @if($st === 'in_progress')
                                <form method="POST" action="{{ route('master.requests.done', $r) }}">
                                    @csrf
                                    <button type="submit">Завершить</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">Нет назначенных заявок</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:12px;">
        {{ $requests->links() }}
    </div>
</div>
@endsection