@extends('layouts.app')

@section('content')
<div class="container" id="treatment-page" data-refresh-url="{{ route('attendance.list.refresh') }}" data-current-turn="{{ $currentTurnKey }}">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h2 class="mb-0">Pacientes para tratamiento</h2>
        <small class="text-muted" id="treatment-updated" aria-live="polite"></small>
    </div>
    <div id="treatment-message" aria-live="assertive">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error') || session('Error'))<div class="alert alert-danger">{{ session('error') ?? session('Error') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    </div>
    <div id="treatment-list">@include('attendance.partials.treatment-accordions')</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const page = document.getElementById('treatment-page');
    const list = document.getElementById('treatment-list');
    const message = document.getElementById('treatment-message');
    const updated = document.getElementById('treatment-updated');
    const storageKey = 'attendance.selectedTurn';
    let manualTurn = sessionStorage.getItem(storageKey);
    let currentTurn = page.dataset.currentTurn || null;
    let refreshing = false;
    let programmaticOpen = false;

    const showMessage = (text, type) => { message.innerHTML = `<div class="alert alert-${type}" role="alert"></div>`; message.firstElementChild.textContent = text; };
    const openTurn = () => {
        const key = manualTurn || currentTurn;
        if (!key) return;
        const item = [...list.querySelectorAll('[data-turn-key]')].find(node => node.dataset.turnKey === key);
        if (item) {
            programmaticOpen = true;
            const collapse = item.querySelector('.accordion-collapse');
            collapse.addEventListener('shown.bs.collapse', () => { programmaticOpen = false; }, {once: true});
            bootstrap.Collapse.getOrCreateInstance(collapse, {toggle: false}).show();
        }
    };
    const bindAccordion = () => {
        list.querySelectorAll('.accordion-collapse').forEach(collapse => collapse.addEventListener('show.bs.collapse', () => {
            if (programmaticOpen) return;
            manualTurn = collapse.closest('[data-turn-key]').dataset.turnKey;
            sessionStorage.setItem(storageKey, manualTurn);
        }));
        openTurn();
    };
    const refresh = async () => {
        if (refreshing || document.hidden) return;
        refreshing = true;
        try {
            const response = await fetch(page.dataset.refreshUrl, {credentials: 'same-origin', headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
            if (!response.ok || !(response.headers.get('content-type') || '').includes('application/json')) throw new Error('invalid-response');
            const data = await response.json();
            if (typeof data.html !== 'string' || !data.html.trim()) throw new Error('invalid-content');
            currentTurn = data.current_turn_key || null;
            list.innerHTML = data.html;
            bindAccordion();
            updated.textContent = `Actualizado ${new Date(data.generated_at).toLocaleTimeString([], {hour: '2-digit', minute: '2-digit', second: '2-digit'})}`;
        } catch (error) { updated.textContent = 'No se pudo actualizar; se conserva la lista visible.'; }
        finally { refreshing = false; }
    };
    document.addEventListener('submit', async event => {
        const form = event.target.closest('[data-assignment-form]');
        if (!form) return;
        event.preventDefault();
        const button = form.querySelector('button[type="submit"]');
        if (button.disabled) return;
        button.disabled = true; button.textContent = 'Asignando…';
        try {
            const response = await fetch(form.action, {method: 'POST', body: new FormData(form), credentials: 'same-origin', headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
            const data = (response.headers.get('content-type') || '').includes('application/json') ? await response.json() : {};
            if (!response.ok) throw {message: data.message || 'No fue posible asignar al paciente.'};
            showMessage(data.message || 'Paciente asignado correctamente.', 'success');
            await refresh();
        } catch (error) { showMessage(error.message || 'Error de red. Inténtalo nuevamente.', 'danger'); button.disabled = false; button.textContent = button.dataset.idleText; }
    });
    bindAccordion();
    const intervalId = window.setInterval(refresh, 5000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
    window.addEventListener('pagehide', () => clearInterval(intervalId), {once: true});
});
</script>
@endsection
