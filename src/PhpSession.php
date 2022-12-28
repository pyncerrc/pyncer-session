<?php
namespace Pyncer\Session;

use Pyncer\Exception\UnexpectedValueException;
use Pyncer\Exception\RuntimeException;
use Pyncer\Session\AbstractSession;

use function count;
use function session_destroy;
use function session_start;
use function session_write_close;
use function strlen;
use function strpos;
use function substr;

class PhpSession extends AbstractSession
{
    private ?string $name;
    private ?string $id;
    private array $options;
    private ?int $idExpirationInterval;
    private ?int $currentIdExpirationInterval;

    public function __construct(
        ?string $name = null,
        array $options = [],
        ?int $idExpirationInterval = null,
    ) {
        $this->setName($name);
        $this->setOptions($options);
        $this->setId(null);
        $this->setIdExpirationInterval($idExpirationInterval);
        $this->currentIdExpirationInterval = null;
    }

    public function getName(): string
    {
        return $this->name ?? session_name();
    }
    public function setName(?string $value): static
    {
        if ($this->hasStarted()) {
            throw new RuntimeException('Session has already started.');
        }

        $this->name = ($value !== '' ? $value : null);

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }
    public function setId(?string $value): static
    {
        if ($this->hasStarted()) {
            throw new RuntimeException('Session has already started.');
        }

        $this->id = ($value !== '' ? $value : null);

        return $this;
    }

    public function getIdExpirationInterval(): ?int
    {
        return $this->idExpirationInterval;
    }
    public function setIdExpirationInterval(?int $value): static
    {
        if ($this->hasStarted()) {
            throw new RuntimeException('Session has already started.');
        }

        $this->idExpirationInterval = $value ?: null;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }
    public function setOptions(array $value): static
    {
        if ($this->hasStarted()) {
            throw new RuntimeException('Session has already started.');
        }

        $this->options = $this->cleanOptions($value);

        return $this;
    }

    public function start(): static
    {
        if (session_status() === PHP_SESSION_DISABLED) {
            throw new RuntimeException('PHP sessions are disabled');
        } elseif (session_status() === PHP_SESSION_ACTIVE) {
            throw new RuntimeException('PHP session has already started.');
        }

        session_name($this->getName());

        $id = $this->getId();
        if ($id !== null) {
            session_id($id);
        }

        session_start($this->getOptions());

        // Update session id if expired
        if ($this->getIdExpirationInterval() !== null) {
            $expiry = time() + $this->getIdExpirationInterval();

            $key = '@id_expiration_interval';

            $this->currentIdExpirationInterval = $_SESSION[$key] ?? $expiry;
            unset($_SESSION[$key]);

            if ($this->currentIdExpirationInterval < time() ||
                $this->currentIdExpirationInterval > $expiry
            ) {
                session_regenerate_id(true);

                $this->currentIdExpirationInterval = $expiry;
            }
        }

        $this->id = session_id();

        // Load data
        foreach ($_SESSION as $key => $values) {
            if ($key === '@id_expiration_interval') {
                continue;
            } elseif ($key === '@csrf') {
                $this->setCsrfToken($values);
            } else {
                $this->get($key)->setData($values);
            }

            unset($_SESSION[$key]);
        }

        $this->setStarted(true);

        return $this;
    }

    public function commit(): static
    {
        if (!$this->hasStarted()) {
            throw new RuntimeException('Session has not started.');
        }

        // Session was changed outside of this class so do nothing
        if (session_status() !== PHP_SESSION_ACTIVE ||
            session_name() !== $this->getName()
        ) {
            return $this;
        }

        foreach ($this->values as $key => $value) {
            if (!count($value)) {
                continue;
            }

            $_SESSION[$key] = $value->getData();
        }

        if ($this->currentIdExpirationInterval !== null) {
            $_SESSION['@id_expiration_interval'] = $this->currentIdExpirationInterval;
        }
        $_SESSION['@csrf'] = $this->getCsrfToken()->getValue();

        session_write_close();

        $this->setStarted(false);

        return $this;
    }

    public function destroy(): static
    {
        if (session_status() !== PHP_SESSION_ACTIVE ||
            session_name() !== $this->getName()
        ) {
            return $this;
        }

        $this->clear();
        session_destroy();
        $this->csrfToken = null;
        $this->setStarted(false);

        return $this;
    }

    private function cleanOptions(array $options): array
    {
        // Name is handled independently
        unset($options['name']);

        // We want to manually create the cookie header so that it can be
        // sent with a psr response
        $options['use_cookies'] = boolval($options['use_cookies'] ?? false);
        if ($options['use_cookies']) {
            throw new RuntimeException('session.use_cookies option must be false.');
        }

        // Ensure URL based sessions are disabled
        $options['use_only_cookies'] = boolval($options['use_only_cookies'] ?? true);
        if (!$options['use_only_cookies']) {
            throw new RuntimeException('session.use_only_cookies option must be true.');
        }

        $options['use_trans_sid'] = boolval($options['use_trans_sid'] ?? false);
        if ($options['use_trans_sid']) {
            throw new RuntimeException('session.use_trans_sid option must be false.');
        }

        return $options;
    }
}
