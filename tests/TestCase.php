<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        if (! $this->app) {
            $this->refreshApplication();
        }

        $connection = \Illuminate\Support\Facades\DB::connection('sqlite');
        if ($connection->getDriverName() === 'sqlite') {
            $grammar = new class($connection) extends \Illuminate\Database\Schema\Grammars\SQLiteGrammar {
                protected function getDefaultValue($value)
                {
                    if ($value instanceof \Illuminate\Database\Query\Expression) {
                        $exprVal = $value->getValue($this);
                        if (str_contains($exprVal, 'gen_random_uuid')) {
                            return null;
                        }
                    }
                    if (is_string($value) && str_contains($value, 'gen_random_uuid')) {
                        return null;
                    }
                    return parent::getDefaultValue($value);
                }

                protected function modifyDefault(\Illuminate\Database\Schema\Blueprint $blueprint, \Illuminate\Support\Fluent $column)
                {
                    if (! is_null($column->default)) {
                        $val = $this->getDefaultValue($column->default);
                        if (is_null($val)) {
                            return '';
                        }
                        return ' default '.$val;
                    }
                }
            };
            $connection->setSchemaGrammar($grammar);
        }

        parent::setUp();
    }
}
