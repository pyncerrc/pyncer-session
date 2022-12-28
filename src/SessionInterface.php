<?php
namespace Pyncer\Session;

use DateTime;
use Pyncer\Utility\ParamsInterface;
use Pyncer\Utility\Token;

interface SessionInterface {
    public function start(): static;

    public function commit(): static;

    public function destroy(): static;

    public function clear(): static;

    public function hasStarted(): bool;

    public function get(string $name): ParamsInterface;

    public function set(string $name, array $values): static;

    public function getCsrfToken(): Token;
}
