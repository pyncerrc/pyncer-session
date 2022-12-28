<?php
namespace Pyncer\Session;

use Pyncer\Session\SessionInterface;
use Pyncer\Utility\Params;
use Pyncer\Utility\ParamsInterface;
use Pyncer\Utility\Token;

abstract class AbstractSession implements SessionInterface
{
    protected array $values = [];
    private bool $hasStarted = false;
    private ?Token $csrfToken = null;

    public function hasStarted(): bool
    {
        return $this->hasStarted;
    }
    protected function setStarted(bool $value): static
    {
        $this->hasStarted = $value;
        return $this;
    }

    public function clear(): static
    {
        foreach ($this->values as $value) {
            $value->clearData();
        }
        $this->values = [];

        return $this;
    }

    public function get(string $name): ParamsInterface
    {
        if (!isset($this->values[$name])) {
            $this->values[$name] = new Params();
        }

        return $this->values[$name];
    }
    public function set(string $name, array $values): static
    {
        $this->values[$name] = new Params($values);

        return $this;
    }

    public function getCsrfToken(): Token
    {
        if ($this->csrfToken === null) {
            $this->csrfToken = new Token();
        }

        return $this->csrfToken;
    }
    protected function setCsrfToken(?string $value): static
    {
        $this->csrfToken = new Token($value);
        return $this;
    }
}
