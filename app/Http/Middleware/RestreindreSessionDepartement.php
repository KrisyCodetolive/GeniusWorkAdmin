<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Le compte technique d'un département (ouvert par le simple choix d'un département et d'un nom) ne sert qu'au portail des dépenses :
 * partout ailleurs (back-office, pointage…) elle est renvoyée vers le portail. Les fichiers des demandes
 * (/depenses/justificatifs…, bon de sortie) restent accessibles : le portail y renvoie des liens.
 */
class RestreindreSessionDepartement
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->estCompteDepartement()
            && ! $request->is('depense', 'depense/*', 'depenses/*')) {
            return redirect()->route('employe.depenses.index');
        }

        return $next($request);
    }
}
