<?php

namespace SuperV\Platform\Domains\Resource\Hook;

use SuperV\Platform\Contracts\Dispatcher;
use SuperV\Platform\Domains\Resource\Hook\Contracts\HookHandler as HookContract;
use SuperV\Platform\Domains\Resource\Hook\Contracts\ListConfigHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\ListDataHook;
use SuperV\Platform\Domains\Resource\Hook\Contracts\ListResolvedHook;

class ListsHookHandler implements HookContract
{
    /**
     * @var \SuperV\Platform\Contracts\Dispatcher
     */
    protected $dispatcher;

    protected $map = [
        'resolved' => ListResolvedHook::class,
        'config'   => ListConfigHook::class,
        'data'     => ListDataHook::class,
    ];

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function hook(string $identifier, string $hookHandler, string $subKey = null)
    {
        $implements = class_implements($hookHandler);

        foreach ($this->map as $eventType => $contract) {
            if (! in_array($contract, $implements)) {
                continue;
            }
            $eventName = sprintf("%s.lists:%s.events:%s", $identifier, $subKey, $eventType);
            $this->dispatcher->listen(
                $eventName,
                function () use ($eventType, $hookHandler) {
                    $this->handle($hookHandler, $eventType, func_get_args());
                }
            );
        }
    }

    protected function handle($hookHandler, $eventType, $payload)
    {
        if (is_string($hookHandler)) {
            $hookHandler = app($hookHandler);
        }

        $hookHandler->{$eventType}(...$payload);
    }
}