@props(['employeur'])

<div class="bg-white rounded-lg shadow overflow-hidden mt-4">
    <div class="p-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">QR Code</h2>
    </div>
    <div class="p-6">
        @if($employeur->qrcode_data)
            <div class="flex flex-col items-center">
                <div class="p-4 bg-white rounded shadow mb-3">
                    {!! $employeur->getQRCodeSVG() !!}
                </div>
                <div class="w-full">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600">Statut:</span>
                        @if($employeur->qrcode_active)
                            <span class="text-green-600">Actif</span>
                        @else
                            <span class="text-red-600">Inactif</span>
                        @endif
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600">Dernière génération:</span>
                        <span>{{ $employeur->qrcode_generated_at ? $employeur->qrcode_generated_at->format('d/m/Y H:i') : 'Jamais' }}</span>
                    </div>
                    <div class="flex space-x-2 mt-3 justify-center">
                        <form action="{{ route('admin.employeurs.regenerate-qrcode', $employeur->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm py-1 px-3 rounded">
                                <i class="fas fa-sync-alt mr-1"></i> Régénérer
                            </button>
                        </form>
                        
                        <form action="{{ route('admin.employeurs.deactivate-qrcode', $employeur->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm py-1 px-3 rounded">
                                <i class="fas fa-ban mr-1"></i> Désactiver
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <p class="text-gray-600 text-center">Aucun QR code n'a été généré pour cet employeur.</p>
            <div class="flex justify-center mt-3">
                <form action="{{ route('admin.employeurs.regenerate-qrcode', $employeur->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm py-1 px-3 rounded">
                        <i class="fas fa-qrcode mr-1"></i> Générer un QR code
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
