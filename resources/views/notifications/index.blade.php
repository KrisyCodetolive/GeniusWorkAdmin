@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Mes notifications</h4>
                    <div>
                        @if($notifications->where('lu', false)->count() > 0)
                            <a href="{{ route('notifications.mark-all-read') }}" class="btn btn-sm btn-primary">
                                Marquer tout comme lu
                            </a>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($notifications->count() > 0)
                        <div class="list-group">
                            @foreach($notifications as $notification)
                                <a href="{{ route('notifications.show', $notification->id) }}" 
                                   class="list-group-item list-group-item-action {{ $notification->estLue() ? '' : 'fw-bold' }}">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">
                                            @if(!$notification->estLue())
                                                <span class="badge bg-primary me-2">Nouveau</span>
                                            @endif
                                            {{ ucfirst($notification->type) }}
                                        </h5>
                                        <small>{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-1">{{ $notification->message }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small>
                                            @if($notification->email_envoye)
                                                <span class="badge bg-success me-1">Email</span>
                                            @endif
                                            @if($notification->sms_envoye)
                                                <span class="badge bg-info me-1">SMS</span>
                                            @endif
                                        </small>
                                        <div>
                                            @if(!$notification->estLue())
                                                <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">Marquer comme lu</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')">
                                                    Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            {{ $notifications->links() }}
                        </div>
                    @else
                        <div class="alert alert-info">
                            Vous n'avez aucune notification.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
