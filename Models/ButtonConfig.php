<?php

class ButtonConfig
{
    private $text;
    private $action;

    /**
     * ButtonConfig constructor.
     * @param string $text Text on the button.
     * @param string $action Action to be appended to the URL.
     */
    public function __construct(string $text, string $action)
    {
        $this->text = $text;
        $this->action = $action;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getAction(): string
    {
        return $this->action;
    }
}