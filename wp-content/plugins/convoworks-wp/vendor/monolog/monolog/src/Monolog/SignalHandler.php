<?php

declare (strict_types=1);
/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Convoworks\Monolog;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use ReflectionExtension;
/**
 * Monolog POSIX signal handler
 *
 * @author Robert Gust-Bardon <robert@gust-bardon.org>
 */
class SignalHandler
{
    private $logger;
    private $previousSignalHandler = [];
    private $signalLevelMap = [];
    private $signalRestartSyscalls = [];
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    public function registerSignalHandler($signo, $level = LogLevel::CRITICAL, bool $callPrevious = \true, bool $restartSyscalls = \true, ?bool $async = \true) : self
    {
        if (!\extension_loaded('pcntl') || !\function_exists('pcntl_signal')) {
            return $this;
        }
        if ($callPrevious) {
            $handler = \pcntl_signal_get_handler($signo);
            if ($handler === \false) {
                return $this;
            }
            $this->previousSignalHandler[$signo] = $handler;
        } else {
            unset($this->previousSignalHandler[$signo]);
        }
        $this->signalLevelMap[$signo] = $level;
        $this->signalRestartSyscalls[$signo] = $restartSyscalls;
        if ($async !== null) {
            \pcntl_async_signals($async);
        }
        \pcntl_signal($signo, [$this, 'handleSignal'], $restartSyscalls);
        return $this;
    }
    public function handleSignal($signo, array $siginfo = null) : void
    {
        static $signals = [];
        if (!$signals && \extension_loaded('pcntl')) {
            $pcntl = new ReflectionExtension('pcntl');
            // HHVM 3.24.2 returns an empty array.
            foreach ($pcntl->getConstants() ?: \get_defined_constants(\true)['Core'] as $name => $value) {
                if (\substr($name, 0, 3) === 'SIG' && $name[3] !== '_' && \is_int($value)) {
                    $signals[$value] = $name;
                }
            }
        }
        $level = $this->signalLevelMap[$signo] ?? LogLevel::CRITICAL;
        $signal = $signals[$signo] ?? $signo;
        $context = $siginfo ?? [];
        $this->logger->log($level, \sprintf('Program received signal %s', $signal), $context);
        if (!isset($this->previousSignalHandler[$signo])) {
            return;
        }
        if ($this->previousSignalHandler[$signo] === \true || $this->previousSignalHandler[$signo] === \SIG_DFL) {
            if (\extension_loaded('pcntl') && \function_exists('pcntl_signal') && \function_exists('pcntl_sigprocmask') && \function_exists('pcntl_signal_dispatch') && \extension_loaded('posix') && \function_exists('posix_getpid') && \function_exists('posix_kill')) {
                $restartSyscalls = $this->signalRestartSyscalls[$signo] ?? \true;
                \pcntl_signal($signo, \SIG_DFL, $restartSyscalls);
                \pcntl_sigprocmask(\SIG_UNBLOCK, [$signo], $oldset);
                \posix_kill(\posix_getpid(), $signo);
                \pcntl_signal_dispatch();
                \pcntl_sigprocmask(\SIG_SETMASK, $oldset);
                \pcntl_signal($signo, [$this, 'handleSignal'], $restartSyscalls);
            }
        } elseif (\is_callable($this->previousSignalHandler[$signo])) {
            $this->previousSignalHandler[$signo]($signo, $siginfo);
        }
    }
}
