<?php

namespace SuperV\Platform\Domains\Resource\Action;

use SuperV\Platform\Support\Composer\Payload;

class ViewEntryAction extends Action
{
    protected $name = 'view';

    protected $title = 'View';

    protected $url;

    public function onComposed(Payload $payload)
    {
        $payload->set('url', $this->getUrl());
        $payload->set('button', [
            'title' => '',
//            'color' => 'primary inverse',
            'icon'  => 'view',
            'size'  => 'sm',
        ]);
    }

    public function getUrl()
    {
        return $this->url ?? 'sv/res/{res.handle}/{entry.id}/view-page';
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }
}