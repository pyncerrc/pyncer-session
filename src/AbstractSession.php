<?php
namespace Pyncer\Session;

use Pyncer\Session\SessionInterface;
use Pyncer\Utility\Params;
use Pyncer\Utility\ParamsInterface;
use Pyncer\Utility\Token;

/**
 * This is a partial session implementation that other sessions
 * can inherit from.
 *
 * It simply handles the getting and setting of data and CSRF token.
 */
abstract class AbstractSession implements SessionInterface
{

    /**
     * Array of \Pyncer\Utility\ParamsInterface to store session data.
     */
    protected array $values = [];

    private bool $hasStarted = false;
    private ?Token $csrfToken = null;

    /**
     * {@inheritdoc}
     */
    public function hasStarted(): bool
    {
        return $this->hasStarted;
    }

    /**
     * Sets whether or not the session has started.
     *
     * @param bool $value The current session start state.
     * @return static
     */
    protected function setStarted(bool $value): static
    {
        $this->hasStarted = $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): static
    {
        foreach ($this->values as $value) {
            $value->clearData();
        }
        $this->values = [];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name): ParamsInterface
    {
        if (!isset($this->values[$name])) {
            $this->values[$name] = new Params();
        }

        return $this->values[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $name, array $values): static
    {
        $this->values[$name] = new Params($values);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCsrfToken(): Token
    {
        if ($this->csrfToken === null) {
            $this->csrfToken = new Token();
        }

        return $this->csrfToken;
    }

    /**
     * Sets the current CSRF token value.
     *
     * @param null|string $value The CSRF token value to set.
     * @return static
     */
    protected function setCsrfToken(?string $value): static
    {
        $this->csrfToken = new Token($value);
        return $this;
    }
}
