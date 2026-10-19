{{-- Progression d'une demande : étapes franchies, étape en cours. $demande requis, $compact optionnel. --}}
@php
    $etapes = \App\Models\DemandeDepense::ETAPES_SUIVI;
    $courante = $demande->etapeSuivi();
    $compact = $compact ?? false;
@endphp
@if ($courante !== null)
    <ol class="flex items-start" aria-label="Progression de la demande">
        @foreach ($etapes as $i => $etape)
            @php $fait = $i < $courante; $enCours = $i === $courante; @endphp
            <li class="flex-1 flex flex-col items-center text-center relative" @if ($enCours) aria-current="step" @endif>
                @if (! $loop->first)
                    <span class="absolute {{ $compact ? 'top-2.5' : 'top-4' }} right-1/2 w-full h-0.5 {{ $i <= $courante ? 'bg-[#3a5faa]' : 'bg-slate-200' }}"></span>
                @endif
                <span class="relative z-10 flex {{ $compact ? 'h-5 w-5' : 'h-8 w-8' }} items-center justify-center rounded-full text-xs font-semibold
                    {{ $fait ? 'bg-[#3a5faa] text-white' : ($enCours ? 'bg-white text-[#3a5faa] border-2 border-[#3a5faa]' : 'bg-slate-100 text-slate-400') }}">
                    @if ($fait)
                        <i data-lucide="check" class="{{ $compact ? 'h-3 w-3' : 'h-4 w-4' }}"></i>
                    @elseif (! $compact)
                        {{ $i + 1 }}
                    @endif
                </span>
                <span class="mt-1 text-[10px] sm:text-xs leading-tight {{ $enCours ? 'font-semibold text-slate-900' : 'text-slate-500' }}">{{ $etape }}</span>
            </li>
        @endforeach
    </ol>
@endif
