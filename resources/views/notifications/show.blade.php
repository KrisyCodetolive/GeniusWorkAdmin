@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Détail de la notification</h4>
                    <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-secondary">
                        Retour à la liste
                    </a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="notification-details">
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2">{{ ucfirst($notification->type) }}</h5>
                            <p class="fs-5">{{ $notification->message }}</p>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p><strong>Date de création:</strong> {{ $notification->created_at->format('d/m/Y H:i') }}</p>
                                @if($notification->date_lecture)
                                    <p><strong>Date de lecture:</strong> {{ $notification->date_lecture->format('d/m/Y H:i') }}</p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <p>
                                    <strong>Statut:</strong> 
                                    @if($notification->estLue())
                                        <span class="badge bg-success">Lu</span>
                                    @else
                                        <span class="badge bg-warning">Non lu</span>
                                    @endif
                                </p>
                                <p>
                                    <strong>Canaux:</strong> 
                                    @if($notification->email_envoye)
                                        <span class="badge bg-success me-1">Email</span>
                                    @endif
                                    @if($notification->sms_envoye)
                                        <span class="badge bg-info me-1">SMS</span>
                                    @endif
                                    <span class="badge bg-primary">Application</span>
                                </p>
                            </div>
                        </div>

                        @if(!empty($notification->data))
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2">Informations supplémentaires</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <tbody>
                                            @foreach($notification->data as $key => $value)
                                                @if(!is_array($value) && !is_object($value))
                                                    <tr>
                                                        <th>{{ ucfirst(str_replace('_', ' ', $key)) }}</th>
                                                        <td>
                                                            @if($key == 'plage_horaire_id' && !empty($value))
                                                                <a href="{{ route('plages-horaires.show', $value) }}">
                                                                    Voir la plage horaire
                                                                </a>
                                                            @elseif($key == 'presence_id' && !empty($value))
                                                                <a href="{{ route('presences.show', $value) }}">
                                                                    Voir la présence
                                                                </a>
                                                            @elseif(in_array($key, ['heure_debut', 'heure_fin']) && !empty($value))
                                                                {{ \Carbon\Carbon::parse($value)->format('H:i') }}
                                                            @else
                                                                {{ $value }}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between mt-4">
                            @if(!$notification->estLue())
                                <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">Marquer comme lu</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
