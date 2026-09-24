<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Garde-fou : RefreshDatabase vide la base avant chaque test. On refuse donc de démarrer
     * sur une base qui n'est pas explicitement une base de test (nom en *_test ou SQLite en mémoire).
     */
    protected function setUpTraits()
    {
        $base = (string) config('database.connections.'.config('database.default').'.database');

        if ($base !== ':memory:' && ! str_ends_with($base, '_test')) {
            throw new RuntimeException("Tests interrompus : la base « {$base} » n'est pas une base de test. Vérifiez DB_DATABASE dans phpunit.xml.");
        }

        return parent::setUpTraits();
    }
}
