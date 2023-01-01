<?php
namespace Pyncer\Session;

use Pyncer\Utility\ParamsInterface;
use Pyncer\Utility\Token;

/**
 * Describes a session instance.
 */
interface SessionInterface {
    /**
     * Starts the session.
     *
     * @return static
     */
    public function start(): static;

    /**
     * Ends the current session and commits any changes.
     *
     * @return static
     */
    public function commit(): static;

    /**
     * Destroys the current session and its data.
     *
     * @return static
     */
    public function destroy(): static;

    /**
     * Clears all session data.
     *
     * @return static
     */
    public function clear(): static;

    /**
     * Returns whether or not the session has started.
     *
     * @return bool
     */
    public function hasStarted(): bool;

    /**
     * Finds a session parameter group by its name and returns it.
     * If it does not exists, it will be created.
     *
     * @param string $name Name of the parameter group to return.
     * @return \Pyncer\Utility\ParamsInterface
     */
    public function get(string $name): ParamsInterface;

    /**
     * Finds a session parameter group by its name and replaces its values.
     * If it does not exists, it will be created.
     *
     * @param string $name Name of the parameter group to set.
     * @param array $values Array of values to set.
     * @return static
     */
    public function set(string $name, array $values): static;

    /**
     * Gets the current CSRF token for the session.
     * If it does not exists, it will be created.
     *
     * @return \Pyncer\Utility\Token
     */
    public function getCsrfToken(): Token;
}
