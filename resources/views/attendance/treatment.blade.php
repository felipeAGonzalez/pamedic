@extends('layouts.app')

@section('content')
<div class="container" id="treatment-page" data-refresh-url="{{ route('attendance.list.refresh') }}" data-current-turn="{{ $currentTurnKey }}">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h2 class="mb-0">Pacientes para tratamiento</h2>
        <div class="d-flex align-items-center gap-2">
            <small class="text-muted" id="treatment-updated" aria-live="polite">Actualización manual</small>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="treatment-refresh">Actualizar</button>
        </div>
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
    const refreshButton = document.getElementById('treatment-refresh');
    const storageKey = 'attendance.selectedTurn';
    let manualTurn = sessionStorage.getItem(storageKey);
    let currentTurn = page.dataset.currentTurn || null;
    let refreshing = false;
    let programmaticOpen = false;

    const showMessage = (text, type) => {
        message.innerHTML = `<div class="alert alert-${type}" role="alert"></div>`;
        message.firstElementChild.textContent = text;
    };

    const openTurn = () => {
        const key = manualTurn || currentTurn;
        if (!key) return;
        const item = [...list.querySelectorAll('[data-turn-key]')].find(node => node.dataset.turnKey === key);
        const collapse = item?.querySelector('.accordion-collapse');
        if (!collapse) return;
        programmaticOpen = true;
        collapse.addEventListener('shown.bs.collapse', () => { programmaticOpen = false; }, {once: true});
        bootstrap.Collapse.getOrCreateInstance(collapse, {toggle: false}).show();
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
        if (refreshing) return;
        refreshing = true;
        refreshButton.disabled = true;
        const scrollPosition = window.scrollY;

        try {
            const response = await fetch(page.dataset.refreshUrl, {
                credentials: 'same-origin',
                headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            });
            if (!response.ok || !(response.headers.get('content-type') || '').includes('application/json')) {
                throw new Error('invalid-response');
            }
            const data = await response.json();
            if (typeof data.html !== 'string') throw new Error('invalid-content');

            currentTurn = data.current_turn_key || null;
            list.innerHTML = data.html;
            bindAccordion();
            window.requestAnimationFrame(() => window.scrollTo(0, scrollPosition));
            updated.textContent = `Actualizado ${new Date(data.generated_at).toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'})}`;
        } catch (error) {
            showMessage('No fue posible actualizar la lista. Se conserva la información visible.', 'danger');
        } finally {
            refreshing = false;
            refreshButton.disabled = false;
        }
    };

    const markAssigned = (form) => {
        const row = form.closest('tr');
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        button.textContent = 'Asignado';
        row?.classList.add('table-secondary');
        row?.setAttribute('aria-disabled', 'true');
    };

    document.addEventListener('submit', async event => {
        const form = event.target.closest('[data-assignment-form]');
        if (!form) return;
        event.preventDefault();

        const button = form.querySelector('button[type="submit"]');
        if (button.disabled) return;
        button.disabled = true;
        button.textContent = 'Asignando…';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                credentials: 'same-origin',
                headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            });
            const data = (response.headers.get('content-type') || '').includes('application/json') ? await response.json() : {};
            if (!response.ok) {
                const error = new Error(data.message || 'No fue posible asignar al paciente.');
                error.status = response.status;
                throw error;
            }

            markAssigned(form);
            showMessage(data.message || 'Paciente asignado correctamente.', 'success');
        } catch (error) {
            showMessage(error.message || 'Error de red. Inténtalo nuevamente.', 'danger');
            if (error.status === 409) {
                markAssigned(form);
            } else {
                button.disabled = false;
                button.textContent = button.dataset.idleText;
            }
        }
    });

    refreshButton.addEventListener('click', refresh);
    bindAccordion();
});
</script>
@endsection
