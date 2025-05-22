<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class QueryLogging
{
    public function handle(Request $request, Closure $next)
    {
        if (config('query-logger.enabled', true)) {
            DB::listen(function($query) {
                $sql = $query->sql;
                $bindings = $this->sanitizeBindings($query->bindings);
                $time = $query->time;

                // Remplacer les paramètres liés dans la requête
                foreach ($bindings as $binding) {
                    $value = is_numeric($binding) ? $binding : "'".$binding."'";
                    $sql = preg_replace('/\?/', $value, $sql, 1);
                }

                // Journaliser avec un format structuré
                Log::channel('queries')->debug('Requête SQL exécutée', [
                    'sql' => $sql,
                    'temps_execution' => $time,
                    'temps_execution_ms' => number_format($time, 2),
                    'url' => request()->fullUrl(),
                    'methode' => request()->method(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                // Alerter si la requête prend trop de temps
                if ($time > config('query-logger.slow_query_threshold', 1000)) {
                    Log::channel('queries')->warning('Requête SQL lente détectée', [
                        'sql' => $sql,
                        'temps_execution' => $time,
                        'temps_execution_ms' => number_format($time, 2),
                        'backtrace' => $this->getRelevantBacktrace(),
                    ]);
                }
            });
        }

        return $next($request);
    }

    protected function sanitizeBindings(array $bindings): array
    {
        $hiddenParams = config('query-logger.hidden_parameters', []);
        
        return array_map(function ($binding) use ($hiddenParams) {
            if (is_array($binding)) {
                return $this->sanitizeBindings($binding);
            }
            
            foreach ($hiddenParams as $param) {
                if (stripos($binding, $param) !== false) {
                    return '********';
                }
            }
            
            return $binding;
        }, $bindings);
    }

    protected function getRelevantBacktrace(): array
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $relevantTrace = [];

        foreach ($trace as $call) {
            if (isset($call['file']) && !str_contains($call['file'], 'vendor')) {
                $relevantTrace[] = [
                    'file' => $call['file'],
                    'line' => $call['line'] ?? '?',
                    'function' => $call['function'] ?? '?',
                    'class' => $call['class'] ?? '?',
                ];
            }
        }

        return array_slice($relevantTrace, 0, 5);
    }
}
