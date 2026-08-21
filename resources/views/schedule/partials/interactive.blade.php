@if($canEdit)

    <div class="modal fade" id="schedule-patient-modal" tabindex="-1" aria-labelledby="schedule-patient-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="schedule-patient-modal-title">Agregar paciente al horario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p id="schedule-target-description" class="text-muted"></p>
                    <form id="schedule-patient-search-form">
                        <label for="schedule-patient-search" class="form-label">Nombre o número de expediente</label>
                        <div class="input-group">
                            <input id="schedule-patient-search" type="search" class="form-control"
                                   minlength="2" maxlength="100" required autocomplete="off">
                            <button type="submit" class="btn btn-primary">Buscar</button>
                        </div>
                        <div id="schedule-search-error" class="text-danger mt-2" role="alert"></div>
                    </form>
                    <div id="schedule-patient-results" class="list-group mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .schedule-cell {
            vertical-align: top;
            transition: background-color .15s ease, outline-color .15s ease;
        }
        .schedule-cell[role="button"]:hover,
        .schedule-cell[role="button"]:focus {
            background-color: #f5f8ff;
            outline: 2px solid #8da8ff;
            outline-offset: -2px;
        }
        .schedule-cell.schedule-drop-target {
            background-color: #e8f4ff;
            outline: 2px dashed #0d6efd;
            outline-offset: -3px;
        }
        .schedule-patient-card[draggable="true"] {
            cursor: grab;
        }
        .schedule-patient-card.is-dragging {
            opacity: .55;
        }
        .schedule-card-actions form {
            margin: 0;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = @json(csrf_token());
            const patientSearchUrl = @json(route('schedule.patientSearch'));
            const assignUrl = @json(route('schedule.assign'));
            const scheduleBaseUrl = @json(url('/schedule'));
            const modalElement = document.getElementById('schedule-patient-modal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            const searchForm = document.getElementById('schedule-patient-search-form');
            const searchInput = document.getElementById('schedule-patient-search');
            const results = document.getElementById('schedule-patient-results');
            const searchError = document.getElementById('schedule-search-error');
            const targetDescription = document.getElementById('schedule-target-description');
            const feedback = document.getElementById('schedule-feedback');
            let targetCell = null;
            let draggedCard = null;

            function showFeedback(message, type) {
                feedback.textContent = message;
                feedback.className = 'alert mt-3 alert-' + (type || 'success');
                feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            async function responseData(response) {
                try {
                    return await response.json();
                } catch (error) {
                    return {};
                }
            }

            function refreshCell(cell) {
                const count = cell.querySelectorAll('.schedule-patient-card').length;
                cell.dataset.recordCount = String(count);
                const placeholder = cell.querySelector('.schedule-empty-placeholder');

                if (placeholder) {
                    placeholder.remove();
                }

                if (count === 0) {
                    const empty = document.createElement('div');
                    empty.className = 'schedule-empty-placeholder text-muted text-center py-2';
                    empty.innerHTML = '<i class="bi bi-person-plus"></i> Agregar';
                    cell.appendChild(empty);
                }
            }

            function openPatientModal(cell) {
                if (cell.dataset.recordCount !== '0') {
                    return;
                }

                targetCell = cell;
                results.replaceChildren();
                searchError.textContent = '';
                searchInput.value = '';
                targetDescription.textContent = 'Fecha: ' + cell.dataset.date
                    + ' · Turno: ' + cell.dataset.scheduleId
                    + ' · Máquina: ' + cell.dataset.machineId;
                modal.show();
                setTimeout(function () {
                    searchInput.focus();
                }, 200);
            }

            document.addEventListener('click', function (event) {
                const cell = event.target.closest('.schedule-cell');
                if (! cell || event.target.closest('.schedule-patient-card')) {
                    return;
                }

                openPatientModal(cell);
            });

            document.addEventListener('keydown', function (event) {
                const cell = event.target.closest('.schedule-cell');
                if (cell && (event.key === 'Enter' || event.key === ' ')) {
                    event.preventDefault();
                    openPatientModal(cell);
                }
            });

            searchForm.addEventListener('submit', async function (event) {
                event.preventDefault();
                const search = searchInput.value.trim();
                results.replaceChildren();
                searchError.textContent = '';

                if (search.length < 2) {
                    searchError.textContent = 'Ingrese al menos 2 caracteres.';
                    return;
                }

                const submitButton = searchForm.querySelector('button[type="submit"]');
                submitButton.disabled = true;

                try {
                    const response = await fetch(patientSearchUrl + '?search=' + encodeURIComponent(search), {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    });
                    const data = await responseData(response);

                    if (! response.ok) {
                        throw new Error(data.message || 'No fue posible buscar pacientes.');
                    }

                    if (! data.patients.length) {
                        searchError.textContent = 'No se encontraron pacientes.';
                        return;
                    }

                    data.patients.forEach(function (patient) {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'list-group-item list-group-item-action';
                        button.textContent = patient.expedient_number + ' · '
                            + [patient.name, patient.last_name, patient.last_name_two].filter(Boolean).join(' ');
                        button.addEventListener('click', function () {
                            assignPatient(patient.id, button);
                        });
                        results.appendChild(button);
                    });
                } catch (error) {
                    searchError.textContent = error.message;
                } finally {
                    submitButton.disabled = false;
                }
            });

            async function assignPatient(patientId, button) {
                if (! targetCell) {
                    return;
                }

                button.disabled = true;
                searchError.textContent = '';

                try {
                    const response = await fetch(assignUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            patient_id: patientId,
                            schedules_id: targetCell.dataset.scheduleId,
                            date: targetCell.dataset.date,
                            machine_id: targetCell.dataset.machineId
                        })
                    });
                    const data = await responseData(response);

                    if (! response.ok) {
                        throw new Error(data.message || 'No fue posible agregar al paciente.');
                    }

                    targetCell.innerHTML = data.html;
                    refreshCell(targetCell);
                    modal.hide();
                    showFeedback(data.message);
                } catch (error) {
                    searchError.textContent = error.message;
                    button.disabled = false;
                }
            }

            document.addEventListener('dragstart', function (event) {
                const card = event.target.closest('.schedule-patient-card');
                if (! card) {
                    return;
                }

                draggedCard = card;
                card.classList.add('is-dragging');
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', card.dataset.recordId);
            });

            document.addEventListener('dragend', function () {
                if (draggedCard) {
                    draggedCard.classList.remove('is-dragging');
                }
                draggedCard = null;
                document.querySelectorAll('.schedule-drop-target').forEach(function (cell) {
                    cell.classList.remove('schedule-drop-target');
                });
            });

            document.addEventListener('dragover', function (event) {
                const cell = event.target.closest('.schedule-cell');
                if (! cell || ! draggedCard) {
                    return;
                }

                const sourceCell = draggedCard.closest('.schedule-cell');
                if (sourceCell.dataset.date !== cell.dataset.date
                    || sourceCell.dataset.scheduleId !== cell.dataset.scheduleId) {
                    return;
                }

                event.preventDefault();
                event.dataTransfer.dropEffect = 'move';
                cell.classList.add('schedule-drop-target');
            });

            document.addEventListener('dragleave', function (event) {
                const cell = event.target.closest('.schedule-cell');
                if (cell && ! cell.contains(event.relatedTarget)) {
                    cell.classList.remove('schedule-drop-target');
                }
            });

            document.addEventListener('drop', async function (event) {
                const destinationCell = event.target.closest('.schedule-cell');
                const sourceCard = draggedCard;

                if (! destinationCell || ! sourceCard) {
                    return;
                }

                event.preventDefault();
                destinationCell.classList.remove('schedule-drop-target');
                const sourceCell = sourceCard.closest('.schedule-cell');

                if (sourceCell === destinationCell) {
                    return;
                }

                if (sourceCell.dataset.date !== destinationCell.dataset.date
                    || sourceCell.dataset.scheduleId !== destinationCell.dataset.scheduleId) {
                    showFeedback('Solo puede cambiar máquinas dentro del mismo día y turno.', 'danger');
                    return;
                }

                const destinationCards = Array.from(destinationCell.querySelectorAll('.schedule-patient-card'));
                if (destinationCards.length > 1) {
                    showFeedback('La máquina destino contiene registros duplicados y no puede intercambiarse automáticamente.', 'danger');
                    return;
                }

                const destinationCard = destinationCards[0] || null;
                if (destinationCard && ! confirm('La máquina está ocupada. ¿Desea intercambiar ambos pacientes?')) {
                    return;
                }

                try {
                    const response = await fetch(scheduleBaseUrl + '/' + sourceCard.dataset.recordId + '/move', {
                        method: 'PATCH',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ machine_id: destinationCell.dataset.machineId })
                    });
                    const data = await responseData(response);

                    if (! response.ok) {
                        throw new Error(data.message || 'No fue posible cambiar la máquina.');
                    }

                    if (destinationCard) {
                        sourceCell.appendChild(destinationCard);
                    }
                    destinationCell.appendChild(sourceCard);
                    refreshCell(sourceCell);
                    refreshCell(destinationCell);
                    showFeedback(data.message);
                } catch (error) {
                    showFeedback(error.message, 'danger');
                }
            });
        });
    </script>
@endif
