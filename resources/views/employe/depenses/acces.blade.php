@extends('layouts.portail-employe')

@section('title', 'Accès')

@section('content')
<div class="max-w-md mx-auto"
     x-data="{
        dept: @js(old('departement_id', '')),
        personne: @js(old('demandeur_id', '')),
        ids: @js($departements->pluck('id')),
        membres: @js($membres),
        installer: null,
        get liste() { return this.membres[this.dept] || []; },
        init() {
            if (!this.dept) {
                try { const memo = localStorage.getItem('depenses_departement'); if (memo && this.ids.includes(memo)) this.dept = memo; } catch (e) {}
            }
            this.restaurerPersonne();
            window.addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); this.installer = e; });
        },
        restaurerPersonne() {
            if (this.personne && this.liste.some((m) => m.id === this.personne)) return;
            this.personne = '';
            try { const memo = localStorage.getItem('depenses_personne_' + this.dept); if (memo && this.liste.some((m) => m.id === memo)) this.personne = memo; } catch (e) {}
        },
        choisir(id) { this.dept = id; try { localStorage.setItem('depenses_departement', id); } catch (e) {} this.restaurerPersonne(); },
        choisirPersonne() { try { localStorage.setItem('depenses_personne_' + this.dept, this.personne); } catch (e) {} },
     }">

    <div class="text-center mb-6">
        <div class="mx-auto h-14 w-14 rounded-2xl bg-[#eef2fa] flex items-center justify-center mb-3">
            <i data-lucide="banknote" class="h-7 w-7 text-[#3a5faa]"></i>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Demande d'argent</h1>
        <p class="text-slate-600 mt-1">Choisissez votre département puis votre nom.</p>
    </div>

    @if ($departements->isEmpty())
        <div class="card p-6 text-center text-slate-600">
            Aucun département n'est encore ouvert aux demandes. Contactez l'administration.
        </div>
    @else
        <form method="POST" action="{{ route('employe.depenses.acces.verifier') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="departement_id" :value="dept">

            <fieldset>
                <legend class="sr-only">Département</legend>
                <div class="grid grid-cols-2 gap-3 max-h-72 overflow-y-auto p-1 -m-1">
                    @foreach ($departements as $departement)
                        <button type="button" @click="choisir('{{ $departement->id }}')"
                                :aria-pressed="dept === '{{ $departement->id }}'"
                                :class="dept === '{{ $departement->id }}' ? 'border-[#3a5faa] bg-[#eef2fa] ring-2 ring-[#3a5faa]/30' : 'border-slate-200 bg-white hover:border-[#3a5faa]'"
                                class="text-left rounded-xl border px-4 py-3.5 transition-colors">
                            <span class="block font-semibold text-slate-900 leading-tight">{{ $departement->nom }}</span>
                            @if ($plusieursEntreprises)
                                <span class="block text-xs text-slate-500 mt-0.5">{{ $departement->entreprise?->nom }}</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </fieldset>

            <div x-show="dept" x-cloak x-transition>
                <label for="demandeur_id" class="block text-sm font-medium text-slate-800 mb-1.5">Votre nom</label>
                <select id="demandeur_id" name="demandeur_id" x-model="personne" @change="choisirPersonne()" required
                        class="w-full rounded-xl border-slate-300 focus:border-[#3a5faa] focus:ring-[#3a5faa]">
                    <option value="">Sélectionnez votre nom…</option>
                    <template x-for="membre in liste" :key="membre.id">
                        <option :value="membre.id" x-text="membre.nom" :selected="membre.id === personne"></option>
                    </template>
                </select>
            </div>

            <button type="submit" :disabled="!dept || !personne"
                    class="btn-primary w-full inline-flex justify-center items-center gap-2 px-6 py-3.5 rounded-xl text-white text-base font-semibold shadow-sm disabled:opacity-40 disabled:cursor-not-allowed">
                <i data-lucide="log-in" class="h-5 w-5"></i> Entrer
            </button>
        </form>
    @endif

    <button type="button" x-show="installer" x-cloak
            @click="installer.prompt(); installer = null"
            class="mt-6 w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium text-[#3a5faa] bg-[#eef2fa] hover:bg-[#dde5f4]">
        <i data-lucide="download" class="h-4 w-4"></i> Installer l'application
    </button>
</div>
@endsection
