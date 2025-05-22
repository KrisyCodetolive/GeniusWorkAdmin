@extends('layouts.app')

@section('title', 'Calendrier des congés')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
<style>
    #calendar {
        height: 700px;
    }
    .fc-event {
        cursor: pointer;
    }
    .fc-day-today {
        background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
    }
    .fc-toolbar-title {
        font-size: 1.5rem !important;
    }
    .fc-button-primary {
        background-color: var(--bs-primary) !important;
        border-color: var(--bs-primary) !important;
    }
    .fc-button-primary:hover {
        background-color: var(--bs-primary-dark) !important;
        border-color: var(--bs-primary-dark) !important;
    }
    .fc-button-active {
        background-color: var(--bs-primary-dark) !important;
        border-color: var(--bs-primary-dark) !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar me-2"></i> Calendrier des congés
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="{{ route('conge.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i> Retour
                                    </a>
                                </div>
                                <div>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary" id="btn-month">Mois</button>
                                        <button type="button" class="btn btn-outline-primary" id="btn-week">Semaine</button>
                                        <button type="button" class="btn btn-outline-primary" id="btn-day">Jour</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales/fr.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            locale: 'fr',
            weekNumbers: true,
            navLinks: true,
            editable: false,
            dayMaxEvents: true,
            events: @json($evenements),
            eventClick: function(info) {
                if (info.event.url) {
                    window.open(info.event.url);
                    info.jsEvent.preventDefault(); // prevents browser from following link in current tab
                }
            },
            eventDidMount: function(info) {
                // Ajouter des tooltips aux événements
                var tooltip = new bootstrap.Tooltip(info.el, {
                    title: info.event.title,
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                });
            }
        });
        
        calendar.render();
        
        // Boutons pour changer de vue
        document.getElementById('btn-month').addEventListener('click', function() {
            calendar.changeView('dayGridMonth');
        });
        
        document.getElementById('btn-week').addEventListener('click', function() {
            calendar.changeView('timeGridWeek');
        });
        
        document.getElementById('btn-day').addEventListener('click', function() {
            calendar.changeView('timeGridDay');
        });
    });
</script>
@endsection
