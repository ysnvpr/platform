<?php

namespace SuperV\Platform\Domains\Task\Event;

use Illuminate\Broadcasting\Channel;
use SuperV\Platform\Domains\Task\Model\TaskModel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TaskOutputEvent implements ShouldBroadcast
{
    /**
     * @var TaskModel
     */
    public $model;

    /**
     * @var
     */
    public $buffer;

    public function __construct(TaskModel $model, $buffer)
    {
        $this->model = $model;
        $this->buffer = $buffer;
    }

    public function broadcastOn()
    {
        return new Channel('Tasks');
    }

    public function broadcastAs()
    {
        return 'task.output';
    }
}
