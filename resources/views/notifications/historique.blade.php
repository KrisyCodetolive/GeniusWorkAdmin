@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Historique des notifications</h4>
                    <div>
                        <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-bell"></i> Notifications
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Message</th>
                                    <th>Statut</th>
                                    <th>Canaux</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($notifications as $notification)
                                    <tr class="{{ $notification->lu ? '' : 'table-primary' }}">
                                        <td>{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $notification->getBadgeColor() }}">
                                                {{ $notification->type_notification }}
                                            </span>
                                        </td>
                                        <td>{{ Str::limit($notification->message, 100) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $notification->getStatusColor() }}">
                                                {{ $notification->status }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $canaux = json_decode($notification->canaux_envoyes, true) ?: [];
                                            @endphp
                                            @foreach ($canaux as $canal)
                                                <span class="badge bg-info">{{ $canal }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('notifications.show', $notification->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if (!$notification->lu)
                                                    <a href="{{ route('notifications.mark-as-read', $notification->id) }}" class="btn btn-sm btn-success">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                @endif
                                                <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette notification?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Aucune notification trouvée</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            Affichage de {{ count($notifications) }} notification(s) sur {{ $total }}
                        </div>
                        <div>
                            <nav aria-label="Pagination des notifications">
                                <ul class="pagination">
                                    @if ($page > 1)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ route('notifications.historique', ['page' => $page - 1, 'limit' => $limit]) }}">Précédent</a>
                                        </li>
                                    @endif
                                    
                                    @if (count($notifications) == $limit)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ route('notifications.historique', ['page' => $page + 1, 'limit' => $limit]) }}">Suivant</a>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
