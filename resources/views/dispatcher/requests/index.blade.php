@extends('layouts.app', ['title' => 'Панель диспетчера'])

@section('content')
<div class="card">
    <h2>Панель диспетчера</h2>

    <form method="GET" action="{{ route('dispatcher.requests.index') }}" class="row">
        <div>
            <label>Фильтр по статусу</label>
            <select name="status">
                <option value="">Все</option>
                @foreach($allStatuses as $s)
                    <option value="{{ $s }}" @selected(($status ?? '') === $s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="submit">Применить</button>
        </div>
    </form>
</div>

<div class="card">
    <h3>Заявки</h3>

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
                <th>Мастер</th>
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
                        @if($r->assignedMaster)
                            {{ $r->assignedMaster->name }} ({{ $r->assignedMaster->email }})
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            @if($st === 'new')
                                <form method="POST" action="{{ route('dispatcher.requests.assign', $r) }}">
                                    @csrf
                                    <div style="margin-bottom:8px;">
                                        <select name="assigned_master_id" required class="select-compact">
                                            <option value="">Выбрать мастера</option>
                                            @foreach($masters as $m)
                                             <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->email }})</option>
                                         @endforeach
                                        </select>
                                    </div>
                                    <button type="submit">Назначить</button>
                                </form>
                            @endif

                            @if(!in_array($st, ['done','canceled'], true))
                                <form method="POST" action="{{ route('dispatcher.requests.cancel', $r) }}">
                                    @csrf
                                    <button type="submit">Отменить</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="muted">Нет заявок</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:12px;">
        {{ $requests->links() }}
    </div>
</div>
@endsection